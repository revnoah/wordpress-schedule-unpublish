<?php
/**
 * Cron tasks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ScheduleUnpublish_Cron {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'schedule_event' ) );
		add_action( 'schedule_unpublish_event', array( $this, 'cron_job' ) );
	}

	/**
	 * Add an event to schedule unpublishing a post
	 *
	 * @return void
	 */
	public function schedule_event() {
		if ( ! wp_next_scheduled( 'schedule_unpublish_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'schedule_unpublish_event' );
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
	 * Clear scheduled event
	 *
	 * @return void
	 */
	public function cron_job() {
		$data = new ScheduleUnpublish_Data();
		$data->update_posts_to_unpublish();
	}
}
