<?php
namespace ElasticPress\Stream\Core;

/**
 * Default setup routine
 *
 * @uses add_action()
 * @uses do_action()
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

	do_action( 'EPStream_loaded' );
}

/**
 * @param $default_driver
 *
 * @return string
 */
function driver( $default_driver ) {

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
 * @uses apply_filters()
 * @uses get_locale()
 * @uses load_textdomain()
 * @uses load_plugin_textdomain()
 * @uses plugin_basename()
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'EPStream' );
	load_textdomain( 'EPStream', WP_LANG_DIR . '/EPStream/EPStream-' . $locale . '.mo' );
	load_plugin_textdomain( 'EPStream', false, plugin_basename( EPSTREAM_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @uses do_action()
 *
 * @return void
 */
function init() {
	do_action( 'EPStream_init' );
}

/**
 * Activate the plugin
 *
 * @uses init()
 * @uses flush_rewrite_rules()
 *
 * @return void
 */
function activate() {
	put_mapping();
	// First load the init scripts in case any rewrite functionality is being loaded
	init();
}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {

}


/**
 * Show admin notice if elasticPress plugin is not present
 */
function no_ep_notice() {
	$class   = 'notice notice-error';
	$message = __( 'Please install and configure ElasticPress plugin to use ElasticPress Stream Connector', 'EPStream' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
}

function put_mapping() {
	$mapping = require( apply_filters( 'ep_stream_config_mapping_file', EPSTREAM_INC . '/mappings.php' ) );

	/**
	 * We are removing shard/replica defaults but need to maintain the filters
	 * for backwards compat.
	 *
	 * @since 1.4
	 */
	global $wp_filter;
	if ( ! empty( $wp_filter['ep_default_index_number_of_shards'] ) ) {
		if ( empty( $mapping['settings']['index'] ) ) {
			$mapping['settings']['index'] = array();
		}

		$mapping['settings']['index']['number_of_shards'] = (int) apply_filters( 'ep_default_index_number_of_shards', 5 ); // Default within Elasticsearch
	}

	if ( ! empty( $wp_filter['ep_default_index_number_of_replicas'] ) ) {
		if ( empty( $mapping['settings']['index'] ) ) {
			$mapping['settings']['index'] = array();
		}

		$mapping['settings']['index']['number_of_replicas'] = (int) apply_filters( 'ep_default_index_number_of_replicas', 1 );
	}

	$mapping = apply_filters( 'ep_stream_config_mapping', $mapping );

	$index = ep_stream_get_index_name();

	$request_args = array(
		'body'   => json_encode( $mapping ),
		'method' => 'PUT',
	);

	$request = ep_remote_request( $index, apply_filters( 'ep_stream_put_mapping_request_args', $request_args ) );

	$request = apply_filters( 'ep_stream_config_mapping_request', $request, $index, $mapping );

	if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {
		$response_body = wp_remote_retrieve_body( $request );

		return json_decode( $response_body );
	}

	return false;
}