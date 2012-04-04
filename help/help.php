<?php
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
						'&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://fw2s.com/fpw-post-instructions-plugin/" target="_blank"> ' . 
						__( 'Plugin\'s site', 'fpw-fpi' ) . '</a><br /><br /><strong>' . 
						__( 'Support', 'fpw-fpi' ) . '</strong><br />' . 
						'&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wordpress.org/tags/fpw-post-instructions?forum_id=10" target="_blank">WordPress</a><br />' . 
						'&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://fw2s.com/support/fpw-post-instructions-support/" target="_blank">FWSS</a></p></blockquote>' . 
						'</td</tr></tbody></table>';
			$contextual_help = $my_help;
?>
