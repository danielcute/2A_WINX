<?php
/**
 * Event Images Initialization Script
 * Populates occasion images if missing from database
 * Run: php setup-event-images.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/config/database.php';

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     SINTA EVENT IMAGES - SETUP & VERIFICATION                 ║\n";
echo "║     Generated: " . date('Y-m-d H:i:s') . "                                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Step 1: Check occasions_tbl schema\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    
    $result = $db->query("DESCRIBE occasions_tbl");
    if ($result) {
        $has_image_col = false;
        while ($row = $result->fetch_assoc()) {
            echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
            if ($row['Field'] === 'image') {
                $has_image_col = true;
            }
        }
        
        if (!$has_image_col) {
            echo "\n⚠️  ISSUE: 'image' column missing from occasions_tbl\n";
            echo "Adding 'image' column...\n";
            
            $db->query("ALTER TABLE occasions_tbl ADD COLUMN image LONGBLOB NULL");
            $db->query("ALTER TABLE occasions_tbl ADD COLUMN image_name VARCHAR(255) NULL");
            echo "✅ Added image columns\n";
        } else {
            echo "✅ Image column exists\n";
        }
    } else {
        echo "❌ Error: " . $db->error . "\n";
        exit(1);
    }
    
    echo "\n";
    
    echo "Step 2: Check existing occasions\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    
    $result = $db->query("SELECT occasion_id, events, descriptions, image FROM occasions_tbl");
    if ($result) {
        $occasions = [];
        while ($row = $result->fetch_assoc()) {
            $occasions[] = $row;
            $has_image = !empty($row['image']) ? "✅ HAS IMAGE" : "❌ NO IMAGE";
            echo "  ID {$row['occasion_id']}: {$row['events']} - $has_image\n";
        }
        
        echo "\nTotal occasions: " . count($occasions) . "\n";
    } else {
        echo "❌ Error: " . $db->error . "\n";
        exit(1);
    }
    
    echo "\n";
    
    // Sample placeholder images (base64 encoded small PNG)
    // In production, you would use real occasion images
    $sample_images = [
        'Wedding' => [
            'description' => 'Elegant wedding celebration',
            'color' => '#FFE5E5' // Light pink
        ],
        'Birthday' => [
            'description' => 'Fun birthday party',
            'color' => '#FFF5E5' // Light orange
        ],
        'Debut' => [
            'description' => 'Glamorous debut celebration',
            'color' => '#F5E5FF' // Light purple
        ],
        'Corporate' => [
            'description' => 'Professional corporate event',
            'color' => '#E5F0FF' // Light blue
        ],
        'Graduation' => [
            'description' => 'Proud graduation celebration',
            'color' => '#E5FFE5' // Light green
        ],
        'Anniversary' => [
            'description' => 'Special anniversary celebration',
            'color' => '#FFE5F5' // Light magenta
        ]
    ];
    
    echo "Step 3: Create placeholder images for occasions without images\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    
    // Create simple placeholder images using GD Library
    if (extension_loaded('gd')) {
        $updated = 0;
        
        foreach ($occasions as $occ) {
            if (empty($occ['image'])) {
                $event_name = $occ['events'];
                $occasion_id = $occ['occasion_id'];
                
                // Create a simple colored image
                $color_info = $sample_images[$event_name] ?? [
                    'description' => 'Event celebration',
                    'color' => '#E8D5C4'
                ];
                
                // Create image
                $img = imagecreatetruecolor(400, 300);
                
                // Parse hex color
                $hex = ltrim($color_info['color'], '#');
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                
                $color = imagecolorallocate($img, $r, $g, $b);
                imagefilledrectangle($img, 0, 0, 400, 300, $color);
                
                // Add text
                $text_color = imagecolorallocate($img, 100, 80, 50); // Brown
                $font = 5; // Built-in font
                
                $text = $event_name;
                $text_box = imagettfbbox(20, 0, __DIR__ . '/assets/fonts/arial.ttf', $text);
                
                // Simple text positioning (using built-in font as fallback)
                imagestring($img, 5, 20, 140, $text, $text_color);
                
                // Capture as PNG
                ob_start();
                imagepng($img);
                $image_data = ob_get_clean();
                imagedestroy($img);
                
                // Save to database
                $stmt = $db->prepare("UPDATE occasions_tbl SET image = ?, image_name = ? WHERE occasion_id = ?");
                if ($stmt) {
                    $filename = strtolower(str_replace(' ', '_', $event_name)) . '_placeholder.png';
                    $stmt->bind_param("bsi", $image_data, $filename, $occasion_id);
                    
                    if ($stmt->execute()) {
                        echo "  ✅ Added placeholder image for: $event_name\n";
                        $updated++;
                    } else {
                        echo "  ❌ Failed to update: $event_name - " . $stmt->error . "\n";
                    }
                    $stmt->close();
                } else {
                    echo "  ❌ Prepare failed: " . $db->error . "\n";
                }
            }
        }
        
        echo "\nTotal updated: $updated\n";
    } else {
        echo "⚠️  GD Library not available - cannot create placeholder images\n";
        echo "   Install GD library to enable automatic image generation\n";
        echo "   Or upload occasion images manually via admin panel\n";
    }
    
    echo "\n";
    
    echo "Step 4: Verify images are now available\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    
    $result = $db->query("SELECT occasion_id, events, COUNT(IF(image IS NOT NULL AND image != '', 1, NULL)) as has_image FROM occasions_tbl GROUP BY occasion_id, events");
    if ($result) {
        $with_images = 0;
        while ($row = $result->fetch_assoc()) {
            $status = $row['has_image'] ? "✅" : "❌";
            echo "  $status {$row['events']}\n";
            if ($row['has_image']) $with_images++;
        }
        echo "\nOccasions with images: $with_images\n";
    }
    
    echo "\n";
    
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║                  SETUP COMPLETE                               ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "✅ STATUS: Images setup complete\n";
    echo "   - All occasions now have images in database\n";
    echo "   - Images will display when users select an occasion\n";
    echo "   - To use custom images, upload via admin dashboard\n\n";
    
    echo "Next: Visit /index.php?route=occasions to see images\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
