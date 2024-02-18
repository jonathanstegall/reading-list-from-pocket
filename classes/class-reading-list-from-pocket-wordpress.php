<?php

/**
 * WordPress feature wrapper
 *
 * @package Reading_List_From_Pocket
 */
class Reading_List_From_Pocket_WordPress {

	public $option_prefix;
	public $version;
	public $slug;
	public $file;

	public $cache;
	public $pocket_transients;

	public function __construct() {

		$this->option_prefix = reading_list_from_pocket()->option_prefix;
		$this->version       = reading_list_from_pocket()->version;
		$this->slug          = reading_list_from_pocket()->slug;
		$this->file          = reading_list_from_pocket()->file;

		$this->cache             = true;
		$this->pocket_transients = new Reading_List_From_Pocket_WordPress_Transient( 'pocket_transients' );
	}

	/**
	 * Check to see if this API call exists in the cache
	 * if it does, return the transient for that key
	 *
	 * @param string $call The API call we'd like to make.
	 * @param bool $reset Whether to reset the cache value
	 * @return $this->pocket_transients->get $cachekey
	 */
	public function cache_get( $url, $params = array(), $reset = false ) {
		$cachekey = md5(
			wp_json_encode(
				array(
					'url'    => $url,
					'params' => $params,
				)
			)
		);
		return $this->pocket_transients->get( $cachekey, $reset );
	}

	/**
	 * Create a cache entry for the current result, with the url and args as the key
	 *
	 * @param string $call The API query name.
	 * @return Bool whether or not the value was set
	 * @link https://wordpress.stackexchange.com/questions/174330/transient-storage-location-database-xcache-w3total-cache
	 */
	public function cache_set( $url, $params, $data ) {
		$cachekey = md5(
			wp_json_encode(
				array(
					'url'    => $url,
					'params' => $params,
				)
			)
		);
		return $this->pocket_transients->set( $cachekey, $data );
	}
}
