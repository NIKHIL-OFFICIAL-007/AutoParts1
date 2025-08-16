<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Don't allow deleting self
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account!";
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['success'] = "User deleted successfully!";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['error'] = "User not found!";
    }
    
    header("Location: manage_users.php");
    exit();
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Management</h2>
            <a href="add_user.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add New User
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        switch($user['role']) {
                                            case 'admin': echo 'bg-primary'; break;
                                            case 'seller': echo 'bg-success'; break;
                                            case 'buyer': echo 'bg-info'; break;
                                            case 'support': echo 'bg-warning'; break;
                                        }
                                        ?>
                                    ">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        switch($user['status']) {
                                            case 'active': echo 'bg-success'; break;
                                            case 'inactive': echo 'bg-secondary'; break;
                                            case 'suspended': echo 'bg-danger'; break;
                                        }
                                        ?>
                                    ">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <a href="manage_users.php?delete=<?php echo $user['user_id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>