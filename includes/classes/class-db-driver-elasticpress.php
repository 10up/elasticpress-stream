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
	public $table;

	/**
	 * Hold meta table name
	 *
	 * @var string
	 */
	public $table_meta;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->query = new Query( $this );
		// @TODO: Implement method and make sure it is returning expected data same as DB_Driver_WPDB
	}

	/**
	 * Insert a record
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function insert_record( $data ) {
		// @TODO: Implement method and make sure it is returning expected data same as DB_Driver_WPDB
		return $record_id;
	}

	/**
	 * Insert record meta
	 *
	 * @param int $record_id
	 * @param string $key
	 * @param string $val
	 *
	 * @return array
	 */
	public function insert_meta( $record_id, $key, $val ) {
		// @TODO: Implement method and make sure it is returning expected data same as DB_Driver_WPDB
		return $result;
	}

	/**
	 * Retrieve records
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_records( $args ) {
		// @TODO: Implement method and make sure it is returning expected data same as DB_Driver_WPDB
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
