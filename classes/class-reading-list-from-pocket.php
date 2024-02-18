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
	 * Suffix for group name in ActionScheduler
	 *
	 * @var string
	 */
	public $action_group_suffix;

	/**
	 * Login credentials for the Pocket API; comes from wp-config or from the plugin settings
	 *
	 * @var array
	 */
	public $login_credentials;

	/**
	 * @var object
	 * Administrative interface features
	 */
	public $admin;

	/**
	 * @var object
	 * Dealing with WordPress data
	 */
	public $wordpress;

	/**
	 * @var object
	 * Dealing with Pocket data
	 */
	public $pocket;

	/**
	 * Class constructor
	 *
	 * @param string $version The current plugin version
	 * @param string $file The main plugin file
	 */
	public function __construct( $version, $file ) {
		$this->version             = $version;
		$this->file                = $file;
		$this->option_prefix       = 'reading_list_from_pocket_';
		$this->slug                = 'reading-list-from-pocket';
		$this->action_group_suffix = '_check_links';
		$this->login_credentials   = $this->get_login_credentials();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	public function init() {
		// WordPress features
		$this->wordpress = new Reading_List_From_Pocket_WordPress();

		// Pocket API features
		$this->pocket = new Reading_List_From_Pocket_Pocket();

		// Admin features
		$this->admin = new Reading_List_From_Pocket_Admin();
	}

	/**
	 * Get the pre-login Pocket credentials.
	 * These depend on the plugin's settings or constants defined in wp-config.php.
	 *
	 * @return array $login_credentials
	 */
	private function get_login_credentials() {
		$consumer_key      = defined( 'POCKET_CONSUMER_KEY' ) ? POCKET_CONSUMER_KEY : get_option( $this->option_prefix . 'consumer_key', '' );
		$request_token_url = defined( 'POCKET_REQUEST_TOKEN_URL' ) ? POCKET_REQUEST_TOKEN_URL : get_option( $this->option_prefix . 'request_token_url', 'https://getpocket.com/v3/oauth/request' );
		$authorize_url     = defined( 'POCKET_AUTHORIZE_URL' ) ? POCKET_AUTHORIZE_URL : get_option( $this->option_prefix . 'authorize_url', 'https://getpocket.com/v3/oauth/authorize' );
		$redirect_url      = defined( 'POCKET_REDIRECT_URL' ) ? POCKET_REDIRECT_URL : get_option( $this->option_prefix . 'redirect_url', '' );
		$login_credentials = array(
			'consumer_key'      => $consumer_key,
			'request_token_url' => $request_token_url,
			'authorize_url'     => $authorize_url,
			'redirect_url'      => $redirect_url,
		);
		return $login_credentials;
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

	/**
	 * Sanitize a string of HTML classes
	 *
	 */
	public function sanitize_html_classes( $classes, $sep = ' ' ) {
		$return = '';
		if ( ! is_array( $classes ) ) {
			$classes = explode( $sep, $classes );
		}
		if ( ! empty( $classes ) ) {
			foreach ( $classes as $class ) {
				$return .= sanitize_html_class( $class ) . ' ';
			}
		}
		return $return;
	}
}
