<?php
/*
Plugin Name: Reading List From Pocket
Description: Generates a reading list from saved Pocket items
Version: 0.0.1
Author: Jonathan Stegall
Author URI: https://jonathanstegall.com
Text Domain: reading-list-from-pocket
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * The full path to the main file of this plugin
 *
 * This can later be passed to functions such as
 * plugin_dir_path(), plugins_url() and plugin_basename()
 * to retrieve information about plugin paths
 *
 * @since 0.0.1
 * @var string
 */
define( 'READING_LIST_FROM_POCKET_FILE', __FILE__ );

/**
 * The plugin's current version
 *
 * @since 0.0.1
 * @var string
 */
define( 'READING_LIST_FROM_POCKET_VERSION', '0.0.1' );

// Load the autoloader.
require_once( 'lib/autoloader.php' );

/**
 * Retrieve the instance of the main plugin class
 *
 * @since 0.0.6
 * @return Reading_List_From_Pocket
 */
function reading_list_from_pocket() {
	static $plugin;

	if ( is_null( $plugin ) ) {
		$plugin = new Reading_List_From_Pocket( READING_LIST_FROM_POCKET_VERSION, READING_LIST_FROM_POCKET_FILE );
	}

	return $plugin;
}

reading_list_from_pocket()->init();
