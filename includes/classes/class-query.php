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

		/**
		 * PARSE PAGINATION PARAMS
		 */
		$from = 0;

		if ( ! isset( $args['records_per_page'] ) ) {
			$args['records_per_page'] = 20;
		}

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
		//Sanitize ip if set
		if ( ! empty( $args['ip'] ) ) {
			$args['ip'] = ep_stream_filter_var( $args['ip'], FILTER_VALIDATE_IP );
		}
		//Allowed field and validate function mapping
		$allowed_and_args = array(
			'site_id'   => 'is_numeric',
			'blog_id'   => 'is_numeric',
			'object_id' => 'is_numeric',
			'user_id'   => 'is_numeric',
			'user_role' => 'empty',
			'connector' => 'empty',
			'context'   => 'empty',
			'action'    => 'empty',
			'ip'        => 'empty',
		);

		foreach ( $allowed_and_args as $field => $validation_func ) {
			if ( isset( $args[ $field ] ) ) {
				if ( 'is_numeric' === $validation_func ) {
					$is_valid = is_numeric( $args[ $field ] );
				} else {
					$is_valid = ! empty( $args[ $field ] );
				}

				if ( $is_valid ) {
					$filter['and'][] = array(
						'term' => array( $field => $args[ $field ] ),
					);
				}
			}
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

			$query                   = array(
				'bool' => array(
					'should' => array(
						array(
							'multi_match' => array(
								'query'     => $args['search'],
								'type'      => 'phrase',
								'fields'    => $search_fields,
								/**
								 * This allow to filter boost parameter, which help to prioritize query clauses
								 * Read more : https://www.elastic.co/guide/en/elasticsearch/guide/current/multi-query-strings.html#prioritising-clauses
								 */
								'boost'     => apply_filters( 'ep_match_phrase_boost', 4, $search_fields, $args ),
								'fuzziness' => 0,
							)
						),
						array(
							'multi_match' => array(
								'query'     => $args['search'],
								'fields'    => $search_fields,
								/**
								 * This allow to filter boost parameter, which help to prioritize query clauses
								 * Read more : https://www.elastic.co/guide/en/elasticsearch/guide/current/multi-query-strings.html#prioritising-clauses
								 */
								'boost'     => apply_filters( 'ep_match_boost', 2, $search_fields, $args ),
								'fuzziness' => 0,
								'operator'  => 'and',
							)
						),
					),
				),
			);
			$formatted_args['query'] = $query;

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
		 * PARSE *__IN PARAM FAMILY
		 */
		$ins = array();
		//This will extract all in query args from main args into $ins array
		// e.g user_id__in,user_role__in,connector__in,ip__in,context__in,action__in,record__in
		foreach ( $args as $arg => $value ) {
			if ( '__in' === substr( $arg, - 4 ) ) {
				$ins[ $arg ] = $value;
			}
		}

		if ( ! empty( $ins ) ) {
			$in_filter = array();
			foreach ( $ins as $key => $value ) {
				//if query value is not an array then egnore
				if ( empty( $value ) || ! is_array( $value ) ) {
					continue;
				}
				//sanitize filed name : remove __in from filed name
				$field = str_replace( array( 'record_', '__in' ), '', $key );
				//if empty then use ID field
				$field = empty( $field ) ? 'ID' : $field;

				if ( ! empty( $value ) ) {
					$not_filter[] = array( 'terms' => array( $field => $value ) );
				}
			}
			if ( false === empty( $in_filter ) ) {
				if ( false === isset( $formatted_args['query'] ) ) {
					$formatted_args['query'] = array();
				}

				if ( false === isset( $formatted_args['query']['bool'] ) ) {
					$formatted_args['query']['bool'] = array();
				}
				if ( false === isset( $formatted_args['query']['bool']['filter'] ) ) {
					$formatted_args['query']['bool']['filter'] = array();
				}
				$formatted_args['query']['bool']['filter'] = $in_filter;
			}
		}

		/**
		 * PARSE __NOT_IN PARAM FAMILY
		 */
		$not_ins = array();
		//Extract all __not_in query args from main query
		foreach ( $args as $arg => $value ) {
			if ( '__not_in' === substr( $arg, - 8 ) ) {
				$not_ins[ $arg ] = $value;
			}
		}

		if ( ! empty( $not_ins ) ) {
			$not_filter = array();
			foreach ( $not_ins as $key => $value ) {
				if ( empty( $value ) || ! is_array( $value ) ) {
					continue;
				}
				//sanitize field name : remove __not_in from field
				$field = str_replace( array( 'record_', '__not_in' ), '', $key );
				$field = empty( $field ) ? 'ID' : $field;

				if ( ! empty( $value ) ) {
					$not_filter[] = array( 'terms' => array( $field => $value ) );
				}
			}
			if ( false === empty( $not_filter ) ) {
				if ( false === isset( $formatted_args['query'] ) ) {
					$formatted_args['query'] = array();
				}

				if ( false === isset( $formatted_args['query']['bool'] ) ) {
					$formatted_args['query']['bool'] = array();
				}
				if ( false === isset( $formatted_args['query']['bool']['must_not'] ) ) {
					$formatted_args['query']['bool']['must_not'] = array();
				}
				$formatted_args['query']['bool']['must_not'] = $not_filter;
			}
		}

		/**
		 * PARSE ORDER PARAMS
		 */
		$order = esc_sql( $args['order'] );
		$order = 'asc' === $order ? 'asc' : 'desc';
		$orderby = esc_sql( $args['orderby'] );
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
			$orderby = 'created';
		}
		$formatted_args['sort'] = array(
			$orderby => array( 'order' => $order )
		);

		/**
		 * PARSE FIELDS PARAMETER
		 */

		$fields = (array) $args['fields'];


		if ( ! empty( $fields ) ) {
			$selects = array();
			foreach ( $fields as $field ) {
				// We'll query the meta table later
				if ( 'meta' === $field ) {
					continue;
				}

				$selects[] = $field;
			}
			if ( false === empty( $selects ) ) {
				$formatted_args['_source'] = $selects;
			}
		}


		if ( ! empty( $filter['and'] ) ) {
			$formatted_args['filter'] = $filter;
		}

		$result = $this->search( $formatted_args );

		return $result;
	}

	/**
	 * Search record from elasticSearch
	 *
	 * @param $formatted_args
	 *
	 * @return array
	 */
	function search( $formatted_args ) {

		if ( is_network_admin() ) {
			$index_name = trailingslashit( ep_stream_get_network_alias() );
		} else {
			$index_name = trailingslashit( ep_stream_get_index_name() );
		}

		$path = $index_name . 'record/_search';

		$request_args = array(
			'body'   => json_encode( $formatted_args ),
			'method' => 'POST',
		);

		$request = ep_stream_remote_request( $path, $request_args );

		$result = array( 'items' => array(), 'count' => 0 );

		if ( ! is_wp_error( $request ) ) {


			$response_body = wp_remote_retrieve_body( $request );

			$response = json_decode( $response_body, true );
			if ( false === isset( $response['hits'] ) ) {
				return $result;
			}
			if ( false === isset( $response['hits']['hits'] ) ) {
				return $result;
			}

			$hits            = $response['hits']['hits'];
			$result['count'] = absint( $response['hits']['total'] );
			foreach ( $hits as $record ) {
				if ( isset( $record ['_source'] ) ) {
					$recordObj = (object) $record ['_source'];
					if ( isset( $recordObj->meta ) ) {
						$new_meta = array();
						foreach ( $recordObj->meta as $meta_key => $value ) {
							if ( is_array( $value ) && ! isset( $value['raw'] ) ) {
								$value = array_pop( $value );
							}
							if ( isset( $value['raw'] ) ) {
								$new_meta[ $meta_key ] = maybe_unserialize( $value['raw'] );
							}
						}

						$recordObj->meta = $new_meta;
					}
					$result['items'][] = $recordObj;
				}
			}


		}

		return $result;

	}
}
