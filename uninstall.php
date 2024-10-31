<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete wp_options if unistall plugin
delete_option ('seobot_monitor_ua');
delete_option ('seobot_monitor_bots_regex');
delete_option ('seobot_monitor_404title');
delete_option ('seobot_monitor_titleorigin');
