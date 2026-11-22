<?php
/**
 * Cron tasks.
 *
 * @package ScheduleUnpublish
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and runs the scheduled unpublish event.
 */
class ScheduleUnpublish_Cron {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_interval' ) );
		add_action( 'init', array( $this, 'schedule_event' ) );
		add_action( 'schedule_unpublish_event', array( $this, 'cron_job' ) );
	}

	/**
	 * Add the fifteen-minute unpublish interval.
	 *
	 * @param array $schedules Available WordPress cron schedules.
	 * @return array The updated cron schedules.
	 */
	public function add_interval( $schedules ) {
		$schedules['schedule_unpublish_fifteen_minutes'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => __( 'Every fifteen minutes', 'schedule-unpublish' ),
		);
		return $schedules;
	}

	/**
	 * Schedule the worker and migrate older recurring events.
	 *
	 * @return void
	 */
	public function schedule_event() {
		$hook     = 'schedule_unpublish_event';
		$interval = 'schedule_unpublish_fifteen_minutes';
		$existing = wp_get_schedule( $hook );
		if ( $interval === $existing ) {
			return;
		}

		if ( wp_next_scheduled( $hook ) ) {
			wp_clear_scheduled_hook( $hook );
		}
		if ( ! wp_next_scheduled( $hook ) ) {
			wp_schedule_event( time(), $interval, $hook );
		}
	}

	/**
	 * Clear scheduled event
	 *
	 * @return void
	 */
	public function unschedule_event() {
		wp_clear_scheduled_hook( 'schedule_unpublish_event' );
	}

	/**
	 * Process a batch of due posts.
	 *
	 * @return void
	 */
	public function cron_job() {
		$data = new ScheduleUnpublish_Data();
		$data->update_posts_to_unpublish();
	}
}
