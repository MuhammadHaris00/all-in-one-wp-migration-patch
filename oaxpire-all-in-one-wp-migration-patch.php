<?php

/*
 * Plugin Name:       Oaxpire - All in One WP Migration Patch
 * Plugin URI:        #
 * Description:       Allows old versions of Unlimited Extention for All in One WP Migrations with updated versions of the plugin.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Muhammad Haris
 * Author URI:        #
 * License:           None
 * License URI:       None
 * Update URI:        #
 * Text Domain:       oaxpire-all-in-one-wp-migration-patch
 * Domain Path:       /languages
 * Requires Plugins:  all-in-one-wp-migration,all-in-one-wp-migration-unlimited-extension
 */


// define('WP_PLUGIN_DIR', __DIR__ .'/wp-content/plugins');

define('OXP_AI1WM_VERSION_BACKUP_FILENAME','/backup_version.oxp');
define('OXP_PLUGIN_T_FILE', 'all-in-one-wp-migration/all-in-one-wp-migration.php');
define('OXP_PLUGIN_MODEL_FILE','/all-in-one-wp-migration/lib/model/class-ai1wm-extensions.php');
define('OXP_EXT_FILE','all-in-one-wp-migration-unlimited-extension/all-in-one-wp-migration-unlimited-extension.php');

function oxp_activate(){
    patch_ai1wm();
}


function patch_ai1wm($remove=false){
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
   function get_unlimited_extension_required_version() {

    // Check if main plugin is active
    if ( is_plugin_active( OXP_PLUGIN_T_FILE ) ) {
        $file = WP_PLUGIN_DIR . OXP_PLUGIN_MODEL_FILE;
        if ( file_exists( $file ) ) {
            require_once $file;

            if ( class_exists( 'Ai1wm_Extensions' ) ) {
                $extensions = Ai1wm_Extensions::get();

                // The Unlimited Extension uses the constant AI1WMUE_PLUGIN_NAME as the key
                if ( defined( 'AI1WMUE_PLUGIN_NAME' ) && isset( $extensions[ AI1WMUE_PLUGIN_NAME ] ) ) {
                    return $extensions[ AI1WMUE_PLUGIN_NAME ]['requires'];
                }
            }
        }
    }
    else{
        error_log("AI1WM Plguin is not active.");
    }

    return null;
    }



    // Get the Unlimied Extention Version
    function get_unlimited_extention_current_version(){
        if(defined('WP_PLUGIN_DIR')){
            $plugin_ext_dir = OXP_EXT_FILE; 

            if( is_plugin_active( $plugin_ext_dir )){
                $plugin_ext_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_ext_dir );
                return $plugin_ext_data['Version'];
                // return "test";
            }
            else{
                error_log("The Unlimited Extention may not be installed or active.");
            }

        }
        else{
            error_log("Plugin DIR Not Set.");
        }
    }

    function edit_ai1wm_plugin_file($old_version, $new_version) {
    $model_file = WP_PLUGIN_DIR . OXP_PLUGIN_MODEL_FILE;

    $backup_file = fopen(__DIR__ . OXP_AI1WM_VERSION_BACKUP_FILENAME, 'w');
    fwrite($backup_file, $old_version);
    fclose($backup_file);


    if (file_exists($model_file)) {
        $contents = file_get_contents($model_file);

        if ($contents === false) {
            error_log("[PATCH] Could not read file.");
            return false;
        }

        // Replace the requires value *for Unlimited Extension only*:
        // Find the block that starts with AI1WMUE_PLUGIN_NAME ... 'requires' => 'old',
        // and replace the old version with $cur_version

        $pattern = "/(AI1WMUE_PLUGIN_NAME.*?'requires'\s*=>\s*')[^']+(')/s";

        $replaced = preg_replace($pattern, '${1}' . $new_version . '$2', $contents, 1);

        if ($replaced !== null && $replaced !== $contents) {
            $result = file_put_contents($model_file, $replaced);
            if ($result !== false) {
                error_log("[PATCH] Successfully set Unlimited Extension requires => {$new_version}");
                return true;
            } else {
                error_log("[PATCH] Failed to write updated file.");
                return false;
            }
        } else {
            error_log("[PATCH] Pattern not found or nothing replaced.");
            return false;
        }

    } else {
        error_log("[PATCH] The model file does not exist: $model_file");
        return false;
    }
}



    // Usage:
    $required_version = get_unlimited_extension_required_version();
    $current_version = get_unlimited_extention_current_version();
    
    if (!$remove) {
        edit_ai1wm_plugin_file($required_version,$current_version);
    } else {
        $backup_file = __DIR__ . OXP_AI1WM_VERSION_BACKUP_FILENAME;
        if (file_exists($backup_file)) {
            $old_version = trim(file_get_contents($backup_file)); // get the backed up version string
            if ($old_version) {
                edit_ai1wm_plugin_file($required_version,$old_version);
                error_log("Reverted the Patch.");
            } else {
                error_log("[PATCH] Backup file is empty, cannot restore old version.");
            }
        } else {
            error_log("[PATCH] Backup file does not exist, cannot restore old version.");
        }
    }


    error_log( 'Unlimited Extension requires plugin version: ' . $required_version );
    error_log("The Current Unlimietd Extention Version is : ". $current_version);

    return true;
}


function oxp_deactivate(){
    patch_ai1wm(true);
}


add_action('upgrader_process_complete', function($upgrader, $hook_extra) {
    // Check if a plugin was updated
    if ( isset($hook_extra['plugins']) && isset($hook_extra['action']) && $hook_extra['action'] === 'update' ) {
        foreach ( $hook_extra['plugins'] as $plugin ) {
            if ( $plugin === OXP_PLUGIN_T_FILE || $plugin === OXP_EXT_FILE) {
                // Plugin was updated - apply patch
                patch_ai1wm();
            }
        }
    }
}, 10, 2);



register_activation_hook(
    __FILE__,
    'oxp_activate'
);

register_deactivation_hook(
    __FILE__,
    'oxp_deactivate'
);

?>