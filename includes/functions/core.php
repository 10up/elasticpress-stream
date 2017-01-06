<?php
namespace ElasticPress\Stream\Core;

/**
 * Default setup routine
 *
 * @since 0.1.0
 *
 * @return void
 */
function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'i18n' ) );
	add_action( 'init', $n( 'init' ) );
	add_filter( 'wp_stream_db_driver', $n( 'driver' ) );
	add_action( 'ep_cli_put_mapping', $n( 'put_mapping' ) );
	add_action( 'ep_put_mapping', $n( 'put_mapping' ) );
	add_action( 'ep_create_network_alias', $n( 'create_network_alias' ) );
	add_action( 'wp_stream_no_tables', '__return_true' );

	/**
	 * Fires after the plugin is loaded.
	 *
	 * @since 0.1.0
	 */
	do_action( 'EPStream_loaded' );
}

/**
 * Load our custom driver, if we can.
 *
 * @since 0.1.0
 *
 * @param string $default_driver Name of default driver class
 * @return string
 */
function driver( $default_driver ) {
	// If the Stream DB Driver interface exists, add our custom driver
	if ( interface_exists( '\WP_Stream\DB_Driver' ) ) {
		require_once EPSTREAM_INC . 'classes/class-query.php';
		require_once EPSTREAM_INC . 'classes/class-db-driver-elasticpress.php';

		return 'ElasticPress\Stream\Driver\DB_Driver_ElasticPress';
	}

	return $default_driver;
}

/**
 * Registers the default textdomain.
 *
 * @since 0.1.0
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'EPStream' );
	load_textdomain( 'EPStream', WP_LANG_DIR . '/EPStream/EPStream-' . $locale . '.mo' );
	load_plugin_textdomain( 'EPStream', false, plugin_basename( EPSTREAM_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin.
 *
 * @since 0.1.0
 *
 * @return void
 */
function init() {

	/**
	 * Fires when the plugin is initialized.
	 *
	 * @since 0.1.0
	 */
	do_action( 'EPStream_init' );
}

/**
 * Show admin notice if Elasticsearch isn't setup properly.
 *
 * @since 0.1.0
 *
 * @return void
 */
function no_es_notice() {
	$class   = 'notice notice-error';
	$active  = ep_stream_check_host();
	$message = ! empty( $active ) ? $active->get_error_message() : esc_html__( 'Please configure and run an index on Elasticsearch to use the ElasticPress Stream Connector', 'EPStream' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
}

/**
 * Insert mapping for Stream into Elasticsearch.
 *
 * @since 0.1.0
 *
 * @return bool
 */
function put_mapping() {
	/**
	 * Filter the EP Stream mapping file.
	 *
	 * @since 0.1.0
	 *
	 * @param string $file Location of mapping file.
	 */
	$mapping = require( apply_filters( 'ep_stream_config_mapping_file', EPSTREAM_INC . '/mappings.php' ) );

	/**
	 * Remove shard/replica defaults but maintain the filters
	 * for backwards compat.
	 */
	global $wp_filter;
	if ( ! empty( $wp_filter['ep_default_index_number_of_shards'] ) ) {
		if ( false === isset( $mapping['settings'] ) ) {
			$mapping['settings'] = array();
		}

		if ( false === isset( $mapping['settings']['index'] ) ) {
			$mapping['settings']['index'] = array();
		}

		/** This filter is documented in ElasticPress plugin file classes/class-ep-api.php */
		$mapping['settings']['index']['number_of_shards'] = (int) apply_filters( 'ep_default_index_number_of_shards', 5 ); // Default within Elasticsearch
	}

	if ( ! empty( $wp_filter['ep_default_index_number_of_replicas'] ) ) {
		if ( empty( $mapping['settings']['index'] ) ) {
			$mapping['settings']['index'] = array();
		}

		/** This filter is documented in ElasticPress plugin file classes/class-ep-api.php */
		$mapping['settings']['index']['number_of_replicas'] = (int) apply_filters( 'ep_default_index_number_of_replicas', 1 );
	}

	/**
	 * Filter the EP Stream mapping config.
	 *
	 * @since 0.1.0
	 *
	 * @param array $mapping Mapping config.
	 */
	$mapping = apply_filters( 'ep_stream_config_mapping', $mapping );

	$index = ep_stream_get_index_name();

	$request_args = array(
		'body'   => json_encode( $mapping ),
		'method' => 'PUT',
	);

	$request = ep_stream_remote_request( $index, $request_args );

	if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {
		$response_body = wp_remote_retrieve_body( $request );

		return json_decode( $response_body );
	}

	return false;
}

/**
 * Add the index into the network alias.
 *
 * @since 0.1.0
 *
 * @param string $index Name of index.
 * @return bool
 */
function create_network_alias( $index ) {
	$path = '_aliases';

	$args = array(
		'actions' => array(),
	);

	$args['actions'][] = array(
		'add' => array(
			'index' => $index,
			'alias' => ep_stream_get_index_name(),
		),
	);

	$request_args = array(
		'body'   => json_encode( $args ),
		'method' => 'POST',
	);

	$request = ep_stream_remote_request( $path, $request_args );

	if ( ! is_wp_error( $request ) && ( 200 >= wp_remote_retrieve_response_code( $request ) && 300 > wp_remote_retrieve_response_code( $request ) ) ) {
		return true;
	}

	return false;
}
