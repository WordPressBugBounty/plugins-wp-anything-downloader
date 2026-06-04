=== WP Anything Downloader ===
Contributors: d3logics, vinit-sharma, devshubham715
Donate link: https://d3logics.com
Tags: theme downloader, plugin downloader, download theme, download plugin, direct download
Requires at least: 5.9
Tested up to: 6.8
Requires PHP: 5.6
Stable tag: 4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Directly download any installed theme or plugin as a .zip file straight from your WordPress admin panel.

== Description ==

WP Anything Downloader adds a simple "Download" link to every theme and plugin listed in your WordPress admin. With one click it packages the selected theme or plugin folder into a .zip file and downloads it to your computer — no FTP, no file manager, no server access needed.

It is the perfect tool for migrating a site, backing up a single theme or plugin, or grabbing a copy of something you installed but no longer have the original files for.

= Features =

* Download any installed plugin as a .zip from the Plugins page.
* Download any installed theme as a .zip from the Themes / Appearance page.
* Works with must-use (mu) plugins.
* No configuration required — install, activate, and the Download links appear.

= Security =

Downloads are restricted to administrators who already have the capability to install plugins (`install_plugins`) or themes (`install_themes`), and every download request is protected by a WordPress nonce.

== Installation ==

1. Upload the `wp-anything-downloader` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress Plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to the Plugins or Appearance > Themes page and use the new "Download" link on any item.

== Frequently Asked Questions ==

= Does this plugin download themes? =

Yes. A "Download" link is added to each theme on the Themes page.

= Does this plugin download plugins? =

Yes. A "Download" link is added to each plugin on the Plugins page, including must-use plugins.

= Who can download files? =

Only logged-in users with permission to install plugins or themes (typically administrators). Every request is also nonce-verified.

== Screenshots ==

1. Theme download button on the Themes page.
2. Plugin download link on the Plugins page.

== Changelog ==

= 4.1 =
* Security: Added capability checks (`install_plugins` / `install_themes`) to the download handler and the action links.
* Security: Properly unslash and sanitize all request data; escape all generated output and URLs.
* Fix: Replaced the deprecated jQuery `$(window).load()` with `$(window).on("load")` so the theme download button works on modern WordPress (jQuery 3.x).
* Added Text Domain and made the "Download" label translatable.
* Replaced `unlink()` with `wp_delete_file()`.
* Added `Requires PHP` header.

= 4.0 =
* Compatible with latest WordPress version.

== Upgrade Notice ==

= 4.1 =
Important security and compatibility update. Adds capability checks, fixes the theme download button on modern WordPress, and confirms compatibility up to WordPress 6.8. Upgrade recommended.
