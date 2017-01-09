<?php

namespace ElasticPress\Stream\Cli;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

\WP_CLI::add_command( 'elasticpress-stream', '\ElasticPress\Stream\Cli\ElasticPress_Stream_CLI_Command' );

/**
 * CLI Commands for ElasticPress
 *
 */
class ElasticPress_Stream_CLI_Command extends \WP_CLI_Command {

	/**
	 * Setup plugin with index and mapping
	 *
	 * @since       0.1.0
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function setup( $args, $assoc_args ) {
		$this->_connect_check();

		\WP_CLI::line( esc_html__( 'Deleting current index...', 'elasticpress' ) );

		\ElasticPress\Stream\Core\delete_index();

		\WP_CLI::line( esc_html__( 'Putting mapping...', 'elasticpress' ) );

		$result = \ElasticPress\Stream\Core\put_mapping();

		if ( empty( $result ) ) {
			\WP_CLI::error( esc_html__( 'ElasticPress Stream unsuccessfully set up.', 'elasticpress' ) );
		} else {
			\WP_CLI::success( esc_html__( 'ElasticPress Stream successfully set up.', 'elasticpress' ) );
		}
	}


	/**
	 * Resets some values to reduce memory footprint.
	 *
	 * @since 0.1.0
	 */
	public function stop_the_insanity() {
		global $wpdb, $wp_object_cache, $wp_actions, $wp_filter;

		$wpdb->queries = array();

		if ( is_object( $wp_object_cache ) ) {
			$wp_object_cache->group_ops = array();
			$wp_object_cache->stats = array();
			$wp_object_cache->memcache_debug = array();

			// Make sure this is a public property, before trying to clear it
			try {
				$cache_property = new ReflectionProperty( $wp_object_cache, 'cache' );
				if ( $cache_property->isPublic() ) {
					$wp_object_cache->cache = array();
				}
				unset( $cache_property );
			} catch ( ReflectionException $e ) {
			}

			/*
			 * In the case where we're not using an external object cache, we need to call flush on the default
			 * WordPress object cache class to clear the values from the cache property
			 */
			if ( ! wp_using_ext_object_cache() ) {
				wp_cache_flush();
			}

			if ( is_callable( $wp_object_cache, '__remoteset' ) ) {
				call_user_func( array( $wp_object_cache, '__remoteset' ) ); // important
			}
		}

		// Prevent wp_actions from growing out of control
		$wp_actions = array();

		// WP_Query class adds filter get_term_metadata using its own instance
		// what prevents WP_Query class from being destructed by PHP gc.
		//    if ( $q['update_post_term_cache'] ) {
		//        add_filter( 'get_term_metadata', array( $this, 'lazyload_term_meta' ), 10, 2 );
		//    }
		// It's high memory consuming as WP_Query instance holds all query results inside itself
		// and in theory $wp_filter will not stop growing until Out Of Memory exception occurs.
		if ( isset( $wp_filter['get_term_metadata'] ) ) {
			/*
			 * WordPress 4.7 has a new Hook infrastructure, so we need to make sure
			 * we're accessing the global array properly
			 */
			if ( class_exists( 'WP_Hook' ) && $wp_filter['get_term_metadata'] instanceof WP_Hook ) {
				$filter_callbacks   = &$wp_filter['get_term_metadata']->callbacks;
			} else {
				$filter_callbacks   = &$wp_filter['get_term_metadata'];
			}
			if ( isset( $filter_callbacks[10] ) ) {
				foreach ( $filter_callbacks[10] as $hook => $content ) {
					if ( preg_match( '#^[0-9a-f]{32}lazyload_term_meta$#', $hook ) ) {
						unset( $filter_callbacks[10][ $hook ] );
					}
				}
			}
		}
	}

	/**
	 * Provide better error messaging for common connection errors
	 *
	 * @since 0.1.0
	 */
	private function _connect_check() {
		$host = ep_get_host();

		if ( empty( $host) ) {
			\WP_CLI::error( __( 'There is no Elasticsearch host set up. Either add one through the dashboard or define one in wp-config.php', 'elasticpress' ) );
		} elseif ( ! ep_get_elasticsearch_version() ) {
			\WP_CLI::error( __( 'Unable to reach Elasticsearch Server! Check that service is running.', 'elasticpress' ) );
		}
	}
}
