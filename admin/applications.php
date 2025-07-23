<?php
include '../config/db.php';
include '../includes/header.php';

// Check if admin is logged in (you should implement proper authentication)
?>

<div class="container mt-5">
    <h2>Farmer Applications</h2>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>State</th>
                    <th>Activities</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT id, full_name, phone, state, activities, status, submission_date 
                        FROM farmer_applications 
                        ORDER BY submission_date DESC";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['full_name']}</td>
                            <td>{$row['phone']}</td>
                            <td>{$row['state']}</td>
                            <td>{$row['activities']}</td>
                            <td><span class='badge bg-".getStatusColor($row['status'])."'>{$row['status']}</span></td>
                            <td>".date('d M Y', strtotime($row['submission_date']))."</td>
                            <td>
                                <a href='view-application.php?id={$row['id']}' class='btn btn-sm btn-primary'>View</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No applications found</td></tr>";
                }
                
                function getStatusColor($status) {
                    switch($status) {
                        case 'Approved': return 'success';
                        case 'Rejected': return 'danger';
                        case 'Reviewed': return 'warning';
                        default: return 'secondary';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>