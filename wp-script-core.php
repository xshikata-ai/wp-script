<?php
/**
 * Plugin Name: WP-Script Core
 * Plugin URI: https://www.wp-script.com
 * Description: WP-Script.com core plugin
 * Author: WP-Script
 * Author URI: https://www.wp-script.com
 * Version: 5.3.3
 * Text Domain: wpscore_lang
 * Domain Path: /languages
 * Requires PHP: 7.2
 *
 * @package CORE\Main
 */

use WPS\Ai\Application\Services\AiDebugMode;
use WPS\Ai\Infrastructure\AiJobRepositoryInWpPostType;
use WPS\Utils\Application\Services\LinkBuilder;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// WPSCORE Autoload classes.
require_once 'vendor/autoload.php';

define( 'WPSCORE_VERSION', '5.3.3' );
define( 'WPSCORE_DIR', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
define( 'WPSCORE_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSCORE_FILE', __FILE__ );
define( 'WPSCORE_LOG_FILE', wp_normalize_path( WPSCORE_DIR . 'admin' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'wpscript.log' ) );
if ( ! defined( 'WPSCORE_WPSCRIPT_URL' ) ) {
	define( 'WPSCORE_WPSCRIPT_URL', 'https://www.wp-script.com' );
}
define( 'WPSCORE_API_URL', WPSCORE_WPSCRIPT_URL . '/wp-json/wpsevsl' );
define( 'WPSCORE_LOGO_URL', wp_normalize_path( WPSCORE_URL . 'admin' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo-wp-script.svg' ) );
define( 'WPSCORE_X_LOGO_URL', wp_normalize_path( WPSCORE_URL . 'admin' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'x.svg' ) );
define( 'WPSCORE_NAME', 'WP-Script' );
define( 'WPSCORE_PHP_REQUIRED', '7.2' );

/**
 * Singleton Class
 *
 * @phpstan-type ScriptJsConfig array{in_pages:string,path:string,require:array<string>,version:string,in_footer:boolean,localize?:array<string,mixed>}
 * @phpstan-type ScriptCssConfig array{in_pages:string,path:string,require:array<string>,version:string,mdeia:string}
 * @phpstan-type Config array{nav:array<string,array{slug:string,callback:string,title:string}>,scripts:array{js:array<string,ScriptJsConfig>,css:array<string,ScriptCssConfig>}}
 * @phpstan-import-type ApiAuthParams from WPSCORE_Api
 */
final class WPSCORE {

	/**
	 * The instance of the CORE plugin
	 *
	 * @var WPSCORE $instance
	 * @static
	 */
	private static $instance;

	/**
	 * The config of the CORE plugin
	 *
	 * @var Config $config
	 * @static
	 */
	private static $config;

	/**
	 * __clone method
	 *
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Do not clone or wake up this class', 'wpscore_lang' ), '1.0' );
	}

	/**
	 * __wakeup method
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Do not clone or wake up this class', 'wpscore_lang' ), '1.0' );
	}

	/**
	 * Instance method
	 *
	 * @return WPSCORE The instance of the CORE plugin.
	 */
	public static function instance() {
		if ( ( ! isset( self::$instance ) ) && ( ! ( self::$instance instanceof WPSCORE ) ) ) {
			self::$instance = new WPSCORE();
			// Load textdomain.
			self::$instance->load_textdomain();
			self::$instance->load_config();
			include_once plugin_dir_path( __FILE__ ) . 'admin/cron-x/cron-init.php';
			include_once plugin_dir_path( __FILE__ ) . 'admin/cron-x/cron-bulk-ai.php';

			// Load log system.
			include_once plugin_dir_path( __FILE__ ) . 'admin/logs/class-wpscore-log.php';

			// Load Cron.
			add_action( 'wpscore_init', 'wpscore_cron_init' );
			add_action( 'wpscore_bulk_ai', 'wpscore_cron_bulk_ai' );

			// Load options system.
			if ( WPSCORE()->php_version_ok() ) {
				include_once plugin_dir_path( __FILE__ ) . 'xbox/xbox.php';
			}

			if ( is_admin() ) {
				self::$instance->load_admin_hooks();
				self::$instance->auto_load_php_files( 'admin' );
				self::$instance->admin_init();
			}

			if ( ! is_admin() ) {
				self::$instance->load_public_hooks();
			}
		}
		return self::$instance;
	}

	/**
	 * Load textdomain method
	 */
	public function admin_init() {
		add_action(
			'admin_init',
			function () {
				self::$instance->init( false );
			}
		);
	}

	/**
	 * Load config method
	 *
	 * @return void
	 */
	public function load_config() {
		add_action(
			'init',
			function () {
				self::$config = include_once plugin_dir_path( __FILE__ ) . 'config.php';
			}
		);
	}

	/**
	 * Load_admin_hooks method
	 *
	 * @return void
	 */
	public function load_admin_hooks() {
		add_action( 'admin_notices', array( $this, 'maybe_display_site_verification_notice' ) );
		add_action( 'admin_notices', array( $this, 'maybe_display_ai_debug_notice' ) );
		add_action( 'admin_init', array( $this, 'add_wordfence_compatibily' ) );
		add_action( 'admin_init', array( $this, 'save_default_options' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'auto_load_scripts' ), 100 );
		add_action( 'admin_init', array( $this, 'reorder_submenu' ) );
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		/**
		 * TODO
		 * Add register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );
		 */
	}

	/**
	 * Load_public_hooks method
	 *
	 * @return void
	 */
	public function load_public_hooks() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Add admin notice when the site needs to be verified.
	 *
	 * @return void
	 */
	public function maybe_display_site_verification_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! WPSCORE()->must_verify_site() ) {
			return;
		}

		if ( 'wpscore-dashboard' === $this->get_current_page_slug() ) {
			return;
		}

		?>
		<div class="update-nag notice notice-warning inline">
			<?php esc_html_e( 'Site verification required for WP-Script products!', 'wpscore_lang' ); ?> <a href="<?php echo esc_url( admin_url( '?page=wpscore-dashboard' ) ); ?>"><?php esc_html_e( 'Please verify now', 'wpscore_lang' ); ?></a>.
		</div>
		<?php
	}

	/**
	 * Add admin notice when ai debug mode is enabled.
	 *
	 * @return void
	 */
	public function maybe_display_ai_debug_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! AiDebugMode::isEnabled() ) {
			return;
		}

		?>
		<div class="update-nag notice notice-warning inline">
			<?php esc_html_e( 'WPS AI Debug mode is enabled:', 'wpscore_lang' ); ?>
			<ul>
				<li>• <?php esc_html_e( 'wps_ai_job CPT is shown in UI', 'wpscore_lang' ); ?></li>
				<li>• <?php esc_html_e( 'Content is generated from pre-rendered data', 'wpscore_lang' ); ?></li>
				<li>• <?php esc_html_e( 'Credits are not used', 'wpscore_lang' ); ?></li>
			</ul>

			<small>
				<?php esc_html_e( 'This mode is only enabled on this device.', 'wpscore_lang' ); ?><br>
				<?php esc_html_e( 'You can disable it by removing the cookie.', 'wpscore_lang' ); ?>
			</small>
		</div>
		<?php
	}

	/**
	 * Bypass Wordfence on ajax call that can be blocked because of requests to adult tubes
	 * admin_init hook callback.
	 *
	 * @see load_hooks()
	 *
	 * @since 1.3.8
	 * @return void
	 */
	public function add_wordfence_compatibily() {
		if ( $this->is_wordfence_activated() ) {
			include_once WP_PLUGIN_DIR . '/wordfence/wordfence.php';
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['action'] ) ) {
				$post_action = sanitize_text_field( wp_unslash( $_POST['action'] ) );
				if ( $this->is_wpscript_ajax_action( $post_action ) ) {
					if ( ! class_exists( 'wfWAF' ) ) {
						return;
					}
					$wordfence = wfWAF::getInstance()->getStorageEngine();
					if ( 'enabled' === $wordfence->getConfig( 'wafStatus' ) ) {
						$wordfence->setConfig( 'wafStatus', 'learning-mode' );
					}
				}
			}
		}
	}

	/**
	 * Detect if Wordfence plugin is activated.
	 *
	 * @return boolean True if it is activated, false if not.
	 */
	public function is_wordfence_activated() {
		return is_plugin_active( 'wordfence/wordfence.php' );
	}

	/**
	 * Check if a given $ajax_action is a wp-script one
	 *
	 * @param string $ajax_action The ajax action.
	 *
	 * @since 1.3.8
	 * @return bool true if it is a wp-script ajax action, false if not.
	 */
	private function is_wpscript_ajax_action( $ajax_action ) {
		if ( ! $ajax_action ) {
			return false;
		}
		$products_skus = $this->get_products_skus();
		foreach ( $products_skus as $product_sku ) {
			$product_sku = strtolower( 'CORE' === $product_sku ? 'WPSCORE' : $product_sku );
			$ajax_action = strtolower( $ajax_action );
			if ( false !== strpos( $ajax_action, $product_sku ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the current page slug.
	 *
	 * @return string The current page slug.
	 */
	private function get_current_page_slug() {
		// PHPCS:ignore WordPress.Security.NonceVerification
		if ( ! isset( $_GET['page'] ) ) {
			return '';
		}
		// PHPCS:ignore WordPress.Security.NonceVerification
		return sanitize_title( wp_unslash( $_GET['page'] ) );
	}

	/**
	 * Method to save default Xbox options on admin_init hook action
	 *
	 * @return void
	 */
	public function save_default_options() {
		check_ajax_referer( 'ajax-nonce', 'nonce', false );
		if ( ! WPSCORE()->php_version_ok() || 'wpscore-dashboard' !== $this->get_current_page_slug() ) {
			return;
		}
		$all_options = xbox_get_all();
		foreach ( (array) $all_options as $xbox_id => $xbox_options ) {
			if ( get_option( $xbox_id ) === false ) {
				$xbox = xbox_get( strtolower( $xbox_id ) );
				if ( $xbox ) {
					$xbox->save_fields( 0, array( 'display_message_on_save' => false ) );
				}
			}
		}
	}

	/**
	 * Register custom post types.
	 *
	 * @return void
	 */
	public function register_post_types() {
		AiJobRepositoryInWpPostType::registerPostType();
	}

	/**
	 * Method to load js and css files in CORE and all WP-SCRIPT products
	 *
	 * @return void
	 */
	public function auto_load_scripts() {
		// phpcs:ignore
		$scripts        = apply_filters( 'WPSCORE-scripts', self::$config['scripts']['js'] + self::$config['scripts']['css'] );
		$wpscript_pages    = $this->get_pages_slugs();
		$current_page_slug = $this->get_current_page_slug();

		if ( in_array( $current_page_slug, $wpscript_pages, true ) && strpos( $current_page_slug, '-options' ) === false ) {
			global $wp_scripts, $wp_styles;
			foreach ( (array) $wp_scripts->registered as $script_key => $script_config ) {
				// Removing Bootstrap scripts on wp-script pages.
				if ( strpos( $script_config->src, 'bootstrap' ) !== false ) {
					wp_deregister_script( $script_key );
				}

				// Removing WP-Legal-Pages to prevent conflicts.
				if ( strpos( $script_config->src, 'wplegalpages' ) !== false ) {
					wp_deregister_script( $script_key );
				}
			}
			foreach ( (array) $wp_styles->registered as $script_key => $script_config ) {
				// Removing Bootstrap styles on wp-script pages.
				if ( strpos( $script_config->src, 'bootstrap' ) !== false ) {
					wp_deregister_script( $script_key );
				}
			}
		}

		// add wp-script scripts and css on WP-Script pages.
		foreach ( (array) $scripts as $k => $v ) {
			if ( ! isset( $v['in_pages'] ) || in_array( $current_page_slug, ( 'wpscript_pages' === $v['in_pages'] ? $wpscript_pages : $v['in_pages'] ), true ) ) {
				$type    = explode( '.', $k );
				$type    = end( $type );
				$sku     = explode( '_', $k );
				$sku     = current( $sku );
				$path    = str_replace( array( 'http:', 'https:' ), array( '', '' ), constant( $sku . '_URL' ) . $v['path'] );
				$uri     = str_replace( array( 'http:', 'https:' ), array( '', '' ), constant( $sku . '_DIR' ) . $v['path'] );
				$version = $v['version'] . '.' . filemtime( $uri );
				switch ( $type ) {
					case 'js':
						// exclude script if option pages and script is bootstrap to avoid dropdown conflicts.
						if ( strpos( $current_page_slug, '-options' ) !== false && 'WPSCORE_bootstrap.js' === $k ) {
							break;
						}
						// exclude script if option pages and script is lodash to avoid gutenberg and underscore conflicts.
						if ( strpos( $current_page_slug, '-options' ) !== false && 'WPSCORE_lodash.js' === $k ) {
							break;
						}
						$v['in_footer'] = true; // Force to load scripts in footer to prevent JS loading issues.
						wp_enqueue_script( $k, $path, $v['require'], $version, $v['in_footer'] );
						if ( isset( $v['localize'] ) && ! empty( $v['localize'] ) ) {
							if ( isset( $v['localize']['ajax'] ) && true === $v['localize']['ajax'] ) {
								$v['localize']['ajax'] = array(
									'url'   => str_replace( array( 'http:', 'https:' ), array( '', '' ), admin_url( 'admin-ajax.php' ) ),
									'nonce' => wp_create_nonce( 'ajax-nonce' ),
								);
							}
							wp_localize_script( $k, str_replace( array( '-', '.js' ), array( '_', '' ), $k ), $v['localize'] );
						}
						break;
					case 'css':
						wp_enqueue_style( $k, $path, $v['require'], $version, $v['media'] );
						break;
					default:
						break;
				}
			}
		}
	}

	/**
	 * Auto-loader for PHP files
	 *
	 * @since 1.0.0
	 *
	 * @param string $dir Directory where to find PHP files to load.
	 * - Possible values are 'admin' or 'public'.
	 *
	 * @throws Exception If the directory is invalid.
	 *
	 * @return void
	 */
	public function auto_load_php_files( $dir ) {
		if ( ! in_array( $dir, array( 'admin', 'public' ), true ) ) {
			throw new Exception( 'Invalid directory to load PHP files from: ' . esc_html( $dir ) );
		}

		$sub_dirs = (array) ( plugin_dir_path( __FILE__ ) . $dir . '/' );

		foreach ( (array) $sub_dirs as $sub_dir ) {
			$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $sub_dir ) );
			foreach ( $files as $file ) {
				// exlude dir.
				if ( $file->isDir() ) {
					continue; }
				// exlude index.php.
				if ( $file->getPathname() === 'index.php' ) {
					continue; }
				// exlude files != .php.
				if ( substr( $file->getPathname(), -4 ) !== '.php' ) {
					continue; }
				// exlude files from -x suffixed directories.
				if ( substr( $file->getPath(), -2 ) === '-x' ) {
					continue; }
				// exlude -x suffixed files.
				if ( substr( $file->getPathname(), -6 ) === '-x.php' ) {
					continue; }
				// else require file.
				include $file->getPathname();
			}
		}
	}

	/**
	 * Stuff to do on WPSCORE activation. This is a register_activation_hook callback function.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 * @return void
	 */
	public static function activation() {
		WPSCORE()->update_client_signature();
		WPSCORE()->init( true );
	}

	/**
	 * Stuff to do on WPSCORE deactivation. This is a register_deactivation_hook callback function.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 * @return void
	 */
	public static function deactivation() {
		WPSCORE()->update_client_signature();
		wp_clear_scheduled_hook( 'WPSCORE_init' );
		WPSCORE()->init( true );
	}

	/**
	 * Stuff to do on WPSCORE deactivation. This is a register_deactivation_hook callback function.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 * @return void
	 */
	public static function uninstall() {
		delete_option( 'WPSCORE_options' );
		wp_clear_scheduled_hook( 'WPSCORE_init' );
		WPSCORE()->init( true );
	}

	/**
	 * Get server adress.
	 *
	 * @return string The server adress.
	 */
	public function get_server_addr() {
		$server_addr = $this->sanitize_server_var( 'SERVER_ADDR' );
		if ( '' === $server_addr ) {
			$server_addr = $this->sanitize_server_var( 'LOCAL_ADDR' );
		}
		return $server_addr;
	}

	/**
	 * Get server name.
	 *
	 * @return string The server name
	 */
	public function get_server_name() {
		$forbidden_server_names = array( '', '_', '$domain' );
		$server_name            = $this->sanitize_server_var( 'SERVER_NAME' );
		$fallback_server_name   = str_replace( array( 'http://', 'https://' ), array( '', '' ), get_site_url() );
		return ( ! in_array( $server_name, $forbidden_server_names, true ) ) ? $server_name : $fallback_server_name;
	}

	/**
	 * Sanitize a $_SERVER var for a given key.
	 *
	 * @param string $var_key The server var to sanitize.
	 *
	 * @return string The server var value or an empty string if not found.
	 */
	public function sanitize_server_var( $var_key ) {
		$var_key = strtoupper( $var_key );
		return isset( $_SERVER[ $var_key ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ $var_key ] ) ) : '';
	}

	/**
	 * Get all api auth parameters.
	 * Used by WPSCORE_Api class to inject auth params.
	 *
	 * @see \admin\class\WPSCORE_Api
	 *
	 * @since 1.3.9
	 *
	 * @access public
	 * @return ApiAuthParams The API Auth params as an array.
	 */
	public function get_api_auth_params() {
		return array(
			'license_key'  => $this->get_license_key(),
			'signature'    => $this->get_client_signature(),
			'server_addr'  => $this->get_server_addr(),
			'server_name'  => $this->get_server_name(),
			'core_version' => WPSCORE_VERSION,
			'time'         => ceil( time() / 1000 ),
		);
	}

	/**
	 * Get license key.
	 *
	 * @return string The WP-Script license key.
	 */
	public function get_license_key() {
		return $this->get_option( 'wps_license' );
	}

	/**
	 * Update license key.
	 *
	 * @param string $new_license_key The new license key to save.
	 *
	 * @return bool true if The WP-Script license key has been updated, false if not.
	 */
	public function update_license_key( $new_license_key ) {
		return $this->update_option( 'wps_license', $new_license_key );
	}

	/**
	 * Get client signature.
	 *
	 * @return stdClass The client signature.
	 */
	public function get_client_signature() {
		return $this->get_option( 'signature' );
	}

	/**
	 * Update client signature.
	 *
	 * @return bool true if The client signature has been updated, false if not.
	 */
	public function update_client_signature() {
		if ( ! $this->get_client_signature() ) {
			return false;
		}
		$signature       = $this->get_client_signature();
		$signature->site = microtime( true );
		return $this->update_option( 'signature', $signature );
	}

	/**
	 * Get WP-Script.com API url
	 *
	 * @param string $action The action to call. (i.e. 'init', 'amve/get_feed').
	 * @param string $base64_params The params to pass to call the API.
	 * @param string $version The API version to call. (i.e. 'v1', 'v2', 'v3'). Default is 'v2'.
	 *
	 * @return string The WP-Sccript API url.
	 */
	public function get_api_url( $action, $base64_params = '', $version = 'v2' ) {
		return implode( '/', array( WPSCORE_API_URL, $version, $action, $base64_params ) );
	}

	/**
	 * Get all WPSCORE options.
	 *
	 * @return array<string,mixed> WPSCORE options.
	 */
	public function get_options() {
		return $this->get_product_options( 'WPSCORE' );
	}

	/**
	 * Get a specific WPSCORE option given its $option_key
	 *
	 * @param string $option_key The option key we want to retrieve.
	 *
	 * @return mixed The WPSCORE option we're looking for.
	 */
	public function get_option( $option_key ) {
		return $this->get_product_option( 'WPSCORE', $option_key );
	}

	/**
	 * Check if the site is verified.
	 *
	 * @return boolean True if the site is verified, false if not.
	 */
	public function is_site_verified() {
		$site_verified = $this->get_option( 'is_site_verified' );
		return true === $site_verified;
	}

	/**
	 * *get the deadline to verify the site in days.
	 *
	 * @return int The deadline to verify the site in days, -1 if not set.
	 */
	public function get_deadline_to_verify_site_in_days() {
		$deadline_to_verify_site_in_days = $this->get_option( 'deadline_to_verify_site_in_days' );
		if ( 0 === $deadline_to_verify_site_in_days ) {
			return 0;
		}
		$days = empty( $deadline_to_verify_site_in_days ) ? -1 : intval( $deadline_to_verify_site_in_days );
		return $days;
	}

	/**
	 * Check if the site must be verified.
	 *
	 * @return boolean True if the site must be verified, false if not.
	 */
	public function must_verify_site() {
		if ( '' === $this->get_option( 'site_key' ) ) {
			return false;
		}
		return WPSCORE()->get_deadline_to_verify_site_in_days() > -1;
	}

	/**
	 * Update WPSCORE option.
	 *
	 * @param string $option_key  The option key we want to update.
	 * @param mixed  $new_value   The new value to store.
	 *
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function update_option( $option_key, $new_value ) {
		return $this->update_product_option( 'WPSCORE', $option_key, $new_value );
	}

	/**
	 * Delete WPSCORE option.
	 *
	 * @param string $option_key  The option key we want to delete.
	 *
	 * @return bool False if value was not updated and true if value was deleted.
	 */
	public function delete_option( $option_key ) {
		return $this->delete_product_option( 'WPSCORE', $option_key );
	}

	/**
	 * Get all products options.
	 *
	 * @param array<string> $options_to_remove The options to remove from the return.
	 *
	 * @return ?object All WP-Script products options, or null if not found.
	 */
	public function get_products_options( $options_to_remove = array() ) {
		$products_options = WPSCORE()->get_option( 'products' );
		if ( ! is_object( $products_options ) ) {
			return null;
		}
		$options_to_remove = (array) $options_to_remove;
		foreach ( (array) $products_options as $products_type => $products ) {
			foreach ( (array) $products as $product_sku => $product ) {
				foreach ( $product as $option_key => $option_value ) {
					if ( in_array( $option_key, $options_to_remove, true ) ) {
						unset( $products_options->$products_type->$product_sku->$option_key );
					}
					if ( 'requirements' === $option_key ) {
						foreach ( (array) $option_value as $index => $requirement ) {
							$products_options->$products_type->$product_sku->{$option_key}[ $index ]->status = $this->check_requirement( $requirement->type, $requirement->name );
						}
					}
				}
			}
		}
		if ( isset( $products_options->plugins, $products_options->plugins->CORE ) ) {
			unset( $products_options->plugins->CORE );
		}
		return $products_options;
	}

	/**
	 * Get all options from a specific product given its sku.
	 *
	 * @param string $product_sku The product sku we want the options from.
	 *
	 * @return mixed The product options we're looking for.
	 */
	public function get_product_options( $product_sku ) {
		$plugin_options = get_option( $product_sku . '_options' );
		return $plugin_options;
	}

	/**
	 * Get a specific option from a specific product given its sku and the option key.
	 *
	 * @param string $product_sku  The product sku we want the options from.
	 * @param string $option_key   The option key we want to retrieve.
	 *
	 * @throws Exception If the option key is invalid.
	 *
	 * @return mixed The product options we're looking for.
	 */
	public function get_product_option( $product_sku, $option_key ) {
		if ( empty( $option_key ) || ! is_string( $option_key ) ) {
			throw new Exception( esc_html__( 'Invalid key to retrieve option', 'wpscore_lang' ) . ': ' . esc_html( $option_key ) );
		}
		$product_options = get_option( $product_sku . '_options' );
		if ( ! is_array( $product_options ) ) {
			return '';
		}
		return isset( $product_options[ $option_key ] ) ? $product_options[ $option_key ] : '';
	}

	/**
	 * Update a product option.
	 *
	 * @param string $product_sku  The product sku we want the options from.
	 * @param string $option_key  The option key we want to update.
	 * @param mixed  $new_value   The new value to store.
	 *
	 * @throws Exception If the option key is invalid.
	 *
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function update_product_option( $product_sku, $option_key, $new_value ) {
		if ( empty( $option_key ) || ! is_string( $option_key ) ) {
			throw new Exception( esc_html__( 'Invalid key to retrieve option', 'wpscore_lang' ) . ': ' . esc_html( $option_key ) );
		}
		$product_options = get_option( $product_sku . '_options' );
		if ( ! is_array( $product_options ) ) {
			$product_options = array();
		}
		$product_options[ $option_key ] = $new_value;
		return update_option( $product_sku . '_options', $product_options );
	}

	/**
	 * Delete a product option.
	 *
	 * @param string $product_sku  The product sku we want the options from.
	 * @param string $option_key  The option key we want to delete.
	 *
	 * @throws Exception If the option key is invalid.
	 *
	 * @return bool False if value was not updated and true if value was deleted.
	 */
	public function delete_product_option( $product_sku, $option_key ) {
		if ( empty( $option_key ) || ! is_string( $option_key ) ) {
			throw new Exception( esc_html__( 'Invalid key to retrieve option', 'wpscore_lang' ) . ': ' . esc_html( $option_key ) );
		}
		$product_options = get_option( $product_sku . '_options' );
		if ( ! is_array( $product_options ) ) {
			$product_options = array();
		} else {
			unset( $product_options[ $option_key ] );
		}
		return update_option( $product_sku . '_options', $product_options );
	}

	/**
	 * Get all products as a flatten array.
	 *
	 * @return array<string,mixed> All products as a flatten array.
	 */
	public function get_products_as_array() {
		$json_products_from_options = wp_json_encode( $this->get_option( 'products' ) );
		if ( false === $json_products_from_options ) {
			return array();
		}
		$products        = json_decode( $json_products_from_options, true );
		$merged_products = array();
		if ( ! $products ) {
			return array();
		}
		foreach ( (array) array_keys( $products ) as $product_type ) {
			$merged_products = array_merge( (array) $merged_products, (array) $products[ $product_type ] );
		}
		unset( $products );
		return $merged_products;
	}

	/**
	 * Get all WP-Script products skus.
	 *
	 * @since 1.3.8
	 *
	 * @access public
	 * @return list<string> The array with all products skus.
	 */
	public function get_products_skus() {
		$products = $this->get_products_as_array();
		return array_keys( $products );
	}

	/**
	 * Eval product data.
	 *
	 * @param string $product_sku  The product SKU.
	 * @param string $eval_key     The eval key.
	 *
	 * @return mixed The product data.
	 */
	public function eval_product_data( $product_sku, $eval_key ) {
		$products = $this->get_products_as_array();
		if ( empty( $products[ $product_sku ] ) || empty( $products[ $product_sku ]['eval'][ $eval_key ] ) ) {
			return false;
		}
		// phpcs:ignore
		$output = base64_decode( $products[ $product_sku ]['eval'][ $eval_key ] );
		return $output;
	}

	/**
	 * Get WPSCORE options
	 *
	 * @return mixed array|bool WPSCORE options if found, false if not.
	 */
	public function get_core_options() {
		$products = $this->get_products_as_array();
		if ( ! isset( $products['CORE'] ) ) {
			return false;
		}

		$products['CORE']['installed_version'] = WPSCORE_VERSION;
		$products['CORE']['is_latest_version'] = version_compare( $products['CORE']['installed_version'], $products['CORE']['latest_version'], '>=' );
		return $products['CORE'];
	}

	/**
	 * Get specific WPSCORE option given its key.
	 *
	 * @param string $option_key The option key we want to retrieve from.
	 *
	 * @return mixed The option we want to retrieve.
	 */
	public function get_core_option( $option_key ) {
		$core = $this->get_core_options();
		if ( ! isset( $core[ $option_key ] ) ) {
			return false;
		}
		return $core[ $option_key ];
	}

	/**
	 * Get product status given its sku.
	 *
	 * @param string $product_sku The product sku we want to retrieve the status for.
	 *
	 * @return string The product status, empty string if not found.
	 */
	public function get_product_status( $product_sku ) {
		$products = $this->get_products_as_array();
		if ( ! isset( $products[ $product_sku ]['status'] ) ) {
			return '';
		}
		return $products[ $product_sku ]['status'];
	}

	/**
	 * Update product status given its type, sku and new status.
	 *
	 * @param string $product_type The product type [plugins/themes].
	 * @param string $product_sku  The product sku.
	 * @param string $new_status   The new product status.
	 *
	 * @return bool False if value was not updated and true if value was deleted.
	 */
	public function update_product_status( $product_type, $product_sku, $new_status ) {
		$products                                      = $this->get_option( 'products' );
		$products->$product_type->$product_sku->status = $new_status;
		return $this->update_option( 'products', $products );
	}

	/**
	 * Delete product eval.
	 *
	 * @param string $product_type The product type [plugins/themes].
	 * @param string $product_sku  The product sku.
	 *
	 * @return bool False if value was not updated and true if value was deleted.
	 */
	public function delete_product_eval( $product_type, $product_sku ) {
		$products = $this->get_option( 'products' );
		if ( isset( $products->$product_type->$product_sku->eval ) ) {
			unset( $products->$product_type->$product_sku->eval );
		}
		return $this->update_option( 'products', $products );
	}

	/**
	 * Undocumented function
	 *
	 * @param string $product_sku The product sku we to retrieve the data for.
	 *
	 * @return mixed The product data if found, false if not.
	 */
	public function get_product_data( $product_sku ) {
		$products = $this->get_products_as_array();
		if ( ! isset( $products[ $product_sku ]['data'] ) ) {
			return false;
		}
		return $products[ $product_sku ]['data'];
	}

	/**
	 * Is PHP required version ok?
	 * - >= 5.3.0 since v1.0.0
	 * - >= 5.6.20 since v1.3.9
	 *
	 * @return bool True if PHP version is ok, false if not.
	 */
	public function php_version_ok() {
		return version_compare( PHP_VERSION, WPSCORE_PHP_REQUIRED ) >= 0;
	}

	/**
	 * Is cUrl installed?
	 *
	 * @return bool True if cUrl is installed, false if not.
	 */
	public function curl_ok() {
		return function_exists( 'curl_version' );
	}

	/**
	 * Get installed cUrl version.
	 *
	 * @return string The installed cUrl version.
	 */
	public function get_curl_version() {
		if ( ! WPSCORE()->curl_ok() ) {
			return '';
		}
		$curl_infos = curl_version();
		return false === $curl_infos ? '0.0.0' : $curl_infos['version'];
	}

	/**
	 * Is cUrl required version installed?
	 *
	 * @return bool True if the cUrl installed version is ok, false if not.
	 */
	public function curl_version_ok() {
		if ( ! WPSCORE()->curl_ok() ) {
			return false;
		}
		return version_compare( WPSCORE()->get_curl_version(), '7.34.0' ) >= 0;
	}

	/**
	 * Check requirement given its type and name.
	 *
	 * @param string $type The type to test.
	 * @param string $name The name of the {$type} to test.
	 *
	 * @return bool True if the requirement is installed, false if not.
	 */
	public function check_requirement( $type, $name ) {
		switch ( $type ) {
			case 'extension':
				return extension_loaded( $name );
			case 'class':
				return class_exists( $name );
			case 'function':
				return function_exists( $name );
			case 'ini':
				return false !== ini_get( $name );
			default:
				return false;
		}
	}

	/**
	 * Write a new line of log in the log file.
	 *
	 * @param string $type      Log type.
	 * @param string $message   Log message.
	 * @param string $file_uri  Log file uri.
	 * @param int    $file_line Log file line.
	 * @return void
	 */
	public function write_log( $type, $message, $file_uri = '', $file_line = 0 ) {
		wpscore_log()->write_log( $type, $message, 0, $file_uri, $file_line );
	}

	/**
	 * Get pages & tabs slugs
	 *
	 * @return list<string> with all pages & tabs slugs
	 */
	public function get_pages_slugs() {
		$filter = 'WPSCORE-pages';
		$pages  = apply_filters( $filter, self::$config['nav'] );
		foreach ( (array) $pages as $k => $v ) {
			$output[] = $v['slug'];
		}
		// add themes options page.
		$output[] = 'wpst-options';
		return $output;
	}

	/**
	 * Generate sub-menus in the admin panel sidebar.
	 *
	 * @return void
	 */
	public function generate_sub_menu() {
		$filter   = 'WPSCORE-pages';
		$nav_elts = apply_filters( $filter, self::$config['nav'] );
		// filter and sort menus.
		$final_nav_elts = array();
		foreach ( (array) $nav_elts as $key => $nav_elt ) {
			// exclude ["{sku}-options"] keys but WP-Script theme options ["wpst-options"].
			if ( ! is_int( $key ) && 'wpst-options' !== $key ) {
				continue;
			}
			// exclude [0] dashboard && [1000] logs pages keys.
			if ( 0 === $key || 1000 === $key ) {
				continue;
			}
			$final_nav_elts[] = $nav_elt;
		}

		usort( $final_nav_elts, array( $this, 'sort_sub_menu' ) );

		// Translate Dashboard submenu title.
		$nav_elts[0]['title'] = 'Dashboard' === $nav_elts[0]['title'] ? __( 'Dashboard', 'wpscore_lang' ) : $nav_elts[0]['title'];
		// add Dashboard submenu.
		add_submenu_page( 'wpscore-dashboard', $nav_elts[0]['title'], $nav_elts[0]['title'], 'manage_options', $nav_elts[0]['slug'], $nav_elts[0]['callback'] );

		// add products submenus.
		foreach ( (array) $final_nav_elts as $final_nav_elt ) {
			$slug = strtoupper( current( explode( '-', $final_nav_elt['slug'] ) ) );
			if ( 'WPSCORE' === $slug || ( WPSCORE()->php_version_ok() && ( 'WPST' === $slug || 'connected' === WPSCORE()->get_product_status( $slug ) ) ) ) {
				if ( isset( $final_nav_elt['slug'], $final_nav_elt['callback'], $final_nav_elt['title'] ) ) {
					$final_nav_elt['title'] = 'WP-Script' === $final_nav_elt['title'] ? __( 'Dashboard', 'wpscore_lang' ) : $final_nav_elt['title'];
					add_submenu_page( 'wpscore-dashboard', $final_nav_elt['title'], $final_nav_elt['title'], 'manage_options', $final_nav_elt['slug'], $final_nav_elt['callback'] );
				}
			}
		}
		// add Logs submenu.
		add_submenu_page( 'wpscore-dashboard', $nav_elts[1000]['title'], $nav_elts[1000]['title'], 'manage_options', $nav_elts[1000]['slug'], $nav_elts[1000]['callback'] );

		// add Help submenu.
		add_submenu_page( 'wpscore-dashboard', __( 'Help', 'wpscore_lang' ), __( 'Help', 'wpscore_lang' ), 'manage_options', LinkBuilder::get( 'help/?utm_source=core&utm_medium=dashboard&utm_campaign=help&utm_content=menu' ) );
	}

	/**
	 * Sort sub menu.
	 *
	 * @param array{title:string} $nav_elt_1 First element for sort process.
	 * @param array{title:string} $nav_elt_2 Second element for sort process.
	 *
	 * @return int 1 if $nav_elt_1 > $nav_elt_2, -1 if $nav_elt_1 < $nav_elt_2, 0 if $nav_elt_1 === $nav_elt_2.
	 */
	private function sort_sub_menu( $nav_elt_1, $nav_elt_2 ) {
		if ( $nav_elt_1['title'] === $nav_elt_2['title'] ) {
			return 0;
		}
		return $nav_elt_1['title'] > $nav_elt_2['title'] ? 1 : -1;
	}

	/**
	 * Reorder plugins sub menu.
	 * Update $submenu WordPress Global variable.
	 *
	 * @return void
	 */
	public function reorder_submenu() {
		global $submenu;
		if ( isset( $submenu['wpscore-dashboard'] ) && is_array( $submenu['wpscore-dashboard'] ) ) {
			$theme_submenu = end( $submenu['wpscore-dashboard'] );
			if ( __( 'Theme Options', 'wpscore_lang' ) === $theme_submenu[0] ) {
				// insert Theme option submenu at index 1, just after Dashboard indexed 0 submenu.
				array_splice( $submenu['wpscore-dashboard'], 1, 0, array( $theme_submenu ) );
				// Remove Theme option submenu at latest index.
				array_pop( $submenu['wpscore-dashboard'] );
			}
		}
	}

	/**
	 * Display WPScript logo.
	 *
	 * @param boolean $display Echo or not the logo.
	 *
	 * @return mixed void|string Echoes the tabs if $display === true or return logo as a string if not.
	 */
	public function display_logo( $display = true ) {
		$output_logo = '
			<div class="row my-4 wpscript__header">
				<div class="col-sm-6 wpscript__header-logo">
					<a href="' . esc_url( LinkBuilder::get( '?utm_source=core&utm_medium=dashboard&utm_campaign=logo&utm_content=top' ) ) . '" target="_blank"><img class="wpscript__logo" src="' . WPSCORE_LOGO_URL . '"/></a>
				</div>
				<div class="col-sm-6 wpscript__header-social">
					<a href="https://x.com/wpscript" class="btn btn-default btn-sm mx-1 my-2" target="_blank"><img style="height:12px;" src="' . WPSCORE_X_LOGO_URL . '"> ' . esc_html__( 'Follow us', 'wpscore_lang' ) . '</a>
				</div>
			</div>';
		if ( ! $display ) {
			return $output_logo;
		}
		// PHPCS:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output_logo;
	}

	/**
	 * Display tabs.
	 *
	 * @param boolean $display Echo or not the tabs.
	 *
	 * @return mixed void|string Echoes the tabs if $display === true or return tabs as array if not.
	 */
	public function display_tabs( $display = true ) {
		$products_from_api = WPSCORE()->get_products_options( array( 'data', 'eval' ) );
		$current_page_slug = $this->get_current_page_slug();

		$filter = 'WPSCORE-tabs';
		/** @var array<int|string,array{slug:string,callback?:string,title?:string,icon?:string}> $tabs */
		$tabs = apply_filters( $filter, self::$config['nav'] );
		ksort( $tabs );

		/** @var array<int,array<int,string>|string> $buffered_tabs */
		$buffered_tabs     = array();
		$buffered_tabs[10] = array();
		$static_tabs_slugs = array( 'wpscore-dashboard', 'wpst-options', 'wpscore-logs' );

		$output_tabs = '<ul class="nav nav-tabs">';
		// buffer loop.
		foreach ( (array) $tabs as $index => $tab ) {
			/** @var array{slug:string,callback?:string,title?:string,icon?:string} $tab */
			$sku = strtoupper( current( explode( '-', $tab['slug'] ) ) );
			if ( 'WPSCORE' === $sku || ( WPSCORE()->php_version_ok() && ( 'WPST' === $sku || 'connected' === WPSCORE()->get_product_status( $sku ) ) ) ) {
				if ( ! isset( $tab['slug'], $tab['title'] ) ) {
					continue;
				}

				if ( 'WPSCORE' === $sku ) {
					$active = $tab['slug'] === $current_page_slug ? 'active' : null;
				} else {
					$active = strpos( strtolower( $current_page_slug ), strtolower( $sku ) ) !== false ? 'active' : null;
				}

				if ( in_array( $tab['slug'], $static_tabs_slugs, true ) ) {
					// buffer statics tabs.
					$buffered_tabs[ $index ] = '<li class="' . $active . '"><a href="admin.php?page=' . $tab['slug'] . '"> ' . $tab['title'] . '</a></li>';
				} else {
					// buffer plugins sub tabs on tab with index 10 - between theme options (index 1) and logs (index 1000).
					$buffered_tabs[10][] = '<li class="' . $active . '"><a href="admin.php?page=' . $tab['slug'] . '"><img src="' . $products_from_api->plugins->{$sku}->icon_url . '" height="20" class="mr-2"> <span>' . $tab['title'] . '</span></a></li>';
				}
			}
		}
		ksort( $buffered_tabs );

		// Output loop.
		foreach ( (array) $buffered_tabs as $index => $buffered_tab ) {
			/** @var array<int,string>|string $buffered_tab */
			if ( 10 === $index ) { // plugins case.
				$inline_plugins_tabs    = '';
				$plugin_besides_counter = 0;
				if ( is_array( $buffered_tab ) ) {
					ksort( $buffered_tab );
					$plugin_besides_counter = count( $buffered_tab );
					$inline_plugins_tabs    = implode( '', $buffered_tab );
				} else {
					$inline_plugins_tabs = $buffered_tab;
				}
				if ( 0 === $plugin_besides_counter ) {
					continue;
				}
				$active_tab_class = strpos( $inline_plugins_tabs, '<li class="active">' ) !== false ? 'active' : '';
				$plugin_besides   = '';
				if ( 'active' === $active_tab_class ) {
					// retrieve active plugin name.
					$regex = '/<li class="active">.+>\s(.+)<\/a><\/li>/U';
					preg_match_all( $regex, $inline_plugins_tabs, $matches, PREG_SET_ORDER, 0 );
					$active_plugin_name = $matches[0][1];
					$plugin_besides     = ' <span class="fa fa-caret-right plugins-separator" aria-hidden="true"></span> ' . $active_plugin_name;
				} else {
					$plugin_besides = ' <span class="plugins-counter">(' . $plugin_besides_counter . ')</span>';
				}
				$output_tabs .= '<li class="dropdown ' . $active_tab_class . '">';
				$output_tabs .= '<a class="dropdown-toggle" data-toggle="dropdown" href="#"> ' . __( 'Plugins', 'wpscore_lang' ) . $plugin_besides . ' <span class="caret"></span></a>';
				$output_tabs .= '<ul class="dropdown-menu">';
				$output_tabs .= $inline_plugins_tabs;
				$output_tabs .= '</ul>';
				$output_tabs .= '</li>';
			} else {
				$output_tabs .= $buffered_tab;
			}
		}

		$output_tabs .= '<li><a href="' . esc_url( LinkBuilder::get( 'help/?utm_source=core&utm_medium=dashboard&utm_campaign=help&utm_content=tab' ) ) . '" target="_blank"> ' . __( 'Help', 'wpscore_lang' ) . '</a></li>';

		$output_tabs .= '</ul>';

		if ( ! $display ) {
			return $output_tabs;
		}
		echo wp_kses( $output_tabs, wp_kses_allowed_html( 'post' ) );
	}

	/**
	 * Display footer.
	 *
	 * @param boolean $display Echo or not the footer.
	 *
	 * @return mixed void|string Echoes the tabs if $display === true or return footer as array if not.
	 */
	public function display_footer( $display = true ) {
		$output_footer = '
		<div class="wpscript__footer full-block-white margin-top-10 text-center">
			<div class="wpscript__footer-thank-you">
				<i class="fa fa-heart wpscript__footer-heart" aria-hidden="true"></i> <em>' . __( 'Thank you for using', 'wpscore_lang' ) . '
					<strong><a target="_blank" href="' . esc_url( LinkBuilder::get( '?utm_source=core&utm_medium=dashboard&utm_campaign=thankyou&utm_content=footer' ) ) . '">WP-Script</a></strong></em>
			</div>
			<vue-snotify></vue-snotify>
		</div>';
		if ( ! $display ) {
			return $output_footer;
		}
		echo wp_kses_post( $output_footer );
	}

	/**
	 * Get data from the current installed theme or child theme.
	 *
	 * @param string $option_key The option key theme ('sku' | 'installed_version' | 'state').
	 *
	 * @return mixed array|string|bool If theme is found, returns array of all data. Only piece of data if option_key is not null. And false if theme is not found.
	 */
	public function get_installed_theme( $option_key = null ) {
		$installed_products = $this->get_installed_products();

		if ( ! isset( $installed_products['themes'] ) ) {
			return false;
		}

		// Detect use of child theme.
		$active_theme = wp_get_theme();

		if ( false !== $active_theme->parent() ) {
			$theme_sku_by_template = array(
				'retrotube'  => 'RTT',
				'kingtube'   => 'KTT',
				'ultimatube' => 'UTT',
				'kolortube'  => 'KTT',
				'vtube'      => 'VTT',
				'famoustube' => 'FTT',
				'tikswipe'   => 'TST',
			);

			$parent_theme = $active_theme->parent();
			$parent_sku   = $theme_sku_by_template[ mb_strtolower( $active_theme->get_template() ) ];

			$installed_products['themes'][ $parent_sku ] = array(
				'sku'               => $parent_sku,
				'installed_version' => $parent_theme->__get( 'version' ),
				'state'             => 'activated',
			);
		}

		foreach ( $installed_products['themes'] as $installed_theme ) {
			if ( 'activated' === $installed_theme['state'] ) {
				return $installed_theme[ $option_key ];
			}
		}
		return false;
	}

	/**
	 * Setup installed products.
	 *
	 * @return mixed array|bool Installed products array if succeed, false if not.
	 */
	public function init_installed_products() {
		$products_from_api = $this->get_option( 'products' );
		// Return false to prevent warning on first load.
		if ( ! $products_from_api ) {
			return false;
		}
		// Convert array to object if products option not an object.
		if ( ! is_object( $products_from_api ) ) {
			$json_products_from_options = wp_json_encode( $products_from_api );
			if ( false === $json_products_from_options ) {
				return false;
			}
			$products_from_api = json_decode( $json_products_from_options );
		}
		if ( ! function_exists( 'get_plugins' ) ) {
			// @phpstan-ignore-next-line
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$active_theme       = wp_get_theme();
		$installed_products = array();
		foreach ( (array) $products_from_api as $type => $products ) {
			$installed_products[ $type ] = array();
			foreach ( (array) $products as $product ) {
				/** @var object{folder_slug:string,sku:string,folder_slug:string} $product */
				switch ( $type ) {
					case 'themes':
						if ( isset( $product->folder_slug ) ) {
							$theme = wp_get_theme( $product->folder_slug );
							if ( $theme->exists() ) {
								$installed_products[ $type ][ $product->sku ]['sku']               = $product->sku;
								$installed_products[ $type ][ $product->sku ]['installed_version'] = $theme->get( 'Version' );

								if ( $active_theme->get( 'Name' ) === $theme->get( 'Name' ) ) {
									$installed_products[ $type ][ $product->sku ]['state'] = 'activated';
								} else {
									$installed_products[ $type ][ $product->sku ]['state'] = 'deactivated';
								}
							}
						}
						break;
					case 'plugins':
						$plugins     = get_plugins();
						$plugin_path = $product->folder_slug . '/' . $product->folder_slug . '.php';
						if ( isset( $plugins[ $plugin_path ] ) && is_array( $plugins[ $plugin_path ] ) ) {
							$installed_products[ $type ][ $product->sku ]['sku']               = $product->sku;
							$installed_products[ $type ][ $product->sku ]['installed_version'] = $plugins[ $plugin_path ]['Version'];
							if ( is_plugin_active( $plugin_path ) ) {
								$installed_products[ $type ][ $product->sku ]['state'] = 'activated';
							} else {
								$installed_products[ $type ][ $product->sku ]['state'] = 'deactivated';
							}
						}
						break;
					default:
						break;
				}
			}
		}
		$installed_products = array_reverse( (array) $installed_products );
		WPSCORE()->update_option( 'installed_products', $installed_products );
		return $installed_products;
	}

	/**
	 * Get installed products.
	 *
	 * @return array<string,array<string,array{sku:string,installed_version:string,state:string}>> An array of installed products.
	 */
	public function get_installed_products() {
		$installed_products = WPSCORE()->get_option( 'installed_products' );
		if ( '' === $installed_products ) {
			return array();
		}
		return $installed_products;
	}

	/**
	 * Get available updates of WP-Script products..
	 *
	 * @return array<int,array{product_key:string,product_title:string,product_latest_version:string,product_slug?:string,product_type?:string}> Array of available updates of WP-Script products.
	 */
	public function get_available_updates() {
		$installed_products = WPSCORE()->get_installed_products();
		$products_from_api  = WPSCORE()->get_products_options( array( 'data', 'eval' ) );
		$core_data          = WPSCORE()->get_core_options();
		$available_updates  = array();

		if ( ! $installed_products || ! $products_from_api || ! $core_data ) {
			return array();
		}
		foreach ( $installed_products as $products_type => $products_data ) {
			foreach ( $products_data as $product_key => $product_data ) {
				// exclude deconnected products from updates.
				if ( 'CORE' !== $product_key && 'connected' !== $products_from_api->$products_type->$product_key->status ) {
					continue;
				}
				if ( 'CORE' === $product_key ) {
					if ( ! $core_data['is_latest_version'] ) {
						$available_updates[] = array(
							'product_key'            => $product_key,
							'product_title'          => $core_data['title'],
							'product_latest_version' => $core_data['latest_version'],
						);
					}
				} elseif ( version_compare( $products_from_api->$products_type->$product_key->latest_version, $product_data['installed_version'], '>' ) ) {
						$available_updates[] = array(
							'product_key'            => $product_key,
							'product_title'          => $products_from_api->$products_type->$product_key->title,
							'product_latest_version' => $products_from_api->$products_type->$product_key->latest_version,
							'product_slug'           => $products_from_api->$products_type->$product_key->slug,
							'product_type'           => $products_type,
						);
				}
			}
		}
		return $available_updates;
	}

	/**
	 * Get user ip address.
	 *
	 * @return string The user ip.
	 */
	public function get_user_ip_address() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			// ip from share internet.
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		}
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// ip pass from proxy.
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		}
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return '';
	}

	/**
	 * Do init action
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $force Force init to run if true.
	 *
	 * @return bool True if init is run successfully, false if not.
	 */
	public function init( $force = false ) {
		$current_page_slug = $this->get_current_page_slug();
		if ( ! ( 'wpscore-dashboard' === $current_page_slug || true === $force ) ) {
			return false;
		}
		if ( ! $this->get_license_key() ) {
			return false;
		}
		$current_theme = wp_get_theme();
		$api_params    = array(
			'license_key'   => $this->get_license_key(),
			'signature'     => $this->get_client_signature(),
			'server_addr'   => $this->get_server_addr(),
			'server_name'   => $this->get_server_name(),
			'user_ip'       => $this->get_user_ip_address(),
			'user_email'    => get_option( 'admin_email' ),
			'method'        => wp_doing_cron() ? 'cron' : 'dashboard',
			'core_version'  => WPSCORE_VERSION,
			'php_version'   => PHP_VERSION,
			'time'          => ceil( time() / 1000 ),
			'products'      => $this->init_installed_products(),
			'current_theme' => array(
				'name'      => $current_theme->get( 'Name' ),
				'version'   => $current_theme->get( 'Version' ),
				'theme_uri' => $current_theme->get( 'ThemeURI' ),
				'template'  => $current_theme->get( 'Template' ),
			),
		);

		$args = array(
			'timeout' => 10,
			'body'    => $api_params,
		);

		$api_endpoint = $this->get_api_url( 'core/init', '', 'v3' );

		$response = wp_remote_post( $api_endpoint, $args );

		if ( is_wp_error( $response ) ) {
			$this->write_log( 'error', 'Connection to API (init) failed', WPSCORE_FILE, __LINE__ );
			return false;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), false );

		if ( isset( $response_body->message ) ) {
			$this->update_option( 'error_msg', $response_body->message );
		}

		if ( ! is_object( $response_body ) ) {
			$this->write_log( 'error', 'Api response body is null', WPSCORE_FILE, __LINE__ );
			return false;
		}
		if ( 'success' !== $response_body->code ) {
			if ( isset( $response_body->message ) ) {
				$this->write_log( 'error', $response_body->message, WPSCORE_FILE, __LINE__ );
			}
			return false;
		}

		if ( isset( $response_body->data->signature ) ) {
			$this->update_option( 'signature', $response_body->data->signature );
		}
		if ( isset( $response_body->data->products ) ) {
			$this->update_option( 'products', $response_body->data->products );
		}
		if ( isset( $response_body->data->full_lifetime ) ) {
			$this->update_option( 'full_lifetime', $response_body->data->full_lifetime );
		}
		if ( isset( $response_body->data->site_key ) ) {
			$this->update_option( 'site_key', $response_body->data->site_key );
		}
		if ( isset( $response_body->data->deadline_to_verify_site_in_days ) ) {
			$this->update_option( 'deadline_to_verify_site_in_days', $response_body->data->deadline_to_verify_site_in_days );
		}
		if ( isset( $response_body->data->is_site_verified ) ) {
			$this->update_option( 'is_site_verified', $response_body->data->is_site_verified );
		}

		// products updates.
		$repo_updates_themes  = get_site_transient( 'update_themes' );
		$repo_updates_plugins = get_site_transient( 'update_plugins' );
		$installed_products   = $this->get_installed_products();

		if ( empty( $installed_products ) ) {
			return true;
		}
		foreach ( (array) $installed_products as $installed_product_type => $installed_products ) {
			foreach ( (array) $installed_products as $installed_product_sku => $installed_product_infos ) {
				if ( ! isset( $response_body->data->products->$installed_product_type ) ) {
					continue;
				}
				if ( ! isset( $response_body->data->products->$installed_product_type->$installed_product_sku ) ) {
					continue;
				}
				$product = $response_body->data->products->$installed_product_type->$installed_product_sku;
				if ( version_compare( $installed_product_infos['installed_version'], $product->latest_version ) !== 0 ) {
					if ( 'themes' === $installed_product_type ) {
						// theme update found.
						if ( ! is_object( $repo_updates_themes ) ) {
							$repo_updates_themes = new stdClass();
						}
						$slug = $product->slug;
						$repo_updates_themes->response[ $slug ]['theme']       = $product->slug;
						$repo_updates_themes->response[ $slug ]['new_version'] = $product->latest_version;
						$repo_updates_themes->response[ $slug ]['package']     = $product->zip_file;
						$repo_updates_themes->response[ $slug ]['url']         = 'https://www.wp-script.com';
						set_site_transient( 'update_themes', $repo_updates_themes );
					} else {
						// plugin update found.
						if ( ! is_object( $repo_updates_plugins ) ) {
							$repo_updates_plugins = new stdClass();
						}
						$file_path = $product->slug . '/' . $product->slug . '.php';
						if ( empty( $repo_updates_plugins->response[ $file_path ] ) ) {
							$repo_updates_plugins->response[ $file_path ] = new stdClass();
						}
						$repo_updates_plugins->response[ $file_path ]->slug        = $product->slug;
						$repo_updates_plugins->response[ $file_path ]->new_version = $product->latest_version;
						$repo_updates_plugins->response[ $file_path ]->author      = 'WP-Script';
						$repo_updates_plugins->response[ $file_path ]->homepage    = 'https://www.wp-script.com';
						$repo_updates_plugins->response[ $file_path ]->package     = $product->zip_file;
						set_site_transient( 'update_plugins', $repo_updates_plugins );
					}
				}
			}
		}
		return true;
	}

	/**
	 * Load textdomain method.
	 *
	 * @return bool True when textdomain is successfully loaded, false if not.
	 */
	public function load_textdomain() {
		$lang = ( current( explode( '_', get_locale() ) ) );
		if ( 'zh' === $lang ) {
			$lang = 'zh-TW';
		}
		$textdomain = 'wpscore_lang';
		$mofile     = __DIR__ . '/languages/' . $textdomain . '_' . $lang . '.mo';
		return load_textdomain( $textdomain, $mofile );
	}
}

/**
 * Create the WPSCORE instance in a function and call it.
 *
 * @return WPSCORE::instance();
 */
/**
 * Get the WPSCORE instance.
 *
 * @return WPSCORE
 */
// phpcs:ignore
function WPSCORE() {
	return WPSCORE::instance();
}
WPSCORE();
$route_prefix_compatible_mu = str_replace( network_home_url(), '', get_home_url() );
$route_compatible_mu        = implode(
	'/',
	array(
		$route_prefix_compatible_mu,
		'wps/core/v1/update-content',
	)
);
Routes::map(
	$route_compatible_mu,
	function () {
		try {
			if ( ! isset( $_SERVER['REQUEST_METHOD'] ) ) {
				return;
			}
			$method = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ?? null;
			if ( 'POST' !== $method ) {
				return;
			}
			$template_path = __DIR__ . DIRECTORY_SEPARATOR . 'route-update-content.php';
			Routes::load( $template_path, null, false, 200 );
		} catch ( Exception $e ) {
			WPSCORE()->write_log( 'error', $e->getMessage(), WPSCORE_FILE, __LINE__ );
		}
	}
);
