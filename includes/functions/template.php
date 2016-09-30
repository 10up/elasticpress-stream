<?php
/**
 * This file contains all the template & helper functions
 */

/**
 * Add our custom mapping.
 *
 * This only runs when the ElasticPress Stream Connector
 * is first activated. It creates our index name and sends
 * our custom mapping information to that index.
 *
 * @since 0.1.0
 *
 * @return void
 */
function ep_stream_activation() {
	if ( ! is_wp_error( ep_stream_check_host() ) ) {
		ElasticPress\Stream\Core\put_mapping();
	}
}

/**
 * Load the ElasticPress Stream Connector.
 *
 * This is only ran when the ElasticPress Stream module
 * has been activated. We then only load the functionality
 * if the Elasticsearch index is set up.
 *
 * @since 0.1.0
 *
 * @return void
 */
function ep_stream_loader() {
	// If Elasticsearch isn't set up properly
	if ( is_wp_error( ep_stream_check_host() ) ) {
		// Show admin notice
		add_action( 'admin_notices', 'ElasticPress\Stream\Core\no_es_notice' );

		return;
	}

	// Bootstrap
	ElasticPress\Stream\Core\setup();
}

/**
 * Output the module box summary.
 *
 * @since 0.1.0
 *
 * @return void
 */
function ep_stream_module_box_summary() {
?>

	<p>
		<?php esc_html_e( 'Increase the performance of Stream, as this module stores and retrieves data from within Elasticsearch, not the database.', 'EPStream' ); ?>
	</p>

<?php
}

/**
 * Output the module box long description.
 *
 * @since 0.1.0
 *
 * @return void
 */
function ep_stream_module_box_long() {
?>

	<p>
		<?php esc_html_e( 'With Stream, you\'re never left in the dark about WordPress Admin activity. Every logged-in user action is displayed in an activity stream and organised for easy filtering by User, Role, Context, Action or IP address.', 'EPStream' ); ?>
	</p>

	<p>
		<?php esc_html_e( 'This is perfect for keeping tabs on what gets changed on your site. When something breaks, Stream is there to help. See what changed and who changed it. The problem is, all this information is stored in the database, making a lot of extra read/write calls.', 'EPStream' ); ?>
	</p>

	<p>
		<?php esc_html_e( 'Using the ElasticPress Stream module in conjunction with Stream will speed things up tremendously. All data is stored and retrieved in Elasticsearch, using the ElasticPress API.', 'EPStream' ); ?>
	</p>

<?php
}

/**
 * Make sure Stream is active before we activate the module.
 *
 * @since 0.1.0
 *
 * @return bool|WP_Error
 */
function ep_stream_dependencies_met_cb() {
	if ( ! class_exists( 'WP_Stream\Plugin' ) ) {
		return new WP_Error( 'ep-no-stream', esc_html__( 'Please install and configure the Stream plugin to use this module.', 'EPStream' ) );
	}

	return true;
}

/**
 * A wrapper function of ElasticPress ep_check_host.
 *
 * @since 0.1.0
 *
 * @return bool
 */
function ep_stream_check_host() {
	if ( function_exists( 'ep_check_host' ) ) {
		return ep_check_host();
	}

	return false;
}

/**
 * Wrapper function of EP_API->prepare_meta_value_types.
 *
 * @since 0.1.0
 *
 * @param mixed $meta_values Values to prepare
 * @return mixed
 */
function ep_stream_prepare_meta_value_types( $meta_values ) {
	if ( class_exists( 'EP_API' ) ) {
		return \EP_API::factory()->prepare_meta_value_types( $meta_values );
	}

	return $meta_values;
}

/**
 * Wrapper for ep_remote_request which is defined in ElasticPress.
 *
 * @since 0.1.0
 *
 * @param string $path Site URL to retrieve.
 * @param array $request_args Optional. Request arguments. Default empty array.
 * @return WP_Error|array The response or WP_Error on failure.
 */
function ep_stream_remote_request( $path, $request_args ) {
	if ( function_exists( 'ep_remote_request' ) ) {
		return ep_remote_request( $path, $request_args );
	}

	return new WP_Error( 'ep_stream_remote_request_failed', esc_html__( 'ElasticPress function ep_remote_request is not present', 'EPStream' ) );
}

/**
 * Helper function to encode json
 *
 * @since 0.1.0
 *
 * @param string $record Record to encode.
 * @return false|mixed|string|void
 */
function ep_stream_json_encode( $record ) {
	if ( function_exists( 'wp_json_encode' ) ) {
		$encoded_record = wp_json_encode( $record );
	} else {
		$encoded_record = json_encode( $record );
	}

	return $encoded_record;
}

/**
 * Wrapper function of wp_stream_filter_var
 *
 * @since 0.1.0
 *
 * @param string $var Variable to filter.
 * @param int $filter The ID of the filter to apply.
 * @return mixed
 */
function ep_stream_filter_var( $var, $filter ) {
	if ( function_exists( 'wp_stream_filter_var' ) ) {
		wp_stream_filter_var( $var, $filter );
	}

	return filter_var( $var, $filter );
}

/**
 * Get the stream index name for site.
 *
 * This is a global index, so if using on
 * Multisite, all sites will use the same
 * index name.
 *
 * @since 0.1.0
 *
 * @return mixed|void
 */
function ep_stream_get_index_name() {
	if ( function_exists( 'ep_get_network_alias' ) ) {
		$alias = ep_get_network_alias() . '-stream';
	} else {
		$alias = 'default-stream';
	}

	/**
	 * Filter the EP Stream alias.
	 *
	 * @since 0.1.0
	 *
	 * @param string $alias Alias name.
	 */
	return apply_filters( 'ep_stream_index_name', $alias );
}

/**
 * Wrapper function of ep_get_sites.
 *
 * @since 0.1.0
 *
 * @return array
 */
function ep_stream_get_sites() {
	if ( function_exists( 'ep_get_sites' ) ) {
		return ep_get_sites();
	}

	return array();
}
