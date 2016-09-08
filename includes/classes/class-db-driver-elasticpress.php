<?php
namespace ElasticPress\Stream\Driver;

class DB_Driver_ElasticPress implements \WP_Stream\DB_Driver {
	/**
	 * Hold the Query class.
	 *
	 * @var \ElasticPress\Stream\Driver\Query
	 */
	protected $query;

	/**
	 * Hold the records table name.
	 *
	 * @var string
	 */
	public $index_name;

	/**
	 * Class constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->query      = new Query( $this );
		$this->index_name = trailingslashit( \ep_stream_get_index_name() );
	}

	/**
	 * Insert a record
	 *
	 * @since 0.1.0
	 *
	 * @param array $data Data to insert.
	 * @return int
	 */
	public function insert_record( $data = array() ) {
		// Return if importing
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			return 0;
		}

		if ( isset( $data['meta'] ) && ! empty( $data['meta'] ) ) {
			$meta         = $data['meta'];
			$data['meta'] = array();

			// Insert record meta
			foreach ( (array) $meta as $meta_key => $meta_values ) {
				$data['meta'][ $meta_key ] = ep_stream_prepare_meta_value_types( $meta_values );
			}
		}

		if ( isset( $data['created'] ) && ! empty( $data['created'] ) ) {
			// Convert date to proper format
			$data['created'] = date( 'Y-m-d H:i:s', strtotime( $data['created'] ) );
		}

		$record_id = $this->index_record( $data );

		return $record_id;
	}

	/**
	 * Index record in Elasticsearch.
	 *
	 * @since 0.1.0
	 *
	 * @param array $record Array of stream record information to index
	 * @param bool $blocking Whether this is a blocking request or not. Default true.
	 * @return bool
	 */
	public function index_record( $record, $blocking = true ) {

		/**
		 * Filter record prior to indexing.
		 *
		 * Allows for last minute indexing of stream record information.
		 *
		 * @since 0.1.0
		 *
		 * @param array $record Array of stream record information to index.
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
			$response      = json_decode( $response_body );

			if ( $response->_id ) {
				return $response->_id;
			}
		}

		return false;
	}

	/**
	 * Retrieve records.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args Arguments to query for.
	 * @return array
	 */
	public function get_records( $args ) {
		return $this->query->query( $args );
	}

	/**
	 * Get existing values for requested column.
	 *
	 * Used to fill search filters with only used items, instead of all items.
	 *
	 * GROUP BY allows query to find just the first occurrence of each value in the column,
	 * increasing the efficiency of the query.
	 *
	 * @since 0.1.0
	 *
	 * @param string $column Column name.
	 * @return array
	 */
	public function get_column_values( $column ) {
		$formatted_args = array(
			'size' => 0,
			'aggs' => array(
				'group_by_column' => array(
					'terms' => array(
						'field' => $column,
						'size'  => 1000,
					),
				),
			),
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
	 * Public getter to return table names
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_table_names() {
		return array();
	}


	/**
	 * Purge storage.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function purge_storage() {
		// @TODO: Implement method and rework class-uninstall to use this method
	}

	/**
	 * Init storage.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function setup_storage() {
		// @TODO: Implement method and rework class-install to use this method
	}

}
