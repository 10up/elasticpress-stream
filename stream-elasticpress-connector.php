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
 * Register the ElasticPress Stream module.
 *
 * Only register this if the ElasticPress plugin
 * is active and the ep_register_module function
 * is present, meaning ElasticPress is the proper
 * version (>= 2.1).
 *
 * @since 0.1.0
 *
 * @return void
 */
function ep_stream_register_module() {
	if ( class_exists( 'EP_Config' ) && function_exists( 'ep_register_module' ) ) {
		ep_register_module( 'stream', array(
			'title'                    => 'ElasticPress Stream Connector',
			'requires_install_reindex' => false,
			'setup_cb'                 => 'ep_stream_loader',
			'module_box_summary_cb'    => 'ep_stream_module_box_summary',
			'module_box_long_cb'       => 'ep_stream_module_box_long',
			'dependencies_met_cb'      => 'ep_stream_dependencies_met_cb',
			'post_activation_cb'       => 'ep_stream_activation',
		) );
	} else {
		// Show admin notice
		add_action( 'admin_notices', 'ElasticPress\Stream\Core\no_ep_notice' );
	}
}
add_action( 'plugins_loaded', 'ep_stream_register_module', 5 );
