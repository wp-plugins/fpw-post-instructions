=== FPW Post Instructions ===
Contributors: frankpw
Donate link: 
Tags: post, page, type, custom, metabox, instructions
Requires at least: 3.0
Tested up to: 3.1.2
Stable tag: 1.1.7

Meta boxes on admin editing screens for posts, pages, links, and custom post 
types with instructions for editors.

== Description ==

This plugin makes meta boxes on admin editing screens for posts, pages, links, 
and custom post types with instructions for editors. The content of these meta 
boxes can be entered/modified on plugin's settings page. Two methods of editing
are available - plain text (HTML codes allowed) and rich text editing.

== Installation ==

1. Place 'fpw-post-instructions' folder in '/wp-content/plugins' directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Settings Page
2. Meta box

== Changelog ==

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
Right now the only one rich text editor can be used per settings page. This is
because current editing mode switching code in WordPress does not handle
multiple instances of the rich text editor.
