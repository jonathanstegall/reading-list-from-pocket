<?php
/**
 * Automatically loads the specified file.
 *
 */

// Start with composer autoload
if ( file_exists( dirname( READING_LIST_FROM_POCKET_FILE ) . '/vendor/autoload.php' ) ) {
	require_once dirname( READING_LIST_FROM_POCKET_FILE ) . '/vendor/autoload.php';
}

/**
 * Enable autoloading of plugin classes
 * @param $class_name
 */
spl_autoload_register(
	function ( $class_name ) {

		// Only autoload classes from this plugin
		if ( 'Reading_List_From_Pocket' !== $class_name && 0 !== strpos( $class_name, 'Reading_List_From_Pocket_' ) ) {
			return;
		}

		// wpcs style filename for each class
		$file_name = 'class-' . str_replace( '_', '-', strtolower( $class_name ) );

		// create file path
		$file = dirname( READING_LIST_FROM_POCKET_FILE ) . '/classes/' . $file_name . '.php';

		// If a file is found, load it
		if ( file_exists( $file ) ) {
			require_once( $file );
		}

	}
);
