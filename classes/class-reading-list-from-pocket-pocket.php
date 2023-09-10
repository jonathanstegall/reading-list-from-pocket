<?php

/**
 * Pocket API features
 *
 * @package Reading_List_From_Pocket
 */
class Reading_List_From_Pocket_Pocket {

	/**
	 * Current version of the plugin
	 *
	 * @var string
	 */
	public $version;

	/**
	 * The main plugin file
	 *
	 * @var string
	 */
	public $plugin_file;

	/**
	 * The plugin's slug so we can include it when necessary
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * The plugin's prefix when saving options to the database
	 *
	 * @var string
	 */
	public $option_prefix;

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
	 * Login credentials for the Pocket API; comes from wp-config or from the plugin settings
	 *
	 * @var array
	 */
	public $wordpress;


	public function __construct() {

		$this->option_prefix       = reading_list_from_pocket()->option_prefix;
		$this->version             = reading_list_from_pocket()->version;
		$this->slug                = reading_list_from_pocket()->slug;
		$this->plugin_file         = reading_list_from_pocket()->file;
		$this->action_group_suffix = reading_list_from_pocket()->action_group_suffix;
		$this->login_credentials   = reading_list_from_pocket()->login_credentials;
		$this->wordpress           = reading_list_from_pocket()->wordpress;

		// use the option value for whether we're in debug mode.
		$this->debug = filter_var( get_option( $this->option_prefix . 'debug_mode', false ), FILTER_VALIDATE_BOOLEAN );

	}

	/**
	 * Determine if the Pocket API integration is fully configured.
	 */
	public function is_authorized() {
		return ! empty( $this->login_credentials['consumer_key'] ) && $this->load_access_token() && $this->load_pocket_username();
	}

	/**
	 * Get current info for the authorized user
	 */
	public function authorized_user_info() {
		$user = array();
		if ( true === $this->is_authorized() ) {
			$user = array(
				'username'     => $this->load_pocket_username(),
				'access_token' => $this->load_access_token(),
			);
		}
		return $user;
	}

	public function get_request_token( $data ) {
		$consumer_key      = $this->login_credentials['consumer_key'];
		$request_token_url = $this->login_credentials['request_token_url'];
		$redirect_url      = $this->login_credentials['redirect_url'];
		$message           = '';
		if ( '' !== $consumer_key && '' !== $request_token_url && '' !== $redirect_url ) {
			$args = array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'charset'      => 'UTF-8',
					'X-Accept'     => 'application/json',
				),
				'body' => wp_json_encode(
					array(
						'consumer_key' => $consumer_key,
						'redirect_uri' => esc_url_raw( $redirect_url ),
					),
				),
			);
			$response = wp_remote_post( esc_url_raw( $request_token_url ), $args );
			if ( ! is_wp_error( $response ) ) {
				$message = json_decode( wp_remote_retrieve_body( $response ), true );
			} else {
				$message = $response->get_error_message();
			}
		}
		return $message;
	}

	public function request_access_token( $request_token ) {
		$consumer_key  = $this->login_credentials['consumer_key'];
		$authorize_url = $this->login_credentials['authorize_url'];
		$message       = '';
		if ( '' !== $consumer_key && '' !== $authorize_url && '' !== $request_token ) {
			$args = array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'charset'      => 'UTF-8',
					'X-Accept'     => 'application/json',
				),
				'body' => wp_json_encode(
					array(
						'consumer_key' => $consumer_key,
						'code'         => esc_attr( $request_token ),
					),
				),
			);
			$response = wp_remote_post( esc_url_raw( $authorize_url ), $args );
			if ( ! is_wp_error( $response ) ) {
				$message = json_decode( wp_remote_retrieve_body( $response ), true );
			} else {
				$message = $response->get_error_message();
			}
		}
		return $message;
	}

	public function save_token( $data ) {
		$this->set_access_token( $data['access_token'] );
		$this->set_pocket_username( $data['username'] );
		return true;
	}

	/**
	 * Get the access token.
	 */
	public function load_access_token() {
		return get_option( $this->option_prefix . 'access_token', '' );
	}

	/**
	 * Set the access token.
	 * It is stored in session.
	 *
	 * @param string $token Access token from Pocket.
	 */
	protected function set_access_token( $token ) {
		update_option( $this->option_prefix . 'access_token', $token );
	}

	/**
	 * Get the Pocket username.
	 */
	public function load_pocket_username() {
		return get_option( $this->option_prefix . 'pocket_username', '' );
	}

	/**
	 * Set the pocket username.
	 *
	 * @param string $token Username from Pocket.
	 */
	protected function set_pocket_username( $pocket_username ) {
		update_option( $this->option_prefix . 'pocket_username', $pocket_username );
	}

	public function retrieve( $params = array() ) {
		$retrieve_url = 'https://getpocket.com/v3/get';
		
		// required by pocket.
		$params['consumer_key'] = $this->login_credentials['consumer_key'];
		$params['access_token'] = $this->load_access_token();
		
		// required by this plugin.
		if ( ! isset( $params['count'] ) && ! isset( $params['since'] ) ) {
			$params['count'] = 25;
		}

		$cached = $this->wordpress->cache_get( $retrieve_url, $params, false );
		if ( is_array( $cached ) ) {
			$data = $cached;
		} else {
			$args = array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'charset'      => 'UTF-8',
					'X-Accept'     => 'application/json',
				),
				'body' => $params,
			);
			$response = wp_remote_get( esc_url_raw( $retrieve_url ), $args );
			if ( ! is_wp_error( $response ) ) {
				$data = json_decode( wp_remote_retrieve_body( $response ), true );
			} else {
				$data = $response->get_error_message();
				$this->log_error( $data, $reset );
			}
			// only save the body of the request, not the headers.
			$cached = $this->wordpress->cache_set( $retrieve_url, $args['body'], $data );
		}
		return $data;
	}

}
	