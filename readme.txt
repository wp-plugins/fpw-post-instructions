=== FPW Post Instructions ===
Contributors: frankpw
Donate link: http://fw2s.com/payments-and-donations/
Tags: post, page, type, custom, metabox, instructions
Requires at least: 3.0
Tested up to: 4.2.3
Stable tag: 1.3.1

Meta boxes on admin editing screens for posts, pages, links, and custom post 
types with instructions for editors.

== Description ==

This plugin makes meta boxes on admin editing screens for posts, pages, links, 
and custom post types with instructions for editors. The content of these meta 
boxes can be entered/modified on plugin's settings page. Two methods of editing
are available - plain text (HTML codes allowed) and visual text editing.

== Installation ==

1. Place 'fpw-post-instructions' folder in '/wp-content/plugins' directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Settings Page
2. Meta box

== Changelog ==

= 1.3.1 =
* Minor bugs fixed
* Added missing help
* Added Polish and Serbian translations

= 1.3.0 =
* CSS improvements
* WordPress 4.0 compatibility

= 1.2.9 =
* Added support for pointers ( WP 3.3+ )
* Minor bugs fixed

= 1.2.8 =
* Fixed issues with PHP notices about uninitialized $_POST index
* Fixed external links to plugin's website ( changed permalinks at fw2s.com )

= 1.2.7 =
* Added missing translation strings
* Included Polish translation

= 1.2.6 =
* Fixed bug in uninstall procedure

= 1.2.5 =
* Fixed bug caused by unnecessary call to wp_tiny_mce function
* More code optimization

= 1.2.4 =
* Minor bug fixes
* Code optimized ( using less resources )

= 1.2.3 =
* Changed support links to reflect changes to FWSS site

= 1.2.2 =
* Adding actions and filters for back end only

= 1.2.1 =
* Maintenance release
* Recoded using classes to prevent naming conflicts 

= 1.2.0 =
* Added WordPress 3.3 support
* Modified contextual help 

= 1.1.9 =
* Added FPW Plugins to admin bar for WordPress 3.1+
* Fixed problem of settings page overlapping its footer

= 1.1.8 =
* Fixed incorrect link to Settings on Plugins page for websites which WordPress core files reside in a subfolder

= 1.1.7 =
* Fixed problem with metabox content display - paragraphs displayed as one long line

= 1.1.6 =
* Fixed bug causing scripts not being loaded when switching mode from non-visual to visual
* Visual changes to settings page

= 1.1.5 =
* Fixed serious problems with rich text editing of version 1.1.4
* Version 1.1.4 will be removed from repository

= 1.1.4 =
* Added contextual help
* Security related code changes
* Improved rich text editing of the content

= 1.1.3 =
* Added support for rich text editing of the content

= 1.1.2 =
* Fixed problem with escaped quotes with instructions content for pages, links,
* and custom post types

= 1.1.1 =
* Added support for links types
* Added Polish translation

= 1.1.0 =
* Major upgrade
* Added support for pages and custom post types
* Added ability to enable / disable metaboxes for selected types

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.5 =
This version fixes problems with using a rich text editor on settins page. If
you have installed version 1.1.4, please upgrade to version 1.1.5 immediately!

== Rich Text Editing ==

= WordPress version pre 3.3 =
Only one rich text editor can be used per settings page. This is
because current editing mode switching code in WordPress does not handle
multiple instances of the rich text editor.

= WordPress version 3.3+ =
Multiple rich text editors allowed

== Translations ==

= Polish =
Frank P. Walentynowicz
(http://fw2s.com)

= Serbian =
Borisa Djuraskovic
(http://www.webhostinghub.com)
