<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Social_by_RealFaviconGenerator_Public {

	public function __construct () {
		add_action( 'wp_head', array( $this, 'add_favicon_markups' ) );

		// Disable WPSEO/Yoast OpenGraph markups
		add_action( 'wpseo_head', array( $this, 'remove_wpseo_open_graph' ), 1 );

		// Disable Jetpack Open Graph markups
		add_filter( 'jetpack_enable_open_graph', '__return_false' );
	}

	public function get_post_open_graph_code() {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return NULL;
		}

		$code = get_post_meta( $post_id,
			Social_by_RealFaviconGenerator::OPTION_HTML_CODE,
			true );

		// Category
		$category = get_the_category( );
		if ( count( $category ) > 0) {
			$category = $category[ 0 ]->name;
		}
		else {
			$category = '';
		}

		// Tags
		$wp_tags = wp_get_post_tags ( get_the_ID() );
		$tags = array();
		foreach ($wp_tags as $t) {
			array_push( $tags, $t->name);
		}

		// Inject information
		foreach(array(
				// Generic
				array( Social_by_RealFaviconGenerator::PLACEHOLDER_URL, wp_get_canonical_url( $post_id ) ),
				array( Social_by_RealFaviconGenerator::PLACEHOLDER_SITE_NAME, get_bloginfo( 'name' ) ),
				array( Social_by_RealFaviconGenerator::PLACEHOLDER_LOCALE, get_locale() ),

				// Article
				array( Social_by_RealFaviconGenerator::PLACEHOLDER_ARTICLE_PUBLISHED_TIME, get_the_time( 'c' ) ),
				array( Social_by_RealFaviconGenerator::PLACEHOLDER_ARTICLE_MODIFIED_TIME, get_the_time( 'c' ) ),
				array( Social_by_RealFaviconGenerator::PLACEHOLDER_ARTICLE_AUTHOR, get_the_author_meta( 'facebook' ) ),
				array( Social_by_RealFaviconGenerator::PLACEHOLDER_ARTICLE_SECTION, $category ),
				array( Social_by_RealFaviconGenerator::PLACEHOLDER_ARTICLE_TAG, $tags ),
				array( Social_by_RealFaviconGenerator::PLACEHOLDER_ARTICLE_PUBLISHER, get_the_author_meta( 'facebook' ) ),
			) as $param) {
				$code = Social_by_RealFaviconGenerator_Public::replace_placeholder(
					$code, $param[0], $param[1] );
		}

		return $code;
	}

	public static function replace_placeholder( $html_code, $placeholder, $value ) {
		$regex_placeholder = preg_quote( $placeholder );
		$matches = array();
		if (! preg_match( "/<[^<]*{$regex_placeholder}[^>]*>[\\n\\r]*/", $html_code, $matches ) || ( count( $matches ) == 0 ) ) {
			return $html_code;
		}

		$markup = $matches[0];

		if ( is_array ( $value ) ) {
			$new_code = '';
			foreach( $value as $v ) {
				$new_code .= str_replace( $placeholder, $v, $markup );
			}
		}
		else if ( $value ) {
			$new_code = str_replace( $placeholder, $value, $markup );
		}
		else {
			$new_code = '';
		}

		return str_replace( $markup, $new_code, $html_code );
	}

	public static function get_the_author_full_name() {
		$fn = get_the_author_meta('first_name');
		$ln = get_the_author_meta('last_name');
		if ( ( ! empty( $fn ) ) || ( ! empty( $ln ) ) ) {
			return trim( "$fn $ln" );
		}
		else {
			return get_the_author();
		}
	}

	public function remove_wpseo_open_graph() {
		$code = $this->get_post_open_graph_code();
		if ( $code ) {
			remove_all_actions( 'wpseo_opengraph' );
		}
	}

	public function add_favicon_markups() {
		$code = $this->get_post_open_graph_code();
		if ( $code ) {
			echo $code;
		}
	}

}
