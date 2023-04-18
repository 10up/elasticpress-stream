<?php

namespace ElasticPress\Stream\Core;

use ElasticPress\Elasticsearch as ElasticSearch;

/**
 * Default setup routine
 *
 * @return void
 * @since 0.1.0
 *
 */
function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'i18n' ) );
	add_filter( 'wp_stream_db_driver', $n( 'driver' ) );
	add_action( 'ep_cli_put_mapping', $n( 'put_mapping' ) );
	add_action( 'ep_put_mapping', $n( 'put_mapping' ) );
	add_action( 'wp_stream_no_tables', '__return_true' );
	add_action( 'wp_stream_erase_records', $n( 'erase_records' ) );
}

/**
 * Add our custom mapping.
 *
 * This only runs when the ElasticPress Stream Connector
 * is first activated. It creates our index name and sends
 * our custom mapping information to that index.
 *
 * @return void
 * @since 0.1.0
 *
 */
function activation() {
	$version = ep_get_elasticsearch_version();

	if ( ! empty( $version ) ) {
		if ( defined( 'EP_IS_NETWORK' ) && EP_IS_NETWORK ) {
			$sites = ep_get_sites();

			foreach ( $sites as $site ) {
				switch_to_blog( $site['blog_id'] );

				put_mapping();

				create_network_alias( get_index_name() );

				restore_current_blog();
			}

			put_mapping( 0 );

			create_network_alias( get_index_name( 0 ) );
		} else {
			put_mapping();
		}
	}
}

/**
 * Output the feature box summary.
 *
 * @return void
 * @since 0.1.0
 *
 */
function feature_box_summary() {
	?>

	<p>
		<?php _e( 'Use ElasticPress to power <a href="http://wp-stream.com/">Stream</a> with Elasticsearch.', 'elasticpress-stream' ); ?>
	</p>

	<?php
}

/**
 * Output the feature box long description.
 *
 * @return void
 * @since 0.1.0
 *
 */
function feature_box_long() {
	?>

	<p>
		<?php esc_html_e( 'With Stream, you\'re never left in the dark about WordPress Admin activity. Every logged-in user action is displayed in an activity stream and organised for easy filtering by User, Role, Context, Action or IP address.', 'elasticpress-stream' ); ?>
	</p>

	<p>
		<?php esc_html_e( 'This is perfect for keeping tabs on what gets changed on your site. When something breaks, Stream is there to help. See what changed and who changed it. The problem is, all this information is stored in the database, making a lot of extra read/write calls.', 'elasticpress-stream' ); ?>
	</p>

	<p>
		<?php esc_html_e( 'Using the ElasticPress Stream feature in conjunction with Stream will speed things up tremendously. All data is stored and retrieved in Elasticsearch, using the ElasticPress API.', 'elasticpress-stream' ); ?>
	</p>

	<?php
}

/**
 * Make sure Stream is active before we activate the module.
 *
 * @param EP_Feature_Requirements_Status $status
 *
 * @return bool|WP_Error
 * @since 0.1.0
 *
 */
function requirements_status_cb( $status ) {
	$host = ep_get_host();

	if ( ! class_exists( 'WP_Stream\Plugin' ) ) {
		$status->code    = 2;
		$status->message = __( 'Please install and configure the <a href="http://wp-stream.com/">Stream plugin</a> to use this module.', 'elasticpress-stream' );
	} elseif ( ! preg_match( '#elasticpress\.io#i', $host ) ) {
		$status->code    = 1;
		$status->message = __( "You aren't using <a href='https://elasticpress.io'>ElasticPress.io</a> so we can't be sure your Elasticsearch instance is secure.", 'elasticpress' );
	}

	return $status;
}

/**
 * Helper function to encode json
 *
 * @param string $record Record to encode.
 *
 * @return false|mixed|string|void
 * @since 0.1.0
 *
 */
function json_encode( $record ) {
	if ( function_exists( 'wp_json_encode' ) ) {
		$encoded_record = wp_json_encode( $record );
	} else {
		$encoded_record = json_encode( $record );
	}

	return $encoded_record;
}

/**
 * Get the stream index name for site or network admin.
 *
 * @param int blog_id
 *
 * @return mixed|void
 * @since 0.1.0
 */
function get_index_name( $blog_id = null ) {
	$site_url = get_site_url();

	if ( ! is_numeric( $blog_id ) ) {
		$blog_id = get_current_blog_id();

		if ( defined( 'EP_IS_NETWORK' ) && EP_IS_NETWORK && is_network_admin() ) {
			$blog_id  = 0; // 0 denotes network admin index
			$site_url = network_site_url();
		}
	}

	$index_name = preg_replace( '#https?://(www\.)?#i', '', $site_url );
	$index_name = preg_replace( '#[^\w]#', '', $index_name );

	$index_name .= '-' . $blog_id . '-stream';

	return apply_filters( 'ep_stream_index_name', $index_name );
}


/**
 * Get stream network alias
 *
 * @return string
 * @since  0.1.0
 */
function get_network_alias() {
	$url = network_site_url();

	$slug = preg_replace( '#https?://(www\.)?#i', '', $url );
	$slug = preg_replace( '#[^\w]#', '', $slug );

	$alias = $slug . '-global-stream';

	return apply_filters( 'ep_stream_global_alias', $alias );
}

/**
 * Load our custom driver, if we can.
 *
 * @param string $default_driver Name of default driver class
 *
 * @return string
 * @since 0.1.0
 *
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
 * @return void
 * @since 0.1.0
 *
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'elasticpress-stream' );
	load_textdomain( 'elasticpress-stream', WP_LANG_DIR . '/EPStream/EPStream-' . $locale . '.mo' );
	load_plugin_textdomain( 'elasticpress-stream', false, plugin_basename( EPSTREAM_PATH ) . '/languages/' );
}

/**
 * Erase all stream records
 *
 * @since 0.1.0
 */
function erase_records() {
	if ( defined( 'EP_IS_NETWORK' ) && EP_IS_NETWORK ) {
		$sites = ep_get_sites();

		foreach ( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );

			delete_index();

			restore_current_blog();
		}

		delete_index( 0 );
	} else {
		delete_index();
	}
}

/**
 * Delete Stream ES index
 *
 * @param int $blog_id
 *
 * @return bool
 * @since 0.1.0
 */
function delete_index( $blog_id = null ) {
	return ep_delete_index( get_index_name( $blog_id ) );
}

/**
 * Insert mapping for Stream into Elasticsearch.
 *
 * @param int $blog_id
 *
 * @return bool
 * @since  0.1.0
 */
function put_mapping( $blog_id = null ) {
	/**
	 * Filter the EP Stream mapping file.
	 *
	 * @param string $file Location of mapping file.
	 *
	 * @since 0.1.0
	 *
	 */
	$mapping = require( apply_filters( 'ep_stream_config_mapping_file', EPSTREAM_INC . '/mappings.php' ) );

	/**
	 * Remove shard/replica defaults but maintain the filters
	 * for backwards compat.
	 */
	global $wp_filter;
	if ( ! empty( $wp_filter['ep_default_index_number_of_shards'] ) ) {
		if ( false === isset( $mapping['settings'] ) ) {
			$mapping['settings'] = [];
		}

		if ( false === isset( $mapping['settings']['index'] ) ) {
			$mapping['settings']['index'] = [];
		}

		/** This filter is documented in ElasticPress plugin file classes/class-ep-api.php */
		$mapping['settings']['index']['number_of_shards'] = (int) apply_filters( 'ep_default_index_number_of_shards', 5 ); // Default within Elasticsearch
	}

	if ( ! empty( $wp_filter['ep_default_index_number_of_replicas'] ) ) {
		if ( empty( $mapping['settings']['index'] ) ) {
			$mapping['settings']['index'] = [];
		}

		/** This filter is documented in ElasticPress plugin file classes/class-ep-api.php */
		$mapping['settings']['index']['number_of_replicas'] = (int) apply_filters( 'ep_default_index_number_of_replicas', 1 );
	}

	/**
	 * Filter the EP Stream mapping config.
	 *
	 * @param array $mapping Mapping config.
	 *
	 * @since 0.1.0
	 *
	 */
	$mapping = apply_filters( 'ep_stream_config_mapping', $mapping );

	$index = get_index_name( $blog_id );

	$request_args = [
		'body'   => json_encode( $mapping ),
		'method' => 'PUT',
	];

//	$request = ep_remote_request( $index, $request_args );
	$es      = new ElasticSearch();
	$request = $es->remote_request( $index, $request_args );

	if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {
		$response_body = wp_remote_retrieve_body( $request );

		return json_decode( $response_body );
	}

	return false;
}

/**
 * Add the index into the network alias.
 *
 * @param string $index Name of index.
 *
 * @return bool
 * @since 0.1.0
 *
 */
function create_network_alias( $index ) {
	$path = '_aliases';

	$args = [
		'actions' => [],
	];

	$args['actions'][] = [
		'add' => [
			'index' => $index,
			'alias' => get_network_alias(),
		],
	];

	$request_args = [
		'body'   => json_encode( $args ),
		'method' => 'POST',
	];

//	$request = ep_remote_request( $path, $request_args );
	$es      = new ElasticSearch();
	$request = $es->remote_request( $path, $request_args );

	if ( ! is_wp_error( $request ) && ( 200 >= wp_remote_retrieve_response_code( $request ) && 300 > wp_remote_retrieve_response_code( $request ) ) ) {
		return true;
	}

	return false;
}
