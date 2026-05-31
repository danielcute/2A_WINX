<?php
/**
 * Weather API Integration
 * Provides weather forecast for event dates
 * Using Open-Meteo API (free, no API key required)
 */

// Prevent PHP from outputting HTML errors/warnings that break JSON parsing
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Ensure JSON header is always sent
header('Content-Type: application/json; charset=utf-8');

// Catch fatal errors and return JSON
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) { http_response_code(500); }
        if (ob_get_length() === 0 || strpos(ob_get_contents(), '{') === false) { ob_clean(); echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e['message']]); }
    }
});

session_start();

$action = $_GET['action'] ?? 'getForecast';
$date = $_GET['date'] ?? date('Y-m-d');

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['error' => 'Invalid date format']);
    exit;
}

if ($action === 'getForecast') {
    getWeatherForecast($date);
} else {
    echo json_encode(['error' => 'Unknown action']);
}

function getWeatherForecast($date) {
    // Default location: Manila, Philippines (latitude: 14.5995, longitude: 120.9842)
    $latitude = 14.5995;
    $longitude = 120.9842;
    
    // Parse the date
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if ($dateObj === false) {
        echo json_encode(['error' => 'Invalid date']);
        exit;
    }
    
    // Check if date is in the past (allow today)
    $today = new DateTime();
    if ($dateObj < $today->modify('-1 day')) {
        echo json_encode(['error' => 'Cannot forecast past dates']);
        exit;
    }
    
    // Use Open-Meteo API (free weather API)
    $url = "https://api.open-meteo.com/v1/forecast?" . http_build_query([
        'latitude' => $latitude,
        'longitude' => $longitude,
        'start_date' => $date,
        'end_date' => $date,
        'hourly' => 'temperature_2m,relative_humidity_2m,weather_code,cloud_cover',
        'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max',
        'temperature_unit' => 'celsius',
        'wind_speed_unit' => 'kmh',
        'timezone' => 'Asia/Manila'
    ]);
    
    // Cache the result for 6 hours to reduce API calls
    $cacheKey = 'weather_' . $date;
    $cacheDir = sys_get_temp_dir();
    $cacheFile = $cacheDir . '/' . $cacheKey;
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 21600)) {
        $cachedData = file_get_contents($cacheFile);
        echo $cachedData;
        exit;
    }
    
    // Fetch from API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo json_encode(['error' => 'Weather service unavailable', 'fallback' => true]);
        exit;
    }
    
    $data = json_decode($response, true);
    
    if (!$data || !isset($data['daily'])) {
        echo json_encode(['error' => 'Invalid weather data']);
        exit;
    }
    
    // Parse weather data
    $weather = parseWeatherData($data, $date);
    
    // Cache the result
    file_put_contents($cacheFile, json_encode($weather));
    
    echo json_encode($weather);
}

function parseWeatherData($data, $date) {
    $daily = $data['daily'];
    $hourly = $data['hourly'];
    
    if (empty($daily['time']) || empty($daily['time'][0])) {
        return ['error' => 'No weather data available'];
    }
    
    $weatherCode = $daily['weather_code'][0];
    $tempMax = $daily['temperature_2m_max'][0];
    $tempMin = $daily['temperature_2m_min'][0];
    $precipitation = $daily['precipitation_sum'][0] ?? 0;
    $windSpeed = $daily['wind_speed_10m_max'][0] ?? 0;
    
    // Convert WMO weather code to description
    $condition = getWeatherCondition($weatherCode);
    $icon = getWeatherIcon($weatherCode);
    
    // Get hourly data for the date
    $hourlyData = [];
    if (isset($hourly['time']) && isset($hourly['temperature_2m'])) {
        foreach ($hourly['time'] as $idx => $time) {
            if (strpos($time, $date) === 0) {
                $hour = substr($time, 11, 2);
                $hourlyData[] = [
                    'hour' => (int)$hour,
                    'temperature' => $hourly['temperature_2m'][$idx],
                    'humidity' => $hourly['relative_humidity_2m'][$idx] ?? 0,
                    'cloud_cover' => $hourly['cloud_cover'][$idx] ?? 0
                ];
            }
        }
    }
    
    return [
        'success' => true,
        'date' => $date,
        'condition' => $condition,
        'icon' => $icon,
        'tempMax' => round($tempMax, 1),
        'tempMin' => round($tempMin, 1),
        'precipitation' => round($precipitation, 1),
        'windSpeed' => round($windSpeed, 1),
        'weatherCode' => $weatherCode,
        'hourly' => array_slice($hourlyData, 6, 18), // Business hours: 6 AM to 12 AM
        'availability' => getEventAvailability($condition)
    ];
}

function getWeatherCondition($code) {
    // WMO Weather interpretation codes
    $conditions = [
        0 => 'Clear sky',
        1 => 'Mainly clear',
        2 => 'Partly cloudy',
        3 => 'Overcast',
        45 => 'Foggy',
        48 => 'Depositing rime fog',
        51 => 'Light drizzle',
        53 => 'Moderate drizzle',
        55 => 'Dense drizzle',
        61 => 'Slight rain',
        63 => 'Moderate rain',
        65 => 'Heavy rain',
        71 => 'Slight snow',
        73 => 'Moderate snow',
        75 => 'Heavy snow',
        77 => 'Snow grains',
        80 => 'Slight rain showers',
        81 => 'Moderate rain showers',
        82 => 'Violent rain showers',
        85 => 'Slight snow showers',
        86 => 'Heavy snow showers',
        95 => 'Thunderstorm',
        96 => 'Thunderstorm with slight hail',
        99 => 'Thunderstorm with heavy hail'
    ];
    
    return $conditions[$code] ?? 'Unknown';
}

function getWeatherIcon($code) {
    // Return emoji or icon based on weather code
    $icons = [
        0 => '☀️',
        1 => '🌤️',
        2 => '⛅',
        3 => '☁️',
        45 => '🌫️',
        48 => '🌫️',
        51 => '🌦️',
        53 => '🌦️',
        55 => '🌧️',
        61 => '🌧️',
        63 => '🌧️',
        65 => '⛈️',
        71 => '🌨️',
        73 => '🌨️',
        75 => '❄️',
        77 => '🌨️',
        80 => '🌧️',
        81 => '⛈️',
        82 => '⛈️',
        85 => '🌨️',
        86 => '❄️',
        95 => '⛈️',
        96 => '⛈️',
        99 => '⛈️'
    ];
    
    return $icons[$code] ?? '🌡️';
}

function getEventAvailability($condition) {
    // Determine if it's good for outdoor events
    $poorWeather = [
        'Heavy rain',
        'Violent rain showers',
        'Heavy snow',
        'Heavy snow showers',
        'Thunderstorm',
        'Thunderstorm with slight hail',
        'Thunderstorm with heavy hail',
        'Dense drizzle'
    ];
    
    $marginally = [
        'Moderate rain',
        'Moderate rain showers',
        'Slight snow',
        'Moderate snow',
        'Slight rain'
    ];
    
    if (in_array($condition, $poorWeather)) {
        return ['status' => 'poor', 'message' => 'Weather may affect events', 'color' => '#ff6b6b'];
    } elseif (in_array($condition, $marginally)) {
        return ['status' => 'fair', 'message' => 'Acceptable weather', 'color' => '#ffd93d'];
    } else {
        return ['status' => 'good', 'message' => 'Good weather for events', 'color' => '#51cf66'];
    }
}
?>
