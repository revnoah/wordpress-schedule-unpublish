<?php
/**
 * Data operations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ScheduleUnpublish_Data {

	/**
	 * Update all posts in array of IDs to unpublish
	 *
	 * @return void
	 */
	public function update_posts_to_unpublish() {
		$posts_to_unpublish = $this->get_posts_to_unpublish();

		if ( empty( $posts_to_unpublish ) ) {
			return; // No posts to unpublish
		}

		foreach ( $posts_to_unpublish as $post ) {
			$post_data = array(
				'ID'          => $post->ID,
				'post_status' => 'pending',
			);

			wp_update_post( $post_data );
		}
	}

	/**
	 * Get posts to unpublish
	 *
	 * @return array
	 */
	public function get_posts_to_unpublish() {
		$post_types = get_post_types();
		$args = array(
			'numberposts' => -1,
			'post_type' => $post_types,
			'post_status' => 'publish',
		);
		$published_posts = get_posts( $args );

		foreach ( $published_posts as $key => $draft_post ) {
			$meta_unpublish_date = get_post_meta( $draft_post->ID, 'schedule_unpublish', true );
			if ( ! $meta_unpublish_date ) {
				unset( $published_posts[ $key ] );
				continue;
			}
		}

		return $published_posts;
	}
}
