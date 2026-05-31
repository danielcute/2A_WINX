<?php
/**
 * SINTA - Local Setup Diagnostic
 * Place this file at: /SINTA/public/diagnostic.php
 * Visit: http://sinta.localhost/public/diagnostic.php
 */

echo "<h1>SINTA Setup Diagnostic</h1>";
echo "<style>
    body { font-family: Arial; margin: 20px; }
    .ok { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .section { margin: 20px 0; padding: 10px; border: 1px solid #ddd; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #f0f0f0; }
</style>";

$root = dirname(__DIR__);

// 1. Check critical files
echo "<div class='section'>";
echo "<h2>1. Critical Files Check</h2>";
$files = [
    'public/index.php' => $root . '/public/index.php',
    'config/database.php' => $root . '/config/database.php',
    'app/models/User.php' => $root . '/app/models/User.php',
    'app/views/landing/landing.php' => $root . '/app/views/landing/landing.php',
    'app/views/user/signin.php' => $root . '/app/views/user/signin.php',
];

echo "<table><tr><th>File</th><th>Status</th></tr>";
foreach ($files as $name => $path) {
    $exists = file_exists($path);
    $status = $exists ? '<span class="ok">✓ EXISTS</span>' : '<span class="error">✗ MISSING</span>';
    echo "<tr><td>$name</td><td>$status</td></tr>";
}
echo "</table>";
echo "</div>";

// 2. Check CSS files
echo "<div class='section'>";
echo "<h2>2. CSS Files Check</h2>";
$cssDir = $root . '/public/assets/css';
$cssFiles = glob($cssDir . '/*.css');
echo "<p>Found " . count($cssFiles) . " CSS files:</p>";
echo "<ul>";
foreach ($cssFiles as $css) {
    echo "<li>" . basename($css) . "</li>";
}
echo "</ul>";
echo "</div>";

// 3. Check JS files
echo "<div class='section'>";
echo "<h2>3. JavaScript Files Check</h2>";
$jsDir = $root . '/public/assets/js';
$jsFiles = glob($jsDir . '/*.js');
echo "<p>Found " . count($jsFiles) . " JS files:</p>";
echo "<ul>";
foreach ($jsFiles as $js) {
    echo "<li>" . basename($js) . "</li>";
}
echo "</ul>";
echo "</div>";

// 4. Check for problematic files
echo "<div class='section'>";
echo "<h2>4. Check for Problematic Files</h2>";
$badFiles = [
    'default.php',
    'fix-paths.php',
];
echo "<table><tr><th>File</th><th>Status</th></tr>";
foreach ($badFiles as $file) {
    $path = $root . '/' . $file;
    $exists = file_exists($path);
    $status = $exists ? '<span class="error">✗ EXISTS (DELETE IT!)</span>' : '<span class="ok">✓ OK</span>';
    echo "<tr><td>$file</td><td>$status</td></tr>";
}
echo "</table>";
echo "</div>";

// 5. Database connection test
echo "<div class='section'>";
echo "<h2>5. Database Connection Test</h2>";
require_once $root . '/config/database.php';
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    if ($conn->ping()) {
        echo '<p class="ok">✓ Database connection successful</p>';
    } else {
        echo '<p class="error">✗ Database connection failed</p>';
    }
} catch (Exception $e) {
    echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
}
echo "</div>";

// 6. Session test
echo "<div class='section'>";
echo "<h2>6. Session Test</h2>";
echo '<p class="ok">✓ Session ID: ' . session_id() . '</p>';
echo '<p>Session data: ' . json_encode($_SESSION) . '</p>';
echo "</div>";

// 7. PATH constants
echo "<div class='section'>";
echo "<h2>7. Path Constants</h2>";
echo "<table>";
echo "<tr><th>Constant</th><th>Value</th></tr>";
echo "<tr><td>ROOT_PATH</td><td>" . $root . "</td></tr>";
echo "<tr><td>VIEW_PATH</td><td>" . $root . '/app/views/' . "</td></tr>";
echo "<tr><td>BASE_URL</td><td>/</td></tr>";
echo "</table>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Fix any ✗ errors above</li>";
echo "<li>Ensure all CSS files are present</li>";
echo "<li>Run this test again to verify</li>";
echo "<li>Upload ALL fixed files to Hostinger</li>";
echo "</ol>";
?>
