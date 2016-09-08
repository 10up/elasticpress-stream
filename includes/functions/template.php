<?php
/**
 * This file contains all the template & helper functions
 */

/**
 * Return stream index name for current site.
 *
 * @since 0.1.0
 *
 * @param int|null $blog_id ID of blog to get name for.
 * @return string
 */
function ep_stream_get_index_name( $blog_id = null ) {
	if ( function_exists( 'ep_get_index_name' ) ) {
		return 'stream-' . ep_get_index_name( $blog_id );
	}

	return 'stream-default';
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
 * Get the network alias.
 *
 * @since 0.1.0
 *
 * @return mixed|void
 */
function ep_stream_get_network_alias() {
	$url  = network_site_url();
	$slug = preg_replace( '#https?://(www\.)?#i', '', $url );
	$slug = preg_replace( '#[^\w]#', '', $slug );

	$alias = 'stream-' . $slug . '-global';

	/**
	 * Filter the EP Stream alias.
	 *
	 * @since 0.1.0
	 *
	 * @param string $alias Alias name.
	 */
	return apply_filters( 'ep_stream_global_alias', $alias );
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
