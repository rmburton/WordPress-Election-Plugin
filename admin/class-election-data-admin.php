<?php
/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://opendemocracymanitoba.ca/
 * @since      1.0.0
 *
 * @package    Election_Data
 * @subpackage Election_Data/admin
 */
/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Election_Data
 * @subpackage Election_Data/admin
 * @author     Your Name <email@example.com>
 */

class Election_Data_Admin {

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
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $current_screen;
		wp_enqueue_script( 'postbox' );
		if ( 'toplevel_page_election-data' == $current_screen->id ) {
			$script_name = 'ed_settings';
			$js_updates = Election_Data_Settings_Definition::get_js_updates();
			$localizations = $js_updates[0];
			$js_updates[1][] = 'jquery';
			$dependancies = array_unique( $js_updates[1] );
			
			wp_register_script( $script_name, plugin_dir_url( __FILE__ ) . 'js/settings.js', $dependancies ); 
			
			foreach ( $localizations as $local_var => $local_array ) {
				wp_localize_script( $script_name, $local_var, $local_array );
			}
			
			wp_enqueue_script( $script_name );
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		add_menu_page(
			__( 'Election Data', $this->plugin_name ),
			__( 'Election Data', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' )
			);

		$tabs = Election_Data_Settings_Definition::get_tabs();

		foreach ( $tabs as $tab_slug => $tab_title ) {

			add_submenu_page(
				$this->plugin_name,
				$tab_title,
				$tab_title,
				'manage_options',
				$this->plugin_name . '&tab=' . $tab_slug,
				array( $this, 'display_plugin_admin_page' )
				);
		}

		remove_submenu_page( $this->plugin_name, $this->plugin_name );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 * @return   array 			Action links
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', $this->plugin_name ) . '</a>'
				),
			$links
			);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {

		$tabs = Election_Data_Settings_Definition::get_tabs();

		$default_tab = Election_Data_Settings_Definition::get_default_tab_slug();

		$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], $tabs ) ? $_GET[ 'tab' ] : $default_tab;

		include_once( 'partials/' . $this->plugin_name . '-admin-display.php' );

	}
}
