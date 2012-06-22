<?php
		$proceed = false;
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		if ( !in_array( 'fpwfpi' . str_replace( '.', '', $this->pluginVersion ), $dismissed ) && apply_filters( 'show_wp_pointer_admin_bar', TRUE ) ) {
			$proceed = true;
			add_action( 'admin_print_footer_scripts', array( &$this, 'custom_print_footer_scripts' ) );
		}
		if ( $proceed ) {
    		wp_enqueue_style('wp-pointer');
    		wp_enqueue_script('wp-pointer');
    		wp_enqueue_script('utils');
		}
?>