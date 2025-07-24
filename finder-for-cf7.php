<?php
defined('ABSPATH') || exit;
/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/Dev-Atmos/contact-form7-finder
 * @since             1.1.0
 * @package           Finder_for_CF7
 *
 * @wordpress-plugin
 * Plugin Name:       Finder for Contact Form 7
 * Plugin URI:        https://github.com/Dev-Atmos/Finder-for-contact-form-7
 * Requires Plugins: contact-form-7
 * Requires at least: 6.0
 * Description:       Finds pages with Contact Form 7, showing forms used and page builder details.
 * Version:           1.1.0
 * Author:            dipalak
 * Author URI:        https://devatmos.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       finder-for-cf7
 * Domain Path:       /languages
 */



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CF7FF_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cf7-form-finder-activator.php
 */
function cf7ff_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-form-finder-activator.php';
	cf7ff_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cf7-form-finder-deactivator.php
 */
function cf7ff_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-form-finder-deactivator.php';
	cf7ff_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'cf7ff_activate' );
register_deactivation_hook( __FILE__, 'cf7ff_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cf7-form-finder.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.1.0
 */
function run_cf7ff() {

	$plugin = new cf7ff();
	$plugin->run();

}
run_cf7ff();
