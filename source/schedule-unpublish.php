<?php
/**
 * Plugin Name: Schedule Unpublish
 * Description: Adds an unpublish datetime field to automate post unpublishing.
 * Version: 1.0.0
 * Author: Noah J. Stewart
 * Author URI: https://noahjstewart.com
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-scheduleunpublish-data.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-scheduleunpublish-cron.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-scheduleunpublish-plugin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-scheduleunpublish-admin.php';

register_deactivation_hook( __FILE__, 'schedule_unpublish_plugin_deactivation' );

add_action(
	'plugins_loaded',
	function () {
		$plugin = ScheduleUnpublish_Plugin::get_instance();
		$plugin->plugins_loaded();
		$admin = new ScheduleUnpublish_Admin();
	}
);

/**
 * Plugin deactivation
 *
 * @return void
 */
function schedule_unpublish_plugin_deactivation() {
	wp_clear_scheduled_hook( 'schedule_unpublish_event' );
}
