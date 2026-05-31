<?php
/**
 * Fix all  paths for subdomain deployment
 * Run once then delete
 */

function fixPathsInDirectory($dir) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $skip = ['vendor', 'node_modules', '.git'];
    $count = 0;
    
    foreach ($files as $file) {
        // Skip directories
        if ($file->isDir()) continue;
        
        // Skip if in skip list
        $path = $file->getPathname();
        $skip_this = false;
        foreach ($skip as $s) {
            if (strpos($path, $s) !== false) {
                $skip_this = true;
                break;
            }
        }
        if ($skip_this) continue;
        
        // Only process text files
        $ext = $file->getExtension();
        if (!in_array($ext, ['php', 'js', 'html', 'css', 'json'])) continue;
        
        // Read and replace
        $content = file_get_contents($path);
        $new_content = str_replace('', '', $content);
        
        if ($content !== $new_content) {
            file_put_contents($path, $new_content);
            echo "Fixed: {$path}<br>";
            $count++;
        }
    }
    
    return $count;
}

$dir = dirname(__FILE__);
$fixed = fixPathsInDirectory($dir);

echo "<h2>✓ Fixed {$fixed} files</h2>";
echo "<p>You can now delete this file (fix-paths.php)</p>";
?>
