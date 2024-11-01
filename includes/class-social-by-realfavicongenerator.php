<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Social_by_RealFaviconGenerator {

	const PLUGIN_PREFIX = 'sbrfg';

	const OPTION_HTML_CODE                   = 'sbrfg_html_code';
	const OPTION_OG_SERIALIZED_DATA          = 'sbrfg_og_serialized_data';
	const OPTION_OG_IMAGE_ID                 = 'sbrfg_og_image_id';

	const PLACEHOLDER_URL                    = 'SbRFG_Placeholder_Url';
	const PLACEHOLDER_SITE_NAME              = 'SbRFG_Placeholder_Site_Name';
	const PLACEHOLDER_LOCALE                 = 'SbRFG_Placeholder_Locale';
	const PLACEHOLDER_ARTICLE_PUBLISHED_TIME = '2016-10-13T15:44:04+0000';
	const PLACEHOLDER_ARTICLE_MODIFIED_TIME  = '2016-10-13T15:45:05+0000';
	const PLACEHOLDER_ARTICLE_AUTHOR         = 'SbRFG_Placeholder_Article_Author';
	const PLACEHOLDER_ARTICLE_SECTION        = 'SbRFG_Placeholder_Article_Section';
	const PLACEHOLDER_ARTICLE_TAG            = 'SbRFG_Placeholder_Article_Tag';
	const PLACEHOLDER_ARTICLE_PUBLISHER      = 'SbRFG_Placeholder_Article_Publisher';

	const PLUGIN_SLUG                        = 'social-by-realfavicongenerator';

	/**
	 * The single instance of Social_by_RealFaviconGenerator.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'social_by_realfavicongenerator';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			new Social_by_RealFaviconGenerator_Admin_API();
		}
		else {
			new Social_by_RealFaviconGenerator_Public();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	} // End __construct ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_enqueue_script( $this->_token . '-admin-rfg-core',
			'https://realfavicongenerator.net/web-components/js/core.min.js',
			array( 'jquery' ), false, true );
		wp_enqueue_script( $this->_token . '-admin-rfg-facebook',
			'https://realfavicongenerator.net/web-components/js/facebook.min.js',
			array( $this->_token . '-admin-rfg-core' ), false, true );
		wp_enqueue_script( $this->_token . '-admin',
			esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js',
			array( $this->_token . '-admin-rfg-facebook' ), $this->_version );
	}

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'social-by-realfavicongenerator', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'social-by-realfavicongenerator';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Social_by_RealFaviconGenerator Instance
	 *
	 * Ensures only one instance of Social_by_RealFaviconGenerator is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Social_by_RealFaviconGenerator()
	 * @return Main Social_by_RealFaviconGenerator instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()




	/**
	 * Returns /www/wordpress/wp-content/uploaded/sbrfg
	 */
	public static function get_files_dir( $post_id = NULL ) {
		$up_dir = wp_upload_dir();
		return $up_dir['basedir'] . '/' .
			Social_by_RealFaviconGenerator::PLUGIN_PREFIX . '/' .
			( $post_id ? $post_id . '/' : '' );
	}

	/**
	 * Returns http//somesite.com/blog/wp-content/upload/sbrfg/
	 */
	public static function get_files_url( $post_id ) {
		$up_dir = wp_upload_dir();
		$baseUrl = $up_dir['baseurl'];
		// Make sure to no duplicate the '/'
		// This is especially important when the base URL is the root directory:
		// When this happens, the generated URL would be
		// "http//somesite.com//fbrfg/" and then "//fbrfg/" when the host name is
		// stripped. But this path is wrong, as it looks like a "same protocol" URL.
		$separator = (substr($baseUrl, -1) == '/') ? '' : '/';
		return $baseUrl . $separator .
			Social_by_RealFaviconGenerator::PLUGIN_PREFIX . '/' . $post_id . '/';
	}

	public static function get_tmp_dir() {
		return Social_by_RealFaviconGenerator::get_files_dir() . 'tmp/';
	}

	public static function remove_directory($directory) {
		foreach( scandir( $directory ) as $v ) {
			if ( is_dir( $directory . '/' . $v ) ) {
				if ( $v != '.' && $v != '..' ) {
					Social_by_RealFaviconGenerator::remove_directory( $directory . '/' . $v );
				}
			}
			else {
				unlink( $directory . '/' . $v );
			}
		}
		rmdir( $directory );
	}

	// See https://www.justinsilver.com/technology/writing-to-the-php-error_log-with-var_dump-and-print_r/
	public static function var_error_log( $object = NULL ) {
		ob_start();
		var_dump( $object );
		$contents = ob_get_contents();
		ob_end_clean();
		error_log( $contents );
	}
}

// Shortcut
define('SBRFG_PLUGIN_SLUG', Social_by_RealFaviconGenerator::PLUGIN_SLUG);
