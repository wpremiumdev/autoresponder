<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Autoresponder
 * Plugin URI:        http://localleadminer.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           2.0.4
 * Author:            mbj-webdevelopment
 * Author URI:        http://localleadminer.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       autoresponder
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!defined('AUTO_PLUGIN_URL')) {
    define('AUTO_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('AUTO_PLUGIN_DIR')) {
    define('AUTO_PLUGIN_DIR', dirname(__FILE__));
}
if (!defined('AUTO_PLUGIN_DIR_PATH')) {
    define('AUTO_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
}
if (!defined('AUTORESPONDER_LOG_DIR')) {
    $upload_dir = wp_upload_dir();
    define('AUTORESPONDER_LOG_DIR', $upload_dir['basedir'] . '/autoresponder-logs/');
}



/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-autoresponder-activator.php
 */
function activate_autoresponder() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-autoresponder-activator.php';
    Autoresponder_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-autoresponder-deactivator.php
 */
function deactivate_autoresponder() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-autoresponder-deactivator.php';
    Autoresponder_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_autoresponder');
register_deactivation_hook(__FILE__, 'deactivate_autoresponder');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-autoresponder.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_autoresponder() {

    $plugin = new Autoresponder();
    $plugin->run();
}
run_autoresponder();