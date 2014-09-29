<?php
/*
Plugin Name: FPW Post Instructions

Description: Adds metaboxes to admin editing screens for posts, pages, links,
and custom post types with instructions for editors.

Plugin URI: http://fw2s.com/fpw-post-instructions-plugin/
Version: 1.3.0
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
//	version check
global $wp_version;

if ( version_compare( $wp_version, '3.0', '<' ) ) 
	wp_die( '<p style="text-align:center">Cannot activate! FPW Post Instructions plugin ' . 
			'requires WordPress version 3.0 or higher!</p><p style="text-align:center">' . 
			'<a href="/wp-admin/plugins.php" title="Go back to Installed Plugins">' . 
			'Go back to Installed Plugins</a></p>' );

//	back end only
if ( is_admin() ) {
	require_once dirname(__FILE__) . '/classes/fpw-post-instructions.class.php';
	new fpwPostInstructions( dirname(__FILE__), '1.3.0' );
}
?>