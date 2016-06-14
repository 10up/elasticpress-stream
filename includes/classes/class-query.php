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
	 * Query records
	 *
	 * @param array Query args
	 *
	 * @return array Stream Records
	 */
	public function query( $args ) {
		// @TODO: Implement method and make sure it is returning expected data same as Stream Query class
		$result = array();
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
