<?php
/**
 * New Feature class to replace the deprecated `ep_register_feature`
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( '\ElasticPress\Feature' ) ) {
	exit;
}

/**
 * Your feature class.
 */
class Ep_Stream_Feature extends \ElasticPress\Feature {

	/**
	 * Initialize feature settings.
	 */
	public function __construct() {
		$this->slug = 'stream';

		$this->title = esc_html__( 'Stream' );

		$this->requires_install_reindex = false;
		$this->default_settings         = [
			'stream_setting' => '',
		];

		parent::__construct();
	}

	/**
	 * Output feature box summary.
	 */
	public function output_feature_box_summary() {
		?>
		<p><?php _e( 'Use ElasticPress to power <a href="http://wp-stream.com/">Stream</a> with Elasticsearch.', 'elasticpress-stream' ); ?></p>
		<?php
	}

	/**
	 * Output feature box long
	 */
	public function output_feature_box_long() {
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
	 * Setup your feature functionality.
	 * Use this method to hook your feature functionality to ElasticPress or WordPress.
	 */
	public function setup() {

		add_action( 'init', '\ElasticPress\Stream\Core\i18n' );
		add_filter( 'wp_stream_db_driver', '\ElasticPress\Stream\Core\driver' );
		add_action( 'ep_cli_put_mapping', '\ElasticPress\Stream\Core\put_mapping' );
		add_action( 'ep_put_mapping', '\ElasticPress\Stream\Core\put_mapping' );
		add_action( 'wp_stream_no_tables', '__return_true' );
		add_action( 'wp_stream_erase_records', '\ElasticPress\Stream\Core\erase_records' );
	}

}
