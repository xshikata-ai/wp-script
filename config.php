<?php
/**
 * Plugin config file.
 *
 * @package CORE\Config
 */

use WPS\Utils\Application\Services\LinkBuilder;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Navigation config
 */
$config['nav'] = array(
	'0'    => array(
		'slug'     => 'wpscore-dashboard',
		'callback' => 'wpscore_dashboard_page',
		'title'    => esc_html__( 'Dashboard', 'wpscore_lang' ),
	),
	'1000' => array(
		'slug'     => 'wpscore-logs',
		'callback' => 'wpscore_logs_page',
		'title'    => esc_html__( 'Logs', 'wpscore_lang' ),
	),
);

/**
 * JS config
 */
$config['scripts']['js'] = array(
	// vendor.
	'WPSCORE_lodash.js'       => array(
		'in_pages'  => 'wpscript_pages',
		'path'      => 'admin/vendors/lodash/lodash.min.js',
		'require'   => array(),
		'version'   => '4.17.21',
		'in_footer' => false,
	),
	'WPSCORE_bootstrap.js'    => array(
		'in_pages'  => 'wpscript_pages',
		'path'      => 'admin/vendors/bootstrap/js/bootstrap.min.js',
		'require'   => array( 'jquery' ),
		'version'   => '3.2.0',
		'in_footer' => false,
	),
	'WPSCORE_vue.js'          => array(
		'in_pages'  => 'wpscript_pages',
		'path'      => 'admin/vendors/vue/vue.min.js',
		'require'   => array(),
		'version'   => '2.7.16',
		'in_footer' => false,
	),
	'WPSCORE_vue-resource.js' => array(
		'in_pages'  => 'wpscript_pages',
		'path'      => 'admin/vendors/vue-resource/vue-resource.min.js',
		'require'   => array(),
		'version'   => '1.5.1',
		'in_footer' => false,
	),
	'WPSCORE_vue-snotify.js'  => array(
		'in_pages'  => 'wpscript_pages',
		'path'      => 'admin/vendors/vue-snotify/vue-snotify.min.js',
		'require'   => array(),
		'version'   => '3.2.0',
		'in_footer' => false,
	),
	'WPSCORE_clipboard.js'    => array(
		'in_pages'  => array( 'wpscore-logs' ),
		'path'      => 'admin/vendors/clipboard/clipboard.min.js',
		'require'   => array(),
		'version'   => '2.0.6',
		'in_footer' => false,
	),
	// pages.
	'WPSCORE_dashboard.js'    => array(
		'in_pages'  => array( 'wpscore-dashboard' ),
		'path'      => 'admin/pages/page-dashboard.js',
		'require'   => array(),
		'version'   => WPSCORE_VERSION,
		'in_footer' => false,
		'localize'  => array(
			'ajax'                  => true,
			'wpscript_url'          => WPSCORE_WPSCRIPT_URL,
			'i18n'                  => wpscore_localize(),
			'link_autologin_params' => LinkBuilder::getAutoLoginParams(),
		),
	),
	'WPSCORE_logs.js'         => array(
		'in_pages'  => array( 'wpscore-logs' ),
		'path'      => 'admin/pages/page-logs.js',
		'require'   => array(),
		'version'   => WPSCORE_VERSION,
		'in_footer' => false,
		'localize'  => array(
			'ajax'       => true,
			'objectL10n' => array(),
		),
	),
);

/**
 * Function to parse ./localize.json file to an array of localized strings.
 *
 * @return array<string,string> Localized strings.
 */
function wpscore_localize() {
	$localize = array();

	// Parse localize.php file.
	$localize_file = wp_normalize_path( WPSCORE_DIR . 'localize.php' );
	if ( file_exists( $localize_file ) ) {
		$localize = require $localize_file;
	}

	return $localize;
}

/**
 *  CSS config.
 */
$config['scripts']['css'] = array(
	// vendor.
	'WPSCORE_fontawesome.css'           => array(
		'in_pages' => 'wpscript_pages',
		'path'     => 'admin/vendors/font-awesome/css/font-awesome.min.css',
		'require'  => array(),
		'version'  => '4.6.0',
		'media'    => 'all',
	),
	'WPSCORE_bootstrap.css'             => array(
		'in_pages' => 'wpscript_pages',
		'path'     => 'admin/vendors/bootstrap/css/bootstrap.min.css',
		'require'  => array(),
		'version'  => '3.2.0',
		'media'    => 'all',
	),
	'WPSCORE_bootstrap-4-utilities.css' => array(
		'in_pages' => 'wpscript_pages',
		'path'     => 'admin/vendors/bootstrap/css/bootstrap-4-utilities.min.css',
		'require'  => array( 'WPSCORE_bootstrap.css' ),
		'version'  => '1.0.0',
		'media'    => 'all',
	),
	'WPSCORE_vue-snotify.css'           => array(
		'in_pages' => 'wpscript_pages',
		'path'     => 'admin/vendors/vue-snotify/vue-snotify.min.css',
		'require'  => array(),
		'version'  => '3.2.0',
		'media'    => 'all',
	),
	// assets.
	'WPSCORE_admin.css'                 => array(
		'in_pages' => 'wpscript_pages',
		'path'     => 'admin/assets/css/admin.css',
		'require'  => array(),
		'version'  => WPSCORE_VERSION,
		'media'    => 'all',
	),
	'WPSCORE_dashboard.css'             => array(
		'in_pages' => array( 'wpscore-dashboard' ),
		'path'     => 'admin/assets/css/dashboard.css',
		'require'  => array(),
		'version'  => WPSCORE_VERSION,
		'media'    => 'all',
	),
);

return $config;
