<?php
/**
 * Plugin Name:       ElasticPress Stream
 * Plugin URI:        https://github.com/10up/elasticpress-stream
 * Description:       Use ElasticPress to power Stream with Elasticsearch.
 * Version:           1.0.0
 * Requires at least:
 * Requires PHP:
 * Author:            10up, Faishal, Taylor Lovett
 * Author URI:        https://10up.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       elasticpress-stream
 * Domain Path:       /languages
 * Update URI:        https://github.com/10up/elasticpress-stream
 *
 */

/**
 * Copyright (c) 2016 Faishal Saiyed (email : faishal@10up.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using yo wp-make:plugin
 * Copyright (c) 2016 10up, LLC
 * https://github.com/10up/generator-wp-make
 */

// Useful global constants
define( 'EPSTREAM_VERSION', '1.0.0' );
define( 'EPSTREAM_URL', plugin_dir_url( __FILE__ ) );
define( 'EPSTREAM_PATH', dirname( __FILE__ ) . '/' );
define( 'EPSTREAM_INC', EPSTREAM_PATH . 'includes/' );

// Include core file
require_once EPSTREAM_INC . 'functions/core.php';

/**
 * WP CLI Commands
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( dirname( __FILE__ ) . '/bin/wp-cli.php' );
}

/**
 * Register the ElasticPress Stream module.
 *
 * Only register this if the ElasticPress plugin
 * is active and the ep_register_module function
 * is present, meaning ElasticPress is the proper
 * version (>= 2.2).
 *
 * @return void
 * @since 0.1.0
 *
 */
//function ep_stream_register_feature() {
//	ep_register_feature( 'stream', array(
//		'title'                    => 'Stream',
//		'requires_install_reindex' => false,
//		'setup_cb'                 => '\ElasticPress\Stream\Core\setup',
//		'feature_box_summary_cb'   => '\ElasticPress\Stream\Core\feature_box_summary',
//		'feature_box_long_cb'      => '\ElasticPress\Stream\Core\feature_box_long',
//		'requirements_status_cb'   => '\ElasticPress\Stream\Core\requirements_status_cb',
//		'post_activation_cb'       => '\ElasticPress\Stream\Core\activation',
//	) );
//}
//add_action( 'ep_setup_features', 'ep_stream_register_feature', 5 );

function ep_stream_register_feature() {
	if ( class_exists( '\ElasticPress\Features' ) ) {
		// Include your class file.
		require 'includes/classes/class-ep-stream-feature.php';
		// Register your feature in ElasticPress.
		\ElasticPress\Features::factory()->register_feature(
			new Ep_Stream_Feature()
		);
	}
}

add_action( 'plugins_loaded', 'ep_stream_register_feature', 11 );
