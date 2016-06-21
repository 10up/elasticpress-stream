<?php

namespace ElasticPress\Stream\Driver;
class DB_Driver_ElasticPress implements \WP_Stream\DB_Driver {
	/**
	 * Hold Query class
	 * @var Query
	 */
	protected $query;

	/**
	 * Hold records table name
	 *
	 * @var string
	 */
	public $index_name;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->query      = new Query( $this );
		$this->index_name = trailingslashit( \ep_stream_get_index_name() );
	}

	/**
	 * Insert a record
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function insert_record( $data ) {
		//Return if importing
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			return false;
		}

		$meta         = $data['meta'];
		$data['meta'] = array();

		$created_date    = new \DateTime( $data['created'] );
		$data['created'] = $created_date->format( 'Y-m-d H:i:s' );
		// Insert record meta
		foreach ( (array) $meta as $meta_key => $meta_values ) {
			$data['meta'][ $meta_key ] = \EP_API::factory()->prepare_meta_value_types( $meta_values );

		}
		$record_id = $this->index_record( $data );

		return $record_id;
	}

	/**
	 * Index record in elasticSearch
	 *
	 * @param $record
	 * @param bool $blocking
	 *
	 * @return bool
	 */
	function index_record( $record, $blocking = true ) {

		/**
		 * Filter post prior to indexing
		 *
		 * Allows for last minute indexing of post information.
		 *
		 * @since 1.7
		 *
		 * @param         array Array of post information to index.
		 */
		$record = apply_filters( 'ep_stream_pre_index_record', $record );


		$path = $this->index_name . 'record';

		if ( function_exists( 'wp_json_encode' ) ) {

			$encoded_post = wp_json_encode( $record );

		} else {

			$encoded_post = json_encode( $record );

		}


		$request_args = array(
			'body'     => $encoded_post,
			'method'   => 'POST',
			'timeout'  => 15,
			'blocking' => $blocking,
		);

		$request = ep_remote_request( $path, apply_filters( 'ep_index_post_request_args', $request_args, $record ) );

		do_action( 'ep_index_post_retrieve_raw_response', $request, $record, $path );

		if ( ! is_wp_error( $request ) ) {
			$response_body = wp_remote_retrieve_body( $request );

			$response = json_decode( $response_body );

			if ( $response->_id ) {
				return $response->_id;
			}
		}

		return false;
	}

	/**
	 * Retrieve records
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_records( $args ) {
		return $this->query->query( $args );
	}

	/**
	 * Returns array of existing values for requested column.
	 * Used to fill search filters with only used items, instead of all items.
	 *
	 * GROUP BY allows query to find just the first occurance of each value in the column,
	 * increasing the efficiency of the query.
	 *
	 * @param string $column
	 *
	 * @return array
	 */
	public function get_column_values( $column ) {
		// @TODO: Implement method and make sure it is returning expected data same as DB_Driver_WPDB
	}

	/**
	 * Purge storage
	 */
	public function purge_storage() {
		// @TODO: Implement method and rework class-uninstall to use this method
	}

	/**
	 * Init storage
	 */
	public function setup_storage() {
		// @TODO: Implement method and rework class-install to use this method
	}
}
