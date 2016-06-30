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
			return 0;
		}

		$meta         = $data['meta'];
		$data['meta'] = array();
		//convert date in proper format
		$data['created'] = date( 'Y-m-d H:i:s', strtotime( $data['created'] ) );
		// Insert record meta
		foreach ( (array) $meta as $meta_key => $meta_values ) {
			$data['meta'][ $meta_key ] = ep_stream_prepare_meta_value_types( $meta_values );

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
		 * Filter record prior to indexing
		 *
		 * Allows for last minute indexing of stream record information.
		 *
		 *
		 * @param         array Array of stream record information to index.
		 */
		$record = apply_filters( 'ep_stream_pre_index_record', $record );


		$path = $this->index_name . 'record';

		$request_args = array(
			'body'     => ep_stream_json_encode( $record ),
			'method'   => 'POST',
			'timeout'  => 15,
			'blocking' => $blocking,
		);

		$request = ep_stream_remote_request( $path, $request_args );

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

		$formatted_args = array(
			'size' => 0,
			'aggs' => array( 'group_by_column' => array( 'terms' => array( 'field' => $column, 'size' => 1000 ) ) )
		);

		$path = ep_stream_get_index_name() . '/record/_search';

		$request_args = array(
			'body'   => ep_stream_json_encode( $formatted_args ),
			'method' => 'POST',
		);

		$request = ep_stream_remote_request( $path, $request_args );
		$result  = array();
		if ( ! is_wp_error( $request ) ) {

			$response_body = wp_remote_retrieve_body( $request );

			$response = json_decode( $response_body, true );
			if ( isset( $response['aggregations'] ) && isset( $response['aggregations']['group_by_column'] ) ) {
				$buckets = $response['aggregations']['group_by_column']['buckets'];
				foreach ( $buckets as $row ) {
					$result[] = array( $column => $row['key'] );
				}
			}
		}

		return $result;

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
