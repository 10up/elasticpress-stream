<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	'settings' => array(
		'analysis' => array(
			'analyzer' => array(
				'default'       => array(
					'tokenizer' => 'standard',
					'filter'    => array( 'standard', 'ewp_word_delimiter', 'lowercase', 'stop', 'ewp_snowball' ),
					/**
					 * Allow to change a set of analyzers aimed at analyzing specific language text.
					 * Checkout https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-lang-analyzer.html
					 * Default is english
					 *
					 * @return string
					 */
					'language'  => apply_filters( 'ep_analyzer_language', 'english', 'analyzer_default' ),
				),
				'ewp_lowercase' => array(
					'type'      => 'custom',
					'tokenizer' => 'keyword',
					'filter'    => array( 'lowercase' ),
				),
			),
			'filter'   => array(
				'ewp_word_delimiter' => array(
					'type'              => 'word_delimiter',
					'preserve_original' => true,
				),
				'ewp_snowball'       => array(
					'type'     => 'snowball',
					/**
					 * Allow to change a set of analyzers aimed at analyzing specific language text.
					 * Checkout https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-lang-analyzer.html
					 * Default is english
					 *
					 * @return string
					 */
					'language' => apply_filters( 'ep_analyzer_language', 'english', 'filter_ewp_snowball' ),
				),
				'edge_ngram'         => array(
					'side'     => 'front',
					'max_gram' => 10,
					'min_gram' => 3,
					'type'     => 'edgeNGram',
				),
			),
		),
	),
	'mappings' => array(
		'record' => array(
			'date_detection'    => false,
			'dynamic_templates' => array(
				array(
					'template_meta_types' => array(
						'path_match' => 'meta.*',
						'mapping'    => array(
							'type'       => 'object',
							'path'       => 'full',
							'properties' => array(
								'value'    => array(
									'type'   => 'string',
									'fields' => array(
										'sortable' => array(
											'type'           => 'string',
											'analyzer'       => 'ewp_lowercase',
											'include_in_all' => false,
										),
										'raw'      => array(
											'type'           => 'string',
											'index'          => 'not_analyzed',
											'include_in_all' => false,
										),
									),
								),
								'raw'      => array( /* Left for backwards compat */
									'type'           => 'string',
									'index'          => 'not_analyzed',
									'include_in_all' => false,
								),
								'long'     => array(
									'type'  => 'long',
									'index' => 'not_analyzed',
								),
								'double'   => array(
									'type'  => 'double',
									'index' => 'not_analyzed',
								),
								'boolean'  => array(
									'type'  => 'boolean',
									'index' => 'not_analyzed',
								),
								'date'     => array(
									'type'   => 'date',
									'format' => 'yyyy-MM-dd',
									'index'  => 'not_analyzed',
								),
								'datetime' => array(
									'type'   => 'date',
									'format' => 'yyyy-MM-dd HH:mm:ss',
									'index'  => 'not_analyzed',
								),
								'time'     => array(
									'type'   => 'date',
									'format' => 'HH:mm:ss',
									'index'  => 'not_analyzed',
								),
							),
						),
					),
				),
			),
			'_all'              => array(
				'analyzer' => 'simple',
			),
			'properties'        => array(
				'ID'        => array(
					'type'           => 'long',
					'index'          => 'not_analyzed',
					'include_in_all' => false,
				),
				'site_id'   => array(
					'type'           => 'long',
					'index'          => 'not_analyzed',
					'include_in_all' => false,
				),
				'blog_id'   => array(
					'type'           => 'long',
					'index'          => 'not_analyzed',
					'include_in_all' => false,
				),
				'object_id' => array(
					'type'           => 'long',
					'index'          => 'not_analyzed',
					'include_in_all' => false,
				),
				'user_id'   => array(
					'type'           => 'long',
					'index'          => 'not_analyzed',
					'include_in_all' => false,
				),
				'user_role' => array(
					'type'           => 'string',
					'index'          => 'not_analyzed',
					'include_in_all' => false,
				),
				'summary'   => array(
					'type'     => 'string',
					'analyzer' => 'default',
				),
				'created'   => array(
					'type'           => 'date',
					'format'         => 'YYYY-MM-dd HH:mm:ss',
					'include_in_all' => false,
				),
				'connector' => array(
					'type'           => 'string',
					'index'          => 'not_analyzed',
					'include_in_all' => false,
				),
				'context'   => array(
					'type'           => 'string',
					'index'          => 'not_analyzed',
					'include_in_all' => false,
				),
				'action'    => array(
					'type'           => 'string',
					'index'          => 'not_analyzed',
					'include_in_all' => false,
				),
				'ip'        => array(
					'type'           => 'ip',
					'index'          => 'not_analyzed',
					'include_in_all' => false,
				),
				'meta'      => array(
					'type' => 'object',
				)
			),
		),
	),
);