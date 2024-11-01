<?php
/*
 * Plugin Name: Social by RealFaviconGenerator
 * Version: 0.0.7
 * Plugin URI: http://realfavicongenerator.net/
 * Description: Craft the appearance of your site when your visitors share it on Facebook
 * Author: Philippe Bernard
 * Author URI: https://realfavicongenerator.net/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: social-by-realfavicongenerator
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author phbernard
 * @since 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-social-by-realfavicongenerator.php' );

// Load plugin libraries
require_once( 'includes/lib/class-social-by-realfavicongenerator-admin-api.php' );
require_once( 'includes/lib/class-social-by-realfavicongenerator-public.php' );

/**
 * Returns the main instance of Social_by_RealFaviconGenerator to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Social_by_RealFaviconGenerator
 */
function Social_by_RealFaviconGenerator () {
	$instance = Social_by_RealFaviconGenerator::instance( __FILE__, '0.0.7' );

	return $instance;
}

Social_by_RealFaviconGenerator();
