<?php
/**
 * Script to quickly populate or reset occasions with images
 * Reads images from assets/img and stores as BLOB in database
 */
require_once dirname(__FILE__) . '/../config/database.php';

$db = Database::getInstance()->getConnection();

// First, clear existing occasions (optional - comment out to keep)
// $db->query("DELETE FROM occasions_tbl");

echo "<h2>Populating Occasions with Images</h2>";
echo "<hr>";

// Define occasions with their image files
$occasions = [
    ['name' => 'Wedding', 'image_file' => 'wedding.jpg', 'description' => 'Full-service planning from intimate to grand'],
    ['name' => 'Birthday', 'image_file' => 'birthday.jpg', 'description' => 'Memorable celebrations for all ages'],
    ['name' => 'Debut', 'image_file' => 'debut.jpg', 'description' => 'Celebrate 18th birthday in elegant style'],
    ['name' => 'Corporate', 'image_file' => 'corporate.jpg', 'description' => 'Professional galas, conferences, and launches'],
    ['name' => 'Anniversary', 'image_file' => 'anniversary.jpg', 'description' => 'Celebrate years of love and commitment'],
];

$success_count = 0;
$fail_count = 0;

foreach ($occasions as $occ) {
    $image_path = dirname(__FILE__) . '/assets/img/' . $occ['image_file'];
    
    if (!file_exists($image_path)) {
        echo "<span style='color: red;'>✗ Image not found: " . $occ['image_file'] . " (checked at: " . $image_path . ")</span><br>";
        $fail_count++;
        continue;
    }
    
    echo "Processing " . $occ['name'] . "... ";
    
    // Check if occasion already exists
    $check = $db->prepare("SELECT occasion_id FROM occasions_tbl WHERE events = ?");
    $check->bind_param("s", $occ['name']);
    $check->execute();
    $result = $check->get_result();
    
    $image_data = file_get_contents($image_path);
    $image_size = strlen($image_data);
    
    if ($result->num_rows > 0) {
        // Update existing occasion with image
        $row = $result->fetch_assoc();
        $occasion_id = $row['occasion_id'];
        
        $stmt = $db->prepare("UPDATE occasions_tbl SET image = ?, image_name = ? WHERE occasion_id = ?");
        $stmt->bind_param("bsi", $image_data, $occ['image_file'], $occasion_id);
        
        if ($stmt->execute()) {
            echo "<span style='color: green;'>✓ Updated with image (" . round($image_size/1024) . " KB)</span><br>";
            $success_count++;
        } else {
            echo "<span style='color: red;'>✗ Failed: " . $stmt->error . "</span><br>";
            $fail_count++;
        }
        $stmt->close();
    } else {
        // Create new occasion with image
        $stmt = $db->prepare("INSERT INTO occasions_tbl (events, descriptions, image, image_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssbs", $occ['name'], $occ['description'], $image_data, $occ['image_file']);
        
        if ($stmt->execute()) {
            echo "<span style='color: green;'>✓ Created with image (" . round($image_size/1024) . " KB)</span><br>";
            $success_count++;
        } else {
            echo "<span style='color: red;'>✗ Failed: " . $stmt->error . "</span><br>";
            $fail_count++;
        }
        $stmt->close();
    }
    
    $check->close();
}

echo "<hr>";
echo "<p><strong>Summary:</strong> $success_count success, $fail_count failed</p>";

if ($success_count > 0) {
    echo "<p style='color: green;'><strong>✓ Done! Occasions are now in the database with images.</strong></p>";
    echo "<p><a href='/SINTA/public/index.php?route=occasions' style='color: #8A7650; font-weight: bold;'>View Occasions →</a></p>";
    echo "<p><a href='/SINTA/public/index.php?route=admin-occasions' style='color: #8A7650; font-weight: bold;'>View Admin Occasions →</a></p>";
} else {
    echo "<p style='color: red;'><strong>⚠ No occasions were populated. Check image paths above.</strong></p>";
}
?>
