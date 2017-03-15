<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	'settings' => array(
		'analysis' => array(
			'analyzer' => array(
				'default' => array(
					'tokenizer' => 'standard',
					'filter' => array( 'standard', 'ewp_word_delimiter', 'lowercase', 'stop', 'ewp_snowball' ),
					'language' => apply_filters( 'ep_analyzer_language', 'english', 'analyzer_default' ),
				),
				'shingle_analyzer' => array(
					'type' => 'custom',
					'tokenizer' => 'standard',
					'filter' => array( 'lowercase', 'shingle_filter' ),
				),
				'ewp_lowercase' => array(
					'type' => 'custom',
					'tokenizer' => 'keyword',
					'filter' => array( 'lowercase' ),
				),
			),
			'filter' => array(
				'shingle_filter' => array(
					'type' => 'shingle',
					'min_shingle_size' => 2,
					'max_shingle_size' => 5,
				),
				'ewp_word_delimiter' => array(
					'type' => 'word_delimiter',
					'preserve_original' => true,
				),
				'ewp_snowball' => array(
					'type' => 'snowball',
					'language' => apply_filters( 'ep_analyzer_language', 'english', 'filter_ewp_snowball' ),
				),
				'edge_ngram' => array(
					'side' => 'front',
					'max_gram' => 10,
					'min_gram' => 3,
					'type' => 'edgeNGram',
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
									'type'   => 'text',
									'fields' => array(
										'sortable' => array(
											'type' => 'keyword',
										),
										'raw'      => array(
											'type' => 'keyword',
										),
									),
								),
								'raw'      => array( /* Left for backwards compat */
									'type' => 'keyword',
								),
								'long'     => array(
									'type'  => 'long',
								),
								'double'   => array(
									'type'  => 'double',
								),
								'boolean'  => array(
									'type'  => 'boolean',
								),
								'date'     => array(
									'type'   => 'date',
									'format' => 'yyyy-MM-dd',
								),
								'datetime' => array(
									'type'   => 'date',
									'format' => 'yyyy-MM-dd HH:mm:ss',
								),
								'time'     => array(
									'type'   => 'date',
									'format' => 'HH:mm:ss',
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
				),
				'site_id'   => array(
					'type'           => 'long',
				),
				'blog_id'   => array(
					'type'           => 'long',
				),
				'object_id' => array(
					'type'           => 'long',
				),
				'user_id'   => array(
					'type'           => 'long',
				),
				'user_role' => array(
					'type'           => 'keyword',
				),
				'summary'   => array(
					'type'     => 'text',
				),
				'created'   => array(
					'type'           => 'date',
					'format'         => 'YYYY-MM-dd HH:mm:ss',
				),
				'connector' => array(
					'type'           => 'keyword',
				),
				'context'   => array(
					'type'           => 'keyword',
				),
				'action'    => array(
					'type'           => 'keyword',
				),
				'ip'        => array(
					'type'           => 'ip',
				),
				'meta'      => array(
					'type' => 'object',
				)
			),
		),
	),
);
