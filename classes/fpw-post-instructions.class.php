<?php
class fpwPostInstructions {
	public	$pluginOptions;
	public	$pluginPath;
	public	$pluginRelPath;
	public	$pluginUrl;
	public	$wpVersion;
	public	$pluginVersion;
	public	$pluginPage;
	public	$allowedVisual;

	//	constructor
	public function __construct( $path, $relPath, $version ) {
		global $wp_version;

		//	set plugin's path
		$this->pluginPath = $path;
		
		//	set plugin's relative path
		$this->pluginRelPath = $relPath;
		
		//	set plugin's url
		$this->pluginUrl = WP_PLUGIN_URL . '/fpw-post-instructions';
		
		//	set WP version
		$this->wpVersion = $wp_version;

		//	set plugin's version
		$this->pluginVersion = $version;
		
		//	actions and filters
		add_action( 'init', array( &$this, 'loadTextDomain' ), 1 );
		add_action( 'admin_menu', array( &$this, 'addToSettingsMenu' ) );
		
		add_action( 'after_plugin_row_fpw-post-instructions/fpw-post-instructions.php', array( &$this, 'afterPluginMeta' ), 10, 2 );
		add_action( 'add_meta_boxes', array( &$this, 'addCustomBox' ) );

		add_filter( 'plugin_action_links_fpw-post-instructions/fpw-post-instructions.php', array( &$this, 'pluginLinks' ), 10, 2);
		add_filter( 'plugin_row_meta', array( &$this, 'pluginMetaLinks'), 10, 2 );

		register_activation_hook( __FILE__, array( &$this, 'pluginActivate' ) );
		
		//	read plugin's options
		$this->pluginOptions = $this->getPluginOptions();

		if ( '3.1' <= $this->wpVersion ) {
			if ( isset( $_POST[ 'fpw_post_instructions_submit' ] ) || isset( $_POST[ 'fpw_post_instructions_submit_top' ] ) )  
				$this->pluginOptions[ 'abar' ] = ( isset( $_POST[ 'abar' ] ) ) ? true : false; 
			if ( $this->pluginOptions[ 'abar' ] ) 
				add_action( 'admin_bar_menu', array( &$this, 'pluginToAdminBar' ), 1020 );
		}
			
	}

	//	register plugin's textdomain 
	//	for translations
	public function loadTextDomain() {
		load_plugin_textdomain( 'fpw-fpi', false, $this->pluginRelPath . '/languages' );
	}	

	//	register plugin's menu in Settings
	public function addToSettingsMenu() {
			
		$pageTitle = __('FPW Post Instructions', 'fpw-fpi') . ' (' . $this->pluginVersion . ')';
		$menuTitle = __('FPW Post Instructions', 'fpw-fpi');
		$this->pluginPage = add_options_page( $pageTitle, $menuTitle, 'manage_options', 'fpw-post-instructions', array( &$this, 'pluginSettings' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueueScripts' ) );
	
		if ( '3.3' <= $this->wpVersion ) {
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueuePointerScripts' ) );
			add_action( 'load-' . $this->pluginPage, array( &$this, 'help33' ) );
		} else {
			add_filter( 'contextual_help', array( &$this, 'help' ), 10, 3 );
		}
	}

	//	add plugin's contextual help ( 3.3+ )
	public function help33() {
		if ( '3.3' <= $this->wpVersion ) 
			include $this->pluginPath . '/help/help33.php';
	}

	//	add plugin's contextual help ( < 3.3 )
	public function help( $contextual_help, $screen_id, $screen ) {
		if ( $screen_id == $this->pluginPage ) {
			include $this->pluginPath . '/help/help.php';
		}	
		return $contextual_help; 
	}

	//	register styles, scripts, and localize javascript
	public function enqueueScripts( $hook ) {
		if ( 'settings_page_fpw-post-instructions' == $hook ) {
			include $this->pluginPath . '/code/enqueuescripts.php';			
		}
	}

	//	enqueue pointer scripts
	public function enqueuePointerScripts( $hook ) {
		$proceed = false;
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		if ( !in_array( 'fpwfpi131', $dismissed ) && apply_filters( 'show_wp_pointer_admin_bar', TRUE ) ) {
			$proceed = true;
			add_action( 'admin_print_footer_scripts', array( &$this, 'custom_print_footer_scripts' ) );
		}
		if ( $proceed ) {
    		wp_enqueue_style('wp-pointer');
    		wp_enqueue_script('wp-pointer');
    		wp_enqueue_script('utils');
		}
	}
	
	// 	handle pointer
	public function custom_print_footer_scripts() {
    	$pointerContent  = '<h3>' . esc_js( __( "What's new in this version?", 'fpw-fpi' ) ) . '</h3>';
		$pointerContent .= '<li style="margin-left:25px;margin-top:20px;list-style:square">' . __( 'Minor bugs fixed', 'fpw-fpi' ) . '</li>';
		$pointerContent .= '<li style="margin-left:25px;list-style:square">' . __( 'Added missing help', 'fpw-fpi' ) . '</li>';
		$pointerContent .= '<li style="margin-left:25px;list-style:square">' . __( 'Added Polish and Serbian translations', 'fpw-fpi' ) . '</li>';
    	?>
    	<script type="text/javascript">
    	// <![CDATA[
    		jQuery(document).ready( function($) {
        		$('#fpi-settings-title').pointer({
        			content: '<?php echo $pointerContent; ?>',
        			position: 'top',
            		close: function() {
						jQuery.post( ajaxurl, {
							pointer: 'fpwfpi131',
							action: 'dismiss-wp-pointer'
						});
            		}
				}).pointer('open');
			});
    	// ]]>
    	</script>
    	<?php
	}

	//	get plugin's options ( add if does not exists )
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
	
	//	add plugin to admin bar ( WordPress 3.1+ )	
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
				$addmain = ( isset( $wp_admin_bar->menu->fpw_plugins ) ) ? false : true;
			} 

			if ( $addmain )
				$wp_admin_bar->add_menu( $main );
			$wp_admin_bar->add_menu( $subm );
		}
	}
	
	//	uninstall file maintenance
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
	
	//	add update information after plugin meta
	public function afterPluginMeta( $file, $plugin_data ) {
		$current = get_site_transient( 'update_plugins' );
		if ( !isset( $current -> response[ $file ] ) ) 
			return false;
		$url = "http://fw2s.com/fpwpostinstructionsupdate.txt";
		$update = wp_remote_fopen( $url );
		echo '<tr class="plugin-update-tr"><td></td><td></td><td class="plugin-update"><div class="update-message">' . 
			'<img class="alignleft" src="' . $this->pluginUrl . '/Thumbs_Up.png" width="64">' . $update . '</div></td></tr>';
	}

	//	add link to Donation to plugins meta
	public function pluginMetaLinks( $links, $file ) {
		if ( 'fpw-post-instructions/fpw-post-instructions.php' == $file ) 
			$links[] = '<a href="http://fw2s.com/payments-and-donations/" target="_blank">' . __( "Donate", "fpw-fpi" ) . '</a>';
		return $links;
	}

	//	add link to settings page in plugins list
	public function pluginLinks( $links, $file ) {
   		$settings_link = '<a href="' . site_url( '/wp-admin/' ) . 'options-general.php?page=fpw-post-instructions">' . __( 'Settings', 'fpw-fpi' ) . '</a>';
		array_unshift( $links, $settings_link );
    	return $links;
	}

	//	plugin's settings page
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
		$this->pluginOptions = $this->getPluginOptions();
	
		if ( '3.3' > $this->wpVersion ) {
			$old_visual = $this->pluginOptions[ 'visual' ];
			$old_visual_type = $this->pluginOptions[ 'visual-type' ];
		}

		foreach ( $post_type_names as $post_type_name ) {
			if ( !is_array( $this->pluginOptions[ 'types' ][ $post_type_name ] ) )
				$this->pluginOptions[ 'types' ][ $post_type_name ] = $my_type;
		}

		/*	remove deleted custom post types arrays from options */
		$opt_keys = array_keys( $this->pluginOptions[ 'types' ] );
		foreach ( $opt_keys as $opt_key ) {
			if ( !in_array( $opt_key, $post_type_names ) )
				unset( $this->pluginOptions[ 'types' ][ $opt_key ] ); 
			}

		/*	check if changes were submitted */
		if ( isset( $_POST[ 'fpw_post_instructions_submit' ] ) || isset( $_POST[ 'fpw_post_instructions_submit_top' ] ) ) {
			if ( !isset( $_POST[ 'fpw-post-instructions-nonce' ] ) ) 
				die( '<br />&nbsp;<br /><p style="padding-left: 20px; color: red"><strong>' . __( 'You did not send any credentials!', 'fpw-fpi' ) . '</strong></p>' );
			if ( !wp_verify_nonce( $_POST[ 'fpw-post-instructions-nonce' ], 'fpw-post-instructions-nonce' ) ) 
				die( '<br />&nbsp;<br /><p style="padding-left: 20px; color: red;"><strong>' . __( 'You did not send the right credentials!', 'fpw-fpi' ) . '</strong></p>' );

			foreach ( $post_type_names as $post_type_name ) {
				$this->pluginOptions[ 'types' ][ $post_type_name ][ 'enabled' ] = ( isset( $_POST[ $post_type_name . '-enabled' ] ) ) ? true : false;
				$this->pluginOptions[ 'types' ][ $post_type_name ][ 'title' ] = stripslashes( $_POST[ $post_type_name . '-title' ] );
			
				if ( '3.3' > $this->wpVersion ) {
					$this->pluginOptions[ 'visual' ] = ( isset( $_POST[ 'visual' ] ) ) ? true : false;
					$this->pluginOptions[ 'visual-type' ] = $_POST[ 'fpw-radio-visual' ];

					if ( $this->allowedVisual && $old_visual && ( $post_type_name == $old_visual_type ) ) {
						$this->pluginOptions[ 'types' ][ $post_type_name ][ 'content' ] = stripslashes( $_POST[ 'content' ] );
					} else {
						$this->pluginOptions[ 'types' ][ $post_type_name ][ 'content' ] = stripslashes( $_POST[ $post_type_name . '-content' ] );
					}
				} else {
					$this->pluginOptions[ 'types' ][ $post_type_name ][ 'content' ] = stripslashes( $_POST[ $post_type_name . '-content' ] );
				}
			}
		
			$this->pluginOptions[ 'clean' ] = ( isset( $_POST[ 'cleanup' ] ) ) ? true : false;

			if ( '3.1' <= $this->wpVersion )
				$this->pluginOptions[ 'abar' ] = ( isset( $_POST[ 'abar' ] ) ) ? true : false;
		
			$update_ok = update_option( 'fpw_post_instructions_options', $this->pluginOptions );
		
			//	if cleanup requested make uninstall.php otherwise make uninstall.txt
			if ( $update_ok ) 
				$this->pluginActivate();
		}

		//	HTML of settings page starts here
		echo '<div class="wrap">';
		echo '<div id="icon-edit-pages" class="icon32"></div><h2 id="fpi-settings-title">' . __( 'FPW Post Instructions', 'fpw-fpi' ) . ' (' . $this->pluginVersion . ')</h2>';

		//	display message about update status
		if ( isset( $_POST[ 'fpw_post_instructions_submit' ] ) || isset( $_POST[ 'fpw_post_instructions_submit_top' ] ) )
			if ( $update_ok ) {
				echo '<div id="message" class="updated fade"><p><strong>' . __( 'Updated successfully.', 'fpw-fpi' ) . '</strong></p></div>';
			} else {
				echo '<div id="message" class="updated fade"><p><strong>' . __( 'No changes detected. Nothing to update.', 'fpw-fpi' ) . '</strong></p></div>';
			}

		//	the form starts here
		echo '<form name="fpw_post_instructions_form" action="?page=' . basename( __FILE__, '.class.php' ) . '" method="post">';

		//	protect this form with nonce
		echo '<input name="fpw-post-instructions-nonce" type="hidden" value="' . wp_create_nonce( 'fpw-post-instructions-nonce' ) . '" />';

		//	cleanup checkbox
		echo '<p><input type="checkbox" name="cleanup" value="yes"';
		if ( $this->pluginOptions[ 'clean' ] ) echo ' checked';
		echo " /> " . __( "Remove plugin's data from database on uninstall", 'fpw-fpi' ) . '<br />';

		//	admin bar checkbox
		if ( '3.1' <= $this->wpVersion ) {
			echo '<input type="checkbox" name="abar" value="yes"';
			if ( $this->pluginOptions[ 'abar' ] ) echo ' checked';
			echo ' /> ' . __( 'Add this plugin to the Admin Bar', 'fpw-fpi' ) . '<br />';
		}
	
		//	visual checkbox and radio selectors
		if ( '3.3' > $this->wpVersion ) {
			echo '<input type="checkbox" name="visual" value="yes"';
			if ( $this->pluginOptions[ 'visual' ] ) echo ' checked';
			echo ' /> ' . __( 'Activate visual editor for:', 'fpw-fpi' ) . '&nbsp;&nbsp| ';

			foreach ( $post_type_names as $post_type_name ) {
				echo '<strong>' . $post_type_name . '</strong> <input type="radio" name="fpw-radio-visual" value="' . $post_type_name . '"';
				if ( $post_type_name == $this->pluginOptions[ 'visual-type' ] ) echo ' checked'; 
				echo ' /> | ';
			}
	
			if ( !$this->allowedVisual && $this->pluginOptions[ 'visual' ] ) 
				echo '&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;"><strong>**** ' . __( 'To use this option you must enable rich text editing in your profile!', 'fpw-fpi' ) . ' ****</strong></span>';
			echo '<br />';
		}
		echo '</p>';
	
		//	top submit button
		echo	'<div class="inputbutton"><input class="button-primary" type="submit" name="fpw_post_instructions_submit_top" value="' . 
				__( 'Update', 'fpw-fpi' ) . '" title="' . __( 'writes all modifications to the database', 'fpw-fpi' ) . '" /></div>';

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
			if ( $this->pluginOptions[ 'types' ][ $post_type_name ][ 'enabled' ] ) 
				echo ' checked';
			echo ' /> ' . __( 'Enabled', 'fpw-fpi' ) . '<br /><br />';
			echo '<strong>' . __( 'Title', 'fpw-fpi' ) . '</strong> ( ' . __( 'default', 'fpw-fpi' ) . ': <strong>' . __( 'Special Instructions for Editors', 'fpw-fpi' ) . '</strong> )<br />';
			echo '<input type="text" name="' . $post_type_name . '-title" value="' . $this->pluginOptions[ 'types' ][ $post_type_name ][ 'title' ] . '" maxlenght="60" size="60" /><br /><br />';
			echo '<strong>' . __( 'Content', 'fpw-fpi' ) . '</strong>';

			if ( '3.3' > $this->wpVersion ) { 
				if ( !$this->allowedVisual || !$this->pluginOptions[ 'visual' ] || ( $post_type_name <> $this->pluginOptions[ 'visual-type' ] ) ) 
					echo ' ( ' . __( 'HTML allowed', 'fpw-fpi' ) . ' )';
				echo '<br />';
				if ( $this->allowedVisual && $this->pluginOptions[ 'visual' ] && ( $post_type_name == $this->pluginOptions[ 'visual-type' ] ) ) {
					echo '<div id="poststuff">';
					the_editor( $this->pluginOptions[ 'types' ][ $post_type_name ][ 'content' ], 'content', '', true );
					echo '</div>';
				} else {
					echo '<textarea rows="12" style="width: 100%;" name="' . $post_type_name . '-content">' . $this->pluginOptions[ 'types' ][ $post_type_name ][ 'content' ] . '</textarea>';
				}
			} else {
				$eargs = array( 'textarea_name' => $post_type_name . '-content' );
				echo '<div style="padding-bottom: 5px;"';
				wp_editor( $this->pluginOptions[ 'types' ][ $post_type_name ][ 'content' ], $post_type_name . '-editor', $eargs );
				echo '</div>';
			}
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';
		}

		//	BOTTOM submit button
		echo 	'<br /><div class="inputbutton"><input class="button-primary" type="submit" name="fpw_post_instructions_submit" value="' . 
				__( 'Update', 'fpw-fpi' ) . '" title="' . __( 'writes all modifications to the database', 'fpw-fpi' ) . '" /></div>';
		
		//	end of form 
		echo '</form>';
		echo '</p>';
		echo '</div>';
	}

	//	add meta box to post editing screen
	public function addCustomBox() {
		$this->pluginOptions = $this->getPluginOptions();
		if ( is_array( $this->pluginOptions ) )
			foreach ( $this->pluginOptions[ 'types' ] as $key => $value ) {
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
?>