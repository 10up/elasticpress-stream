<?php
/**
 * Plugin Name: Stream ElasticPress Connector
 * Plugin URI:  http://10up.com
 * Description: Stream ElasticPress Connector
 * Version:     0.1.0
 * Author:      10up, Faishal
 * Author URI:  http://10up.com
 * Text Domain: EPStream
 * Domain Path: /languages
 * License:     GPL-2.0+
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
define( 'EPSTREAM_VERSION', '0.1.0' );
define( 'EPSTREAM_URL', plugin_dir_url( __FILE__ ) );
define( 'EPSTREAM_PATH', dirname( __FILE__ ) . '/' );
define( 'EPSTREAM_INC', EPSTREAM_PATH . 'includes/' );

// Include core file
require_once EPSTREAM_INC . 'functions/template.php';
require_once EPSTREAM_INC . 'functions/core.php';

/**
 * Load the ElasticPress Stream Connector.
 *
 * Only load this if Steam is present, ElasticPress is
 * present and the Elasticsearch index is set up.
 *
 * @since 0.1.0
 *
 * @return void
 */
function ep_stream_loader() {
	// If Stream isn't active
	if ( ! class_exists( 'WP_Stream\Plugin' ) ) {
		// Show admin notice
		add_action( 'admin_notices', 'ElasticPress\Stream\Core\no_stream_notice' );

		return;
	}
	// If ElasticPress isn't active
	else if ( ! class_exists( 'EP_Config' ) ) {
		// Show admin notice
		add_action( 'admin_notices', 'ElasticPress\Stream\Core\no_ep_notice' );

		return;
	}
	// If Elasticsearch isn't set up properly
	else if ( is_wp_error( ep_stream_check_host() ) ) {
		// Show admin notice
		add_action( 'admin_notices', 'ElasticPress\Stream\Core\no_es_notice' );

		return;
	}

	// Bootstrap
	ElasticPress\Stream\Core\setup();
}
add_action( 'plugins_loaded', 'ep_stream_loader', 5 );
