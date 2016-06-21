<?php
namespace ElasticPress\Stream\Core;

/**
 * Default setup routine
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
 *
 * @return void
 */
function init() {
	do_action( 'EPStream_init' );
}

/**
 * Activate the plugin
 *
 *
 * @return void
 */
function activate() {
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