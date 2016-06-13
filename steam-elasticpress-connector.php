<?php
/**
 * Plugin Name: Steam ElasticPress Connector
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
define( 'EPSTREAM_URL',     plugin_dir_url( __FILE__ ) );
define( 'EPSTREAM_PATH',    dirname( __FILE__ ) . '/' );
define( 'EPSTREAM_INC',     EPSTREAM_PATH . 'includes/' );

// Include files
require_once EPSTREAM_INC . 'functions/core.php';


// Activation/Deactivation
register_activation_hook( __FILE__, '\ElasticPress\Stream\Core\activate' );
register_deactivation_hook( __FILE__, '\ElasticPress\Stream\Core\deactivate' );

// Bootstrap
ElasticPress\Stream\Core\setup();