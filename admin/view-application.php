<?php
include '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: applications.php');
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM farmer_applications WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    header('Location: applications.php');
    exit;
}

$application = $result->fetch_assoc();

// Function to display status badge
function getStatusBadge($status) {
    $statusClasses = [
        'Pending' => 'bg-secondary',
        'Reviewed' => 'bg-info',
        'Approved' => 'bg-success',
        'Rejected' => 'bg-danger'
    ];
    return '<span class="badge '.$statusClasses[$status].'">'.$status.'</span>';
}
?>

<div class="container py-4">
    <!-- Header with back button and status -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="applications.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Applications
            </a>
        </div>
        <div>
            <?= getStatusBadge($application['status']) ?>
        </div>
    </div>

    <!-- Main card -->
    <div class="card border-0 shadow-sm">
        <!-- Card header with applicant info -->
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h5 mb-1"><?= htmlspecialchars($application['full_name']) ?></h2>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <?php if ($application['woman_in_agri'] === 'Yes'): ?>
                        <span class="badge bg-purple">
                            <i class="bi bi-gender-female me-1"></i> Woman in Agriculture
                        </span>
                    <?php endif; ?>
                    <?php if ($application['is_youth'] === 'Yes'): ?>
                        <span class="badge bg-primary">Youth (18-35)</span>
                    <?php endif; ?>
                    <span class="badge bg-dark">
                        <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($application['state']) ?>
                    </span>
                </div>
            </div>
            <div class="text-end">
                <small class="text-muted">Applied: <?= date('M j, Y', strtotime($application['submission_date'])) ?></small>
            </div>
        </div>

        <!-- Card body with tabs navigation -->
        <div class="card-body">
            <ul class="nav nav-tabs mb-4" id="applicationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                        <i class="bi bi-person me-1"></i> Profile
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="farming-tab" data-bs-toggle="tab" data-bs-target="#farming" type="button" role="tab">
                        <i class="bi bi-tree me-1"></i> Farming
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="training-tab" data-bs-toggle="tab" data-bs-target="#training" type="button" role="tab">
                        <i class="bi bi-book me-1"></i> Training
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="grant-tab" data-bs-toggle="tab" data-bs-target="#grant" type="button" role="tab">
                        <i class="bi bi-cash-coin me-1"></i> Grant
                    </button>
                </li>
            </ul>

            <!-- Tab content -->
            <div class="tab-content" id="applicationTabsContent">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4 border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-primary mb-3">
                                        <i class="bi bi-person-lines-fill me-2"></i> Personal Details
                                    </h5>
                                    <div class="row">
                                        <div class="col-6 mb-2">
                                            <small class="text-muted">Gender</small>
                                            <p><?= htmlspecialchars($application['gender']) ?></p>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <small class="text-muted">Date of Birth</small>
                                            <p><?= date('d/m/Y', strtotime($application['dob'])) ?></p>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <small class="text-muted">Phone</small>
                                            <p><?= htmlspecialchars($application['phone']) ?></p>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <small class="text-muted">Email</small>
                                            <p><?= $application['email'] ? htmlspecialchars($application['email']) : 'Not provided' ?></p>
                                        </div>
                                        <div class="col-12 mb-2">
                                            <small class="text-muted">Address</small>
                                            <p><?= htmlspecialchars($application['address']) ?></p>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <small class="text-muted">State</small>
                                            <p><?= htmlspecialchars($application['state']) ?></p>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <small class="text-muted">LGA</small>
                                            <p><?= htmlspecialchars($application['lga']) ?></p>
                                        </div>
                                        <div class="col-12 mb-2">
                                            <small class="text-muted">Nationality</small>
                                            <p><?= htmlspecialchars($application['nationality']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4 border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-primary mb-3">
                                        <i class="bi bi-check-circle me-2"></i> Declaration
                                    </h5>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <?php if ($application['agree_training'] === 'Yes'): ?>
                                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                            <?php else: ?>
                                                <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="mb-0">Agrees to participate in training</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <?php if ($application['agree_info'] === 'Yes'): ?>
                                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                            <?php else: ?>
                                                <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="mb-0">Information is accurate</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-primary mb-3">
                                        <i class="bi bi-phone me-2"></i> Technology Access
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <?php if ($application['has_smartphone'] === 'Yes'): ?>
                                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                            <?php else: ?>
                                                <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="mb-0">Has access to smartphone/internet device</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Farming Tab -->
                <div class="tab-pane fade" id="farming" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4 border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-primary mb-3">
                                        <i class="bi bi-grid me-2"></i> Farming Activities
                                    </h5>
                                    <div class="mb-3">
                                        <?php
                                        $activities = explode(', ', $application['activities']);
                                        foreach ($activities as $activity): ?>
                                            <span class="badge bg-success mb-1"><?= $activity ?></span>
                                        <?php endforeach; ?>
                                        <?php if ($application['other_activity']): ?>
                                            <span class="badge bg-success mb-1"><?= htmlspecialchars($application['other_activity']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Years in Business</small>
                                        <p><?= htmlspecialchars($application['years_in_business']) ?></p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Farming Type</small>
                                        <p><?= htmlspecialchars($application['farming_type']) ?>
                                            <?php if ($application['coop_name']): ?>
                                                <br><small>(<?= htmlspecialchars($application['coop_name']) ?>)</small>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-primary mb-3">
                                        <i class="bi bi-file-text me-2"></i> Business Description
                                    </h5>
                                    <div class="bg-white p-3 rounded border">
                                        <?= nl2br(htmlspecialchars($application['business_description'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Training Tab -->
                <div class="tab-pane fade" id="training" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4 border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-primary mb-3">
                                        <i class="bi bi-bookmark-check me-2"></i> Training Interests
                                    </h5>
                                    <div class="mb-3">
                                        <?php
                                        $trainings = explode(', ', $application['trainings_interested']);
                                        foreach ($trainings as $training): ?>
                                            <span class="badge bg-info text-dark mb-1"><?= $training ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Expected Benefits</small>
                                        <div class="bg-white p-3 rounded border">
                                            <?= nl2br(htmlspecialchars($application['training_benefit'])) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-primary mb-3">
                                        <i class="bi bi-exclamation-triangle me-2"></i> Challenges Faced
                                    </h5>
                                    <div class="mb-3">
                                        <?php
                                        $challenges = explode(', ', $application['challenges']);
                                        foreach ($challenges as $challenge): ?>
                                            <span class="badge bg-warning text-dark mb-1"><?= $challenge ?></span>
                                        <?php endforeach; ?>
                                        <?php if ($application['other_challenge']): ?>
                                            <span class="badge bg-warning text-dark mb-1"><?= htmlspecialchars($application['other_challenge']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grant Tab -->
                <div class="tab-pane fade" id="grant" role="tabpanel">
                    <?php if ($application['business_idea']): ?>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-4 border-0 bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary mb-3">
                                            <i class="bi bi-lightbulb me-2"></i> Business Idea
                                        </h5>
                                        <div class="bg-white p-3 rounded border">
                                            <?= nl2br(htmlspecialchars($application['business_idea'])) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card mb-4 border-0 bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary mb-3">
                                            <i class="bi bi-cash-stack me-2"></i> Funding Request
                                        </h5>
                                        <div class="text-center">
                                            <div class="display-5 text-success mb-2">
                                                ₦<?= number_format($application['funding_amount'], 2) ?>
                                            </div>
                                            <p>Amount needed to scale business</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary mb-3">
                                            <i class="bi bi-piggy-bank me-2"></i> Previous Grants
                                        </h5>
                                        <div class="mb-3">
                                            <small class="text-muted">Received grant before?</small>
                                            <p><?= htmlspecialchars($application['received_grant']) ?></p>
                                        </div>
                                        <?php if ($application['grant_details']): ?>
                                            <div>
                                                <small class="text-muted">Details</small>
                                                <p><?= htmlspecialchars($application['grant_details']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> This applicant did not apply for the grant competition.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Status update section -->
        <div class="card-footer bg-light">
            <form method="post" action="update_status.php" class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <label class="me-3 mb-0">Update Status:</label>
                        <select class="form-select" name="status" style="max-width: 200px;">
                            <option value="Pending" <?= $application['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Reviewed" <?= $application['status'] === 'Reviewed' ? 'selected' : '' ?>>Reviewed</option>
                            <option value="Approved" <?= $application['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="Rejected" <?= $application['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <input type="hidden" name="id" value="<?= $application['id'] ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>

    .nav-tabs .nav-link {
        border: none;
        padding: 12px 20px;
        color: #495057 !important;
        font-weight: 500;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        background-color: transparent;
        border-bottom: 3px solid #0d6efd;
    }
    .card {
        border-radius: 10px;
    }
    .badge {
        font-weight: 500;
        padding: 6px 10px;
    }
</style>

<?php include '../includes/footer.php'; ?>