<?php
/**
 * This fill contains all the template/helper functions
 */

/**
 * Return stream index name for current site
 * @return string
 */
function ep_stream_get_index_name( $blog_id = null ) {
	if ( function_exists( 'ep_get_index_name' ) ) {
		return 'stream-' . ep_get_index_name( $blog_id );
	}

	return 'stream-default';
}

/**
 * A wrapper function of elasticPress ep_check_host
 * return false if elasticPress is not present
 * @return bool
 */
function ep_stream_check_host() {
	if ( function_exists( 'ep_check_host' ) ) {
		return ep_check_host();
	}

	return false;
}

/**
 * Wrapper function of EP_API->prepare_meta_value_types
 *
 * @param $meta_values
 *
 * @return mixed
 */
function ep_stream_prepare_meta_value_types( $meta_values ) {
	if ( class_exists( 'EP_API' ) ) {
		return \EP_API::factory()->prepare_meta_value_types( $meta_values );
	}

	return $meta_values;
}

/**
 * Wrapper for ep_remote_request which is defined in ElasticPress
 *
 * @param string $path Site URL to retrieve.
 * @param array $args Optional. Request arguments. Default empty array.
 *
 * @return WP_Error|array The response or WP_Error on failure.
 */
function ep_stream_remote_request( $path, $request_args ) {
	if ( function_exists( 'ep_remote_request' ) ) {
		return ep_remote_request( $path, $request_args );
	}

	return new WP_Error( 'ep_stream_remote_request_failed', esc_html__( 'ElasticPress function ep_remote_request is not present' ) );
}

/**
 * Hlper function to encode json
 *
 * @param $record
 *
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
 * @param $var  Variable to filter
 * @param $filter Filter Name
 *
 * @return mixed filtered value
 */
function ep_stream_filter_var( $var, $filter ) {
	if ( function_exists( 'wp_stream_filter_var' ) ) {
		wp_stream_filter_var( $var, $filter );
	}

	return filter_var( $var, $filter );;
}

/**
 * Return network alias
 * @return mixed|void
 */
function ep_stream_get_network_alias() {
	$url  = network_site_url();
	$slug = preg_replace( '#https?://(www\.)?#i', '', $url );
	$slug = preg_replace( '#[^\w]#', '', $slug );

	$alias = 'stream-' . $slug . '-global';

	return apply_filters( 'ep_stream_global_alias', $alias );
}

/**
 * Wrapper function of ep_get_sites
 * return empty array if elasticPress is not present
 * @return array
 */
function ep_stream_get_sites() {
	if ( function_exists( 'ep_get_sites' ) ) {
		return ep_get_sites();
	}

	return array();
}