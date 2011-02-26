<?php
/*
Plugin Name: FPW Post Instructions
Description: Adds a metabox to Add New Post / Edit Post screens with instructions for editors.
Plugin URI: http://fw2s.com/
Version: 1.0.0
Author: Frank P. Walentynowicz
Author URI: http://fw2s.com/

Copyright 2011 Frank P. Walentynowicz (email : frankpw@fw2s.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined( 'FPW_POST_INSTRUCTIONS_VERSION') )
	define( 'FPW_POST_INSTRUCTIONS_VERSION', '1.0.0' );
	
/*	--------------------------------
	Load text domain for translation
	----------------------------- */

add_action('init', 'fpw_post_instructions_init', 1);

function fpw_post_instructions_init(){
	load_plugin_textdomain( 'fpw-post-instructions', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}	

/*	----------------------------------
	Register plugin's menu in Settings
	------------------------------- */

//	Add plugin's options page
add_action('admin_menu', 'fpw_post_instructions_settings_menu');

function fpw_post_instructions_settings_menu() {
	global 	$fpw_post_instructions_hook;
	$page_title = __('FPW Post Instructions - Settings', 'fpw-post-instructions') . ' (' . FPW_POST_INSTRUCTIONS_VERSION . ')';
	$menu_title = __('FPW Post Instructions', 'fpw-post-instructions');
	$fpw_post_instructions_hook = add_options_page( $page_title, $menu_title, 'manage_options', 'fpw-post-instructions', 'fpw_post_instructions_settings');
}

/*	-------------------------------------
	Register plugin's filters and actions
	---------------------------------- */

register_activation_hook( __FILE__, 'fpw_post_instructions_activate' );

function fpw_post_instructions_activate() {
	/*	base name for uninstall file */
	$uninstall = ABSPATH . PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/uninstall.';
	
	/*	get options array */
	$fpw_options = get_option( 'fpw_post_instructions_options' );
	if ( is_array( $fpw_options ) ) {

		/* if cleanup requested make uninstall.php otherwise make uninstall.txt */
		if ( $fpw_options[ 'clean' ] ) {
			if ( file_exists( $uninstall . 'txt' ) )
				rename( $uninstall . 'txt', $uninstall . 'php' );
		} else {
			if ( file_exists( $uninstall . 'php' ) )
				rename( $uninstall . 'php', $uninstall . 'txt' );
		}
	}
}

add_filter('plugin_action_links_fpw-post-instructions/fpw-post-instructions.php', 'fpw_post_instructions_plugin_links', 10, 2);

function fpw_post_instructions_plugin_links($links, $file) {
   	$settings_link = '<a href="/wp-admin/options-general.php?page=fpw-post-instructions">'.__("Settings", "fpw-post-instructions").'</a>';
	array_unshift($links, $settings_link);
    return $links;
}

add_action('after_plugin_row_fpw-post-instructions/fpw-post-instructions.php', 'fpw_post_instructions_add_after_plugin_meta', 10, 2);

function fpw_post_instructions_add_after_plugin_meta($file,$plugin_data) {
	$current = get_site_transient('update_plugins');
	if (!isset($current->response[$file])) return false;
	$url = "http://fw2s.com/fpwpostinstructionsupdate.txt";
	$update = wp_remote_fopen($url);
	echo '<tr class="plugin-update-tr"><td></td><td></td><td class="plugin-update"><div class="update-message">'.$update.'</div></td></tr>';
}

add_filter('contextual_help', 'fpw_post_instructions_help', 10, 3);

function fpw_post_instructions_help($contextual_help, $screen_id, $screen) {
	global $fpw_post_instructions_hook;
	
	if ($screen_id == $fpw_post_instructions_hook) {
	}	
	return $contextual_help; 
}

/*	----------------------
	Plugin's settings page
	------------------- */

function fpw_post_instructions_settings() {
	/* base name for uninstall file */
	$uninstall = ABSPATH . PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/uninstall.';
	
	/* initialize options array */
	$fpw_options = get_option( 'fpw_post_instructions_options' );
	if ( !is_array( $fpw_options ) ) {
		$fpw_options = array( 'clean' => FALSE, 'instructions' => '' );
		update_option( 'fpw_post_instructions_options', $fpw_options );
	}

	/*	read options */
	$do_cleanup = $fpw_options[ 'clean' ];
	$instructions = $fpw_options[ 'instructions' ];
	
	/*	check if changes were submitted */
	if ( $_POST['fpw_post_instructions_submit'] ) {    
		$do_cleanup = ( $_POST[ 'cleanup' ] == 'yes' );
 		
		/*	database update */
		$fpw_options[ 'clean' ] = $do_cleanup;
		$instructions = stripslashes( $_POST[ 'fpw-post-instructions-text' ] );
		$fpw_options[ 'instructions' ] = $instructions;
		$updateok = update_option( 'fpw_post_instructions_options', $fpw_options );
		
		/* if cleanup requested make uninstall.php otherwise make uninstall.txt */
		if ( $updateok ) 
			fpw_post_instructions_activate();
	}

/*	-------------------------
	Settings page starts here
	---------------------- */
	
	echo '<div class="wrap">' . PHP_EOL;
	echo '	<h2>' . __( 'FPW Post Instructions - Settings', 'fpw-post-instructions' ) . ' (' . FPW_POST_INSTRUCTIONS_VERSION . ')</h2>' . PHP_EOL;

	/*	display message about update status */
	if ( $_POST['fpw_post_instructions_submit'] )
		if ( $updateok ) {
			echo '	<div id="message" class="updated fade"><p><strong>' . __( 'Settings updated successfully.', 'fpw-post-instructions' ) . '</strong></p></div>' . PHP_EOL;
		} else {
			echo '	<div id="message" class="updated fade"><p><strong>' . __( 'No changes detected. Nothing to update.', 'fpw-post-instructions' ) . '</strong></p></div>' . PHP_EOL;
		}
	
	/*	about instructions */
	echo	'	<p class="alignright">' . __( 'For guidelines click on', 'fpw-post-instructions' ) . ' <strong>' . __( 'Help', 'fpw-post-instructions' ) . '</strong> ' . __( 'above', 'fpw-post-instructions' ) . '.</p>' . PHP_EOL;

	/*	the form starts here */
	echo '	<p>' . PHP_EOL;
	echo '		<form name="fpw_post_instructions_form" action="';
	print '?page=' . basename( __FILE__, '.php' );
	echo '" method="post">' . PHP_EOL;
	
	/*	protect this form with nonce */
	if ( function_exists('wp_nonce_field') ) 
		wp_nonce_field('fpw_post_instructions_options_', 'updates'); 

	/*	cleanup checkbox */
	echo '			<input type="checkbox" name="cleanup" value="yes"';
	if ( $do_cleanup ) echo ' checked';
	echo "> " . __( "Remove plugin's data from database on uninstall", 'fpw-post-instructions' ) . '<br /><br />' . PHP_EOL;
	echo '<strong>' . __( 'Special Instructions for Editors metabox - Content', 'fpw-post-instructions') . ':</strong><br />' . PHP_EOL;
	
	/*	instructions textarea */
	echo '<div id="poststuff">';
	echo '<textarea rows="15" style="width: 100%;" class="fpw-post-instructions-text" id="fpw-post-instructions-text" name="fpw-post-instructions-text">' . $instructions . '</textarea></div><br />' . PHP_EOL;
	
	/*	submit button */
	echo '			<div class="inputbutton"><input type="submit" name="fpw_post_instructions_submit" value="' . __( 'Update Settings', 'fpw-post-instructions' ) . '" /></div>' . PHP_EOL;
	
	/*	end of form */
	echo '		</form>' . PHP_EOL;
	echo '	</p>' . PHP_EOL;
	echo '</div>' . PHP_EOL;
}

/*	add meta box to post editing screen */
add_action('add_meta_boxes', 'fpw_post_instructions_add_custom_box');

function fpw_post_instructions_add_custom_box() {
	add_meta_box( 'fpw_post_instructions_sectionid', __( 'Special Instructions for Editors', 'fpw-post-instuctions' ), 'fpw_post_instructions_box', 'post', 'advanced', 'high' );
}

/* Prints the box content */
function fpw_post_instructions_box() {
	// The actual text in the box
	$fpw_options = get_option( 'fpw_post_instructions_options' );
	if ( is_array( $fpw_options ) )
		echo $fpw_options[ 'instructions' ];
}
?>