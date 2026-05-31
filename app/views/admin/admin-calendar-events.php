<?php
/**
 * Admin Event Calendar with Filtering
 * Responsive calendar view with event filtering support
 */

// Check if BASE_URL is defined
if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}
if (!defined('APP_URL')) {
    define('APP_URL', BASE_URL . '/index.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Event Calendar - Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .calendar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
            flex-wrap: wrap;
            gap: 15px;
        }

        .calendar-header h1 {
            margin: 0;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.9rem;
            color: #2C2820;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
        }

        .filter-controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #2C2820;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            min-width: 120px;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #8A7650;
            box-shadow: 0 0 0 3px rgba(138, 118, 80, 0.1);
        }

        .filter-btn {
            background: #8A7650;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: background 0.25s;
        }

        .filter-btn:hover {
            background: #6B5A3E;
        }

        .filter-btn-clear {
            background: #f0f0f0;
            color: #333;
        }

        .filter-btn-clear:hover {
            background: #e0e0e0;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .calendar-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #8A7650;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-card-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #8A7650;
        }

        .stat-card-label {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-card.pending {
            border-left-color: #ffc107;
        }

        .stat-card.pending .stat-card-value {
            color: #ffc107;
        }

        .stat-card.confirmed {
            border-left-color: #28a745;
        }

        .stat-card.confirmed .stat-card-value {
            color: #28a745;
        }

        .stat-card.canceled {
            border-left-color: #dc3545;
        }

        .stat-card.canceled .stat-card-value {
            color: #dc3545;
        }

        .events-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .events-list-header {
            background: #f9f7f3;
            padding: 15px 20px;
            border-bottom: 2px solid #ddd;
            font-weight: 600;
            color: #2C2820;
        }

        .event-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .event-item:last-child {
            border-bottom: none;
        }

        .event-item:hover {
            background: #faf8f4;
        }

        .event-info {
            flex: 1;
            min-width: 200px;
        }

        .event-title {
            font-weight: 600;
            color: #2C2820;
            font-size: 1rem;
        }

        .event-meta {
            font-size: 0.85rem;
            color: #666;
            margin-top: 4px;
        }

        .event-meta span {
            display: inline-block;
            margin-right: 15px;
        }

        .event-customer {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 150px;
        }

        .event-customer-name {
            font-weight: 600;
            color: #2C2820;
            font-size: 0.95rem;
        }

        .event-customer-email {
            font-size: 0.85rem;
            color: #8A7650;
        }

        .event-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            min-width: 100px;
            text-align: center;
        }

        .event-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .event-status.confirmed {
            background: #d4edda;
            color: #155724;
        }

        .event-status.canceled {
            background: #f8d7da;
            color: #721c24;
        }

        .event-price {
            font-weight: 700;
            color: #8A7650;
            font-size: 1.1rem;
            min-width: 100px;
            text-align: right;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #999;
        }

        .empty-state-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #ddd;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .calendar-container {
                padding: 15px;
            }

            .calendar-header {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 20px;
            }

            .calendar-header h1 {
                font-size: 1.5rem;
            }

            .filter-controls {
                width: 100%;
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-group select,
            .filter-group input {
                width: 100%;
                min-width: auto;
            }

            .filter-btn {
                width: 100%;
                justify-content: center;
            }

            .calendar-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .event-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .event-customer,
            .event-price {
                width: 100%;
            }

            .event-price {
                text-align: left;
            }

            .event-status {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .calendar-container {
                padding: 12px;
            }

            .calendar-header h1 {
                font-size: 1.3rem;
            }

            .calendar-stats {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 12px;
            }

            .event-item {
                padding: 12px;
            }

            .event-title {
                font-size: 0.95rem;
            }

            .event-meta {
                font-size: 0.8rem;
            }

            .event-customer-name {
                font-size: 0.9rem;
            }

            .event-status {
                min-width: auto;
                width: 100%;
            }

            .filter-group label {
                font-size: 0.85rem;
            }

            .filter-group select,
            .filter-group input {
                padding: 6px 10px;
                font-size: 0.85rem;
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,0.1);
            border-top-color: #8A7650;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="calendar-container">
        <div class="calendar-header">
            <h1>
                <i class="fas fa-calendar-alt"></i>
                Event Calendar
            </h1>
        </div>

        <!-- Filter Controls -->
        <div class="filter-controls">
            <div class="filter-group">
                <label for="filterStatus">Status:</label>
                <select id="filterStatus">
                    <option value="all">All Events</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="canceled">Canceled</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="filterStartDate">From:</label>
                <input type="date" id="filterStartDate">
            </div>

            <div class="filter-group">
                <label for="filterEndDate">To:</label>
                <input type="date" id="filterEndDate">
            </div>

            <div class="filter-group">
                <label for="filterSearch">Search:</label>
                <input type="text" id="filterSearch" placeholder="Event, venue, customer...">
            </div>

            <button class="filter-btn" onclick="applyFilters()">
                <i class="fas fa-filter"></i> Filter
            </button>

            <button class="filter-btn filter-btn-clear" onclick="clearFilters()">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="calendar-stats" id="statsContainer" style="display: none;">
            <div class="stat-card">
                <div class="stat-card-value" id="statTotal">0</div>
                <div class="stat-card-label">Total Events</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-card-value" id="statPending">0</div>
                <div class="stat-card-label">Pending</div>
            </div>
            <div class="stat-card confirmed">
                <div class="stat-card-value" id="statConfirmed">0</div>
                <div class="stat-card-label">Confirmed</div>
            </div>
            <div class="stat-card canceled">
                <div class="stat-card-value" id="statCanceled">0</div>
                <div class="stat-card-label">Canceled</div>
            </div>
        </div>

        <!-- Events List -->
        <div class="events-list" id="eventsList" style="display: none;">
            <div class="events-list-header">
                Upcoming Events
            </div>
            <div id="eventsContainer"></div>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-state-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <p>No events found matching your filters</p>
        </div>

        <!-- Loading State -->
        <div class="empty-state" id="loadingState">
            <div style="margin-bottom: 15px;">
                <span class="loading-spinner"></span>
            </div>
            <p>Loading events...</p>
        </div>
    </div>

    <script>
        let allEvents = [];

        function loadEvents() {
            const loadingState = document.getElementById('loadingState');
            const eventsList = document.getElementById('eventsList');
            const emptyState = document.getElementById('emptyState');

            loadingState.style.display = 'block';
            eventsList.style.display = 'none';
            emptyState.style.display = 'none';

            fetch('<?php echo BASE_URL; ?>/api-calendar.php?action=getAll')
                .then(response => response.json())
                .then(data => {
                    allEvents = data;
                    loadingState.style.display = 'none';
                    applyFilters();
                })
                .catch(error => {
                    console.error('Error loading events:', error);
                    loadingState.innerHTML = '<p style="color: red;">Error loading events</p>';
                });
        }

        function applyFilters() {
            const status = document.getElementById('filterStatus').value;
            const startDate = document.getElementById('filterStartDate').value;
            const endDate = document.getElementById('filterEndDate').value;
            const search = document.getElementById('filterSearch').value.toLowerCase();

            let filtered = allEvents.filter(event => {
                // Status filter
                if (status !== 'all' && event.extendedProps.status !== status) {
                    return false;
                }

                // Date range filter
                if (startDate && event.start < startDate) {
                    return false;
                }
                if (endDate && event.start > endDate) {
                    return false;
                }

                // Search filter
                if (search) {
                    const eventStr = (event.title + ' ' + event.extendedProps.venue + ' ' + 
                                    event.extendedProps.customer).toLowerCase();
                    if (!eventStr.includes(search)) {
                        return false;
                    }
                }

                return true;
            });

            displayEvents(filtered);
        }

        function displayEvents(events) {
            const eventsList = document.getElementById('eventsList');
            const emptyState = document.getElementById('emptyState');
            const statsContainer = document.getElementById('statsContainer');
            const container = document.getElementById('eventsContainer');

            if (events.length === 0) {
                eventsList.style.display = 'none';
                emptyState.style.display = 'block';
                statsContainer.style.display = 'none';
                return;
            }

            // Calculate statistics
            const stats = {
                total: events.length,
                pending: events.filter(e => e.extendedProps.status === 'pending').length,
                confirmed: events.filter(e => e.extendedProps.status === 'confirmed').length,
                canceled: events.filter(e => e.extendedProps.status === 'canceled').length
            };

            document.getElementById('statTotal').textContent = stats.total;
            document.getElementById('statPending').textContent = stats.pending;
            document.getElementById('statConfirmed').textContent = stats.confirmed;
            document.getElementById('statCanceled').textContent = stats.canceled;

            // Render events
            container.innerHTML = events.map(event => `
                <div class="event-item">
                    <div class="event-info">
                        <div class="event-title">${event.title}</div>
                        <div class="event-meta">
                            <span><i class="fas fa-calendar"></i> ${new Date(event.start).toLocaleDateString()}</span>
                            <span><i class="fas fa-clock"></i> ${event.extendedProps.time || 'TBD'}</span>
                            <span><i class="fas fa-map-marker-alt"></i> ${event.extendedProps.venue || 'TBD'}</span>
                        </div>
                    </div>
                    <div class="event-customer">
                        <div class="event-customer-name">${event.extendedProps.customer}</div>
                        <div class="event-customer-email">${event.extendedProps.email || 'N/A'}</div>
                    </div>
                    <span class="event-status ${event.extendedProps.status}">${event.extendedProps.status}</span>
                    <div class="event-price">₱${parseFloat(event.extendedProps.price || 0).toLocaleString('en-PH', {minimumFractionDigits: 2})}</div>
                </div>
            `).join('');

            statsContainer.style.display = 'grid';
            eventsList.style.display = 'block';
            emptyState.style.display = 'none';
        }

        function clearFilters() {
            document.getElementById('filterStatus').value = 'all';
            document.getElementById('filterStartDate').value = '';
            document.getElementById('filterEndDate').value = '';
            document.getElementById('filterSearch').value = '';
            applyFilters();
        }

        // Load events on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadEvents();

            // Add event listeners for real-time filtering
            document.getElementById('filterStatus').addEventListener('change', applyFilters);
            document.getElementById('filterStartDate').addEventListener('change', applyFilters);
            document.getElementById('filterEndDate').addEventListener('change', applyFilters);
            document.getElementById('filterSearch').addEventListener('input', applyFilters);
        });
    </script>
</body>
</html>
