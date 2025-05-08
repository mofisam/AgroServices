<?php
session_start();
include '../config/db.php';

if ($_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build SQL Query with Filters
$query = "SELECT id, first_name, last_name, email, role, created_at FROM users WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
}
if (!empty($role_filter)) {
    $query .= " AND role = ?";
}
$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

// Prepare statement
$stmt = $conn->prepare($query);

if (!empty($search) && !empty($role_filter)) {
    $search_param = "%$search%";
    $stmt->bind_param("sssiii", $search_param, $search_param, $search_param, $role_filter, $limit, $offset);
} elseif (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("sssi", $search_param, $search_param, $search_param, $limit, $offset);
} elseif (!empty($role_filter)) {
    $stmt->bind_param("sii", $role_filter, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Get total users count for pagination
$total_query = "SELECT COUNT(*) as total FROM users WHERE 1=1";
if (!empty($search)) {
    $total_query .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
}
if (!empty($role_filter)) {
    $total_query .= " AND role = ?";
}
$total_stmt = $conn->prepare($total_query);
if (!empty($search) && !empty($role_filter)) {
    $total_stmt->bind_param("sss", $search_param, $search_param, $search_param, $role_filter);
} elseif (!empty($search)) {
    $total_stmt->bind_param("sss", $search_param, $search_param, $search_param);
} elseif (!empty($role_filter)) {
    $total_stmt->bind_param("s", $role_filter);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_users = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="container mt-4">
    <h2>Manage Users</h2>

    <form method="GET" class="mb-3">
        <input type="text" name="search" placeholder="Search by Name or Email" value="<?= htmlspecialchars($search) ?>" class="form-control mb-2">
        <select name="role" class="form-select mb-2">
            <option value="">All Roles</option>
            <option value="buyer" <?= $role_filter == 'buyer' ? 'selected' : '' ?>>Buyer</option>
            <option value="seller" <?= $role_filter == 'seller' ? 'selected' : '' ?>>Seller</option>
            <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <table class="table table-striped">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
        <?php while ($user = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($user["first_name"] . " " . $user["last_name"]) ?></td>
                <td><?= htmlspecialchars($user["email"]) ?></td>
                <td><?= htmlspecialchars(ucfirst($user["role"])) ?></td>
                <td><?= $user["created_at"] ?></td>
                <td>
                    <button class="btn btn-info btn-sm view-user" data-id="<?= $user['id'] ?>" data-bs-toggle="modal" data-bs-target="#userModal">View</button>
                    <button class="btn btn-warning btn-sm edit-user" data-id="<?= $user['id'] ?>" data-bs-toggle="modal" data-bs-target="#editUserModal">Edit</button>
                    <a href="admin_suspend_user.php?id=<?= $user["id"] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Suspend this user?')">Suspend</a>
                </td>

            </tr>
        <?php endwhile; ?>
    </table>

    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>&role=<?= htmlspecialchars($role_filter) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

    <!-- User Details Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modal-body">
                    <p>Loading...</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="mb-3">
                        <label>First Name:</label>
                        <input type="text" id="editFirstName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Last Name:</label>
                        <input type="text" id="editLastName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email:</label>
                        <input type="email" id="editEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role:</label>
                        <select id="editRole" class="form-select">
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
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
            }
        });
    });

    $("#editUserForm").submit(function(e) {
        e.preventDefault();
        var userId = $("#editUserId").val();
        var firstName = $("#editFirstName").val();
        var lastName = $("#editLastName").val();
        var email = $("#editEmail").val();
        var role = $("#editRole").val();

        $.ajax({
            url: "admin_update_user.php",
            method: "POST",
            data: {
                id: userId, first_name: firstName, last_name: lastName, email: email, role: role
            },
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });
</script>

    <script>
        $(document).on("click", ".view-user", function() {
            var userId = $(this).data("id");
            $.ajax({
                url: "admin_view_user.php",
                method: "GET",
                data: { id: userId },
                success: function(data) {
                    $("#modal-body").html(data);
                }
            });
        });
    </script>
    
</body>
</html>
