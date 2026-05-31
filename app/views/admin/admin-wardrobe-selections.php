<?php
// $selections, $filter_status, and $filter_plan are provided by WardrobeController::selections()
$page       = 'admin-wardrobe-selections';
$page_title = 'Wardrobe Rentals';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wardrobe Rentals - Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1400px;
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
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-section h1 {
            margin: 0;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.9rem;
            color: #2C2820;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
        }

        .filter-section {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-section select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            cursor: pointer;
        }

        .table-container {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .rentals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .rentals-table thead {
            background: #f9f7f3;
            border-bottom: 2px solid #ddd;
        }

        .rentals-table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #2C2820;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .rentals-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .rentals-table tbody tr:hover {
            background: #faf8f4;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-rented {
            background: #d4edda;
            color: #155724;
        }

        .status-returned {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .price-badge {
            background: #e8f4f8;
            color: #006085;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-update {
            background: #ffc107;
            color: #000;
        }

        .btn-update:hover {
            background: #e0a800;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #999;
        }

        @media (max-width: 768px) {
            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-section {
                width: 100%;
            }

            .rentals-table {
                font-size: 0.9rem;
            }

            .rentals-table th,
            .rentals-table td {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <?php include VIEW_PATH . '/admin/admin-nav.php'; ?>

    <div class="admin-container">
        <div class="header-section">
            <h1>
                <i class="fas fa-shopping-bag"></i>
                Wardrobe <em>Rentals</em>
            </h1>
            <div class="filter-section">
                <form method="get" action="<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe-selections" style="display: flex; gap: 10px;">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">-- All Status --</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="rented" <?php echo $filter_status === 'rented' ? 'selected' : ''; ?>>Rented</option>
                        <option value="returned" <?php echo $filter_status === 'returned' ? 'selected' : ''; ?>>Returned</option>
                        <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="table-container">
            <?php if (!empty($selections)): ?>
                <table class="rentals-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">User</th>
                            <th style="width: 15%;">Event</th>
                            <th style="width: 15%;">Wardrobe</th>
                            <th style="width: 10%;">Category</th>
                            <th style="width: 10%;">Qty</th>
                            <th style="width: 10%;">Price</th>
                            <th style="width: 12%;">Rental Dates</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 6%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($selections as $selection): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($selection['first_name'] . ' ' . $selection['last_name']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($selection['event_name'] ?? 'N/A'); ?>
                                    <br>
                                    <small style="color: #999;">
                                        <?php echo $selection['event_date'] ? date('M d, Y', strtotime($selection['event_date'])) : 'N/A'; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($selection['name']); ?>
                                    <br>
                                    <small style="color: #999;"><?php echo htmlspecialchars($selection['size_selected']); ?></small>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($selection['category']); ?></small>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo $selection['quantity_selected']; ?>
                                </td>
                                <td>
                                    <span class="price-badge">₱<?php echo number_format($selection['subtotal_price'], 2); ?></span>
                                </td>
                                <td style="font-size: 0.9rem;">
                                    <?php 
                                    echo date('M d', strtotime($selection['rental_start_date']));
                                    echo ' - ';
                                    echo date('M d', strtotime($selection['rental_end_date']));
                                    ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $selection['status']; ?>">
                                        <?php echo ucfirst($selection['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin-wardrobe-selections-edit&id=<?php echo $selection['selection_id']; ?>" class="btn-small btn-update">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 2rem; margin-bottom: 10px;">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p>No wardrobe rentals yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add any interactive features here
    </script>
</body>
</html>