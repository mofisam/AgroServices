<?php
session_start();
include '../../config/db.php';

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../login");
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build SQL Query with Filters
$query = "SELECT u.id, u.first_name, u.last_name, u.email, u.role, u.created_at, 
                 u.profile_picture, u.status, 
                 COUNT(o.id) as order_count
          FROM users u
          LEFT JOIN orders o ON o.buyer_id = u.id
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
}
if (!empty($role_filter)) {
    $query .= " AND u.role = ?";
}
if (!empty($status_filter)) {
    $query .= " AND u.status = ?";
}
$query .= " GROUP BY u.id ORDER BY u.created_at DESC LIMIT ? OFFSET ?";

// Prepare statement
$stmt = $conn->prepare($query);

if (!empty($search) && !empty($role_filter) && !empty($status_filter)) {
    $search_param = "%$search%";
    $stmt->bind_param("sssssii", $search_param, $search_param, $search_param, $role_filter, $status_filter, $limit, $offset);
} elseif (!empty($search) && !empty($role_filter)) {
    $search_param = "%$search%";
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $role_filter, $limit, $offset);
} elseif (!empty($search) && !empty($status_filter)) {
    $search_param = "%$search%";
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $status_filter, $limit, $offset);
} elseif (!empty($role_filter) && !empty($status_filter)) {
    $stmt->bind_param("ssii", $role_filter, $status_filter, $limit, $offset);
} elseif (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("sssi", $search_param, $search_param, $search_param, $limit, $offset);
} elseif (!empty($role_filter)) {
    $stmt->bind_param("sii", $role_filter, $limit, $offset);
} elseif (!empty($status_filter)) {
    $stmt->bind_param("sii", $status_filter, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Get total users count for pagination
$total_query = "SELECT COUNT(*) as total FROM users u WHERE 1=1";
if (!empty($search)) {
    $total_query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
}
if (!empty($role_filter)) {
    $total_query .= " AND u.role = ?";
}
if (!empty($status_filter)) {
    $total_query .= " AND u.status = ?";
}
$total_stmt = $conn->prepare($total_query);
if (!empty($search) && !empty($role_filter) && !empty($status_filter)) {
    $total_stmt->bind_param("sssss", $search_param, $search_param, $search_param, $role_filter, $status_filter);
} elseif (!empty($search) && !empty($role_filter)) {
    $total_stmt->bind_param("ssss", $search_param, $search_param, $search_param, $role_filter);
} elseif (!empty($search) && !empty($status_filter)) {
    $total_stmt->bind_param("ssss", $search_param, $search_param, $search_param, $status_filter);
} elseif (!empty($role_filter) && !empty($status_filter)) {
    $total_stmt->bind_param("ss", $role_filter, $status_filter);
} elseif (!empty($search)) {
    $total_stmt->bind_param("sss", $search_param, $search_param, $search_param);
} elseif (!empty($role_filter)) {
    $total_stmt->bind_param("s", $role_filter);
} elseif (!empty($status_filter)) {
    $total_stmt->bind_param("s", $status_filter);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_users = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

include '../../includes/header.php';
?>

<body class="bg-light">
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class=" shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">User Management</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle me-1"></i> Add User
                    </button>
                </div>
                <br>
                <div class="card-body">
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <input type="text" name="search" placeholder="Search by name or email" 
                                   value="<?= htmlspecialchars($search) ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <select name="role" class="form-select">
                                <option value="">All Roles</option>
                                <option value="buyer" <?= $role_filter == 'buyer' ? 'selected' : '' ?>>Buyer</option>
                                <option value="seller" <?= $role_filter == 'seller' ? 'selected' : '' ?>>Seller</option>
                                <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="suspended" <?= $status_filter == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Contact</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Activity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($user = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $user['profile_picture'] ? '../../uploads/profile_pics/'.$user['profile_picture'] : '../../assets/images/default-user.png' ?>" 
                                                         class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($user["first_name"] . " " . $user["last_name"]) ?></h6>
                                                        <small class="text-muted">Joined: <?= date('M j, Y', strtotime($user["created_at"])) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?= htmlspecialchars($user["email"]) ?></div>
                                                <?php if($user['role'] == 'seller'): ?>
                                                    <small class="text-muted"><?= $user['order_count'] ?> orders</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'seller' ? 'info' : 'primary') ?>">
                                                    <?= htmlspecialchars(ucfirst($user["role"])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'secondary' ?>">
                                                    <?= htmlspecialchars(ucfirst($user["status"])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= $user['order_count'] ?> orders</small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary view-user" 
                                                            data-id="<?= $user['id'] ?>" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#userModal">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning edit-user" 
                                                            data-id="<?= $user['id'] ?>" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editUserModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php if($user['status'] == 'active'): ?>
                                                        <a href="admin_suspend_user.php?id=<?= $user["id"] ?>" 
                                                           class="btn btn-sm btn-outline-danger" 
                                                           onclick="return confirm('Are you sure you want to suspend this user?')">
                                                            <i class="bi bi-lock"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="admin_activate_user.php?id=<?= $user["id"] ?>" 
                                                           class="btn btn-sm btn-outline-success" 
                                                           onclick="return confirm('Are you sure you want to activate this user?')">
                                                            <i class="bi bi-unlock"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-people display-5 text-muted mb-3"></i>
                                            <h5>No users found</h5>
                                            <p class="text-muted">Try adjusting your search filters</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <nav class="d-flex justify-content-center mt-4">
                            <ul class="pagination">
                                <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= htmlspecialchars($search) ?>&role=<?= htmlspecialchars($role_filter) ?>&status=<?= htmlspecialchars($status_filter) ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = max(1, $page-2); $i <= min($page+2, $total_pages); $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>&role=<?= htmlspecialchars($role_filter) ?>&status=<?= htmlspecialchars($status_filter) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= htmlspecialchars($search) ?>&role=<?= htmlspecialchars($role_filter) ?>&status=<?= htmlspecialchars($status_filter) ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" id="editFirstName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" id="editLastName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="editEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select id="editRole" class="form-select">
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="editStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php" ?>

<script>
$(document).ready(function() {
    // View User Details
    $(document).on("click", ".view-user", function() {
        var userId = $(this).data("id");
        $.ajax({
            url: "admin_view_user.php",
            method: "GET",
            data: { id: userId },
            beforeSend: function() {
                $('#modal-body').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            },
            success: function(data) {
                $("#modal-body").html(data);
            }
        });
    });

    // Edit User - Load Data
    $(document).on("click", ".edit-user", function() {
        var userId = $(this).data("id");
        $.ajax({
            url: "admin_fetch_user.php",
            method: "GET",
            data: { id: userId },
            success: function(data) {
                var user = JSON.parse(data);
                $("#editUserId").val(user.id);
                $("#editFirstName").val(user.first_name);
                $("#editLastName").val(user.last_name);
                $("#editEmail").val(user.email);
                $("#editRole").val(user.role);
                $("#editStatus").val(user.status);
            }
        });
    });

    // Edit User - Submit Form
    $("#editUserForm").submit(function(e) {
        e.preventDefault();
        var formData = {
            id: $("#editUserId").val(),
            first_name: $("#editFirstName").val(),
            last_name: $("#editLastName").val(),
            email: $("#editEmail").val(),
            role: $("#editRole").val(),
            status: $("#editStatus").val()
        };

        $.ajax({
            url: "admin_update_user.php",
            method: "POST",
            data: formData,
            beforeSend: function() {
                $('#editUserForm button[type="submit"]').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...').prop('disabled', true);
            },
            success: function(response) {
                $('#editUserForm button[type="submit"]').html('<i class="bi bi-check-circle me-1"></i> Save Changes').prop('disabled', false);
                $('#editUserModal').modal('hide');
                showAlert('User updated successfully!', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function() {
                $('#editUserForm button[type="submit"]').html('<i class="bi bi-check-circle me-1"></i> Save Changes').prop('disabled', false);
                showAlert('Error updating user. Please try again.', 'danger');
            }
        });
    });

    // Add New User
    $("#addUserForm").submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: "admin_add_user.php",
            method: "POST",
            data: formData,
            beforeSend: function() {
                $('#addUserForm button[type="submit"]').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...').prop('disabled', true);
            },
            success: function(response) {
                $('#addUserForm button[type="submit"]').html('<i class="bi bi-plus-circle me-1"></i> Create User').prop('disabled', false);
                $('#addUserModal').modal('hide');
                showAlert('User created successfully!', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function() {
                $('#addUserForm button[type="submit"]').html('<i class="bi bi-plus-circle me-1"></i> Create User').prop('disabled', false);
                showAlert('Error creating user. Please try again.', 'danger');
            }
        });
    });

    // Show alert function
    function showAlert(message, type) {
        const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        $('body').append(alertHtml);
        setTimeout(function() {
            $('.alert').alert('close');
        }, 3000);
    }
});
</script>
</body>
</html>