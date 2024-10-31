<?php

/**
 * Features for the options page in the WordPress backend
 *
 * @since      1.0.0
 * @package    seobot_Monitor
 * @subpackage seobot_Monitor/admin
 * @author     Santiago Alonso @salonsoweb <salonsoweb@gmail.com>
 */

/**
 * Add an option to WordPress menu
 * The seobot Monitor option is included as a subpage of the Tools menu.
 */
function seobot_monitor_admin_menu(){
	add_submenu_page( 
		'tools.php', 
		'Seobot Monitor', 
		'Seobot Monitor', 
		'manage_options', 
		'seobot_monitor',
		'seobot_monitor_display_page'
	);
}
add_action( 'admin_menu', 'seobot_monitor_admin_menu' );


/*
 * Displays the plugin options page
*/
function seobot_monitor_display_page(){
	current_user_can ('manage_options') or wp_die (__('Sorry, you are not allowed to access this page.'));
	require plugin_dir_path( __FILE__ ) . 'partials/seobot-monitor-admin-display.php';
}

/*
 * Register the plugin options for the wp_options table 
 */
function seobot_monitor_register_options() {

	$options = array('seobot_monitor_ua','seobot_monitor_bots_regex','seobot_monitor_404title','seobot_monitor_titleorigin');
	$args = array(
		'type' => 'string', 
		'sanitize_callback' => 'sanitize_text_field',
		'default' => NULL,
		);

	foreach ($options as $option){
		register_setting ('seobot_monitor-options', $option, $args);
	}
} 
add_action( 'admin_init', 'seobot_monitor_register_options' );