<?php
			//	prevent direct access
			if ( preg_match( '#' . basename(__FILE__) . '#', $_SERVER[ 'PHP_SELF' ] ) ) 
				die( "Direct access to this script is forbidden!" );

			$this->allowedVisual = user_can_richedit();

			//wp_register_script( 'fpw-fpi', $this->pluginUrl . '/js/fpw-fpi.js', array( 'jquery' ) );
			wp_enqueue_script( 'fpw-fpi', $this->pluginUrl . '/js/fpw-fpi.js', array( 'jquery' ), false, true );

			wp_localize_script( 'fpw-fpi', 'fpw_fpi', array(
				'help_link_text' => esc_html( __( 'Help for FPW Post Instructions', 'fpw-fpi' ) )
			));
?>