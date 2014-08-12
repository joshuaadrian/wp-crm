<?php

/*
Plugin Name: WP CRM
Plugin URI: https://github.com/joshuaadrian/wp-crm
Description: Create a CRM in WordPress
Author: Joshua Adrian
Version: 100.0.0
Author URI: https://github.com/joshuaadrian/
*/

if ( !function_exists('_log') ) {
  function _log( $message ) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log( print_r( $message, true ) );
      } else {
        error_log( $message );
      }
    }
  }
}

require_once plugin_dir_path( __FILE__ ) . '../../../wp-load.php';

if ( file_exists( ABSPATH . 'wp-config.php') ) {
	require_once( ABSPATH . 'wp-config.php' );
}

require_once(ABSPATH . 'wp-admin/includes/template.php' );

if( !class_exists( 'WP_Screen' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/screen.php' );
}

if ( !class_exists( 'WP_List_Table' ) ){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once( plugin_dir_path( __FILE__ ) . 'assets/classes/class-lead-list-table.php' );
$lead_list_table = new Lead_List_Table();
		$lead_list_table->prepare_items();

class wp_crm {

	public $wp_crm_db_version;
	public $wp_crm_options;
	public $lead_list_table;

	public function __construct() {
		$this->wp_crm_db_version = '1.0';
		$this->wp_crm_options = get_option( 'wp_crm_options' );
		define( 'WP_CRM_PATH', plugin_dir_path( __FILE__ ) ); // DEFINE PLUGIN BASE
		define( 'WP_CRM_URL_PATH', plugins_url() . '/wp-crm' ); // DEFINE PLUGIN URL
		define( 'WP_CRM_PLUGINOPTIONS_ID', 'wp-crm' ); // DEFINE PLUGIN ID
		define( 'WP_CRM_PLUGINOPTIONS_NICK', 'WP CRM' ); // DEFINE PLUGIN NICK
		register_activation_hook( __FILE__, array( $this, 'wp_crm_default_options' ) ); // ACTIVATION HOOK
		register_activation_hook( __FILE__, array( $this, 'wp_crm_create_database_tables' ) ); // ACTIVATION HOOK
		register_activation_hook( __FILE__, array( $this, 'wp_crm_seed_database_tables' ) ); // ACTIVATION HOOK
		register_uninstall_hook( __FILE__, array( $this, 'wp_crm_delete_options' ) ); // UNINSTALL HOOK
		register_uninstall_hook( __FILE__, array( $this, 'wp_crm_delete_database_tables' ) ); // UNINSTALL HOOK
		add_action( 'admin_init', array( $this, 'wp_crm_init' ) ); // ADD LINK TO ADMIN
		add_action( 'admin_menu', array( $this, 'wp_crm_add_options_page' ) ); // ADD LINK TO ADMIN
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_crm_enqueue' ) );
		add_filter( 'plugin_action_links', array( $this, 'wp_crm_plugin_action_links' ), 10, 2 ); // ADD LINK TO ADMIN
  }

  public function wp_crm_default_options() {

  	if ( get_option( 'wp_crm_options' ) === false ) {

  		$wp_crm_options_defaults = array(
  			'this' => 'that'
  		);

	    $this->wp_crm_options = add_option( 'wp_crm_options', $wp_crm_options_defaults );
		
		}

  }

  public function wp_crm_create_database_tables() {
  	
  	global $wpdb;

  	$tables = array(

  		$wpdb->prefix . 'crm_leads',
  		$wpdb->prefix . 'crm_lead_requests',
  		$wpdb->prefix . 'crm_lead_syncs',
  		$wpdb->prefix . 'crm_lead_backups'

  	);

  	foreach ( $tables as $table ) {

			$table_name      = $table;
			$charset_collate = '';

			if ( ! empty( $wpdb->charset ) ) {
			  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}

			if ( ! empty( $wpdb->collate ) ) {
			  $charset_collate .= " COLLATE {$wpdb->collate}";
			}

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				name tinytext NOT NULL,
				text text NOT NULL,
				url VARCHAR(55) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			add_option( 'wp_crm_db_version', $this->wp_crm_db_version );

		}

  }

  public function wp_crm_seed_database_tables() {
  	
  }

  public function wp_crm_delete_options() {
  	delete_option( 'wp_crm_options' );
  	delete_option( 'wp_crm_db_version' );
  }

  public function wp_crm_delete_database_tables() {
  	
  }

  public function wp_crm_init() {

		register_setting( 'wp_crm_plugin_options', 'wp_crm_options', 'wp_crm_validate_options' );

	}

	public function wp_crm_add_options_page() {
    add_menu_page( WP_CRM_PLUGINOPTIONS_NICK, 'Leads', 'manage_options', WP_CRM_PLUGINOPTIONS_ID, array( $this, 'wp_crm_render_lead_table' ), 'dashicons-groups', 100 );
    add_submenu_page( WP_CRM_PLUGINOPTIONS_ID, WP_CRM_PLUGINOPTIONS_NICK, 'Lead Syncs', 'manage_options', 'lead-syncs', array( $this, 'wp_crm_render_form' ) );
    add_submenu_page( WP_CRM_PLUGINOPTIONS_ID, WP_CRM_PLUGINOPTIONS_NICK, 'Lead API Requests', 'manage_options', 'lead-requests', array( $this, 'wp_crm_render_form' ) );
    add_submenu_page( WP_CRM_PLUGINOPTIONS_ID, WP_CRM_PLUGINOPTIONS_NICK, 'Lead Backups', 'manage_options', 'lead-backups', array( $this, 'wp_crm_render_form' ) );
    add_submenu_page( WP_CRM_PLUGINOPTIONS_ID, WP_CRM_PLUGINOPTIONS_NICK, 'WP CRM Settings', 'manage_options', 'wp-crm-settings', array( $this, 'wp_crm_render_form' ) );
	}

	public function wp_crm_render_form() {

	}

	public function wp_crm_render_lead_table() {

		echo '<div class="wrap"><h2>Leads	<a href="lead-new.php" class="add-new-h2">Add New</a></h2><form action="" method="get">';
		$lead_list_table = new Lead_List_Table();
		$lead_list_table->prepare_items();
		$lead_list_table->search_box( 'Search Leads' );
		$lead_list_table->display();
		echo '</form></div>';

	}



	public function wp_crm_validate_options( $input ) {

		return $input;

	}

	public function wp_crm_plugin_action_links( $links, $file ) {

		$tmp_id = WP_CRM_PLUGINOPTIONS_ID . '/wp-crm.php';

		if ( $file == $tmp_id ) {
			$wp_crm_links = '<a href="' . get_admin_url() . 'options-general.php?page=' . WP_CRM_PLUGINOPTIONS_ID . '">' . __('Settings') . '</a>';
			array_unshift( $links, $wp_crm_links );
		}

		return $links;

	}

	public function wp_crm_enqueue() {

	  wp_register_style('wp_crm_css', plugins_url('/assets/css/wp-crm-admin.css', __FILE__), false, '1.0.0');
	  wp_enqueue_style('wp_crm_css');
	  wp_enqueue_script('wp_crm_script', plugins_url('/assets/js/wp-crm-admin.min.js', __FILE__), array('jquery'));
	  wp_localize_script( 'wp_crm_script', 'wp_crm_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'value' => 1234 ) );

	}

}

$wp_crm = new wp_crm;