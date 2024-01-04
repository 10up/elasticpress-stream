<?php

namespace ElasticPress\Stream\Driver;

use ElasticPress\Elasticsearch;
use ElasticPress\Indexables;
use function ElasticPress\Stream\Core\get_index_name;

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
	 * Hold the ElasticPress class.
	 *
	 * @var \ElasticPress\Elasticsearch
	 */
	protected $es;

	/**
	 * Class constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->query      = new Query( $this );
		$this->index_name = trailingslashit( get_index_name() );
		$this->es         = new Elasticsearch();
	}

	/**
	 * Insert a record
	 *
	 * @param array $data Data to insert.
	 *
	 * @return int
	 * @since 0.1.0
	 *
	 */
	public function insert_record( $data = [] ) {

		// Return if importing
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			return 0;
		}

		if ( isset( $data['meta'] ) && ! empty( $data['meta'] ) ) {
			$meta         = $data['meta'];
			$data['meta'] = [];

			// Insert record meta
			foreach ( (array) $meta as $meta_key => $meta_values ) {
				$indexable                 = Indexables::factory()->get( 'wp_stream_alerts' );
				$data['meta'][ $meta_key ] = $indexable->prepare_meta_value_types( $meta_values );
				//              $data['meta'][ $meta_key ] = \EP_API::factory()->prepare_meta_value_types( $meta_values );
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
	 * @param array $record Array of stream record information to index
	 * @param bool $blocking Whether this is a blocking request or not. Default true.
	 *
	 * @return bool
	 * @since 0.1.0
	 *
	 */
	public function index_record( $record, $blocking = true ) {

		/**
		 * Filter record prior to indexing.
		 *
		 * Allows for last minute indexing of stream record information.
		 *
		 * @param array $record Array of stream record information to index.
		 *
		 * @since 0.1.0
		 *
		 */
		$record = apply_filters( 'ep_stream_pre_index_record', $record );

		$path = $this->index_name . 'record';

		$request_args = [
			'body'     => \ElasticPress\Stream\Core\json_encode( $record ),
			'method'   => 'POST',
			'timeout'  => 15,
			'blocking' => $blocking,
		];

		$request = $this->es->remote_request( $path, $request_args );

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
	 * @param array $args Arguments to query for.
	 *
	 * @return array
	 * @since 0.1.0
	 *
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
	 * @param string $column Column name.
	 *
	 * @return array
	 * @since 0.1.0
	 *
	 */
	public function get_column_values( $column ) {
		$formatted_args = [
			'size' => 0,
			'aggs' => [
				'group_by_column' => [
					'terms' => [
						'field' => $column,
						'size'  => 1000,
					],
				],
			],
		];

		$path = get_index_name() . '/record/_search';

		$request_args = [
			'body'   => \ElasticPress\Stream\Core\json_encode( $formatted_args ),
			'method' => 'POST',
		];

		//      $request = ep_remote_request( $path, $request_args );
		$request = $this->es->remote_request( $path, $request_args );
		$result  = [];

		if ( ! is_wp_error( $request ) ) {
			$response_body = wp_remote_retrieve_body( $request );

			$response = json_decode( $response_body, true );

			if ( isset( $response['aggregations'] ) && isset( $response['aggregations']['group_by_column'] ) ) {
				$buckets = $response['aggregations']['group_by_column']['buckets'];
				foreach ( $buckets as $row ) {
					$result[] = [ $column => $row['key'] ];
				}
			}
		}

		return $result;
	}

	/**
	 * Public getter to return table names
	 *
	 * @return array
	 * @since 0.1.0
	 *
	 */
	public function get_table_names() {
		return [];
	}

	/**
	 * Init storage.
	 *
	 * @param \WP_Stream\Plugin $plugin Instance of the plugin.
	 *
	 * @return void
	 * @since 0.1.0
	 *
	 */
	public function setup_storage( $plugin ) {
		// @TODO: If desired, could sync what's already in Stream into Elasticsearch here.
	}

	/**
	 * Purge storage.
	 *
	 * @param \WP_Stream\Plugin $plugin Instance of the plugin.
	 *
	 * @return void
	 * @since 0.1.0
	 *
	 */
	public function purge_storage( $plugin ) {
		// @TODO: If desired, could delete the Elasticsearch storage here, when the plugin is deactivated.
	}
}
