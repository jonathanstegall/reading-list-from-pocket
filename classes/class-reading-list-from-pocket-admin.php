<?php

/**
 * Administrative interface features
 *
 * @package Reading_List_From_Pocket
 */
class Reading_List_From_Pocket_Admin {

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
	 * Reading_List_From_Pocket_Pocket class
	 * This contains Pocket API methods
	 *
	 * @var array
	 */
	public $pocket;

	/**
	 * Reading_List_From_Pocket_Pocket class
	 * This contains Pocket API methods
	 *
	 * @var array
	 */
	public $wordpress;

	/**
	 * Object_Sync_Sf_WordPress_Transient class
	 *
	 * @var object
	 */
	private $transients;

	public function __construct() {

		$this->option_prefix       = reading_list_from_pocket()->option_prefix;
		$this->version             = reading_list_from_pocket()->version;
		$this->slug                = reading_list_from_pocket()->slug;
		$this->plugin_file         = reading_list_from_pocket()->file;
		$this->action_group_suffix = reading_list_from_pocket()->action_group_suffix;
		$this->login_credentials   = reading_list_from_pocket()->login_credentials;
		$this->pocket              = reading_list_from_pocket()->pocket;
		$this->wordpress           = reading_list_from_pocket()->wordpress;

		//$this->pages = $this->get_admin_pages();
		$this->tabs = $this->get_admin_tabs();

		$this->add_actions();

	}
	
	/**
	* Create the action hooks to create the admin page(s)
	*
	*/
	private function add_actions() {
		if ( is_admin() ) {
			add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
			add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
			//add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_and_styles' ) );
			add_action( 'admin_init', array( $this, 'admin_settings_form' ) );
		}

	}

	/**
	* Display a Settings link on the main Plugins page
	*
	* @param array $links
	* @param string $file
	* @return array $links
	* These are the links that go with this plugin's entry
	*/
	public function plugin_action_links( $links, $file ) {
		if ( plugin_basename( $this->plugin_file ) === $file ) {
			$settings = '<a href="' . get_admin_url() . 'options-general.php?page=' . $this->slug . '">' . __( 'Settings', 'reading-list-from-pocket' ) . '</a>';
			array_unshift( $links, $settings );
		}
		return $links;
	}
	
		/**
		* Default display for <input> fields
		*
		* @param array $args
		*/
		public function create_admin_menu() {
			add_options_page( __( 'Pocket Settings', 'reading-list-from-pocket' ), __( 'Pocket Settings', 'reading-list-from-pocket' ), 'manage_options', 'reading-list-from-pocket', array( $this, 'show_admin_page' ) );
		}
	
		/**
		* Admin styles. Load the CSS and/or JavaScript for the plugin's settings
		*
		* @return void
		*/
		public function admin_scripts_and_styles() {
			wp_enqueue_script( $this->slug . '-admin', plugins_url( 'assets/js/' . $this->slug . '-admin.min.js', dirname( __FILE__ ) ), array( 'jquery' ), $this->version, true );
			wp_enqueue_style( $this->slug . '-admin', plugins_url( 'assets/css/' . $this->slug . '-admin.min.css', dirname( __FILE__ ) ), array(), $this->version, 'all' );
		}
	
		private function get_admin_tabs() {
			$tabs = array(
				'pocket_settings' => __( 'Pocket Settings', 'reading-list-from-pocket' ),
				'authorize'       => __( 'Authorize', 'reading-list-from-pocket' ),
				//'embed_ads_settings'    => 'Embed Ads Settings',
			); // this creates the tabs for the admin	
			return $tabs;
		}
	
		/**
		* Display the admin settings page
		*
		* @return void
		*/
		public function show_admin_page() {
			$get_data = filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			?>
			<div class="wrap">
				<h1><?php _e( get_admin_page_title() , 'reading-list-from-pocket' ); ?></h1>
	
				<?php
				$tabs = $this->tabs;
				$tab  = isset( $get_data['tab'] ) ? sanitize_key( $get_data['tab'] ) : 'pocket_settings';
				$this->render_tab_menu( $tabs, $tab );
	
				switch ( $tab ) {
					case 'authorize':
						if ( false === is_object( $this->pocket ) ) {
							return;
						}
						$is_authorized = $this->pocket->is_authorized();
						if ( false === $is_authorized ) {
							require_once( plugin_dir_path( $this->plugin_file ) . 'templates/admin/authorize.php' );
						} else {
							$days = array(
								0 => 'Sunday',
								1 => 'Monday',
								2 => 'Tuesday',
								3 => 'Wednesday',
								4 => 'Thursday',
								5 => 'Friday',
								6 => 'Saturday'
							);
							$authorized           = $this->pocket->authorized_user_info();
							$start_of_week        = (int) get_option( 'start_of_week', 1 );
							$start_day_of_week    = $days[ $start_of_week ];
							$demo_result_args     = array(
								'since' => date( 'm/d/Y', strtotime( 'last ' . $start_day_of_week ) )
							);
							$demo_result_args = array();
							$demo_result          = $this->pocket->retrieve( $demo_result_args );
							$demo_retrieved_items = array();
							if ( isset( $demo_result['data']['list'] ) ) {
								$demo_retrieved_items = $demo_result['data']['list'];
							}
							require_once( plugin_dir_path( $this->plugin_file ) . 'templates/admin/authorized.php' );
						}
						break;
					case 'logout':
						$this->logout();
						break;
					default:
						require_once( plugin_dir_path( $this->plugin_file ) . 'templates/admin/settings.php' );
						break;
				} // End switch().
				?>
			</div>
			<?php
		}

		/**
		 * Deauthorize WordPress from Pocket.
		 * This deletes the tokens from the database; it does not currently do anything in Pocket
		 */
		private function logout() {
			$delete_access_token    = delete_option( $this->option_prefix . 'access_token' );
			$delete_pocket_username = delete_option( $this->option_prefix . 'pocket_username' );
			if ( true === $delete_access_token && true === $delete_pocket_username ) {
				echo sprintf(
					'<p>You have been logged out. You can use the <a href="%1$s">%2$s</a> tab to log in again.</p>',
					esc_url( get_admin_url( null, 'options-general.php?page=' . $this->slug . '&tab=authorize' ) ),
					esc_html__( 'Authorize', 'reading-list-for-pocket' )
				);
			}
			
		}
	
		/**
		* Render tabs for settings pages in admin
		* @param array $tabs
		* @param string $tab
		*/
		private function render_tab_menu( $tabs, $tab = '' ) {
	
			$get_data = filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	
			$current_tab = $tab;
			echo '<h2 class="nav-tab-wrapper">';
			foreach ( $tabs as $tab_key => $tab_caption ) {
				$active = $current_tab === $tab_key ? ' nav-tab-active' : '';
				echo sprintf(
					'<a class="nav-tab%1$s" href="%2$s">%3$s</a>',
					esc_attr( $active ),
					esc_url( '?page=' . $this->slug . '&tab=' . $tab_key ),
					esc_html( $tab_caption )
				);
			}
			echo '</h2>';
	
			if ( isset( $get_data['tab'] ) ) {
				$tab = sanitize_key( $get_data['tab'] );
			} else {
				$tab = '';
			}
		}
	
		/**
		* Register items for the settings api
		* @return void
		*
		*/
		public function admin_settings_form() {
	
			$get_data = filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$page     = isset( $get_data['tab'] ) ? sanitize_key( $get_data['tab'] ) : 'pocket_settings';
			$section  = isset( $get_data['tab'] ) ? sanitize_key( $get_data['tab'] ) : 'pocket_settings';
	
			$input_callback_default    = array( $this, 'display_input_field' );
			$textarea_callback_default = array( $this, 'display_textarea' );
			$editor_callback_default   = array( $this, 'display_editor' );
			$input_checkboxes_default  = array( $this, 'display_checkboxes' );
			$input_radio_default       = array( $this, 'display_radio' );
			$input_select_default      = array( $this, 'display_select' );
			$link_default              = array( $this, 'display_link' );
	
			$all_field_callbacks = array(
				'text'       => $input_callback_default,
				'textarea'   => $textarea_callback_default,
				'editor'     => $editor_callback_default,
				'checkboxes' => $input_checkboxes_default,
				'radio'      => $input_radio_default,
				'select'     => $input_select_default,
				'link'       => $link_default,
			);

			if ( strpos( $page, 'settings', true ) ) {
				$this->{ $page }( $page, $section, $all_field_callbacks );
			}
		}
	
	
		/**
		* Fields for the Pocket Settings tab
		* This runs add_settings_section once, as well as add_settings_field and register_setting methods for each option
		*
		* @param string $page
		* @param string $section
		* @param string $input_callback
		*/
		private function pocket_settings( $page, $section, $callbacks ) {
			$tabs = $this->tabs;
			foreach ( $tabs as $key => $value ) {
				if ( $key === $page ) {
					$title = ucwords( str_replace( '-', ' ', $value ) );
				}
			}
			add_settings_section( $page, $title, null, $page );
	
			$settings = array(
				'consumer_key' => array(
					'title'    => __( 'Pocket Consumer Key', 'reading-list-from-pocket' ),
					'callback' => $callbacks['text'],
					'page'     => $page,
					'section'  => $section,
					'args'     => array(
						'type' => 'text',
						'desc' => __( 'Enter the consumer key from the Pocket Developer Program.', 'reading-list-from-pocket' ),
					),
				),
				'redirect_url'                   => array(
					'title'    => __( 'Redirect URL', 'reading-list-from-pocket' ),
					'callback' => $callbacks['text'],
					'page'     => $page,
					'section'  => $section,
					'args'     => array(
						'type'     => 'url',
						'validate' => FILTER_SANITIZE_URL,
						'desc'     => sprintf(
							// translators: %1$s is the admin URL for the Authorize tab.
							__( 'In most cases, you will want to use %1$s for this value.', 'reading-list-from-pocket' ),
							get_admin_url( null, 'options-general.php?page=' . $this->slug . '&tab=authorize' )
						),
						'constant' => 'READING_LIST_FROM_POCKET_REDIRECT_URL',
					),
				),
				'test_mode'       => array(
					'title'    => __( 'Test mode?', 'reading-list-from-pocket' ),
					'callback' => $callbacks['text'],
					'page'     => $page,
					'section'  => $section,
					'args'     => array(
						'type' => 'checkbox',
						'desc' => __( 'If checked, no POST calls will be made to Pocket. This is useful for debugging.', 'reading-list-from-pocket' ),
					),
				),
			);
	
			foreach ( $settings as $key => $attributes ) {
				$id       = $this->option_prefix . $key;
				$name     = $this->option_prefix . $key;
				$title    = $attributes['title'];
				$callback = $attributes['callback'];
				$page     = $attributes['page'];
				$section  = $attributes['section'];
				$args     = array_merge(
					$attributes['args'],
					array(
						'title'     => $title,
						'id'        => $id,
						'label_for' => $id,
						'name'      => $name,
					)
				);
	
				add_settings_field( $id, $title, $callback, $page, $section, $args );
				register_setting( $section, $id );
			}
		}

		private function get_request_token() {
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
							'redirect_uri' => $redirect_url,
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

		private function sign_in_link_to_pocket() {
			$request_token = $this->get_request_token();
			$link          = '';
			if ( isset( $request_token['code'] ) && '' !== $request_token['code'] && isset( $this->login_credentials['redirect_url'] ) ) {
				$link = 'https://getpocket.com/auth/authorize?request_token=' . $request_token['code'] . '&redirect_uri=' . urlencode( $this->login_credentials['redirect_url'] . '&amp;request_token=' . $request_token['code'] );
			}
			return $link;
		}
	
		/**
		* Add the option items individually to the option tabs for benefit pages
		*
		* @param array $sections
		* @param array $names
		* @return $array $sections
		*
		*/
		private function generate_sections( $sections, $names = array() ) {
			if ( ! empty( $names ) ) {
				$names = explode( "\r\n", $names );
				foreach ( $names as $names ) {
					$names       = ltrim( $names, '/' );
					$names_array = explode( '/', $names );
					if ( ! isset( $names_array[1] ) && ! isset( $names_array[2] ) ) {
						$name  = $names_array[0];
						$title = ucwords( str_replace( '-', ' ', $names_array[0] ) );
					} elseif ( isset( $names_array[1] ) && ! isset( $names_array[2] ) ) {
						$name  = $names_array[0] . '-' . $names_array[1];
						$title = ucwords( str_replace( '-', ' ', $names_array[1] ) );
					} elseif ( isset( $names_array[1] ) && isset( $names_array[2] ) ) {
						$name  = $names_array[0] . '-' . $names_array[1] . '-' . $names_array[2];
						$title = ucwords( str_replace( '-', ' ', $names_array[2] ) );
					}
					$sections[ $name ] = $title;
				}
			}
			return $sections;
		}
	
		/**
		* Default display for <input> fields
		*
		* @param array $args
		*/
		public function display_input_field( $args ) {
			$type    = $args['type'];
			$id      = $args['label_for'];
			$name    = $args['name'];
			$desc    = $args['desc'];
			$checked = '';
	
			$class = 'regular-text';
	
			if ( 'checkbox' === $type ) {
				$class = 'checkbox';
			}
	
			if ( isset( $args['class'] ) ) {
				$class = $args['class'];
			}
	
			if ( ! isset( $args['constant'] ) || ! defined( $args['constant'] ) ) {
				$value = esc_attr( get_option( $id, '' ) );
				if ( 'checkbox' === $type ) {
					$value = filter_var( get_option( $id, false ), FILTER_VALIDATE_BOOLEAN );
					if ( true === $value ) {
						$checked = 'checked ';
					}
					$value = 1;
				}
				if ( '' === $value && isset( $args['default'] ) && '' !== $args['default'] ) {
					$value = $args['default'];
				}
	
				echo sprintf(
					'<input type="%1$s" value="%2$s" name="%3$s" id="%4$s" class="%5$s"%6$s>',
					esc_attr( $type ),
					esc_attr( $value ),
					esc_attr( $name ),
					esc_attr( $id ),
					reading_list_from_pocket()->sanitize_html_classes( $class, esc_html( ' code' ) ),
					esc_html( $checked )
				);
				if ( '' !== $desc ) {
					echo sprintf(
						'<p class="description">%1$s</p>',
						esc_html( $desc )
					);
				}
			} else {
				echo sprintf(
					'<p><code>%1$s</code></p>',
					esc_html__( 'Defined in wp-config.php', 'reading-list-from-pocket' )
				);
			}
		}
	
		/**
		* Default display for <textarea> fields
		*
		* @param array $args
		*/
		public function display_textarea( $args ) {
			$id      = $args['id'];
			$name    = $args['name'];
			$desc    = $args['desc'];
			$checked = '';
	
			$class = 'regular-text';
	
			if ( ! isset( $args['constant'] ) || ! defined( $args['constant'] ) ) {
				$value = esc_attr( get_option( $id, '' ) );
				if ( '' === $value && isset( $args['default'] ) && '' !== $args['default'] ) {
					$value = $args['default'];
				}
	
				echo sprintf(
					'<textarea name="%1$s" id="%2$s" class="%3$s" rows="10">%4$s</textarea>',
					esc_attr( $name ),
					esc_attr( $id ),
					reading_list_from_pocket()->sanitize_html_classes( $class . esc_html( ' code' ) ),
					esc_attr( $value )
				);
				if ( '' !== $desc ) {
					echo sprintf(
						'<p class="description">%1$s</p>',
						esc_html( $desc )
					);
				}
			} else {
				echo sprintf(
					'<p><code>%1$s</code></p>',
					esc_html__( 'Defined in wp-config.php', 'reading-list-from-pocket' )
				);
			}
		}
	
		/**
		* Display for a wysiwyg editir
		*
		* @param array $args
		*/
		public function display_editor( $args ) {
			$id      = $args['label_for'];
			$name    = $args['name'];
			$desc    = $args['desc'];
			$checked = '';
	
			$class = 'regular-text';
	
			if ( ! isset( $args['constant'] ) || ! defined( $args['constant'] ) ) {
				$value = wp_kses_post( get_option( $id, '' ) );
				if ( '' === $value && isset( $args['default'] ) && '' !== $args['default'] ) {
					$value = $args['default'];
				}
	
				$settings = array();
				if ( isset( $args['wpautop'] ) ) {
					$settings['wpautop'] = $args['wpautop'];
				}
				if ( isset( $args['media_buttons'] ) ) {
					$settings['media_buttons'] = $args['media_buttons'];
				}
				if ( isset( $args['default_editor'] ) ) {
					$settings['default_editor'] = $args['default_editor'];
				}
				if ( isset( $args['drag_drop_upload'] ) ) {
					$settings['drag_drop_upload'] = $args['drag_drop_upload'];
				}
				if ( isset( $args['name'] ) ) {
					$settings['textarea_name'] = $args['name'];
				}
				if ( isset( $args['rows'] ) ) {
					$settings['textarea_rows'] = $args['rows']; // default is 20
				}
				if ( isset( $args['tabindex'] ) ) {
					$settings['tabindex'] = $args['tabindex'];
				}
				if ( isset( $args['tabfocus_elements'] ) ) {
					$settings['tabfocus_elements'] = $args['tabfocus_elements'];
				}
				if ( isset( $args['editor_css'] ) ) {
					$settings['editor_css'] = $args['editor_css'];
				}
				if ( isset( $args['editor_class'] ) ) {
					$settings['editor_class'] = $args['editor_class'];
				}
				if ( isset( $args['teeny'] ) ) {
					$settings['teeny'] = $args['teeny'];
				}
				if ( isset( $args['dfw'] ) ) {
					$settings['dfw'] = $args['dfw'];
				}
				if ( isset( $args['tinymce'] ) ) {
					$settings['tinymce'] = $args['tinymce'];
				}
				if ( isset( $args['quicktags'] ) ) {
					$settings['quicktags'] = $args['quicktags'];
				}
	
				wp_editor( $value, $id, $settings );
				if ( '' !== $desc ) {
					echo sprintf(
						'<p class="description">%1$s</p>',
						esc_html( $desc )
					);
				}
			} else {
				echo sprintf(
					'<p><code>%1$s</code></p>',
					esc_html__( 'Defined in wp-config.php', 'reading-list-from-pocket' )
				);
			}
		}
	
		/**
		* Display for multiple checkboxes
		* Above method can handle a single checkbox as it is
		*
		* @param array $args
		*/
		public function display_checkboxes( $args ) {
			$type         = 'checkbox';
			$name         = $args['name'];
			$overall_desc = $args['desc'];
			$options      = get_option( $name, array() );
			$html         = '<div class="checkboxes">';
			foreach ( $args['items'] as $key => $value ) {
				$text        = $value['text'];
				$id          = $value['id'];
				$desc        = $value['desc'];
				$checked     = '';
				$field_value = isset( $value['value'] ) ? esc_attr( $value['value'] ) : esc_attr( $key );
	
				if ( is_array( $options ) && in_array( (string) $field_value, $options, true ) ) {
					$checked = 'checked';
				} elseif ( is_array( $options ) && empty( $options ) ) {
					if ( isset( $value['default'] ) && true === $value['default'] ) {
						$checked = 'checked';
					}
				}
				$html .= sprintf(
					'<div class="checkbox"><label><input type="%1$s" value="%2$s" name="%3$s[]" id="%4$s"%5$s>%6$s</label></div>',
					esc_attr( $type ),
					$field_value,
					esc_attr( $name ),
					esc_attr( $id ),
					esc_html( $checked ),
					esc_html( $text )
				);
				if ( '' !== $desc ) {
					$html .= sprintf(
						'<p class="description">%1$s</p>',
						esc_html( $desc )
					);
				}
			}
			if ( '' !== $overall_desc ) {
				$html .= sprintf(
					'<p class="description">%1$s</p>',
					esc_html( $overall_desc )
				);
			}
			$html .= '</div>';
			echo $html;
		}
	
		/**
		* Display for mulitple radio buttons
		*
		* @param array $args
		*/
		public function display_radio( $args ) {
			$type = 'radio';
	
			$name       = $args['name'];
			$group_desc = $args['desc'];
			$options    = get_option( $name, array() );
	
			foreach ( $args['items'] as $key => $value ) {
				$text = $value['text'];
				$id   = $value['id'];
				$desc = $value['desc'];
				if ( isset( $value['value'] ) ) {
					$item_value = $value['value'];
				} else {
					$item_value = $key;
				}
				$checked = '';
				if ( is_array( $options ) && in_array( (string) $item_value, $options, true ) ) {
					$checked = 'checked';
				} elseif ( is_array( $options ) && empty( $options ) ) {
					if ( isset( $value['default'] ) && true === $value['default'] ) {
						$checked = 'checked';
					}
				}
	
				$input_name = $name;
	
				echo sprintf(
					'<div class="radio"><label><input type="%1$s" value="%2$s" name="%3$s[]" id="%4$s"%5$s>%6$s</label></div>',
					esc_attr( $type ),
					esc_attr( $item_value ),
					esc_attr( $input_name ),
					esc_attr( $id ),
					esc_html( $checked ),
					esc_html( $text )
				);
				if ( '' !== $desc ) {
					echo sprintf(
						'<p class="description">%1$s</p>',
						esc_html( $desc )
					);
				}
			}
	
			if ( '' !== $group_desc ) {
				echo sprintf(
					'<p class="description">%1$s</p>',
					esc_html( $group_desc )
				);
			}
	
		}
	
		/**
		* Display for a dropdown
		*
		* @param array $args
		*/
		public function display_select( $args ) {
			$type = $args['type'];
			$id   = $args['label_for'];
			$name = $args['name'];
			$desc = $args['desc'];
			if ( ! isset( $args['constant'] ) || ! defined( $args['constant'] ) ) {
				$current_value = get_option( $name );
	
				echo sprintf(
					'<div class="select"><select id="%1$s" name="%2$s"><option value="">- Select one -</option>',
					esc_attr( $id ),
					esc_attr( $name )
				);
	
				foreach ( $args['items'] as $key => $value ) {
					$text     = $value['text'];
					$value    = $value['value'];
					$selected = '';
					if ( $key === $current_value || $value === $current_value ) {
						$selected = ' selected';
					}
	
					echo sprintf(
						'<option value="%1$s"%2$s>%3$s</option>',
						esc_attr( $value ),
						esc_attr( $selected ),
						esc_html( $text )
					);
	
				}
				echo '</select>';
				if ( '' !== $desc ) {
					echo sprintf(
						'<p class="description">%1$s</p>',
						esc_html( $desc )
					);
				}
				echo '</div>';
			} else {
				echo sprintf(
					'<p><code>%1$s</code></p>',
					esc_html__( 'Defined in wp-config.php', 'reading-list-from-pocket' )
				);
			}
		}
	
		/**
		* Default display for <a href> links
		*
		* @param array $args
		*/
		public function display_link( $args ) {
			$label = $args['label'];
			$desc  = $args['desc'];
			$url   = $args['url'];
			if ( isset( $args['link_class'] ) ) {
				echo sprintf(
					'<p><a class="%1$s" href="%2$s">%3$s</a></p>',
					esc_attr( $args['link_class'] ),
					esc_url( $url ),
					esc_html( $label )
				);
			} else {
				echo sprintf(
					'<p><a href="%1$s">%2$s</a></p>',
					esc_url( $url ),
					esc_html( $label )
				);
			}
	
			if ( '' !== $desc ) {
				echo sprintf(
					'<p class="description">%1$s</p>',
					esc_html( $desc )
				);
			}
	
		}
	
	}
	