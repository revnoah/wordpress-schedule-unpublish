<?php
/**
 * Plugin instance and meta box
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ScheduleUnpublish_Plugin {

	/**
	 * Holds the class instance.
	 *
	 * @var ScheduleUnpublish_Plugin $instance
	 */
	private static $instance = null;

	/**
	 * Return an instance of the class
	 *
	 * Return an instance of the ScheduleUnpublish_Plugin Class.
	 *
	 * @since 1.0.0
	 *
	 * @return ScheduleUnpublish_Plugin class instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Plugin init function
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		// Add meta boxes
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_data' ) );

		// Instantiate the cron class
		$cron = new ScheduleUnpublish_Cron();

		// Register activation and deactivation hooks
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Schedule the cron event
		add_action( 'init', array( $cron, 'schedule_event' ) );

		// Hook the cron job
		add_action( 'schedule_unpublish_event', array( $cron, 'cron_job' ) );
	}

	/**
	 * Register meta_boxes
	 *
	 * @return void
	 */
	public function register_meta_boxes() {
		$post_types = get_post_types( array( 'public' => true ) );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'schedule_unpublish_metabox',
				'Schedule Unpublish',
				array( $this, 'unpublish_meta_box_callback' ),
				$post_type,
				'side',
				'high'
			);
		}
	}

	/**
	 * Callback for meta_box
	 *
	 * @param Post $post The Post.
	 * @return void
	 */
	public function unpublish_meta_box_callback( $post ) {
		$schedule_unpublish = get_post_meta( $post->ID, 'schedule_unpublish', true );
		?>
		<label for="schedule-unpublish">Schedule Unpublish:</label>
		<input type="datetime-local" id="schedule-unpublish" name="schedule_unpublish" value="<?php echo esc_attr( $schedule_unpublish ); ?>">
		<?php wp_nonce_field( 'schedule_unpublish', 'unpublish' ); ?>
		<?php
	}

	/**
	 * Save meta box option
	 *
	 * @param integer $post_id The ID of the post.
	 * @return void
	 */
	public function save_meta_data( $post_id ) {
		// phpcs:ignore
		if ( isset( $_POST['schedule_unpublish'] ) ) {
			wp_verify_nonce( 'schedule_unpublish', 'unpublish' );
			$schedule_unpublish = sanitize_text_field( $_POST['schedule_unpublish'] );
			update_post_meta( $post_id, 'schedule_unpublish', $schedule_unpublish );
		}
	}
}
