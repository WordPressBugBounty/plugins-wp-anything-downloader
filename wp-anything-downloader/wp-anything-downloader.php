<?php
/**
 * Plugin Name: WP Anything Downloader
 * Plugin URI: https://d3logics.com/plugins
 * Description: This plugin allows you to directly download any theme or plugin from the WordPress admin panel. The best plugin to download a theme or plugin from wp-admin.
 * Version: 4.1
 * Author: Vinit Sharma, D3logics
 * Author URI: https://d3logics.com
 * Requires at least: 5.9
 * Requires PHP: 5.6
 * Tested up to: 6.8
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-anything-downloader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'wad_load' );

/**
 * Hooks some actions and filters, and handles the download link.
 *
 * @return void
 */
function wad_load() {
	// Hook for the plugin action links.
	add_filter( 'plugin_action_links', 'wad_plugin_action_links', 10, 4 );
	// Hook for the theme action links in WordPress < 3.8.
	add_filter( 'theme_action_links', 'wad_theme_action_links', 10, 2 );
	// Hook for adding JavaScript on the themes page.
	add_action( 'admin_footer-themes.php', 'wad_scripts', 99 );

	// Check if there is a 'wad' GET param with a valid nonce, and process the download.
	if ( isset( $_GET['wad'], $_GET['_wpnonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wad-download' )
	) {
		wad_download();
	}
}

/**
 * Returns the capability required to download a given object type.
 *
 * @param string $what The object type ('plugin', 'muplugin' or 'theme').
 * @return string The required capability.
 */
function wad_required_cap( $what ) {
	return ( 'theme' === $what ) ? 'install_themes' : 'install_plugins';
}

/**
 * Displays the "Download" link on the plugins page.
 *
 * @param array  $links       List of action links.
 * @param string $file        Plugin file name.
 * @param array  $plugin_data An array of plugin data.
 * @param string $context     The plugin context.
 * @return array
 */
function wad_plugin_action_links( $links, $file, $plugin_data, $context ) {
	if ( 'dropins' === $context ) {
		return $links;
	}
	if ( ! current_user_can( 'install_plugins' ) ) {
		return $links;
	}

	$what = ( 'mustuse' === $context ) ? 'muplugin' : 'plugin';

	$download_query = build_query( array( 'wad' => $what, 'object' => $file ) );
	$download_link  = sprintf(
		'<a href="%s">%s</a>',
		esc_url( wp_nonce_url( admin_url( '?' . $download_query ), 'wad-download' ) ),
		esc_html__( 'Download', 'wp-anything-downloader' )
	);

	array_push( $links, $download_link );
	return $links;
}

/**
 * Displays the "Download" link on the themes page in WordPress < 3.8.
 *
 * @param array  $links List of action links.
 * @param object $theme Theme object.
 * @return array
 */
function wad_theme_action_links( $links, $theme ) {
	if ( ! current_user_can( 'install_themes' ) ) {
		return $links;
	}

	$download_query = build_query( array( 'wad' => 'theme', 'object' => $theme->get_stylesheet() ) );
	$download_link  = sprintf(
		'<a href="%s">%s</a>',
		esc_url( wp_nonce_url( admin_url( '?' . $download_query ), 'wad-download' ) ),
		esc_html__( 'Download', 'wp-anything-downloader' )
	);

	array_push( $links, $download_link );
	return $links;
}

/**
 * Displays JavaScript code in the footer of themes.php.
 *
 * @return void
 */
function wad_scripts() {
	if ( ! current_user_can( 'install_themes' ) ) {
		return;
	}

	// Download URL.
	$query = build_query( array( 'wad' => 'theme', 'object' => '_obj_' ) );
	$url   = wp_nonce_url( admin_url( '?' . $query ), 'wad-download' );
	// Label used for download links.
	$label = __( 'Download', 'wp-anything-downloader' );
	// Current theme.
	$current_theme = get_stylesheet();

	$script_template = '<script type="text/javascript" id="wp-downloader">
		(function($){
			var url = "%s",
				label = "%s",
				current = "%s",
				button = \'<a class="button button-primary download hide-if-no-js" href="\' + url + \'">\' + label + \'</a>\';

			$(window).on("load", function(){
				// For current theme in WordPress < 3.8.
				$("#current-theme .theme-options").after(\'<div class="theme-options"><a href="\' + url.replace("_obj_", current) + \'">\' + label + \'</a></div>\');

				// Add download button for each theme on the themes page.
				$("#wpbody .theme .theme-actions .load-customize").each(function(i, e){
					var btn = $(button),
						$e = $(e),
						href = $e.prop("href");

					btn.prop("href", url.replace("_obj_", href.replace(/.*theme=(.*)(&|$)/, "$1")));

					$e.parent().append(btn);
				});
			});

			// Modify single theme template to add the download button.
			var d = $("#tmpl-theme-single").html(),
				ar = new RegExp(\'(<div class="active-theme">)(([\n\t]*(<#|<a).*[\n\t]*)*)(</div>)\', "mi");
				ir = new RegExp(\'(<div class="inactive-theme">)(([\n\t]*(<#|<a).*[\n\t]*)*)(</div>)\', "mi");

			d = d.replace(ar, "$1$2" + button + "$5");
			d = d.replace(ir, "$1$2" + button + "$5");

			$("#tmpl-theme-single").html(d);

			$(document).on("click", "a.button.download", function(e){
				e.preventDefault();
				var $this = $(this),
					href = $(this).parent().find(".load-customize").attr("href"),
					theme;

				theme = href.replace(/.*theme=(.*)(&|$)/, "$1");
				href = url.replace("_obj_", theme).replace(new RegExp("&amp;", "g"), "&");

				window.location = href;
			});
		}(jQuery))
	</script>';

	// Print JavaScript. URL/label/theme are escaped for safe JS string output.
	printf(
		$script_template, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static template.
		esc_js( $url ),
		esc_js( $label ),
		esc_js( $current_theme )
	);
}

/**
 * Handles the download.
 *
 * @return void
 */
function wad_download() {
	// Kind of object we download (theme, plugin or muplugin).
	$what = isset( $_GET['wad'] ) ? sanitize_text_field( wp_unslash( $_GET['wad'] ) ) : '';
	// The name of the object.
	$object = isset( $_GET['object'] ) ? sanitize_text_field( wp_unslash( $_GET['object'] ) ) : '';

	// Capability check: only users allowed to install plugins/themes may download them.
	if ( ! current_user_can( wad_required_cap( $what ) ) ) {
		wp_die( esc_html__( 'You do not have permission to download this file.', 'wp-anything-downloader' ) );
	}

	if ( ! class_exists( 'PclZip' ) ) {
		// Load the class file if it is not loaded yet.
		include ABSPATH . 'wp-admin/includes/class-pclzip.php';
	}

	// Prepare object name and root path for the given object type.
	switch ( $what ) {
		case 'plugin':
			if ( strpos( $object, '/' ) ) {
				$object = dirname( $object );
			}
			$root = WP_PLUGIN_DIR;
			break;
		case 'muplugin':
			if ( strpos( $object, '/' ) ) {
				$object = dirname( $object );
			}
			$root = WPMU_PLUGIN_DIR;
			break;
		case 'theme':
			$root = get_theme_root( $object );
			break;
		default:
			// Bad URL.
			wp_die( 'Cheatin&#8217; uh?' );
	}

	$object = sanitize_file_name( $object );
	if ( empty( $object ) ) {
		// No object name.
		wp_die( 'Cheatin&#8217; uh?' );
	}

	// Prepare the full path to the desired object.
	$path = $root . '/' . $object;
	// Filename for the zip package.
	$file_name = $object . '.zip';

	// Temporary file name in the upload dir.
	$upload_dir = wp_upload_dir();
	$tmp_file   = trailingslashit( $upload_dir['path'] ) . $file_name;

	// Create a new archive.
	$archive = new PclZip( $tmp_file );
	// Add the entire folder to the archive.
	$archive->add( $path, PCLZIP_OPT_REMOVE_PATH, $root );

	// Set headers for the zip archive.
	header( 'Content-type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );

	// Read file content directly.
	readfile( $tmp_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- streaming a generated zip.
	// Remove the zip file.
	wp_delete_file( $tmp_file );

	// Exit. No wp_die(), it produces HTML.
	exit;
}
