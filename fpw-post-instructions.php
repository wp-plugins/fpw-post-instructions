<?php
/*
Plugin Name: FPW Post Instructions
Description: Adds a metabox to Add New Post / Edit Post screens with instructions for editors.
Plugin URI: http://fw2s.com/
Version: 1.1.1
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
	define( 'FPW_POST_INSTRUCTIONS_VERSION', '1.1.1' );
	
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
	$instructions = "";
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
	/* initialize options array */
	$args = array( 'public' => TRUE, '_builtin' => FALSE );
	$output = 'names';
	$operator = 'and';
	$post_types = get_post_types( $args, $output, $operator );
	$fpw_options = get_option( 'fpw_post_instructions_options' );
	$my_type = array( 'enabled' => FALSE, 'title' => '', 'content' => '' );
	if ( !is_array( $fpw_options ) ) {
		$my_types = array( 'post' => $my_type, 'page' => $my_type, 'link' => $my_type );
		/*	initialize public custom post types part */
		foreach ( $post_types as $post_type ) {
			$my_types[ $post_type ] = $my_type;
		}
		$fpw_options = array( 'clean' => FALSE, 'types' => $my_types );
	} else {
		$do_cleanup = $fpw_options[ 'clean '];
		if ( is_string( $fpw_options[ 'instructions' ] ) ) {
			$my_post = array( 'enabled' => TRUE, 'title' => '', 'content' => $fpw_options[ 'instructions' ] );
			$my_types = array( 'post' => $my_post, 'page' => $my_type, 'link' => $my_type );
			/*	initialize public custom post types part */
			foreach ( $post_types as $post_type ) {
				$my_types[ $post_type ] = $my_type;
			}
			$fpw_options = array( 'clean' => FALSE, 'types' => $my_types );
		}
		if ( !is_array( $fpw_options[ 'types' ][ 'link' ] ) )
			$fpw_options[ 'types' ][ 'link' ] = $my_type;
	}
	update_option( 'fpw_post_instructions_options', $fpw_options );
	
	/*	add new custom post types to options if new */
	$new_post_types = false;
	foreach ( $post_types as $post_type ) {
		if ( !is_array( $fpw_options[ 'types' ][ $post_type ] ) ) {
			$fpw_options[ 'types' ][ $post_type ] = $my_type;
			$new_post_types = true;
		}
	}
	if ( $new_post_types )
		update_option( 'fpw_post_instructions_options', $fpw_options );

	/*	read options */
	$do_cleanup = $fpw_options[ 'clean' ];
	
	/*	check if changes were submitted */
	if ( $_POST['fpw_post_instructions_submit'] ) {    
		$do_cleanup = ( $_POST[ 'cleanup' ] == 'yes' );
 		
		/*	database update */
		$fpw_options[ 'clean' ] = $do_cleanup;
		
		/*	Post type: POST */
		$fpw_options[ 'types' ][ 'post' ][ 'enabled' ] = ( $_POST[ 'post-enabled' ] == 'yes' );
		$fpw_options[ 'types' ][ 'post' ][ 'title' ] = $_POST[ 'post-title' ];
		$fpw_options[ 'types' ][ 'post' ][ 'content' ] = stripslashes( $_POST[ 'post-content' ] );

		/*	Post type: PAGE */
		$fpw_options[ 'types' ][ 'page' ][ 'enabled' ] = ( $_POST[ 'page-enabled' ] == 'yes' );
		$fpw_options[ 'types' ][ 'page' ][ 'title' ] = $_POST[ 'page-title' ];
		$fpw_options[ 'types' ][ 'page' ][ 'content' ] = stripslashes( $_POST[ 'page-content' ] );

		/*	Post type: LINK */
		$fpw_options[ 'types' ][ 'link' ][ 'enabled' ] = ( $_POST[ 'link-enabled' ] == 'yes' );
		$fpw_options[ 'types' ][ 'link' ][ 'title' ] = $_POST[ 'link-title' ];
		$fpw_options[ 'types' ][ 'link' ][ 'content' ] = stripslashes( $_POST[ 'link-content' ] );

		/*	Post type: CUSTOM */
		foreach ( $post_types as $post_type ) {
			$fpw_options[ 'types' ][ $post_type ][ 'enabled' ] = ( $_POST[ $post_type . '-enabled' ] == 'yes' );
			$fpw_options[ 'types' ][ $post_type ][ 'title' ] = $_POST[ $post_type . '-title' ];
			$fpw_options[ 'types' ][ $post_type ][ 'content' ] = stripslashes( $_POST[ $post_type . '-content' ] );
		}

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
			echo '	<div id="message" class="updated fade"><p><strong>' . __( 'Settings updated successfully.', 'fpw-post-instructions' ) . '</strong></p></div><br />' . PHP_EOL;
		} else {
			echo '	<div id="message" class="updated fade"><p><strong>' . __( 'No changes detected. Nothing to update.', 'fpw-post-instructions' ) . '</strong></p></div><br />' . PHP_EOL;
		}
	
	/*	about instructions */
	echo	'	<p class="alignright">' . __( 'For guidelines click on', 'fpw-post-instructions' ) . ' <strong>' . __( 'Help', 'fpw-post-instructions' ) . '</strong> ' . __( 'above', 'fpw-post-instructions' ) . '.</p>' . PHP_EOL;

	/*	the form starts here */
	echo '    <form name="fpw_post_instructions_form" action="';
	print '?page=' . basename( __FILE__, '.php' );
	echo '" method="post">' . PHP_EOL;
	
	/*	protect this form with nonce */
	if ( function_exists('wp_nonce_field') ) 
		wp_nonce_field('fpw_post_instructions_options_', 'updates'); 

	/*	cleanup checkbox */
	echo '		<p><input type="checkbox" name="cleanup" value="yes"';
	if ( $do_cleanup ) echo ' checked';
	echo " /> " . __( "Remove plugin's data from database on uninstall", 'fpw-post-instructions' ) . '<br /></p><hr />' . PHP_EOL;
	
	/*	Post type: POST */
	echo '<p><h3>' . __( 'Type', 'fpw-post-instructions' ) . ': post</h3>' . PHP_EOL;
	echo '<input type="checkbox" name="post-enabled" value="yes"';
	if ( $fpw_options[ 'types' ][ 'post' ][ 'enabled' ] )
		echo ' checked';
	echo ' /> ' . __( 'Enabled', 'fpw-post-instructions' ) . '<br /><br />' . PHP_EOL;
	echo __( 'Title ( default', 'fpw-post-instructions' ) . ': <strong>' . __( 'Special Instructions for Editors', 'fpw-post-instructions' ) . '</strong> )<br />' . PHP_EOL;
	echo '<input type="text" name="post-title" value="' . $fpw_options[ 'types' ][ 'post' ][ 'title' ] . '" maxlenght="60" size="60" /><br /><br />' . PHP_EOL;
	echo __( 'Content ( HTML allowed )', 'fpw-post-instructions' ) . '<br />' . PHP_EOL;
	echo '<textarea rows="7" style="width: 100%;" name="post-content">' . $fpw_options[ 'types' ][ 'post' ][ 'content' ] . '</textarea><hr />' . PHP_EOL;
	
	/*	Post type: PAGE */
	echo '<h3>' . __( 'Type', 'fpw-post-instructions' ) . ': page</h3>' . PHP_EOL;
	echo '<input type="checkbox" name="page-enabled" value="yes"';
	if ( $fpw_options[ 'types' ][ 'page' ][ 'enabled' ] )
		echo ' checked';
	echo ' /> ' . __( 'Enabled', 'fpw-post-instructions' ) . '<br /><br />' . PHP_EOL;
	echo __( 'Title ( default', 'fpw-post-instructions' ) . ': <strong>' . __( 'Special Instructions for Editors', 'fpw-post-instructions' ) . '</strong> )<br />' . PHP_EOL;
	echo '<input type="text" name="page-title" value="' . $fpw_options[ 'types' ][ 'page' ][ 'title' ] . '" maxlenght="60" size="60" /><br /><br />' . PHP_EOL;
	echo __( 'Content ( HTML allowed )', 'fpw-post-instructions' ) . '<br />' . PHP_EOL;
	echo '<textarea rows="7" style="width: 100%;" name="page-content">' . $fpw_options[ 'types' ][ 'page' ][ 'content' ] . '</textarea><hr />' . PHP_EOL;
	
	/*	Post type: LINK */
	echo '<h3>' . __( 'Type', 'fpw-post-instructions' ) . ': link</h3>' . PHP_EOL;
	echo '<input type="checkbox" name="link-enabled" value="yes"';
	if ( $fpw_options[ 'types' ][ 'link' ][ 'enabled' ] )
		echo ' checked';
	echo ' /> ' . __( 'Enabled', 'fpw-post-instructions' ) . '<br /><br />' . PHP_EOL;
	echo __( 'Title ( default', 'fpw-post-instructions' ) . ': <strong>' . __( 'Special Instructions for Editors', 'fpw-post-instructions' ) . '</strong> )<br />' . PHP_EOL;
	echo '<input type="text" name="link-title" value="' . $fpw_options[ 'types' ][ 'link' ][ 'title' ] . '" maxlenght="60" size="60" /><br /><br />' . PHP_EOL;
	echo __( 'Content ( HTML allowed )', 'fpw-post-instructions' ) . '<br />' . PHP_EOL;
	echo '<textarea rows="7" style="width: 100%;" name="link-content">' . $fpw_options[ 'types' ][ 'link' ][ 'content' ] . '</textarea><hr />' . PHP_EOL;

	/*	Post type: custom */
	foreach ( $post_types as $post_type ) {
		echo '<h3>' . __( 'Type', 'fpw-post-instructions' ) . ': ' . $post_type . '</h3>' . PHP_EOL;
		echo '<input type="checkbox" name="' . $post_type . '-enabled" value="yes"';
		if ( $fpw_options[ 'types' ][ $post_type ][ 'enabled' ] )
			echo ' checked';
		echo ' /> ' . __( 'Enabled', 'fpw-post-instructions' ) . '<br /><br />' . PHP_EOL;
		echo __( 'Title ( default', 'fpw-post-instructions' ) . ': <strong>' . __( 'Special Instructions for Editors', 'fpw-post-instructions' ) . '</strong> )<br />' . PHP_EOL;
		echo '<input type="text" name="' . $post_type . '-title" value="' . $fpw_options[ 'types' ][ $post_type ][ 'title' ] . '" maxlenght="60" size="60" /><br /><br />' . PHP_EOL;
		echo __( 'Content ( HTML allowed )', 'fpw-post-instructions' ) . '<br />' . PHP_EOL;
		echo '<textarea rows="7" style="width: 100%;" name="' . $post_type . '-content">' . $fpw_options[ 'types' ][ $post_type ][ 'content' ] . '</textarea><hr />' . PHP_EOL;
	}	
	/*	submit button */
	echo '</p><div class="inputbutton"><input type="submit" name="fpw_post_instructions_submit" value="' . __( 'Update Settings', 'fpw-post-instructions' ) . '" /></div>' . PHP_EOL;
	
	/*	end of form */
	echo '		</form>' . PHP_EOL;
	echo '	</p>' . PHP_EOL;
	echo '</div></div>' . PHP_EOL;
}

/*	add meta box to post editing screen */
add_action('add_meta_boxes', 'fpw_post_instructions_add_custom_box');

function fpw_post_instructions_add_custom_box() {
	$opt = get_option( 'fpw_post_instructions_options' );
	if ( is_array( $opt ) )
		foreach ( $opt[ 'types' ] as $key => $value ) {
			if ( $value[ 'enabled' ] ) {
				$title = $value[ 'title' ];
				if ( "" == $title )
					$title = __( 'Special Instructions for Editors', 'fpw-post-instructions' );
				add_meta_box( 'fpw_post_instructions_sectionid', $title, 'fpw_post_instructions_box', $key, 'advanced', 'high', array( 'content' => $value[ 'content' ] ) );
			}
		}
}

function fpw_post_instructions_box( $post, $metabox ) {
	echo $metabox[ 'args' ][ 'content' ];
}
?>