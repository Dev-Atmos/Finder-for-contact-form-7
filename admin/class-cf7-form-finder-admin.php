<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/Dev-Atmos/contact-form7-finder
 * @since      1.0.0
 *
 * @package    Cf7_Form_Finder
 * @subpackage Cf7_Form_Finder/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cf7_Form_Finder
 * @subpackage Cf7_Form_Finder/admin
 * @author     Dental Focus <info@test.com>
 */
class Cf7_Form_Finder_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cf7_Form_Finder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cf7_Form_Finder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/cf7-form-finder-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cf7_Form_Finder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cf7_Form_Finder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/cf7-form-finder-admin.js', array('jquery'), $this->version, false);
	}

	public function add_plugin_admin_menu()
	{
		add_menu_page(
			'CF7 Form Finder',
			'CF7 Form Finder',
			'manage_options',
			'cf7-form-finder',
			[$this, 'display_plugin_admin_page'],
			'dashicons-search',
			76 // Position below "Settings"
		);
	}
	public function display_plugin_admin_page()
	{
		include_once plugin_dir_path(__FILE__) . 'partials/cf7-form-finder-admin-display.php';
	}
	public function handle_csv_export()
	{
		if (!current_user_can('manage_options') || !check_admin_referer('cf7_form_finder_export')) {
			wp_die('Permission denied.');
		}


		$data = CF7_Form_Finder_Data::get_form_usage();

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=cf7-form-usage.csv');
		header('Pragma: no-cache');
		header('Expires: 0');

		$output = fopen('php://output', 'w');
		fputcsv($output, ['Post Title', 'Post Type', 'Builder', 'Form ID', 'Form Title', 'URL']);

		foreach ($data as $row) {
			fputcsv($output, [
				$row['title'],
				$row['type'],
				$row['builder'],
				$row['form_id'],
				$row['form_title'],
				$row['url']
			]);
		}

		fclose($output);
		exit;
	}

	/**
	 * Enqueue admin assets for the CF7 Form Finder plugin.
	 *
	 * This function is hooked to the admin_enqueue_scripts action and
	 * loads the necessary styles and scripts for the plugin's admin page.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_assets()
	{
		$screen = get_current_screen();
		if ($screen->id !== 'toplevel_page_cf7-form-finder') return;

		// DataTables CSS & JS
		wp_enqueue_style(
			'cf7ff-datatables-css',
			plugin_dir_url(__DIR__) . '/assets/css/jquery.dataTables.min.css',
			[],
			'1.13.6',
			'all'
		);
		// wp_enqueue_style('cf7ff-datatables-css', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css');

		// wp_enqueue_script('cf7ff-datatables-js', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', ['jquery'], null, true);
		wp_enqueue_script('cf7ff-datatables-js', plugin_dir_url(__DIR__) . 'assets/js/jquery.dataTables.min.js', ['jquery'], '1.13.6', true);

		// Our custom script
		wp_enqueue_script('cf7ff-admin-js', plugin_dir_url(__FILE__) . 'js/cf7ff-admin.js', ['cf7ff-datatables-js'], '1.13.6', true);
		wp_localize_script('cf7ff-admin-js', 'cf7ff_admin_params', [
			'nonce' => wp_create_nonce('cf7_form_finder_export'),
			'ajaxurl' => admin_url('admin-ajax.php')
		]);
	}
	/**
	 * Handles AJAX request to filter forms based on builder and form ID.
	 *
	 * @return void
	 */
	public function handle_ajax_filter()
	{
		// Verify nonce for security
		if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_key($_POST['nonce']), 'cf7_form_finder_export')) {
			wp_send_json_error(['message' => 'Invalid nonce']);
		}

		$builder = isset($_POST['builder']) ? sanitize_key($_POST['builder']) : '';
		$form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;


		$data = CF7_Form_Finder_Data::get_form_usage($builder, $form_id);

		// $data = CF7_Form_Finder_Data::get_form_usage($builder);

		// Return the filtered data as JSON for DataTables to update the table
		wp_send_json_success(['data' => $data]);
	}

	/**
	 * Handles AJAX request to get details of selected forms.
	 *
	 * @return void
	 */
	public function handle_get_details()
	{
		check_ajax_referer('cf7_form_finder_export', 'nonce');

		$form_ids = isset($_POST['form_ids']) ? array_map('absint', $_POST['form_ids']) : [];

		if (empty($form_ids)) {
			wp_send_json_error(['message' => 'No form IDs provided']);
		}

		$html = '';

		foreach ($form_ids as $fid) {
			$form_post = get_post($fid);

			if (!$form_post || $form_post->post_type !== 'wpcf7_contact_form') {
				continue;
			}

			$contact_form = WPCF7_ContactForm::get_instance($fid);
			$mail = $contact_form->prop('mail');

			$html .= '<div style="margin-bottom:20px;"><strong>Form ID:</strong> ' . esc_html($fid) . '<br>';
			$html .= '<strong>Title:</strong> ' . esc_html($form_post->post_title) . '<br><br>';

			$html .= '<strong>Mail Settings:</strong><ul>';
			$html .= '<li><strong>To:</strong> ' . esc_html($mail['recipient']) . '</li>';
			$html .= '<li><strong>From:</strong> ' . esc_html($mail['sender']) . '</li>';
			$html .= '<li><strong>Subject:</strong> ' . esc_html($mail['subject']) . '</li>';
			$html .= '<li><strong>Headers:</strong><br><pre>' . esc_html($mail['additional_headers']) . '</pre></li>';
			$html .= '<li><strong>Message Body:</strong><br><pre>' . esc_html($mail['body']) . '</pre></li>';
			$html .= '</ul></div>';
		}

		wp_send_json_success(['html' => $html]);
	}

	public function handle_csv_download()
	{
		if (! current_user_can('manage_options')) {
			wp_die('Permission denied.', 'Error', ['response' => 403]);
		}
		check_admin_referer('cf7_form_finder_export', 'nonce');

		$form_ids_raw = isset($_POST['form_ids']) ? sanitize_text_field(wp_unslash($_POST['form_ids'])) : ''; // Use wp_unslash before explode
		$form_ids_array = explode(',', $form_ids_raw);
		$form_ids = array_map('absint', $form_ids_array); // Ensure each ID is a non-negative integer
		$form_ids = array_filter($form_ids);
		if (empty($form_ids)) {
			wp_die('No form selected');
		}

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="cf7-form-usage.csv"');
		header('Pragma: no-cache'); // Add these for better cache control
		header('Expires: 0');       // for downloads

		$output = fopen('php://output', 'w');
		fputcsv($output, ['Form ID', 'Form Title']);

		foreach ($form_ids as $fid) {
			$form = get_post($fid); // get_post can handle integer or WP_Post object


			if ($form && is_a($form, 'WP_Post') && 'wpcf7_contact_form' === $form->post_type) {
				fputcsv($output, [$fid, $form->post_title]);
			} else {

				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log('CF7 Form Finder: Could not retrieve form for ID ' . $fid);
				}
			}
		}

		fclose($output);
		exit;
	}
}
