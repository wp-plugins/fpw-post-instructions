<?php
/*
Plugin Name: FPW Post Instructions
Description: Adds metaboxes to admin editing screens for posts, pages, links,
and custom post types with instructions for editors.
Plugin URI: http://fw2s.com/2011/02/28/fpw-post-instructions-plugin/
Version: 1.1.6
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

global $fpw_visual;

if ( !defined( 'FPW_POST_INSTRUCTIONS_VERSION') )
	define( 'FPW_POST_INSTRUCTIONS_VERSION', '1.1.6' );

/*	--------------------------------
	Load text domain for translation
	----------------------------- */

function fpw_post_instructions_init() {
	load_plugin_textdomain( 'fpw-post-instructions', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}	
add_action('init', 'fpw_post_instructions_init', 1);

/*	----------------------------------
	Register plugin's menu in Settings
	------------------------------- */

function fpw_post_instructions_settings_menu() {
	global 	$fpw_post_instructions_hook;

	$page_title = __('FPW Post Instructions - Settings', 'fpw-post-instructions') . ' (' . FPW_POST_INSTRUCTIONS_VERSION . ')';
	$menu_title = __('FPW Post Instructions', 'fpw-post-instructions');
	$fpw_post_instructions_hook = add_options_page( $page_title, $menu_title, 'manage_options', 'fpw-post-instructions', 'fpw_post_instructions_settings');
}
add_action('admin_menu', 'fpw_post_instructions_settings_menu');

/*	-------------------------------------
	Register plugin's filters and actions
	---------------------------------- */

function fpw_post_instructions_activate() {
	/*	base name for uninstall file */
	$uninstall = ABSPATH . PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/uninstall.';

	/*	get options array */
	$fpw_options = get_option( 'fpw_post_instructions_options' );
	
	/*	if no options yet let's build it */
	if ( !$fpw_options ) {
		$fpw_options = array( 'clean' => false, 'visual' => false, 'visual-type' => 'post', 'types' => array() );
	} 

	/* if cleanup requested make uninstall.php otherwise make uninstall.txt */
	if ( $fpw_options[ 'clean' ] ) {
		if ( file_exists( $uninstall . 'txt' ) )
			rename( $uninstall . 'txt', $uninstall . 'php' );
	} else {
		if ( file_exists( $uninstall . 'php' ) )
			rename( $uninstall . 'php', $uninstall . 'txt' );
	}
}
register_activation_hook( __FILE__, 'fpw_post_instructions_activate' );

function fpw_post_instructions_plugin_links($links, $file) {
   	$settings_link = '<a href="/wp-admin/options-general.php?page=fpw-post-instructions">'.__("Settings", "fpw-post-instructions").'</a>';
	array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_fpw-post-instructions/fpw-post-instructions.php', 'fpw_post_instructions_plugin_links', 10, 2);

function fpw_post_instructions_add_after_plugin_meta($file,$plugin_data) {
	$current = get_site_transient('update_plugins');
	if (!isset($current->response[$file]))
		return false;
	$url = "http://fw2s.com/fpwpostinstructionsupdate.txt";
	$update = wp_remote_fopen($url);
	echo '<tr class="plugin-update-tr"><td></td><td></td><td class="plugin-update"><div class="update-message">'.$update.'</div></td></tr>';
}
add_action('after_plugin_row_fpw-post-instructions/fpw-post-instructions.php', 'fpw_post_instructions_add_after_plugin_meta', 10, 2);

function fpw_post_instructions_help($contextual_help, $screen_id, $screen) {
	global $fpw_post_instructions_hook;

	if ($screen_id == $fpw_post_instructions_hook) {

		/*	display description block */
		$my_help  = '<h3>' . __( 'Description', 'fpw-post-instructions' ) . '</h3>' . PHP_EOL;
		$my_help .= '<p>' . __( 'This plugin provides ability to create instructional metaboxes on editing screens for posts, pages, links, and custom post types.', 'fpw-post-instructions' );
		$my_help .= ' ' . __( 'Its purpose is explained in detail on', 'fpw-post-instructions' ) . ' <a href="http://fw2s.com/2011/02/28/fpw-post-instructions-plugin/" target="_blank">';
		$my_help .= __( "author's website.", "fpw-post-instructions" ) . '</a></p>' . PHP_EOL;

		/*	display support block */
		$my_help .= '<h3>' . __( 'Support', 'fpw-post-instructions' ) . '</h3>' . PHP_EOL;
		$my_help .= '<p>' . __( 'For support please go to', 'fpw-post-instructions' ) .' <a href="http://wordpress.org/tags/fpw-post-instructions?forum_id=10" target="_blank">';
		$my_help .= __( "plugin's support forum.", "fpw-post-instructions" ) . '</a></p>' . PHP_EOL;

		/*	WordPress default help */
		$my_help .= '<h3>WordPress</h3>' . PHP_EOL;
		$my_help .= '<p>' . PHP_EOL;
		$my_help .= $contextual_help;

		$my_help .= '</p>' . PHP_EOL;
		$contextual_help = $my_help;
	}	
	return $contextual_help; 
}
add_filter('contextual_help', 'fpw_post_instructions_help', 10, 3);

/*	----------------
	Rich Text Editor
	------------- */

function fpw_post_instructions_editor_admin_init() {
	global $fpw_visual;

	$fpw_visual = user_can_richedit();
	$fpw_options = get_option( 'fpw_post_instructions_options' );

	if ( !fpw_options ) {
		$visual_ok = false;
	} else {
		$visual_ok = ( true === $fpw_options[ 'visual' ] );
	}

	wp_enqueue_script('word-count');
	wp_enqueue_script('post');
	wp_enqueue_script('editor');
	add_thickbox();
	wp_enqueue_script('media-upload');

	if ( $fpw_visual ) {

		function fpw_post_instructions_editor_admin_head() {
			wp_tiny_mce();
		}
		add_action('admin_head', 'fpw_post_instructions_editor_admin_head');
	}
}
add_action('admin_init', 'fpw_post_instructions_editor_admin_init');

/*	----------------------
	Plugin's settings page
	------------------- */

function fpw_post_instructions_settings() {
	global $fpw_visual;

	/*	get custom post type names array */
	$args = array( 'public' => true, '_builtin' => false );
	$output = 'names';
	$operator = 'and';
	$post_type_names = array( 'post', 'page', 'link' );
	$cust_type_names = get_post_types( $args, $output, $operator );

    foreach ( $cust_type_names as $cust_type_name )
    	array_push( $post_type_names, $cust_type_name );

	$my_type = array( 'enabled' => false, 'title' => '', 'content' => '' );
	$my_types = array();

	foreach ( $post_type_names as $post_type_name )
		$my_types[ $post_type_name ] = $my_type;

	/*	get plugin's options */
	$fpw_options = get_option( 'fpw_post_instructions_options' );
	if ( !$fpw_options ) {
		/*	there is no plugin's option, let's build it */
		$fpw_options = array( 'clean' => false, visual => false, 'visual-type' => 'post', 'types' => $my_types );
		update_option( 'fpw_post_instructions_options', $fpw_options );
	} else {
        $do_cleanup = ( true === $fpw_options[ 'clean' ] );
		$visual_ok = ( true === $fpw_options[ 'visual' ] );
		$visual_type = is_string( $fpw_options[ 'visual-type' ] ) ? $fpw_options[ 'visual-type' ] : 'post';

		$fpw_options[ 'clean' ] = $do_cleanup;
		$fpw_options[ 'visual' ] = $visual_ok;
		$fpw_options[ 'visual-type' ] = $visual_type;

		foreach ( $post_type_names as $post_type_name ) {
			if ( !is_array( $fpw_options[ 'types' ][ $post_type_name ] ) )
				$fpw_options[ 'types' ][ $post_type_name ] = $my_type;
		}

		/*	now let's remove deleted custom post types arrays from options */
		$opt_keys = array_keys( $fpw_options[ 'types' ] );
		foreach ( $opt_keys as $opt_key ) {
			if ( !in_array( $opt_key, $post_type_names ) )
				unset( $fpw_options[ 'types' ][ $opt_key ] ); 
		}
		update_option( 'fpw_post_instructions_options', $fpw_options );
	}

	/*	make sure we have following two as pre POST values */
	$visual_ok = $fpw_options[ 'visual' ];
	$visual_type = $fpw_options[ 'visual-type' ];

	$update_ok = false;

	/*	check if changes were submitted */
	if ( ( $_POST[ 'fpw_post_instructions_submit' ] ) || ( $_POST[ 'fpw_post_instructions_submit_top' ] ) ) {
		if ( !isset( $_POST[ 'fpw-post-instructions-nonce' ] ) ) 
			die( '<br />&nbsp;<br /><p style="padding-left: 20px; color: red"><strong>' . __( 'You did not send any credentials!', 'fpw-post-instructions' ) . '</strong></p>' );
		if ( !wp_verify_nonce( $_POST[ 'fpw-post-instructions-nonce' ], 'fpw-post-instructions-nonce' ) ) 
			die( '<br />&nbsp;<br /><p style="padding-left: 20px; color: red;"><strong>' . __( 'You did not send the right credentials!', 'fpw-post-instructions' ) . '</strong></p>' );

		foreach ( $post_type_names as $post_type_name ) {
			$fpw_options[ 'types' ][ $post_type_name ][ 'enabled' ] = ( 'yes' == $_POST[ $post_type_name . '-enabled' ] );
			$fpw_options[ 'types' ][ $post_type_name ][ 'title' ] = stripslashes( $_POST[ $post_type_name . '-title' ] );
			if ( $fpw_visual && $visual_ok && ( $post_type_name == $visual_type ) ) {
				$fpw_options[ 'types' ][ $post_type_name ][ 'content' ] = stripslashes( $_POST[ 'content' ] );
			} else {
				$fpw_options[ 'types' ][ $post_type_name ][ 'content' ] = stripslashes( $_POST[ $post_type_name . '-content' ] );
			}
		}

		$fpw_options[ 'clean' ] = ( 'yes' == $_POST[ 'cleanup' ] );
		$fpw_options[ 'visual' ] = ( 'yes' == $_POST[ 'visual' ] );
		$fpw_options[ 'visual-type' ] = $_POST[ 'fpw-radio-visual' ];

		$update_ok = update_option( 'fpw_post_instructions_options', $fpw_options );

		$do_cleanup = $fpw_options[ 'clean' ];
		$visual_ok = $fpw_options[ 'visual' ];
		$visual_type = $fpw_options[ 'visual-type' ];

		/* if cleanup requested make uninstall.php otherwise make uninstall.txt */
		if ( $update_ok ) 
			fpw_post_instructions_activate();
	}

/*	---------------------------------
	HTML of settings page starts here
	------------------------------ */

	echo '<div class="wrap">' . PHP_EOL;
	echo '<div id="icon-edit-pages" class="icon32"></div><h2>' . __( 'FPW Post Instructions - Settings', 'fpw-post-instructions' ) . ' (' . FPW_POST_INSTRUCTIONS_VERSION . ')</h2>' . PHP_EOL;

	/*	display message about update status */
	if ( ( $_POST[ 'fpw_post_instructions_submit' ] ) || ( $_POST[ 'fpw_post_instructions_submit_top' ] ) )
		if ( $update_ok ) {
			echo '<div id="message" class="updated fade"><p><strong>' . __( 'Updated successfully.', 'fpw-post-instructions' ) . '</strong></p></div>' . PHP_EOL;
		} else {
			echo '<div id="message" class="updated fade"><p><strong>' . __( 'No changes detected. Nothing to update.', 'fpw-post-instructions' ) . '</strong></p></div>' . PHP_EOL;
		}

	/*	about HELP instructions */
	echo '<p class="alignright">' . __( 'For guidelines click on', 'fpw-post-instructions' ) . ' <strong>' . __( 'Help', 'fpw-post-instructions' ) . '</strong> ' . __( 'above', 'fpw-post-instructions' ) . '.</p>' . PHP_EOL;

	/*	the form starts here */
	echo '<form name="fpw_post_instructions_form" action="?page=' . basename( __FILE__, '.php' ) . '" method="post">' . PHP_EOL;

	/*	protect this form with nonce */
	echo '<input name="fpw-post-instructions-nonce" type="hidden" value="' . wp_create_nonce( 'fpw-post-instructions-nonce' ) . '" />' . PHP_EOL;

	/*	cleanup checkbox */
	echo '<p><input type="checkbox" name="cleanup" value="yes"';
	if ( $do_cleanup ) echo ' checked';
	echo " /> " . __( "Remove plugin's data from database on uninstall", 'fpw-post-instructions' ) . '<br />' . PHP_EOL;

	/*	visual checkbox and radio selectors */
	echo '<input type="checkbox" name="visual" value="yes"';
	if ( $visual_ok ) 
		echo ' checked';
	echo ' /> ' . __( "Activate visual editor for:", 'fpw-post-instructions' ) . '&nbsp;&nbsp| ';

	foreach ( $post_type_names as $post_type_name ) {
		echo '<strong>' . $post_type_name . '</strong> <input type="radio" name="fpw-radio-visual" value="' . $post_type_name . '" ';
		$tmp = '';
		if ( $post_type_name == $visual_type )
			$tmp = 'CHECKED';
		echo $tmp . ' /> | ';
	}

	echo PHP_EOL;

	if ( !$fpw_visual ) 
		echo '&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;"><strong>**** ' . __( 'To use this option you must enable rich text editing in your profile!', 'fpw-post-instructions' ) . ' ****</strong></span>';
	echo '<br /></p>' .PHP_EOL;

	/*	top submit button */
	echo '<div class="inputbutton"><input class="button-primary" type="submit" name="fpw_post_instructions_submit_top" value="' . __( 'Update', 'fpw-post-instructions' ) . '" /></div>' . PHP_EOL;

	/*	for each post type */
	foreach ( $post_type_names as $post_type_name ) {
		echo '<br />' . PHP_EOL;
		echo '<table class="widefat">' . PHP_EOL;
		echo '<thead' . PHP_EOL;
		echo '<tr>' . PHP_EOL;
		echo '<th><span style="font-size:1.5em">' . __( 'Type', 'fpw-post-instructions' ) . ': <em>' . $post_type_name . '</em></span></th>' .PHP_EOL;
		echo '</tr>' . PHP_EOL;
		echo '</thead>' . PHP_EOL;
		echo '<tbody' . PHP_EOL;
		echo '<tr>' . PHP_EOL;
		echo '<td><input type="checkbox" name="' . $post_type_name . '-enabled" value="yes"';
		if ( $fpw_options[ 'types' ][ $post_type_name ][ 'enabled' ] )
			echo ' checked';
		echo ' /> ' . __( 'Enabled', 'fpw-post-instructions' ) . '<br /><br />' . PHP_EOL;
		echo '<strong>' . __( 'Title', 'fpw-post-instructions' ) . '</strong> ( ' . __( 'default', 'fpw-post-instructions' ) . ': <strong>' . __( 'Special Instructions for Editors', 'fpw-post-instructions' ) . '</strong> )<br />' . PHP_EOL;
		echo '<input type="text" name="' . $post_type_name . '-title" value="' . $fpw_options[ 'types' ][ $post_type_name ][ 'title' ] . '" maxlenght="60" size="60" /><br /><br />' . PHP_EOL;
		echo '<strong>' . __( 'Content', 'fpw-post-instructions' ) . '</strong>';
		if ( !$fpw_visual || !$visual_ok )
			echo ' ( ' . __( 'HTML allowed', 'fpw-post-instructions' ) . ' )';
		echo '<br />' . PHP_EOL;
		if ( $fpw_visual && $visual_ok && ( $post_type_name == $visual_type ) ) {
			echo '<div id="poststuff">' . PHP_EOL;
			the_editor( $fpw_options[ 'types' ][ $post_type_name ][ 'content' ], 'content', '', true );
			echo '<table id="post-status-info" cellspacing="0"><tbody><tr>' . PHP_EOL;
			echo '<td id="wp-word-count"></td>' . PHP_EOL;
			echo '</tr></tbody></table>' . PHP_EOL;
			echo '</div>' . PHP_EOL;
		} else {
			echo '<textarea rows="12" style="width: 100%;" name="' . $post_type_name . '-content">' . $fpw_options[ 'types' ][ $post_type_name ][ 'content' ] . '</textarea>' . PHP_EOL;
		}
		echo '</td>' . PHP_EOL;
		echo '</tr>' . PHP_EOL;
		echo '</tbody>' . PHP_EOL;
		echo '</table>' . PHP_EOL;
	}

	/*	BOTTOM submit button */
	echo '<br /><div class="inputbutton"><input class="button-primary" type="submit" name="fpw_post_instructions_submit" value="' . __( 'Update', 'fpw-post-instructions' ) . '" /></div>' . PHP_EOL;

	/*	end of form */
	echo '</form>' . PHP_EOL;
	echo '</p>' . PHP_EOL;
	echo '</div></div>' . PHP_EOL;
}

/*	add meta box to post editing screen */
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
add_action('add_meta_boxes', 'fpw_post_instructions_add_custom_box');

/*	display metabox */
function fpw_post_instructions_box( $post, $metabox ) {
	echo $metabox[ 'args' ][ 'content' ];
}
?>