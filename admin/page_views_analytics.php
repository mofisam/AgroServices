<?php
session_start();
include '../config/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login");
    exit();
}

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page - 1) * $per_page : 0;

// Get analytics data
$page_stats = [];
$location_stats = [];
$device_stats = [];

// Get total views
$total_views = $conn->query("SELECT SUM(view_count) as total FROM page_views")->fetch_assoc()['total'];

// Get visitor statistics for the last 7 days
$visitor_stats_7days = $conn->query("
    SELECT 
        DATE(created_at) as date,
        COUNT(DISTINCT ip_address) as unique_visitors,
        SUM(view_count) as total_views
    FROM page_views
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC
")->fetch_all(MYSQLI_ASSOC);

// Get today's visitor count
$today_stats = $conn->query("
    SELECT 
        COUNT(DISTINCT ip_address) as unique_visitors,
        SUM(view_count) as total_views
    FROM page_views
    WHERE DATE(created_at) = CURDATE()
")->fetch_assoc();

// Get visitor data for the graph (last 30 days)
$visitor_graph_data = $conn->query("
    SELECT 
        DATE(created_at) as date,
        COUNT(DISTINCT ip_address) as unique_visitors
    FROM page_views
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
")->fetch_all(MYSQLI_ASSOC);

// Prepare data for the chart
$chart_labels = [];
$chart_data = [];
foreach ($visitor_graph_data as $row) {
    $chart_labels[] = date('M j', strtotime($row['date']));
    $chart_data[] = $row['unique_visitors'];
}

// Get page view statistics with pagination
$page_result = $conn->query("
    SELECT page_url, SUM(view_count) as views, COUNT(DISTINCT ip_address) as visitors
    FROM page_views 
    GROUP BY page_url 
    ORDER BY views DESC
    LIMIT $start, $per_page
");
while ($row = $page_result->fetch_assoc()) {
    $page_stats[] = $row;
}

// Get total pages for pagination
$total_pages = $conn->query("SELECT COUNT(DISTINCT page_url) as total FROM page_views")->fetch_assoc()['total'];
$total_pages = ceil($total_pages / $per_page);

// Get location statistics with pagination
$location_result = $conn->query("
    SELECT country, region, city, COUNT(DISTINCT ip_address) as visitors
    FROM page_views 
    GROUP BY country, region, city 
    ORDER BY visitors DESC
    LIMIT $start, $per_page
");
while ($row = $location_result->fetch_assoc()) {
    $location_stats[] = $row;
}

// Get device statistics
$device_result = $conn->query("
    SELECT device_type, COUNT(DISTINCT ip_address) as visitors
    FROM page_views 
    GROUP BY device_type 
    ORDER BY visitors DESC
");
while ($row = $device_result->fetch_assoc()) {
    $device_stats[] = $row;
}

include '../includes/header.php';
?>

<div class="container py-4">
    <h2 class="mb-4">Website Analytics</h2>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Page Views</h5>
                    <h2 class="card-text"><?= number_format($total_views) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Today's Visitors</h5>
                    <h2 class="card-text"><?= number_format($today_stats['unique_visitors'] ?? 0) ?></h2>
                    <small class="d-block"><?= number_format($today_stats['total_views'] ?? 0) ?> views</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">7-Day Visitors</h5>
                    <h2 class="card-text"><?= number_format(array_sum(array_column($visitor_stats_7days, 'unique_visitors'))) ?></h2>
                    <small class="d-block"><?= number_format(array_sum(array_column($visitor_stats_7days, 'total_views'))) ?> views</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark">
                <div class="card-body">
                    <h5 class="card-title">Unique Pages</h5>
                    <h2 class="card-text"><?= number_format(count($page_stats)) ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Visitor Graph -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Unique Visitors (Last 30 Days)</h4>
        </div>
        <div class="card-body">
            <canvas id="visitorChart" height="100"></canvas>
        </div>
    </div>
    
    <!-- 7-Day Visitor Details -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Recent Visitor Statistics (Last 7 Days)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Unique Visitors</th>
                            <th>Total Views</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visitor_stats_7days as $day): ?>
                        <tr>
                            <td><?= date('D, M j, Y', strtotime($day['date'])) ?></td>
                            <td><?= number_format($day['unique_visitors']) ?></td>
                            <td><?= number_format($day['total_views']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Page Views Table -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Page View Statistics</h4>
            <div>
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Page URL</th>
                            <th>Total Views</th>
                            <th>Unique Visitors</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($page_stats as $page): ?>
                        <tr>
                            <td><?= htmlspecialchars($page['page_url']) ?></td>
                            <td><?= number_format($page['views']) ?></td>
                            <td><?= number_format($page['visitors']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Location Statistics -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Visitor Locations</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Country</th>
                                    <th>Region</th>
                                    <th>City</th>
                                    <th>Visitors</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($location_stats as $location): ?>
                                <tr>
                                    <td><?= htmlspecialchars($location['country']) ?></td>
                                    <td><?= htmlspecialchars($location['region']) ?></td>
                                    <td><?= htmlspecialchars($location['city']) ?></td>
                                    <td><?= number_format($location['visitors']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Device Statistics -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Device Types</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Device Type</th>
                                    <th>Visitors</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($device_stats as $device): ?>
                                <tr>
                                    <td><?= htmlspecialchars($device['device_type']) ?></td>
                                    <td><?= number_format($device['visitors']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Visitor Chart
const ctx = document.getElementById('visitorChart').getContext('2d');
const visitorChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Unique Visitors',
            data: <?= json_encode($chart_data) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>