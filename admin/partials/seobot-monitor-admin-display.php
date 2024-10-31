<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://salonsoweb.es
 * @since      1.0.0
 *
 * @package    seobot_Monitor
 * @subpackage seobot_Monitor/admin/partials
 */
?>

<div class="wrap">
	<h2 class="opciones"><?php _e('Settings for SEObot Monitor', 'seobot-monitor'); ?></h2>
	<form method="post" action="options.php" id="opciones">

		<?php

			settings_fields ('seobot_monitor-options');
			do_settings_sections ('seobot_monitor-options');

			$ua   			= get_option ('seobot_monitor_ua', 'UA-XXXXXXXX');
			$bots_regex    	= get_option ('seobot_monitor_bots_regex', "");
			$title404 		= get_option ('seobot_monitor_404title', 'error 404');
			$titleorigin	= get_option ('seobot_monitor_titleorigin', 'wp');
			
		?>

		<table cellpadding="5">

			<tr>
				<td><?php echo __('Google Analytics UA tracking code', 'seobot-monitor');?>:</td>
				<td>
					<input type="text" name="seobot_monitor_ua" value="<?php echo $ua;?>">
				</td>
			</tr>
			<tr>
				<td><?php echo __('Page title origin', 'seobot-monitor');?>:</td>
				<td>
					<div class="container">
						<span class="select">
						<select name="seobot_monitor_titleorigin">
								<option value="yoast" <?php selected ('yoast', $titleorigin); ?> id="yoast">YOAST SEO plugin</option>
								<option value="wp" <?php selected ('wp', $titleorigin); ?>><?php echo __('Default WordPress page title', 'seobot-monitor');?></option>
							</select>
						</span>
					</div>
				</td>
			</tr>
			<tr>
				<td><?php echo __('Default 404 page title', 'seobot-monitor');?>:</td>
				<td>
					<input type="text" name="seobot_monitor_404title" value="<?php echo $title404;?>">
				</td>
			</tr>
			<tr>
				<td><?php echo __('Regex for bot user agent', 'seobot-monitor');?>:</td>
				<td>
					<input type="text" name="seobot_monitor_bots_regex" value="<?php echo $bots_regex;?>">
				</td>
			</tr>

		</table>

		<p><?php submit_button(); ?></p>

		<div id="help">
			<p><b>⚠️<?php echo __('IMPORTANT','seobot-monitor');?>:</b> <?php echo __('NEVER use the same Google Analytics UA-XXXXX that you usually use for tracking. The best thing to do is to create a new unique property for this', 'seobot-monitor');?>.</p>
		</div>

	</form>
</div>
