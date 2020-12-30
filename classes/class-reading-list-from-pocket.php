<?php

/**
 * The main plugin class
 *
 * @package Reading_List_From_Pocket
 */
class Reading_List_From_Pocket {

	/**
	 * The version number for this release of the plugin.
	 * This will later be used for upgrades and enqueuing files
	 *
	 * This should be set to the 'Plugin Version' value defined
	 * in the plugin header.
	 *
	 * @var string A PHP-standardized version number string
	 */
	public $version;

	/**
	 * Filesystem path to the main plugin file
	 * @var string
	 */
	public $file;

	/**
	 * Prefix for plugin options
	 * @var string
	 */
	public $option_prefix;

	/**
	 * Plugin slug
	 * @var string
	 */
	public $slug;

	/**
	 * @var object
	 * Administrative interface features
	 */
	public $admin;

	/**
	 * Class constructor
	 *
	 * @param string $version The current plugin version
	 * @param string $file The main plugin file
	 */
	public function __construct( $version, $file ) {
		$this->version       = $version;
		$this->file          = $file;
		$this->option_prefix = 'reading_list_from_pocket';
		$this->slug          = 'reading-list-from-pocket';

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

	}

	public function init() {
		// Admin features
		$this->admin = new Reading_List_From_Pocket_Admin();
	}

	/**
	 * Get the URL to the plugin admin menu
	 *
	 * @return string          The menu's URL
	 */
	public function get_menu_url() {
		$url = 'options-general.php?page=' . $this->slug;
		return admin_url( $url );
	}

	/**
	 * Load up the localization file if we're using WordPress in a different language.
	 *
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'reading-list-from-pocket', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

}
