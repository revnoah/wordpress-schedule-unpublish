<?php
/**
 * Plugin instance and meta box.
 *
 * @package ScheduleUnpublish
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the metabox and saves its schedule.
 */
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
		// Add meta boxes.
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_data' ) );

		$data = new ScheduleUnpublish_Data();
		add_action( 'init', array( $data, 'register_index' ), 99 );
		add_action( 'added_post_meta', array( $data, 'on_meta_change' ), 10, 3 );
		add_action( 'updated_post_meta', array( $data, 'on_meta_change' ), 10, 3 );
		add_action( 'deleted_post_meta', array( $data, 'on_meta_change' ), 10, 3 );

		// Instantiate the cron class.
		new ScheduleUnpublish_Cron();
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
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['unpublish'], $_POST['schedule_unpublish'] ) ||
			! is_string( $_POST['unpublish'] ) || ! is_string( $_POST['schedule_unpublish'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['unpublish'] ) );
		if ( ! wp_verify_nonce( $nonce, 'schedule_unpublish' ) ) {
			return;
		}

		$schedule_unpublish = sanitize_text_field( wp_unslash( $_POST['schedule_unpublish'] ) );
		update_post_meta( $post_id, 'schedule_unpublish', $schedule_unpublish );
	}
}
