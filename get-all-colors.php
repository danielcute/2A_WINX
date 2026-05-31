<?php
$conn = new mysqli('localhost', 'root', '', 'sinta_db');

// Get all distinct colors from existing color combinations
$result = $conn->query("SELECT DISTINCT colors_json FROM customization_options_tbl WHERE category = 'Color Combinations' AND colors_json IS NOT NULL ORDER BY option_id");

$allColors = [];
while ($row = $result->fetch_assoc()) {
    $colors = json_decode($row['colors_json'], true);
    if (is_array($colors)) {
        $allColors = array_merge($allColors, $colors);
    }
}

$allColors = array_unique($allColors);
sort($allColors);

echo "All colors found: " . count($allColors) . "\n";
echo json_encode($allColors) . "\n";

// Now update the "Other" option
$colorsJson = json_encode($allColors);
$updateQuery = "UPDATE customization_options_tbl SET colors_json = ? WHERE name = 'Other' AND category = 'Color Combinations'";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param('s', $colorsJson);
$stmt->execute();

echo "Updated 'Other' option with " . count($allColors) . " colors\n";
$stmt->close();
$conn->close();
?>
