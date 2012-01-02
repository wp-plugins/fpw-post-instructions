<?php
/*
Plugin Name: FPW Post Instructions

Description: Adds metaboxes to admin editing screens for posts, pages, links,
and custom post types with instructions for editors.

Plugin URI: http://fw2s.com/2011/02/28/fpw-post-instructions-plugin/
Version: 1.2.2
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

class fpwPostInstructions {
	public	$pluginOptions;
	public	$pluginPath;
	public	$pluginUrl;
	public	$wpVersion;
	public	$pluginVersion;
	public	$pluginPage;
	public	$allowedVisual;

	//	constructor
	public	function __construct() {
		global $wp_version;

		//	set plugin's path
		$this->pluginPath = dirname(__FILE__);
		
		//	set plugin's url
		$this->pluginUrl = WP_PLUGIN_URL . '/fpw-post-instructions';
		
		//	set WP version
		$this->wpVersion = $wp_version;

		//	set plugin's version
		$this->pluginVersion = '1.2.2';
		
		//	actions and filters
		add_action( 'init', array( &$this, 'loadTextDomain' ), 1 );
		if ( is_admin() ) {
			add_action( 'admin_menu', array( &$this, 'addToSettingsMenu' ) );
			add_action( 'after_plugin_row_fpw-post-instructions/fpw-post-instructions.php', array( &$this, 'afterPluginMeta' ), 10, 2 );
			add_action( 'add_meta_boxes', array( &$this, 'addCustomBox' ) );

			add_filter( 'plugin_action_links_fpw-post-instructions/fpw-post-instructions.php', array( &$this, 'pluginLinks' ), 10, 2);
			add_filter( 'plugin_row_meta', array( &$this, 'pluginMetaLinks'), 10, 2 );

			register_activation_hook( __FILE__, array( &$this, 'pluginActivate' ) );
		}
		
		//	Read plugin's options
		$this->pluginOptions = $this->getPluginOptions();

		if ( '3.1' <= $this->wpVersion ) {
			if ( ( $_POST[ 'fpw_post_instructions_submit' ] ) || ( $_POST[ 'fpw_post_instructions_submit_top' ] ) ) 
				$this->pluginOptions[ 'abar' ] = ( $_POST[ 'abar' ] == 'yes' ); 
			if ( $this->pluginOptions[ 'abar' ] ) 
				add_action( 'admin_bar_menu', array( &$this, 'pluginToAdminBar' ), 1020 );
		}
			
	}

	//	Register plugin's textdomain 
	//	for translations
	public function loadTextDomain() {
		load_plugin_textdomain( 'fpw-fpi', false, $this->pluginPath . '/languages' );
	}	

	//	Register plugin's menu in Settings
	public function addToSettingsMenu() {
			
		$pageTitle = __('FPW Post Instructions', 'fpw-fpi') . ' (' . $this->pluginVersion . ')';
		$menuTitle = __('FPW Post Instructions', 'fpw-fpi');
		$this->pluginPage = add_options_page( $pageTitle, $menuTitle, 'manage_options', 'fpw-post-instructions', array( &$this, 'pluginSettings' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueueScripts' ) );
	
		if ( '3.3' <= $this->wpVersion ) {
			add_action( 'load-' . $this->pluginPage, array( &$this, 'help33' ) );
		} else {
			add_filter( 'contextual_help', array( &$this, 'help' ), 10, 3 );
		}
	}

	//	Add plugin's contextual help ( 3.3+ )
	public function help33() {
		global	$current_screen;

		$sidebar =	'<p style="font-size: larger">' . __( 'More information', 'fpw-fpi' ) . '</p>' . 
					'<blockquote><a href="http://fw2s.com/2011/02/28/fpw-post-instructions-plugin/" target="_blank">' . __( 'Plugin\'s site', 'fpw-fpi' ) . '</a></blockquote>' . 
					'<p style="font-size: larger">' . __( 'Support', 'fpw-fpi' ) . '</p>' . 
					'<blockquote><a href="http://wordpress.org/tags/fpw-post-instructions?forum_id=10" target="_blank">WordPress</a><br />' . 
					'<a href="http://fw2s.com/forum/topic/fpw-post-instructions-plugin-support/" target="_blank">FWSS</a></blockquote>'; 
			
		$current_screen->set_help_sidebar( $sidebar );

		$intro =	'<p style="font-size: larger">' . __( 'Introduction', 'fpw-fpi' ) . '</p>' . 
					'<blockquote style="text-align: justify"><p>' . 
					__( 'New features in WordPress 3.0+ as custom post types and post formats introduced some challenges to webmasters of corporate websites with many users holding post publishing / editing privileges.', 'fpw-fpi' ) . ' ' .
					__( 'Especially post formats which use the standard post editing screens but may have certain rules imposed by the implementation.', 'fpw-fpi' ) . '</p><p>' . 
					__( 'Webmasters could try the following options: make a course / training for editors, distribute instructions for editors in a form of printed material, or give editors a link to the documentation. All these methods are good based on one assumption that editors will remember where to find this information. Unfortunately information obtained during the course can be forgotten, printed materials lost or misplaced, and, as everybody knows, people tend to avoid reading documentation.', 'fpw-fpi' ) . '</p><p>' . 
					__( 'This plugin adds a metabox to post editing screens with special instructions for editors. You can make it as eye-catching as you want ( html in the content is allowed ). It will show on every post editing screen so it cannot be forgotten or misplaced.', 'fpw-fpi' ) . 
					'</p></blockquote>';

		$current_screen->add_help_tab( array(
   			'title'   => __( 'Introduction', 'fpw-fpi' ),
    		'id'      => 'fpw-fpi-help-introduction',
	   		'content' => $intro,
		) );
	
		$options =	'<p style="font-size: larger">' . __( 'Options', 'fpw-fpi' ) . '</p><blockquote style="text-align: justify"><strong>' . 
					__( 'Remove plugin\'s data from database on uninstall', 'fpw-fpi' ) . '</strong> (' . __( 'checked', 'fpw-fpi' ) . ') - ' . 
					__( 'during uninstall procedure all plugin\'s information will be removed from the database', 'fpw-fpi' ) . 
					'<br /><strong>' . __( 'Add this plugin to the Admin Bar', 'fpw-fpi' ) . '</strong> ( ' . __( 'checked', 'fpw-fpi' ) . ') - ' . 
					__( 'the plugin\'s link to its settings page will be added to the Admin Bar', 'fpw-fpi' ) . ' ( WordPress 3.1+ )<br /><strong>' . 
					__( 'Activate visual editor for:', 'fpw-fpi' ) . '</strong> <em>' . __( 'radio buttons for each post type', 'fpw-fpi' ) . '</em> (' . 
					__( 'checked', 'fpw-fpi' ) . ') - ' . 
					__( 'allows rich text editing for post type selected', 'fpw-fpi' ) . ' ( WordPress ' . __( 'version', 'fpw-fpi' ) . 
					' < 3.3, '. __( 'not used in WordPress 3.3+ which allows more than one rich text editor', 'fpw-fpi' ) . ' )</blockquote>';

		$current_screen->add_help_tab( array(
   			'title'   => __( 'Options', 'fpw-fpi' ),
    		'id'      => 'fpw-fpi-help-options',
	   		'content' => $options,
		) );
	
	}

	//	Add plugin's contextual help ( < 3.3 )
	public function help($contextual_help, $screen_id, $screen) {

		if ( $screen_id == $this->pluginPage ) {
			$my_help  = '<table class="widefat">';
			$my_help .= '<thead>';
			$my_help .= '<tr>';
			$my_help .= '<th width="50%" style="text-align: left;">' . __( 'Introduction', 'fpw-fpi' ) . '</th>';
			$my_help .= '<th width="50%" style="text-align: left;">' . __( 'Options', 'fpw-fpi' ) . '</th>';
			$my_help .= '</tr>';
			$my_help .= '</thead>';
			$my_help .= '<tbody>';
			$my_help .= '<tr><td><blockquote><p style="text-align: justify">' . 
						__( 'New features in WordPress 3.0+ as custom post types and post formats introduced some challenges to webmasters of corporate websites with many users holding post publishing / editing privileges.', 'fpw-fpi' ) . ' ' .
						__( 'Especially post formats which use the standard post editing screens but may have certain rules imposed by the implementation.', 'fpw-fpi' ) . '</p><p style="text-align: justify">' . 
						__( 'Webmasters could try the following options: make a course / training for editors, distribute instructions for editors in a form of printed material, or give editors a link to the documentation. All these methods are good based on one assumption that editors will remember where to find this information. Unfortunately information obtained during the course can be forgotten, printed materials lost or misplaced, and, as everybody knows, people tend to avoid reading documentation.', 'fpw-fpi' ) . 
						'</p><p style="text-align: justify">' . 
						__( 'This plugin adds a metabox to post editing screens with special instructions for editors. You can make it as eye-catching as you want ( html in the content is allowed ). It will show on every post editing screen so it cannot be forgotten or misplaced.', 'fpw-fpi' ) . 
						'</p></blockquote></td><td style="vertical-align: top;"><blockquote><p style="text-align: justify"><strong>' . 
						__( 'Remove plugin\'s data from database on uninstall', 'fpw-fpi' ) . '</strong> (' . __( 'checked', 'fpw-fpi' ) . ') - ' . 
						__( 'during uninstall procedure all plugin\'s information will be removed from the database', 'fpw-fpi' ) . 
						'<br /><strong>' . __( 'Add this plugin to the Admin Bar', 'fpw-fpi' ) . '</strong> ( ' . __( 'checked', 'fpw-fpi' ) . ') - ' . 
						__( 'the plugin\'s link to its settings page will be added to the Admin Bar', 'fpw-fpi' ) . ' ( WordPress 3.1+ )<br /><strong>' . 
						__( 'Activate visual editor for:', 'fpw-fpi' ) . '</strong> <em>' . __( 'radio buttons for each post type', 'fpw-fpi' ) . '</em> (' . 
						__( 'checked', 'fpw-fpi' ) . ') - ' . 
						__( 'allows rich text editing for post type selected', 'fpw-fpi' ) . ' ( WordPress ' . __( 'version', 'fpw-fpi' ) . 
						' < 3.3, '. __( 'not used in WordPress 3.3+ which allows more than one rich text editor', 'fpw-fpi' ) . ' )</p><hr /><p><strong>' . 
						__( 'More information', 'fpw-fpi' ) . '</strong><br />' . 
						'&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://fw2s.com/2011/02/28/fpw-post-instructions-plugin/" target="_blank"> ' . 
						__( 'Plugin\'s site', 'fpw-fpi' ) . '</a><br /><br /><strong>' . 
						__( 'Support', 'fpw-fpi' ) . '</strong><br />' . 
						'&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wordpress.org/tags/fpw-post-instructions?forum_id=10" target="_blank">WordPress</a><br />' . 
						'&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://fw2s.com/forum/topic/fpw-post-instructions-plugin-support/" target="_blank">FWSS</a></p></blockquote>' . 
						'</td</tr></tbody></table>';
			$contextual_help = $my_help;
		}	
	
		return $contextual_help; 
	}

	//	Register styles, scripts, and localize javascript
	public function enqueueScripts( $hook ) {
		if ( 'settings_page_fpw-post-instructions' == $hook ) {
			
			$this->allowedVisual = user_can_richedit();

			wp_register_script( 'fpw-fpi-script', $this->pluginUrl . '/js/fpw-fpi-script.js', array( 'jquery' ) );
			wp_enqueue_script( 'fpw-fpi-script' );

			wp_localize_script( 'fpw-fpi-script', 'fpw_fpi_text', array(
				'fpw_fpi_help_link_text'	=> esc_html( __( 'Help for FPW Post Instructions', 'fpw-fpi' ) )
			));

			if ( '3.3' > $this->wpVersion ) {

				/*	check if changes were submitted */
				if ( ( $_POST[ 'fpw_post_instructions_submit' ] ) || ( $_POST[ 'fpw_post_instructions_submit_top' ] ) ) {
					$visual_checked = ( 'yes' == $_POST[ 'visual' ] );
				} else {
					/*	get options array */
					$opt = $this->getPluginOptions();
					$visual_checked = $opt[ 'visual' ];
				}
		
				if ( $visual_checked ) {
					wp_enqueue_script( 'post' );
					wp_enqueue_script( 'editor' );
					add_thickbox();
					wp_enqueue_script( 'media-upload' );
					if ( $this->allowedVisual ) 
						add_action( 'admin_head', array( &$this, 'adminHead' ) );
				}
			}
		}
	}

	//	Load tinyMCE
	public function adminHead() {
		wp_tiny_mce();
	}
	
	//	Get plugin's options ( add if does not exists )
	private function getPluginOptions() {
	
		$opt = get_option( 'fpw_post_instructions_options' );
	
		if ( !is_array( $opt ) ) {
			$opt = array();
			$opt[ 'clean' ] = FALSE;
	
			if ( '3.1' <= $this->wpVersion )
				$opt[ 'abar' ] = FALSE;
	
			if ( '3.3' > $this->wpVersion ) {
				$opt[ 'visual' ] = FALSE;
				$opt[ 'visual-type' ] = 'post';
			}
	
			$opt[ 'types' ] = array();
			update_option( 'fpw_post_instructions_options', $opt );
		}
	
		return $opt;
	}
	
	//	Add plugin to admin bar ( WordPress 3.1+ )	
	public function pluginToAdminBar() {
		if ( current_user_can( 'manage_options' ) ) {
			global $wp_admin_bar;

			$main = array(
				'id' => 'fpw_plugins',
				'title' => __( 'FPW Plugins', 'fpw-fpi' ),
				'href' => '#' );

			$subm = array(
				'id' => 'fpw_bar_post_instructions',
				'parent' => 'fpw_plugins',
				'title' => __( 'FPW Post Instructions', 'fpw-fpi' ),
				'href' => get_admin_url() . 'options-general.php?page=fpw-post-instructions' );

			if ( '3.3' <= $this->wpVersion ) {
				$addmain = ( is_array( $wp_admin_bar->get_node( 'fpw_plugins' ) ) ) ? false : true;
			} else {
				$addmain = ( is_array( $wp_admin_bar->menu->fpw_plugins ) ) ? false : true;
			} 

			if ( $addmain )
				$wp_admin_bar->add_menu( $main );
			$wp_admin_bar->add_menu( $subm );
		}
	}
	
	//	Uninstall file maintenance
	public function pluginActivate() {
		//	if cleanup requested make uninstall.php otherwise make uninstall.txt
		if ( $this->pluginOptions[ 'clean' ] ) {
			if ( file_exists( $this->pluginPath . '/uninstall.txt' ) ) 
				rename( $this->pluginPath . '/uninstall.txt', $this->pluginPath . '/uninstall.php' );
		} else {
			if ( file_exists( $this->pluginPath . '/uninstall.php' ) ) 
				rename( $this->pluginPath . '/uninstall.php', $this->pluginPath . '/uninstall.txt' );
		}
	}	
	
	//	Add update information after plugin meta
	public function afterPluginMeta( $file, $plugin_data ) {
		$current = get_site_transient( 'update_plugins' );
		if ( !isset( $current -> response[ $file ] ) ) 
			return false;
		$url = "http://fw2s.com/fpwpostinstructionsupdate.txt";
		$update = wp_remote_fopen( $url );
		echo '<tr class="plugin-update-tr"><td></td><td></td><td class="plugin-update"><div class="update-message">' . 
			'<img class="alignleft" src="' . $this->pluginUrl . '/Thumbs_Up.png" width="64">' . $update . '</div></td></tr>';
	}

	//	Add link to Donation to plugins meta
	public function pluginMetaLinks( $links, $file ) {
		if ( 'fpw-post-instructions/fpw-post-instructions.php' == $file ) 
			$links[] = '<a href="http://fw2s.com/payments-and-donations/" target="_blank">' . __( "Donate", "fpw-fpi" ) . '</a>';
		return $links;
	}

	//	Add link to settings page in plugins list
	public function pluginLinks( $links, $file ) {
   		$settings_link = '<a href="' . site_url( '/wp-admin/' ) . 'options-general.php?page=fpw-post-instructions">' . __( 'Settings', 'fpw-fpi' ) . '</a>';
		array_unshift( $links, $settings_link );
    	return $links;
	}

	//	Plugin's settings page
	public function pluginSettings() {
	
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

		/*	get options array */
		$opt = $this->getPluginOptions();
	
		$old_visual = $opt[ 'visual' ];
		$old_visual_type = $opt[ 'visual-type' ];

		foreach ( $post_type_names as $post_type_name ) {
			if ( !is_array( $opt[ 'types' ][ $post_type_name ] ) )
				$opt[ 'types' ][ $post_type_name ] = $my_type;
		}

		/*	remove deleted custom post types arrays from options */
		$opt_keys = array_keys( $opt[ 'types' ] );
		foreach ( $opt_keys as $opt_key ) {
			if ( !in_array( $opt_key, $post_type_names ) )
				unset( $opt[ 'types' ][ $opt_key ] ); 
			}

		/*	check if changes were submitted */
		if ( ( $_POST[ 'fpw_post_instructions_submit' ] ) || ( $_POST[ 'fpw_post_instructions_submit_top' ] ) ) {
			if ( !isset( $_POST[ 'fpw-post-instructions-nonce' ] ) ) 
				die( '<br />&nbsp;<br /><p style="padding-left: 20px; color: red"><strong>' . __( 'You did not send any credentials!', 'fpw-fpi' ) . '</strong></p>' );
			if ( !wp_verify_nonce( $_POST[ 'fpw-post-instructions-nonce' ], 'fpw-post-instructions-nonce' ) ) 
				die( '<br />&nbsp;<br /><p style="padding-left: 20px; color: red;"><strong>' . __( 'You did not send the right credentials!', 'fpw-fpi' ) . '</strong></p>' );

			foreach ( $post_type_names as $post_type_name ) {
				$opt[ 'types' ][ $post_type_name ][ 'enabled' ] = ( 'yes' == $_POST[ $post_type_name . '-enabled' ] );
				$opt[ 'types' ][ $post_type_name ][ 'title' ] = stripslashes( $_POST[ $post_type_name . '-title' ] );
			
				if ( '3.3' > $this->wpVersion ) {
					$opt[ 'visual' ] = ( 'yes' == $_POST[ 'visual' ] );
					$opt[ 'visual-type' ] = $_POST[ 'fpw-radio-visual' ];

					if ( $this->allowedVisual && $old_visual && ( $post_type_name == $old_visual_type ) ) {
						$opt[ 'types' ][ $post_type_name ][ 'content' ] = stripslashes( $_POST[ 'content' ] );
					} else {
						$opt[ 'types' ][ $post_type_name ][ 'content' ] = stripslashes( $_POST[ $post_type_name . '-content' ] );
					}
				} else {
					$opt[ 'types' ][ $post_type_name ][ 'content' ] = stripslashes( $_POST[ $post_type_name . '-content' ] );
				}
			}
		
			$opt[ 'clean' ] = ( 'yes' == $_POST[ 'cleanup' ] );

			if ( '3.1' <= $this->wpVersion )
				$opt[ 'abar' ] = ( 'yes' == $_POST[ 'abar' ] );
		
			$update_ok = update_option( 'fpw_post_instructions_options', $opt );
		
			//	if cleanup requested make uninstall.php otherwise make uninstall.txt
			if ( $update_ok ) 
				$this->pluginActivate();
		}

		//	HTML of settings page starts here
		echo '<div class="wrap">';
		echo '<div id="icon-edit-pages" class="icon32"></div><h2>' . __( 'FPW Post Instructions', 'fpw-fpi' ) . ' (' . $this->pluginVersion . ')</h2>';

		//	display message about update status
		if ( ( $_POST[ 'fpw_post_instructions_submit' ] ) || ( $_POST[ 'fpw_post_instructions_submit_top' ] ) )
			if ( $update_ok ) {
				echo '<div id="message" class="updated fade"><p><strong>' . __( 'Updated successfully.', 'fpw-fpi' ) . '</strong></p></div>';
			} else {
				echo '<div id="message" class="updated fade"><p><strong>' . __( 'No changes detected. Nothing to update.', 'fpw-fpi' ) . '</strong></p></div>';
			}

		//	the form starts here
		echo '<form name="fpw_post_instructions_form" action="?page=' . basename( __FILE__, '.php' ) . '" method="post">';

		//	protect this form with nonce
		echo '<input name="fpw-post-instructions-nonce" type="hidden" value="' . wp_create_nonce( 'fpw-post-instructions-nonce' ) . '" />';

		//	cleanup checkbox
		echo '<p><input type="checkbox" name="cleanup" value="yes"';
		if ( $opt[ 'clean' ] ) echo ' checked';
		echo " /> " . __( "Remove plugin's data from database on uninstall", 'fpw-fpi' ) . '<br />';

		//	admin bar checkbox
		if ( '3.1' <= $this->wpVersion ) {
			echo '<input type="checkbox" name="abar" value="yes"';
			if ( $opt[ 'abar' ] ) echo ' checked';
			echo ' /> ' . __( 'Add this plugin to the Admin Bar', 'fpw-fpi' ) . '<br />';
		}
	
		//	visual checkbox and radio selectors
		if ( '3.3' > $this->wpVersion ) {
			echo '<input type="checkbox" name="visual" value="yes"';
			if ( $opt[ 'visual' ] ) echo ' checked';
			echo ' /> ' . __( 'Activate visual editor for:', 'fpw-fpi' ) . '&nbsp;&nbsp| ';

			foreach ( $post_type_names as $post_type_name ) {
				echo '<strong>' . $post_type_name . '</strong> <input type="radio" name="fpw-radio-visual" value="' . $post_type_name . '"';
				if ( $post_type_name == $opt[ 'visual-type' ] ) echo ' checked'; 
				echo ' /> | ';
			}
	
			if ( !$this->allowedVisual && $opt[ 'visual' ] ) 
				echo '&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;"><strong>**** ' . __( 'To use this option you must enable rich text editing in your profile!', 'fpw-fpi' ) . ' ****</strong></span>';
			echo '<br />';
		}
		echo '</p>';
	
		//	top submit button
		echo '<div class="inputbutton"><input class="button-primary" type="submit" name="fpw_post_instructions_submit_top" value="' . __( 'Update', 'fpw-fpi' ) . '" /></div>';

		//	for each post type
		foreach ( $post_type_names as $post_type_name ) {
			echo '<br />';
			echo '<table class="widefat">';
			echo '<thead';
			echo '<tr>';
			echo '<th><span style="font-size:1.5em">' . __( 'Type', 'fpw-fpi' ) . ': <em>' . $post_type_name . '</em></span></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody';
			echo '<tr>';
			echo '<td><input type="checkbox" name="' . $post_type_name . '-enabled" value="yes"';
			if ( $opt[ 'types' ][ $post_type_name ][ 'enabled' ] ) 
				echo ' checked';
			echo ' /> ' . __( 'Enabled', 'fpw-fpi' ) . '<br /><br />';
			echo '<strong>' . __( 'Title', 'fpw-fpi' ) . '</strong> ( ' . __( 'default', 'fpw-fpi' ) . ': <strong>' . __( 'Special Instructions for Editors', 'fpw-fpi' ) . '</strong> )<br />';
			echo '<input type="text" name="' . $post_type_name . '-title" value="' . $opt[ 'types' ][ $post_type_name ][ 'title' ] . '" maxlenght="60" size="60" /><br /><br />';
			echo '<strong>' . __( 'Content', 'fpw-fpi' ) . '</strong>';

			if ( '3.3' > $this->wpVersion ) { 
				if ( !$this->allowedVisual || !$opt[ 'visual' ] || ( $post_type_name <> $opt[ 'visual-type' ] ) ) 
					echo ' ( ' . __( 'HTML allowed', 'fpw-fpi' ) . ' )';
				echo '<br />';
				if ( $this->allowedVisual && $opt[ 'visual' ] && ( $post_type_name == $opt[ 'visual-type' ] ) ) {
					echo '<div id="poststuff">';
					the_editor( $opt[ 'types' ][ $post_type_name ][ 'content' ], 'content', '', true );
					echo '</div>';
				} else {
					echo '<textarea rows="12" style="width: 100%;" name="' . $post_type_name . '-content">' . $opt[ 'types' ][ $post_type_name ][ 'content' ] . '</textarea>';
				}
			} else {
				$eargs = array( 'textarea_name' => $post_type_name . '-content' );
				echo '<div style="padding-bottom: 5px;"';
				wp_editor( $opt[ 'types' ][ $post_type_name ][ 'content' ], $post_type_name . '-editor', $eargs );
				echo '</div>';
			}
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';
		}

		//	BOTTOM submit button
		echo '<br /><div class="inputbutton"><input class="button-primary" type="submit" name="fpw_post_instructions_submit" value="' . __( 'Update', 'fpw-fpi' ) . '" /></div>';
		
		//	end of form 
		echo '</form>';
		echo '</p>';
		echo '</div>';
	}

	//	add meta box to post editing screen
	public function addCustomBox() {
		$opt = $this->getPluginOptions();
		if ( is_array( $opt ) )
			foreach ( $opt[ 'types' ] as $key => $value ) {
				if ( $value[ 'enabled' ] ) {
					$title = $value[ 'title' ];
					if ( "" == $title )
						$title = __( 'Special Instructions for Editors', 'fpw-fpi' );
					add_meta_box( 'fpw_post_instructions_sectionid', $title, array( &$this, 'instructionsBox'), $key, 'advanced', 'high', array( 'content' => $value[ 'content' ] ) );
				}
			}
	}

	//	display instructions metabox
	public function instructionsBox( $post, $metabox ) {
		echo wpautop( $metabox[ 'args' ][ 'content' ], 1 );
	}
}

new fpwPostInstructions;

?>