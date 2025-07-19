<?php
echo "=== PATCH TEST START ===\n";

require_once __DIR__ . '/../../../wp-load.php';

// Adjust this if needed:
require_once __DIR__ . '/../oaxpire-all-in-one-wp-migration-patch.php';

$current_version = '3.01';
$backup_file = __DIR__ . '/../backup_version.oxp';
$plugin_file = WP_PLUGIN_DIR . '/all-in-one-wp-migration/lib/model/class-ai1wm-extensions.php';

echo "\n== Running patch 3 times ==\n";
for ($i = 0; $i < 3; $i++) {
    echo "Patch run #$i...\n";
    $result = edit_ai1wm_plugin_file($current_version);
    var_dump($result);

    // Check what 'requires' is now
    $contents = file_get_contents($plugin_file);
    if (preg_match("/'requires'\s*=>\s*'([^']+)'/", $contents, $matches)) {
        echo "Current requires value: " . $matches[1] . "\n";
    } else {
        echo "Could not find 'requires' in plugin file.\n";
    }
    sleep(1);
}

echo "\n== Running restore 3 times ==\n";
if (file_exists($backup_file)) {
    $old_version = trim(file_get_contents($backup_file));
    for ($i = 0; $i < 3; $i++) {
        echo "Restore run #$i...\n";
        $result = edit_ai1wm_plugin_file($old_version);
        var_dump($result);

        // Check what 'requires' is now
        $contents = file_get_contents($plugin_file);
        if (preg_match("/'requires'\s*=>\s*'([^']+)'/", $contents, $matches)) {
            echo "Current requires value: " . $matches[1] . "\n";
        } else {
            echo "Could not find 'requires' in plugin file.\n";
        }
        sleep(1);
    }
} else {
    echo "Backup file not found. Skipping restore tests.\n";
}

echo "\n=== PATCH TEST DONE ===\n";
