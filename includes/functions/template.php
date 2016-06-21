<?php
/**
 * This fill contains all the template/helper functions
 */

/**
 * Return stream index name for current site
 * @return string
 */
function ep_stream_get_index_name() {
	return 'stream-' . ep_get_network_alias();
}