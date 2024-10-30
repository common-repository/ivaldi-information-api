=== Plugin Name ===
Contributors: dimitri010, ivaldi
Tags: Ivaldi, Information, WordPress, API, updates, notifications, comments, themes, logins, versions
Requires at least: 2.8
Tested up to: 4.0.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a plugin which provides an API for your WordPress-site to retrieve your site-information.

== Description ==

This plugin outputs information about the WordPress-site in .json-format. This information can only be retrieved using a random generated code. The code can be seen at the plugin-settings page. Everytime the plugin is activated, the code will be regenerated. 

The output can be found at http://YOURSITEURL.com/?hash=CODE. This API can be used for your own purposes, like dashboards, apps etc. 

Your information is not sent to or saved on any server or other instances. The information is only displayed on your secured page. 

== Installation ==

How to install the plugin and retrieve the information:

1. Upload the folder `ivaldi-information-api` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. The auto generated code is provided at the plugin-settings page which can be found via `Settings` > `Ivaldi Information API`.
4. Go to http://YOURSITEURL.com/?hash=CODE to see your site-information. You can use this URL to retrieve the site information for your purposes.
== Changelog ==

= 0.2 =
Add WordPress Plugin icon

= 0.1 =
First version in WordPress-directory
