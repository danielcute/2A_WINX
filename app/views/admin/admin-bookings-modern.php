<?php
// admin-bookings-modern.php - Modern Minimalist Bookings Management

// Sample bookings data - replace with actual database queries
$bookings = [
    ['id' => 'BK001', 'client' => 'John Doe', 'email' => 'john@example.com', 'package' => 'Wedding Gold', 'date' => '2024-02-14', 'time' => '10:00 AM', 'guests' => 150, 'amount' => 5000, 'status' => 'confirmed', 'payment' => 'paid'],
    ['id' => 'BK002', 'client' => 'Jane Smith', 'email' => 'jane@example.com', 'package' => 'Birthday Standard', 'date' => '2024-02-20', 'time' => '2:00 PM', 'guests' => 50, 'amount' => 2000, 'status' => 'pending', 'payment' => 'pending'],
    ['id' => 'BK003', 'client' => 'Mike Johnson', 'email' => 'mike@example.com', 'package' => 'Corporate Platinum', 'date' => '2024-03-10', 'time' => '9:00 AM', 'guests' => 200, 'amount' => 8500, 'status' => 'confirmed', 'payment' => 'paid'],
    ['id' => 'BK004', 'client' => 'Sarah Williams', 'email' => 'sarah@example.com', 'package' => 'Anniversary Deluxe', 'date' => '2024-03-15', 'time' => '6:00 PM', 'guests' => 75, 'amount' => 3500, 'status' => 'confirmed', 'payment' => 'paid'],
    ['id' => 'BK005', 'client' => 'Alex Brown', 'email' => 'alex@example.com', 'package' => 'Engagement Standard', 'date' => '2024-03-22', 'time' => '4:00 PM', 'guests' => 60, 'amount' => 2500, 'status' => 'cancelled', 'payment' => 'refunded'],
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - Admin</title>
    <link rel="stylesheet" href="/SINTA/public/assets/css/admin-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .search-box {
            flex: 1;
            position: relative;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            color: var(--text-primary);
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-select:hover {
            border-color: var(--primary);
        }

        .booking-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
            border-bottom: 1px solid var(--border);
            padding: 1rem 0;
            transition: var(--transition);
        }

        .booking-row:hover {
            background: var(--bg-50);
            padding: 1rem;
            border-radius: 8px;
            margin: 0 -1rem;
            padding: 1rem;
        }

        .booking-row:last-child {
            border-bottom: none;
        }

        .row-actions {
            display: flex;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .header-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }

            .filter-bar {
                flex-direction: column;
            }

            .filter-select {
                width: 100%;
            }
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .modal-large {
            max-width: 700px;
        }
    </style>
</head>
<body>
    <?php include 'admin-nav-new.php'; ?>

    <div class="admin-content">
        <!-- Section Header -->
        <div class="section-header">
            <h1 class="section-title">Bookings Management</h1>
            <p class="section-subtitle">Manage and track all your event bookings</p>
        </div>

        <!-- Header Actions -->
        <div class="header-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="search" placeholder="Search by client name or booking ID...">
            </div>
            <a href="#" class="btn btn--primary" onclick="openNewBookingModal(event)">
                <i class="fas fa-plus"></i> New Booking
            </a>
        </div>

        <!-- Filters -->
        <div class="filter-bar">
            <select class="filter-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <select class="filter-select" id="paymentFilter">
                <option value="">All Payment Status</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="refunded">Refunded</option>
            </select>
            <select class="filter-select" id="monthFilter">
                <option value="">All Months</option>
                <option value="02">February 2024</option>
                <option value="03">March 2024</option>
                <option value="04">April 2024</option>
            </select>
        </div>

        <!-- Bookings Card -->
        <div class="card">
            <?php if (count($bookings) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Client</th>
                                <th>Package</th>
                                <th>Event Date</th>
                                <th>Guests</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($booking['id']); ?></strong></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($booking['client']); ?></div>
                                        <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($booking['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['package']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['date'])); ?></td>
                                    <td><?php echo $booking['guests']; ?> guests</td>
                                    <td><strong>$<?php echo number_format($booking['amount']); ?></strong></td>
                                    <td>
                                        <span class="badge badge--<?php echo $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge--<?php echo $booking['payment'] === 'paid' ? 'success' : ($booking['payment'] === 'pending' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($booking['payment']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <button class="btn btn--sm btn--secondary" onclick="viewBooking('<?php echo htmlspecialchars($booking['id']); ?>')">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn--sm btn--secondary" onclick="editBooking('<?php echo htmlspecialchars($booking['id']); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div style="display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border);">
                    <button class="btn btn--sm btn--secondary"><i class="fas fa-chevron-left"></i></button>
                    <span>Page 1 of 1</span>
                    <button class="btn btn--sm btn--secondary"><i class="fas fa-chevron-right"></i></button>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No bookings found</h3>
                    <p>Start by creating a new booking</p>
                    <a href="#" class="btn btn--primary mt-2" onclick="openNewBookingModal(event)">
                        <i class="fas fa-plus"></i> Create Booking
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for viewing/editing bookings -->
    <div class="modal-overlay" id="bookingModal">
        <div class="modal modal-large">
            <div class="modal-header">
                <h2 class="modal-title">Booking Details</h2>
                <button class="modal-close" onclick="closeModal('bookingModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    <div>
                        <label class="form-label">Client Name</label>
                        <input type="text" class="form-input" placeholder="John Doe" readonly>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" placeholder="john@example.com" readonly>
                    </div>
                    <div>
                        <label class="form-label">Package</label>
                        <input type="text" class="form-input" placeholder="Wedding Gold" readonly>
                    </div>
                    <div>
                        <label class="form-label">Event Date</label>
                        <input type="date" class="form-input" readonly>
                    </div>
                    <div>
                        <label class="form-label">Number of Guests</label>
                        <input type="number" class="form-input" placeholder="150" readonly>
                    </div>
                    <div>
                        <label class="form-label">Amount</label>
                        <input type="text" class="form-input" placeholder="$5,000.00" readonly>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option>Confirmed</option>
                            <option>Pending</option>
                            <option>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Payment Status</label>
                        <select class="form-select">
                            <option>Paid</option>
                            <option>Pending</option>
                            <option>Refunded</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label class="form-label">Special Requests</label>
                    <textarea class="form-textarea" readonly>No special requests</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn--secondary" onclick="closeModal('bookingModal')">Close</button>
                <button class="btn btn--primary">Save Changes</button>
            </div>
        </div>
    </div>

    <script>
        function openNewBookingModal(e) {
            e.preventDefault();
            document.getElementById('bookingModal').classList.add('active');
        }

        function viewBooking(bookingId) {
            document.getElementById('bookingModal').classList.add('active');
        }

        function editBooking(bookingId) {
            document.getElementById('bookingModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Search functionality
        document.getElementById('search').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            filterTable();
        });

        document.getElementById('paymentFilter').addEventListener('change', function() {
            filterTable();
        });

        document.getElementById('monthFilter').addEventListener('change', function() {
            filterTable();
        });

        function filterTable() {
            // Add filtering logic here
            console.log('Filtering table...');
        }

        // Update page title
        document.addEventListener('DOMContentLoaded', function() {
            const pageTitle = document.querySelector('#page-title');
            if (pageTitle) {
                pageTitle.textContent = 'Bookings Management';
            }
        });

        // Close modal when clicking outside
        document.getElementById('bookingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal('bookingModal');
            }
        });
    </script>
</body>
</html>
