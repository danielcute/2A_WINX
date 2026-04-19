<?php
// Check admin authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php?route=admin-login");
    exit;
}

require_once ROOT_PATH . '/app/models/User.php';
$user = new User();
$admin = $user->findById($_SESSION['user_id']);

if (!$admin || $admin['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?route=home");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customizations - Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .header-section h1 {
            margin: 0;
            color: #333;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background: #45a049;
        }

        .btn-danger {
            background: #f44336;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-danger:hover {
            background: #da190b;
        }

        .btn-edit {
            background: #2196F3;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-edit:hover {
            background: #0b7dda;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .filter-section {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-section select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }

        .table-container {
            overflow-x: auto;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state p {
            margin-bottom: 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <?php include ROOT_PATH . '/app/views/admin/admin-nav.php'; ?>

    <div class="admin-container">
        <div class="header-section">
            <h1>📝 Manage Customizations</h1>
            <a href="<?php echo BASE_URL; ?>/index.php?route=admin-customize-add" class="btn-primary">+ Add New Customization</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✓ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ✗ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($options)): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Price (₱)</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($options as $option): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($option['category']); ?></strong></td>
                                <td><?php echo htmlspecialchars($option['name']); ?></td>
                                <td>₱<?php echo number_format($option['price'], 2); ?></td>
                                <td>
                                    <span class="status-<?php echo $option['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $option['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($option['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="<?php echo BASE_URL; ?>/index.php?route=admin-customize-edit&id=<?php echo $option['option_id']; ?>" class="btn-edit">Edit</a>
                                        <button onclick="confirmDelete(<?php echo $option['option_id']; ?>)" class="btn-danger">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>📦 No customization options found</p>
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin-customize-add" class="btn-primary">Create First Customization</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete(optionId) {
            if (confirm('Are you sure you want to delete this customization option? This action cannot be undone.')) {
                window.location.href = '<?php echo BASE_URL; ?>/index.php?route=admin-customize-delete&id=' + optionId;
            }
        }
    </script>
</body>
</html>
