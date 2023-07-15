<?php
/**
 * Admin screen for plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ScheduleUnpublish_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Callback to add an admin menu
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'tools.php',
			'Unpublish Queue',
			'Unpublish Queue',
			'manage_options',
			'schedule-unpublish-queue',
			array( $this, 'render_publish_queue_page' )
		);
	}

	/**
	 * Output a table displaying the queue
	 *
	 * @return void
	 */
	public function render_publish_queue_page() {
		$data = new ScheduleUnpublish_Data();
		$posts_to_publish = $data->get_posts_to_unpublish();
		?>
		<div class="wrap">
			<h1><?php echo esc_html_e( 'Unpublish Queue', 'schedule-unpublish' ); ?></h1>
			<?php if ( ! empty( $posts_to_publish ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php echo esc_html_e( 'Title', 'schedule-unpublish' ); ?></th>
							<th><?php echo esc_html_e( 'Status', 'schedule-unpublish' ); ?></th>
							<th><?php echo esc_html_e( 'Unpublish Date', 'schedule-unpublish' ); ?></th>
							<th><?php echo esc_html_e( 'Edit', 'schedule-unpublish' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $posts_to_publish as $post ) : ?>
							<?php
							$post_id = $post->ID;
							$status = $post->post_status;
							$unpublish_date = get_post_meta( $post_id, 'schedule_unpublish', true );
							?>
							<tr id="post-<?php echo esc_html( $post_id ); ?>">
								<td><?php echo esc_html( get_the_title( $post_id ) ); ?></td>
								<td><?php echo esc_html( $status ); ?></td>
								<td><?php echo esc_html( $unpublish_date ); ?></td>
								<td><a href="<?php echo esc_attr( get_edit_post_link( $post_id ) ); ?>">Edit</a></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php echo esc_html_e( 'No posts to publish', 'schedule-unpublish' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

}
