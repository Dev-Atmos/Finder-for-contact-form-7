<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/Dev-Atmos/contact-form7-finder
 * @since             1.0.0
 * @package           Cf7_Finder
 *
 * @wordpress-plugin
 * Plugin Name:       CF7 Finder
 * Plugin URI:        https://github.com/Dev-Atmos/contact-form7-finder
 * Description:       Finds pages with Contact Form 7, showing forms used and page builder details.
 * Version:           1.0.0
 * Author:            Dev Atmos
 * Author URI:        https://devatmos.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf7-form-finder
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
define( 'CF7_FORM_FINDER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cf7-form-finder-activator.php
 */
function activate_cf7_form_finder() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-form-finder-activator.php';
	Cf7_Form_Finder_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cf7-form-finder-deactivator.php
 */
function deactivate_cf7_form_finder() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-form-finder-deactivator.php';
	Cf7_Form_Finder_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cf7_form_finder' );
register_deactivation_hook( __FILE__, 'deactivate_cf7_form_finder' );

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
 * @since    1.0.0
 */
function run_cf7_form_finder() {

	$plugin = new Cf7_Form_Finder();
	$plugin->run();

}
run_cf7_form_finder();
