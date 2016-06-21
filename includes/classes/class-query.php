<?php
namespace ElasticPress\Stream\Driver;
class Query {
	/**
	 * Hold the number of records found
	 *
	 * @var int
	 */
	public $found_records = 0;

	/**
	 * Query records - build query for elasticsearch
	 *
	 * @param array Query args
	 *
	 * @return array Stream Records
	 */
	public function query( $args ) {
		$result = array();
		//pagination
		$from = 0;
		if ( isset( $args['paged'] ) ) {
			$from = ( absint( $args['paged'] ) - 1 ) * absint( $args['records_per_page'] );
		}

		$formatted_args = array(
			'from' => $from,
			'size' => $args['records_per_page'],
		);

		$filter = array(
			'and' => array(),
		);

		/**
		 * PARSE CORE PARAMS
		 */

		if ( is_numeric( $args['site_id'] ) ) {
			$filter['and'][] = array( 'term' => array( 'site_id' => $args['site_id'] ) );
		}

		if ( is_numeric( $args['blog_id'] ) ) {
			$filter['and'][] = array( 'term' => array( 'blog_id' => $args['blog_id'] ) );
		}

		if ( is_numeric( $args['object_id'] ) ) {
			$filter['and'][] = array( 'term' => array( 'object_id' => $args['object_id'] ) );
		}
		if ( is_numeric( $args['user_id'] ) ) {
			$filter['and'][] = array( 'term' => array( 'user_id' => $args['user_id'] ) );
		}

		if ( ! empty( $args['user_role'] ) ) {
			$filter['and'][] = array( 'term' => array( 'user_role' => $args['user_role'] ) );
		}

		if ( ! empty( $args['connector'] ) ) {
			$filter['and'][] = array( 'term' => array( 'connector' => $args['connector'] ) );
		}

		if ( ! empty( $args['context'] ) ) {
			$filter['and'][] = array( 'term' => array( 'context' => $args['context'] ) );
		}

		if ( ! empty( $args['action'] ) ) {
			$filter['and'][] = array( 'term' => array( 'action' => $args['action'] ) );
		}

		if ( ! empty( $args['ip'] ) ) {
			$filter['and'][] = array( 'term' => array( 'ip' => wp_stream_filter_var( $args['ip'], FILTER_VALIDATE_IP ) ) );
		}


		if ( ! empty( $args['search'] ) ) {
			$search_fields = array(
				'ID',
				'site_id',
				'blog_id',
				'object_id',
				'user_id',
				'user_role',
				'created',
				'summary',
				'connector',
				'context',
				'action',
				'ip'
			);
			$field         = ! empty( $args['search_field'] ) ? $args['search_field'] : 'summary';
			if ( in_array( $field, $search_fields, true ) ) {
				$search_fields = array( $field );
			}

			$query = array(
				'bool' => array(
					'should' => array(
						array(
							'multi_match' => array(
								'query'     => '',
								'type'      => 'phrase',
								'fields'    => $search_fields,
								'boost'     => apply_filters( 'ep_match_phrase_boost', 4, $search_fields, $args ),
								'fuzziness' => 0,
							)
						),
						array(
							'multi_match' => array(
								'query'     => '',
								'fields'    => $search_fields,
								'boost'     => apply_filters( 'ep_match_boost', 2, $search_fields, $args ),
								'fuzziness' => 0,
								'operator'  => 'and',
							)
						),
						array(
							'multi_match' => array(
								'fields'    => $search_fields,
								'query'     => '',
								'fuzziness' => apply_filters( 'ep_fuzziness_arg', 1, $search_fields, $args ),
							),
						)
					),
				),
			);

			$query['bool']['should'][1]['multi_match']['query'] = $args['search'];
			$query['bool']['should'][0]['multi_match']['query'] = $args['search'];
			$formatted_args['query']                            = $query;

		}


		$range = array( 'created' => array() );
		/**
		 * PARSE DATE PARAM FAMILY
		 */
		if ( ! empty( $args['date'] ) ) {
			$args['date_from'] = date( 'Y-m-d', strtotime( $args['date'] ) );
			$args['date_to']   = date( 'Y-m-d', strtotime( $args['date'] ) );
		}

		if ( ! empty( $args['date_from'] ) ) {
			$date                    = get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( $args['date_from'] . ' 00:00:00' ) ) );
			$range['created']['gte'] = $date;
		}

		if ( ! empty( $args['date_to'] ) ) {
			$date                    = get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( $args['date_to'] . ' 23:59:59' ) ) );
			$range['created']['lte'] = $date;
		}

		if ( ! empty( $args['date_after'] ) ) {
			$date                   = get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( $args['date_after'] ) ) );
			$range['created']['gt'] = $date;

		}

		if ( ! empty( $args['date_before'] ) ) {
			$date                   = get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( $args['date_before'] ) ) );
			$range['created']['lt'] = $date;
		}

		if ( ! empty( $range['created'] ) ) {
			$filter['and'][] = array( 'range' => $range );
		}

		/**
		 * PARSE ORDER PARAMS
		 */
		$order     = esc_sql( $args['order'] );
		$orderby   = esc_sql( $args['orderby'] );
		$orderable = array(
			'ID',
			'site_id',
			'blog_id',
			'object_id',
			'user_id',
			'user_role',
			'summary',
			'created',
			'connector',
			'context',
			'action'
		);

		if ( in_array( $orderby, $orderable, true ) ) {
			$orderby = $orderby;
		} elseif ( 'meta_value_num' === $orderby && ! empty( $args['meta_key'] ) ) {
			$orderby = '';
		} elseif ( 'meta_value' === $orderby && ! empty( $args['meta_key'] ) ) {
			$orderby = '';
		} else {
			$orderby = "created";
		}
		$formatted_args['sort'] = array(
			$orderby => array( 'order' => $order )
		);

		if ( ! empty( $filter['and'] ) ) {
			$formatted_args['filter'] = $filter;
		}

		$result = $this->search( $formatted_args );

		return $result;
	}

	/**
	 * Search record from elasticSearch
	 * @param $formatted_args
	 *
	 * @return array
	 */
	function search( $formatted_args ) {
		$path = ep_stream_get_index_name() . '/record/_search';

		$request_args = array(
			'body'   => json_encode( $formatted_args ),
			'method' => 'POST',
		);

		$request = ep_remote_request( $path, $request_args );

		$result = array( 'items' => array(), 'count' => 0 );

		if ( ! is_wp_error( $request ) ) {


			$response_body = wp_remote_retrieve_body( $request );

			$response = json_decode( $response_body, true );

			$hits            = $response['hits']['hits'];
			$result['count'] = absint( $response['hits']['total'] );

			foreach ( $hits as $record ) {
				$recordObj = new \stdClass();
				foreach ( $record ['_source'] as $key => $value ) {
					$recordObj->$key = $value;
				}
				if ( isset( $recordObj->meta ) ) {
					$new_meta = array();
					foreach ( $recordObj->meta as $meta_key => $value ) {
						if ( is_array( $value ) && $value[0] ) {
							$value = array_pop( $value );
						}
						$new_meta[ $meta_key ] = maybe_unserialize( $value[0]['raw'] );
					}

					$recordObj->meta = $new_meta;

				}
				$result['items'][] = $recordObj;
			}


		}

		return $result;

	}

	/**
	 * Add meta to a set of records
	 *
	 * @param array $records
	 *
	 * @return array
	 */
	public function add_record_meta( $records ) {
		// @TODO: Implement method and make sure it is returning expected data same as Stream Query class
		return (array) $records;
	}
}
