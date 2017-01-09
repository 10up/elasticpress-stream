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

		if ( isset( $assoc_args['network-wide'] ) && is_multisite() ) {
			if ( ! is_numeric( $assoc_args['network-wide'] ) ){
				$assoc_args['network-wide'] = 0;
			}

			$sites = ep_get_sites( $assoc_args['network-wide'] );

			foreach ( $sites as $site ) {
				switch_to_blog( $site['blog_id'] );

				\WP_CLI::line( sprintf( esc_html__( 'Deleting current index for site %s', 'elasticpress-stream' ), (int) $site['blog_id'] ) );

				// Deletes index first
				\ElasticPress\Stream\Core\delete_index();

				$result = \ElasticPress\Stream\Core\put_mapping();

				if ( $result ) {
					\WP_CLI::success( sprintf( esc_html__( 'Mapping sent for site %s', 'elasticpress-stream' ), (int) $site['blog_id'] ) );
				} else {
					\WP_CLI::error( esc_html__( 'Mapping failed', 'elasticpress-stream' ) );
				}

				$result = \ElasticPress\Stream\Core\create_network_alias( \ElasticPress\Stream\Core\get_index_name() );

				if ( empty( $result ) ) {
					\WP_CLI::error( sprintf( esc_html__( 'Network alias failed for site %s.', 'elasticpress-stream' ), (int) $site['blog_id'] ) );
				} else {
					\WP_CLI::success( sprintf( esc_html__( 'Network alias sent for site %s.', 'elasticpress-stream' ), (int) $site['blog_id'] ) );
				}

				restore_current_blog();
			}

			// Create global index

			\WP_CLI::line( esc_html__( 'Deleting current index for network admin', 'elasticpress-stream' ) );

			\ElasticPress\Stream\Core\delete_index( 0 );

			$result = \ElasticPress\Stream\Core\put_mapping( 0 );

			if ( $result ) {
				\WP_CLI::success( __( 'Mapping sent for network admin', 'elasticpress-stream' ) );
			} else {
				\WP_CLI::error( __( 'Mapping failed', 'elasticpress-stream' ) );
			}

			$result = \ElasticPress\Stream\Core\create_network_alias( \ElasticPress\Stream\Core\get_index_name( 0 ) );

			if ( empty( $result ) ) {
				\WP_CLI::error( esc_html__( 'Network alias failed for network admin.', 'elasticpress-stream' ) );
			} else {
				\WP_CLI::success( esc_html__( 'Network alias sent for network admin.', 'elasticpress-stream' ) );
			}
		} else {
			\WP_CLI::line( esc_html__( 'Deleting current index...', 'elasticpress-stream' ) );

			\ElasticPress\Stream\Core\delete_index();

			\WP_CLI::line( esc_html__( 'Putting mapping...', 'elasticpress-stream' ) );

			$result = \ElasticPress\Stream\Core\put_mapping();

			if ( empty( $result ) ) {
				\WP_CLI::error( esc_html__( 'Mapping failed.', 'elasticpress' ) );
			} else {
				\WP_CLI::success( esc_html__( 'Mapping sent.', 'elasticpress' ) );
			}

			$result = \ElasticPress\Stream\Core\create_network_alias( \ElasticPress\Stream\Core\get_index_name() );

			if ( empty( $result ) ) {
				\WP_CLI::error( esc_html__( 'Network alias failed.', 'elasticpress-stream' ) );
			} else {
				\WP_CLI::success( esc_html__( 'Network alias sent.', 'elasticpress-stream' ) );
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
			\WP_CLI::error( esc_html__( 'There is no Elasticsearch host set up. Either add one through the dashboard or define one in wp-config.php', 'elasticpress' ) );
		} elseif ( ! ep_get_elasticsearch_version() ) {
			\WP_CLI::error( esc_html__( 'Unable to reach Elasticsearch Server! Check that service is running.', 'elasticpress' ) );
		}
	}
}
