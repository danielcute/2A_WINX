<?php
// Session already started in index.php
if (!isset($_SESSION['user_id']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ' . BASE_URL . '/index.php?route=signin');
    exit;
}

$page = 'admin-dashboard';
$page_title = 'Dashboard';

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/PlanAutoConfirmation.php';
$db = Database::getInstance()->getConnection();

// Get stats (unchanged)
$packages_result = $db->query("SELECT COUNT(*) as count FROM packages_tbl");
$total_packages = $packages_result->fetch_assoc()['count'] ?? 0;

$bookings_result = $db->query("SELECT COUNT(*) as count FROM plans_tbl");
$total_bookings = $bookings_result->fetch_assoc()['count'] ?? 0;

$testimonials_result = $db->query("SELECT COUNT(*) as count FROM testimonials_tbl");
$total_testimonials = $testimonials_result->fetch_assoc()['count'] ?? 0;

$users_result = $db->query("SELECT COUNT(*) as count FROM users_tbl WHERE role = 'user'");
$total_users = $users_result->fetch_assoc()['count'] ?? 0;

$recent_bookings = $db->query("SELECT p.plan_id as checkout_id, p.*, u.first_name, u.last_name, u.email
                                FROM plans_tbl p 
                                LEFT JOIN users_tbl u ON p.user_id = u.user_id
                                ORDER BY p.plan_id DESC LIMIT 5");

$autoConfirm = new PlanAutoConfirmation();
$bookings_array = [];
if ($recent_bookings && $recent_bookings->num_rows > 0) {
    while ($row = $recent_bookings->fetch_assoc()) {
        $planStatusInfo = $autoConfirm->getPlanStatusInfo($row['plan_id']);
        if ($planStatusInfo) $row['status'] = $planStatusInfo['status'];
        $bookings_array[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin Dashboard | Sinta</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    <style>
        /* Your existing dashboard specific styles (stats-grid, etc.) */
        .dashboard-container { 
            width: 100%; 
            padding: 1.5rem; 
            box-sizing: border-box;
            padding-top: 2rem;
        }
        .dashboard-header { margin-bottom: 2.5rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; border: 2px solid var(--border); border-radius: 24px; padding: 1.5rem; transition: all 0.3s; box-shadow: var(--shadow-sm); text-align: center; }
        .stat-card:hover { border-color: var(--primary); transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .stat-card h3 { font-size: 2rem; margin: 0; color: var(--primary); font-weight: 800; font-family: var(--sans); }
        .stat-card p { color: var(--text-secondary); font-weight: 600; margin-top: 0.4rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; }
        
        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem; margin: 1.5rem 0; }
        .action-btn { background: white; border: 2px solid #E2D9C8; border-radius: 15px; padding: 1.25rem 1rem; text-align: center; text-decoration: none; color: #2C2820; transition: all 0.3s; font-size: 0.9rem; font-weight: 600; }
        .action-btn:hover { border-color: #8A7650; transform: translateY(-2px); box-shadow: var(--shadow-sm); }
        
        /* Calendar Styles */
        .event-calendar { background: white; border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid #E2D9C8; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .event-calendar h2 { margin-top: 0; font-size: 1.5rem; color: #2C2820; display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; }
        .event-calendar h2 i { color: #8A7650; }
        
        /* FullCalendar Customization */
        .fc { font-family: 'Inter', sans-serif; }
        .fc .fc-button-primary { background-color: #8A7650; border-color: #8A7650; }
        .fc .fc-button-primary:hover { background-color: #6B5A3E; border-color: #6B5A3E; }
        .fc .fc-button-primary.fc-button-active { background-color: #6B5A3E; border-color: #6B5A3E; }
        .fc .fc-col-header-cell { background-color: #F5F0E8; color: #8A7650; font-weight: 600; }
        .fc .fc-daygrid-day { border-color: #E2D9C8; cursor: pointer; transition: background-color 0.2s; }
        .fc .fc-daygrid-day:hover { background-color: #F5F0E8; }
        .fc .fc-daygrid-day.fc-day-other { background-color: #FAFAF7; }
        .fc .fc-event { border: none; cursor: pointer; }
        .fc .fc-event-title { font-weight: 500; padding: 0.2rem 0.4rem; }
        .fc .fc-daygrid-day-number { color: #8A7650; }
        .fc .fc-daygrid-day-frame { position: relative; }
        .fc .fc-event:hover { opacity: 0.85; }
        .fc .fc-daygrid-event { margin-top: 0.1rem; }
        .fc .fc-daygrid-event-harness { cursor: pointer; }
        
        
        /* Event Details Popup */
        .event-details-popup { 
            background: white; 
            border-radius: 12px; 
            padding: 1.25rem; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 380px;
            min-width: 320px;
            z-index: 99999 !important;
            border: 2px solid #8A7650;
            animation: popupSlideIn 0.3s ease;
        }
        @keyframes popupSlideIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .event-details-popup h4 { margin: 0 0 0.75rem; color: #8A7650; font-size: 1.1rem; font-weight: 600; }
        .event-details-popup p { margin: 0.25rem 0; font-size: 0.85rem; color: #6B6463; }
        .event-details-popup .detail-row { display: flex; gap: 0.5rem; margin: 0.5rem 0; align-items: flex-start; }
        .event-details-popup .detail-label { font-weight: 600; color: #2C2820; min-width: 80px; flex-shrink: 0; }
        .event-details-popup .detail-value { color: #555; word-break: break-word; }
        
        /* Weather Widget */
        .weather-widget {
            background: linear-gradient(135deg, #364655 0%, #2a3a45 100%);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            min-height: 340px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            gap: 0.5rem;
        }
        .weather-widget .location-name {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1rem;
            letter-spacing: 0.5px;
        }
        .weather-widget .weather-icon {
            font-size: 3.5rem;
            margin: 0.5rem 0;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        .weather-widget .temp {
            font-size: 2.8rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }
        .weather-widget .condition {
            font-size: 1rem;
            opacity: 0.95;
            margin-bottom: 1.5rem;
            letter-spacing: 0.5px;
        }
        .weather-widget .details {
            font-size: 0.9rem;
            opacity: 0.9;
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            margin-top: auto;
            justify-content: center;
            width: 100%;
        }
        .weather-widget .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            align-items: center;
            flex: 1;
            padding: 0.5rem;
        }
        .weather-widget .detail-label {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        .weather-widget .detail-value {
            font-size: 0.95rem;
            font-weight: 600;
        }
        .dashboard-grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        /* Modernized Buttons */
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1.2rem;
            }

            .dashboard-grid-2col { 
                grid-template-columns: 1fr; 
            }

            .stats-grid { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 1.2rem;
            }

            .dashboard-header h1 { 
                font-size: 1.8rem; 
            }

            .recent-bookings { 
                border-radius: 20px; 
                border: none; 
                background: transparent; 
                box-shadow: none; 
            }

            .recent-bookings table { 
                display: block; 
            }

            .recent-bookings tbody { 
                display: block; 
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .event-calendar h2 {
                font-size: 1.3rem;
            }

            .weather-widget {
                min-height: 320px;
            }
        }

        .recent-bookings { 
            background: white; 
            border-radius: 24px; 
            overflow: hidden; 
            border: 2px solid var(--border); 
            box-shadow: var(--shadow-sm); 
            margin-bottom: 2rem; 
        }

        .recent-bookings table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        .recent-bookings th, .recent-bookings td { 
            padding: 1.2rem 1rem; 
            text-align: left; 
            border-bottom: 1px solid var(--border); 
        }

        .recent-bookings th { 
            background-color: #F9F7F3; 
            font-weight: 700; 
            color: var(--primary); 
            font-size: 0.85rem; 
            text-transform: uppercase; 
            letter-spacing: 0.05em; 
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            .admin-page-header {
                padding: 1.2rem 1.5rem;
                margin-bottom: 1.5rem;
            }

            .admin-page-header h1 {
                font-size: 1.4rem;
            }

            .stats-grid { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 1rem;
            }

            .stat-card { 
                padding: 1.25rem; 
                border-radius: 16px;
            }

            .stat-card h3 { 
                font-size: 1.5rem; 
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.6rem;
                margin: 1.2rem 0;
            }

            .action-btn {
                padding: 1rem 0.75rem;
                font-size: 0.8rem;
                border-radius: 12px;
            }

            .action-btn i {
                display: block;
                margin-bottom: 0.4rem;
                font-size: 1.3rem;
            }

            .recent-bookings {
                border-radius: 16px;
                margin-bottom: 1.5rem;
            }

            .recent-bookings thead { 
                display: none; 
            }

            .recent-bookings table, .recent-bookings tbody, .recent-bookings tr, .recent-bookings td { 
                display: block; 
                width: 100%; 
                box-sizing: border-box;
            }

            .recent-bookings tr { 
                border-bottom: 2px solid var(--border); 
                padding: 1rem; 
                margin-bottom: 0.5rem;
                border-radius: 12px;
                background: white;
            }

            .recent-bookings td { 
                text-align: right; 
                padding: 0.6rem 0; 
                border: none; 
                position: relative; 
                display: flex;
                justify-content: space-between;
                font-size: 0.95rem;
            }

            .recent-bookings td::before { 
                content: attr(data-label); 
                font-weight: 700; 
                color: var(--primary);
            }

            .dashboard-grid-2col {
                gap: 1.2rem;
                margin-bottom: 1.5rem;
            }

            .event-calendar {
                padding: 1.2rem;
                border-radius: 16px;
            }

            .event-calendar h2 {
                font-size: 1.2rem;
                margin-bottom: 1rem;
            }

            .weather-widget {
                padding: 1.5rem;
                border-radius: 16px;
                min-height: 300px;
            }

            .weather-widget .location-name {
                font-size: 1.1rem;
            }

            .weather-widget .weather-icon {
                font-size: 2.5rem;
            }

            .weather-widget .temp {
                font-size: 2rem;
            }

            .weather-widget .condition {
                font-size: 0.9rem;
            }

            .weather-widget .details {
                gap: 1rem;
            }

            .weather-widget .detail-item {
                flex: 1;
                padding: 0.3rem;
            }

            .weather-widget .detail-label {
                font-size: 0.75rem;
            }

            .weather-widget .detail-value {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 600px) {
            .dashboard-container {
                padding: 0.75rem;
            }

            .admin-page-header {
                padding: 1rem 1.2rem;
                margin-bottom: 1.2rem;
                border-radius: 12px;
            }

            .admin-page-header h1 {
                font-size: 1.1rem;
                gap: 0.5rem;
            }

            .admin-page-header h1 i {
                font-size: 1.2rem;
            }

            .stats-grid { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 0.75rem;
            }

            .stat-card { 
                padding: 1rem; 
                border-radius: 12px;
            }

            .stat-card h3 { 
                font-size: 1.2rem; 
            }

            .stat-card p {
                font-size: 0.75rem;
            }

            .quick-actions {
                grid-template-columns: 1fr;
                gap: 0.5rem;
                margin: 1rem 0;
            }

            .action-btn {
                padding: 0.9rem 0.75rem;
                font-size: 0.75rem;
                border-radius: 10px;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                justify-content: flex-start;
            }

            .action-btn i {
                display: inline;
                margin-bottom: 0;
                font-size: 1rem;
                min-width: 20px;
            }

            .recent-bookings {
                border-radius: 12px;
                margin-bottom: 1.2rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            .recent-bookings thead { 
                display: none; 
            }

            .recent-bookings tr { 
                border-bottom: 1px solid var(--border); 
                padding: 0.75rem; 
                margin-bottom: 0.4rem;
                border-radius: 8px;
            }

            .recent-bookings td { 
                padding: 0.4rem 0; 
                font-size: 0.8rem;
            }

            .recent-bookings td::before { 
                font-size: 0.75rem;
                min-width: 70px;
            }

            .dashboard-grid-2col {
                gap: 1rem;
                margin-bottom: 1.2rem;
            }

            .event-calendar {
                padding: 1rem;
                border-radius: 12px;
                margin-bottom: 1.2rem;
            }

            .event-calendar h2 {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }

            #adminEventCalendar {
                height: 300px !important;
            }

            .weather-widget {
                padding: 1.2rem;
                border-radius: 12px;
                min-height: 280px;
                gap: 0.25rem;
            }

            .weather-widget .location-name {
                font-size: 0.95rem;
                margin-bottom: 0.75rem;
            }

            .weather-widget .weather-icon {
                font-size: 2rem;
                margin: 0.3rem 0;
            }

            .weather-widget .temp {
                font-size: 1.5rem;
                margin: 0.3rem 0;
            }

            .weather-widget .condition {
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }

            .weather-widget .details {
                gap: 0.75rem;
            }

            .weather-widget .detail-item {
                padding: 0.2rem;
            }

            .weather-widget .detail-label {
                font-size: 0.65rem;
            }

            .weather-widget .detail-value {
                font-size: 0.75rem;
            }

            .status-badge { 
                padding: 0.2rem 0.6rem; 
                font-size: 0.7rem; 
            }

            h3[style*="font-size: 1.6rem"] {
                font-size: 1.2rem !important;
                margin: 1.5rem 0 0.75rem !important;
            }
        }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-confirmed { background: #D1ECF1; color: #0c5460; }
        .status-canceled { background: #FFE5E5; color: #C41C3B; }
    </style>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
</head>
<body>
<?php 
$page_title = 'Dashboard';
include 'admin-nav.php'; 
?>
<div class="dashboard-container">
    <div class="admin-page-header">
        <h1><i class="fas fa-chart-line animated-icon"></i> Admin <em>Overview</em></h1>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card"><h3><?= $total_packages ?></h3><p>Total Packages</p></div>
        <div class="stat-card"><h3><?= $total_bookings ?></h3><p>Total Bookings</p></div>
        <div class="stat-card"><h3><?= $total_testimonials ?></h3><p>Testimonials</p></div>
        <div class="stat-card"><h3><?= $total_users ?></h3><p>Active Users</p></div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="/index.php?route=admin-packages" class="action-btn"><i class="fas fa-plus-circle"></i> Manage Packages</a>
        <a href="/index.php?route=admin-occasions" class="action-btn"><i class="fas fa-calendar-alt"></i> Manage Occasions</a>
        <a href="/index.php?route=admin-bookings" class="action-btn"><i class="fas fa-list-check"></i> View Bookings</a>
        <a href="/index.php?route=admin-messages" class="action-btn"><i class="fas fa-envelope-open"></i> Read Messages</a>
    </div>

    <!-- Event Calendar & Weather -->
    <div class="dashboard-grid-2col">
        <div class="event-calendar">
            <h2><i class="fas fa-calendar-check"></i> Event Calendar - All Bookings</h2>
            <div id="adminEventCalendar" style="height: 420px;"></div>
        </div>
        <div class="weather-widget" id="weatherWidget">
            <div class="location-name" id="weatherLocation">Manila, Philippines</div>
            <div class="weather-icon"><i class="fas fa-cloud-sun"></i></div>
            <div class="temp" id="weatherTemp">--°F</div>
            <div class="condition" id="weatherCondition">Loading weather...</div>
            <div class="details" id="weatherDetails">
                <div class="detail-item">
                    <span class="detail-label">Wind</span>
                    <span class="detail-value" id="weatherWind">-- mph</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Humidity</span>
                    <span class="detail-value" id="weatherHumidity">--%</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Feels like</span>
                    <span class="detail-value" id="weatherFeelsLike">--°F</span>
                </div>
            </div>
            <div class="availability-info" id="availabilityInfo" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.2); font-size: 0.9rem; text-align: center;">
                <div id="weatherAvailability" style="font-style: italic;">Checking availability...</div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <h3 style="font-family: var(--serif); font-size: 1.6rem; margin: 2rem 0 1rem; color: var(--dark);">Recent Bookings</h3>
    <div class="recent-bookings">
        <table>
            <thead><tr><th>ID</th><th>Customer</th><th>Event</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                <?php if (empty($bookings_array)): ?>
                    <tr><td colspan="6" style="text-align:center">No bookings yet</td></tr>
                <?php else: ?>
                    <?php foreach ($bookings_array as $booking): ?>
                        <tr>
                            <td data-label="ID">#<?= str_pad($booking['checkout_id'] ?? 0, 5, '0', STR_PAD_LEFT) ?></td>
                            <td data-label="Customer"><?= htmlspecialchars(($booking['first_name'] ?? '') . ' ' . ($booking['last_name'] ?? '')) ?></td>
                            <td data-label="Event"><?= htmlspecialchars($booking['event_name'] ?? 'Custom Event') ?></td>
                            <td data-label="Date"><?= !empty($booking['event_date']) ? date('M d, Y', strtotime($booking['event_date'])) : 'TBD' ?></td>
                            <td data-label="Amount">₱<?= number_format($booking['total_price'] ?? 0, 0) ?></td>
                            <td data-label="Status"><span class="status-badge status-<?= strtolower($booking['status'] ?? 'pending') ?>"><?= $booking['status'] ?? 'pending' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'admin-footer.php'; ?>

<script>
// Weather conversion helper
function celsiusToFahrenheit(celsius) {
    return (celsius * 9/5) + 32;
}

function kmhToMph(kmh) {
    return kmh * 0.621371;
}

// Update weather display with given data
function updateWeatherDisplay(weatherData) {
    console.log('Updating weather with data:', weatherData);
    
    const temp = weatherData.temp;
    const feelsLike = weatherData.feels_like;
    const humidity = weatherData.humidity;
    const windSpeed = weatherData.wind_speed;
    const weatherCode = weatherData.weather_code;
    
    // Convert to Fahrenheit and mph (like the Sydney widget)
    const tempF = Math.round(celsiusToFahrenheit(temp));
    const feelsLikeF = Math.round(celsiusToFahrenheit(feelsLike));
    const windMph = Math.round(kmhToMph(windSpeed) * 10) / 10; // Correct conversion
    
    // Update weather widget
    document.getElementById('weatherTemp').textContent = tempF + '°F';
    document.getElementById('weatherFeelsLike').textContent = feelsLikeF + '°F';
    document.getElementById('weatherHumidity').textContent = humidity + '%';
    document.getElementById('weatherWind').textContent = windMph + ' mph';
    
    // Get weather condition and icon
    let condition = 'Clear';
    let icon = 'fa-sun';
    
    if (weatherCode === 0) { condition = 'Clear'; icon = 'fa-sun'; }
    else if (weatherCode === 1 || weatherCode === 2) { condition = 'Mostly Clear'; icon = 'fa-cloud-sun'; }
    else if (weatherCode === 3) { condition = 'Overcast'; icon = 'fa-cloud'; }
    else if (weatherCode === 45 || weatherCode === 48) { condition = 'Foggy'; icon = 'fa-smog'; }
    else if (weatherCode >= 51 && weatherCode <= 67) { condition = 'Light Intensity Shower Rain'; icon = 'fa-cloud-rain'; }
    else if (weatherCode >= 80 && weatherCode <= 82) { condition = 'Rainy'; icon = 'fa-cloud-showers-heavy'; }
    else if (weatherCode >= 85 && weatherCode <= 86) { condition = 'Snow'; icon = 'fa-snowflake'; }
    else if (weatherCode >= 80 && weatherCode <= 99) { condition = 'Thunderstorm'; icon = 'fa-bolt'; }
    
    document.getElementById('weatherCondition').textContent = condition;
    document.querySelector('.weather-widget .weather-icon i').className = `fas ${icon}`;
    
    console.log('Weather updated:', { tempF, feelsLikeF, windMph, condition });
}

// Load weather for specific date - using same API as user side
function fetchWeatherForDate(dateStr) {
    console.log('Fetching weather for date:', dateStr);
    
    fetch(`<?= BASE_URL ?>/api-weather.php?action=getForecast&date=${dateStr}`, {
      credentials: 'same-origin'
    })
      .then(response => response.json())
      .then(data => {
        console.log('Weather API response:', data);
        
        if (data.success) {
          // Update weather widget with API response
          document.getElementById('weatherCondition').textContent = data.condition;
          document.getElementById('weatherTemp').textContent = `${data.tempMin}°C - ${data.tempMax}°C`;
          
          // Update availability info
          const availElement = document.getElementById('weatherAvailability');
          availElement.textContent = data.availability.message;
          availElement.style.color = data.availability.color;
          availElement.style.display = 'block';
          
          // Update weather icon
          const iconElement = document.querySelector('.weather-widget .weather-icon i');
          if (data.icon) {
            iconElement.textContent = data.icon;
            iconElement.style.fontSize = '2.5rem';
          }
          
          console.log('Weather updated successfully');
        } else {
          console.log('API returned no success or error:', data);
          loadCurrentWeather();
        }
      })
      .catch(error => {
        console.error('Weather fetch error:', error);
        loadCurrentWeather();
      });
}

// Load current weather from Open-Meteo API
async function loadCurrentWeather() {
    try {
        console.log('Loading current weather...');
        const response = await fetch(
            'https://api.open-meteo.com/v1/forecast?latitude=14.5994&longitude=120.9842&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,apparent_temperature&temperature_unit=celsius&timezone=Asia/Manila'
        );
        const data = await response.json();
        const current = data.current;
        
        console.log('Current weather data:', current);
        
        updateWeatherDisplay({
            temp: current.temperature_2m,
            feels_like: current.apparent_temperature,
            humidity: current.relative_humidity_2m,
            wind_speed: current.wind_speed_10m,
            weather_code: current.weather_code
        });
    } catch (error) {
        console.error('Current weather API error:', error);
        document.getElementById('weatherCondition').textContent = 'Unable to load weather';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing admin dashboard...');
    
    // Load current weather on page load
    loadCurrentWeather();
    // Refresh weather every 30 minutes
    setInterval(loadCurrentWeather, 30 * 60 * 1000);
    
    const calendarEl = document.getElementById('adminEventCalendar');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        selectable: false,
        dateClick: function(info) {
            console.log('Date clicked:', info.dateStr);
            // Load weather when a day is clicked
            fetchWeatherForDate(info.dateStr);
        },
        events: function(info, successCallback, failureCallback) {
            console.log('Fetching calendar events...');
            fetch('<?= BASE_URL ?>/api-calendar.php?action=getAll', {
              credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Calendar events loaded:', data);
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error loading events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            console.log('Event clicked:', info.event);
            
            const event = info.event;
            const props = event.extendedProps;
            
            // Load weather for the event date
            const eventDate = event.start ? event.start.toISOString().split('T')[0] : null;
            console.log('Event date:', eventDate);
            if (eventDate) {
                fetchWeatherForDate(eventDate);
            }
            
            let detailsHTML = `
                <div class="event-details-popup">
                    <h4>${event.title}</h4>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value" style="text-transform: capitalize;">${props.status}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Customer:</span>
                        <span class="detail-value">${props.customer}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${props.email}</span>
                    </div>
                    ${props.time ? `<div class="detail-row"><span class="detail-label">Time:</span><span class="detail-value">${props.time}</span></div>` : ''}
                    ${props.venue ? `<div class="detail-row"><span class="detail-label">Venue:</span><span class="detail-value">${props.venue}</span></div>` : ''}
                    ${props.price ? `<div class="detail-row"><span class="detail-label">Amount:</span><span class="detail-value">₱${new Intl.NumberFormat('en-PH').format(props.price)}</span></div>` : ''}
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #E2D9C8;">
                        <a href="/index.php?route=admin-bookings" class="btn" style="display: inline-block; background: #8A7650; color: white; padding: 0.6rem 1.2rem; border-radius: 8px; text-decoration: none; font-size: 0.85rem; width: 100%; text-align: center;">View Details</a>
                    </div>
                </div>
            `;
            
            // Create popup wrapper
            const popupWrapper = document.createElement('div');
            popupWrapper.style.position = 'fixed';
            popupWrapper.style.zIndex = '99999';
            popupWrapper.style.pointerEvents = 'all';
            popupWrapper.innerHTML = detailsHTML;
            
            // Get calendar element position
            const calendarEl = document.getElementById('adminEventCalendar');
            const calendarRect = calendarEl.getBoundingClientRect();
            const clickRect = info.jsEvent.target.getBoundingClientRect();
            
            // Position popup next to the calendar, not too far
            let top = Math.max(100, clickRect.top - 50);
            let left = clickRect.right + 20;
            
            // If popup would go off-screen to the right, position to the left instead
            if (left + 380 > window.innerWidth) {
                left = clickRect.left - 400;
            }
            
            // Keep popup within viewport vertically
            if (top + 400 > window.innerHeight) {
                top = window.innerHeight - 420;
            }
            
            popupWrapper.style.top = top + 'px';
            popupWrapper.style.left = Math.max(10, left) + 'px';
            
            document.body.appendChild(popupWrapper);
            
            console.log('Popup created at:', { top, left });
            
            // Remove popup on click outside
            setTimeout(() => {
                document.addEventListener('click', function removePopup(e) {
                    if (!popupWrapper.contains(e.target)) {
                        if (popupWrapper.parentNode) {
                            popupWrapper.parentNode.removeChild(popupWrapper);
                        }
                        document.removeEventListener('click', removePopup);
                    }
                });
            }, 100);
        },
        datesSet: function(info) {
            console.log('Calendar view changed');
        }
    });
    
    calendar.render();
    console.log('Calendar rendered');
});
</script>