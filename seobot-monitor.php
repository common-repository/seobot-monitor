<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       SEObot Monitor for Googlebot, Bingbot and search engine spiders
 * Plugin URI:        https://buenamanera.com
 * Description:       Dumps and analyzes server logs to Google Analytics, easily and automatically. This will allow you to detect SEO failures before it will be too late.
 * Version:           2.0.0
 * Author:            Buenamanera
 * Author URI:        https://profiles.wordpress.org/buenamanera/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       seobot-monitor
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'SEOBOT_MONITOR_VERSION', '2.0.0' );

/*
 * Create default option on activation hook action
 */
function seobot_monitor_activation()
{
	add_option('seobot_monitor_bots_regex', '/googlebot|bingbot/i', '', 'yes');
}
register_activation_hook(__FILE__,'seobot_monitor_activation');

/**
 * Include necesary proccess to track Googlebot with Google Analytics Measure Protocol
 * More info: https://developers.google.com/analytics/devguides/collection/protocol/v1
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seobot-monitor.php';


/**
 * Backend interface for admin plugin
 */
require plugin_dir_path( __FILE__ ) . 'admin/seobot-monitor-admin.php';


/**
 *	Launch monitor on wp_head() hook
 */
add_action( 'wp_head', ['Seobot_Monitor','track'], 10 );

/*
 *	Enqueue custom script
 */
add_action( 'wp_enqueue_scripts', ['Seobot_Monitor','enqueue_head_scripts'], 10 );

/*
 * AJAX frontend hooks
 */
add_action( 'wp_ajax_seobotmonitor_event_render', ['Seobot_Monitor', 'track_render_event'], 10 );
add_action( 'wp_ajax_nopriv_seobotmonitor_event_render', ['Seobot_Monitor', 'track_render_event'], 10 ); // need this to serve non logged in users