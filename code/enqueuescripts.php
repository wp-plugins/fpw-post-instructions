<?php
			$this->allowedVisual = user_can_richedit();

			wp_register_script( 'fpw-fpi-script', $this->pluginUrl . '/js/fpw-fpi-script.js', array( 'jquery' ) );
			wp_enqueue_script( 'fpw-fpi-script' );

			wp_localize_script( 'fpw-fpi-script', 'fpw_fpi_text', array(
				'fpw_fpi_help_link_text'	=> esc_html( __( 'Help for FPW Post Instructions', 'fpw-fpi' ) )
			));

			if ( '3.3' > $this->wpVersion ) {

				/*	check if changes were submitted */
				if ( isset( $_POST[ 'fpw_post_instructions_submit' ] ) || isset( $_POST[ 'fpw_post_instructions_submit_top' ] ) ) {
					$visual_checked = ( isset( $_POST[ 'visual' ] ) ) ? true : false;
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
				}
			}
?>