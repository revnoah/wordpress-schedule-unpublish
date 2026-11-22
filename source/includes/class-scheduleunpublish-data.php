<?php
/**
 * Data operations.
 *
 * @package ScheduleUnpublish
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Queries and updates posts scheduled for unpublishing.
 */
class ScheduleUnpublish_Data {

	const TAXONOMY = 'schedule_unpublish_queue';

	/**
	 * Register the private index used by the worker and queue.
	 */
	public function register_index() {
		register_taxonomy(
			self::TAXONOMY,
			get_post_types(),
			array( 'public' => false )
		);
	}

	/**
	 * Keep the index in sync when a schedule is written or removed.
	 *
	 * @param mixed  $meta_id Metadata ID or array of deleted IDs.
	 * @param int    $post_id Post ID.
	 * @param string $key     Metadata key.
	 */
	public function on_meta_change( $meta_id, $post_id, $key ) {
		if ( 'schedule_unpublish' === $key && taxonomy_exists( self::TAXONOMY ) ) {
			$this->index_post( $post_id );
		}
	}

	/**
	 * Index the current schedule for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array|WP_Error Term taxonomy IDs or an error.
	 */
	private function index_post( $post_id ) {
		$value = get_post_meta( $post_id, 'schedule_unpublish', true );
		return wp_set_object_terms( $post_id, $value ? array( 'scheduled' ) : array(), self::TAXONOMY );
	}

	/**
	 * Backfill existing schedules in batches, advancing only after successful writes.
	 */
	private function migrate_index() {
		$cursor = get_option( 'schedule_unpublish_index_post_cursor', 0 );
		if ( 'complete' === $cursor ) {
			return;
		}

		$query    = new WP_Query();
		$after_id = static function ( $where, $current_query ) use ( $query, $cursor ) {
			global $wpdb;

			if ( $query === $current_query ) {
				$where .= $wpdb->prepare( " AND {$wpdb->posts}.ID > %d", (int) $cursor );
			}
			return $where;
		};

		add_filter( 'posts_where', $after_id, 10, 2 );
		try {
			$post_ids = $query->query(
				array(
					'fields'         => 'ids',
					'posts_per_page' => 100,
					'post_type'      => array_values( get_post_types() ),
					'post_status'    => array_values( get_post_stati() ),
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'no_found_rows'  => true,
				)
			);
		} finally {
			remove_filter( 'posts_where', $after_id, 10 );
		}

		update_meta_cache( 'post', $post_ids );
		foreach ( $post_ids as $post_id ) {
			if ( get_post_meta( $post_id, 'schedule_unpublish', true ) && is_wp_error( $this->index_post( $post_id ) ) ) {
				break;
			}
			$cursor = $post_id;
		}

		if ( empty( $post_ids ) || ( count( $post_ids ) < 100 && end( $post_ids ) === $cursor ) ) {
			$cursor = 'complete';
		}
		update_option( 'schedule_unpublish_index_post_cursor', $cursor, false );
	}

	/**
	 * Move up to 50 due posts to pending on each cron run.
	 *
	 * @return void
	 */
	public function update_posts_to_unpublish() {
		$this->migrate_index();

		$timezone = $this->site_timezone();
		$now      = new DateTimeImmutable( 'now', $timezone );
		$format   = 'Y-m-d\TH:i';
		$due      = array();

		foreach ( $this->get_posts_to_unpublish() as $post ) {
			$value = get_post_meta( $post->ID, 'schedule_unpublish', true );
			if ( ! is_string( $value ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/D', $value ) ) {
				continue;
			}

			$scheduled = DateTimeImmutable::createFromFormat( '!' . $format, $value, $timezone );
			if ( $scheduled && $scheduled->format( $format ) === $value && $scheduled <= $now ) {
				$due[ $post->ID ] = $scheduled->getTimestamp();
			}
		}

		uksort(
			$due,
			static function ( $left, $right ) use ( $due ) {
				$by_date = $due[ $left ] <=> $due[ $right ];
				return 0 === $by_date ? $left <=> $right : $by_date;
			}
		);
		foreach ( array_slice( array_keys( $due ), 0, 50 ) as $post_id ) {
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'pending',
				)
			);
		}
	}

	/**
	 * Get the site timezone, including on WordPress versions before 5.3.
	 *
	 * @return DateTimeZone The site timezone.
	 */
	private function site_timezone() {
		if ( function_exists( 'wp_timezone' ) ) {
			return wp_timezone();
		}

		$timezone = get_option( 'timezone_string' );
		if ( $timezone ) {
			return new DateTimeZone( $timezone );
		}

		$minutes = (int) round( 60 * (float) get_option( 'gmt_offset' ) );
		$sign    = $minutes < 0 ? '-' : '+';
		$minutes = abs( $minutes );
		return new DateTimeZone( sprintf( '%s%02d:%02d', $sign, intdiv( $minutes, 60 ), $minutes % 60 ) );
	}

	/**
	 * Get posts to unpublish
	 *
	 * @return array
	 */
	public function get_posts_to_unpublish() {
		$published_posts = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => get_post_types(),
				'post_status' => 'publish',
				'taxonomy'    => self::TAXONOMY,
				'term'        => 'scheduled',
			)
		);

		foreach ( $published_posts as $key => $post ) {
			if ( ! get_post_meta( $post->ID, 'schedule_unpublish', true ) ) {
				unset( $published_posts[ $key ] );
			}
		}

		return $published_posts;
	}
}
