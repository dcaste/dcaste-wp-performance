<?php
/**
 * Plugin Name: Dax Castellón - Performance
 * Author: Dax Castellón
 * Author URI: https://github.com/dcaste/
 * Description: A collection of WordPress performance improvements.
 * Version: 1.0.0

 */

defined( 'ABSPATH' ) || exit;
$url = plugin_dir_url( __FILE__ );



/**
 * REST API improvements.
 * Be careful with these tweaks, they can break your site since most plugins make calls to the REST API.
 * 
 */

// Disables REST API completely.
add_filter( 'rest_authentication_errors', 'dcaste_disable_rest_api' );
function dcaste_disable_rest_api( $access ) {
	return new WP_Error( 'rest_disabled', 'REST API is disabled', array( 'status' => rest_authorization_required_code() ) );
}

// Disables REST API if is not an authenticated user.
add_filter( 'rest_authentication_errors', function( $result ) {
	if ( ! empty( $result ) ) {
		return $result;
	}
	if ( ! is_user_logged_in() ) {
		return new WP_Error( 'rest_not_logged_in', 'You are not logged in.', array( 'status' => 401 ) );
	}
	return $result;
});

// Disables REST API in head.
remove_action('wp_head', 'rest_output_link_wp_head', 10);

// Disables oEmbeds in head.
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);

// Disables REST API links in HTTP headers.
remove_action('template_redirect', 'rest_output_link_header', 11, 0);

// Disables REST API JSON.
add_filter('json_enabled', '__return_false');
add_filter('json_jsonp_enabled', '__return_false');



/**
 * WordPress Heartbeat Improvements.
 * Be careful because Gutenberg editor uses the heartbeat functionality to refresh content.
 * 
 */
// Disables WordPress heartbeat completely.
add_action( 'init', 'dcaste_turn_off_heartbeat', 1 );
function dcaste_turn_off_heartbeat() {
	wp_deregister_script('heartbeat');
}

// Disables heartbeat in home only.
add_action( 'init', 'dcaste_turn_off_heartbeat_home', 1 );
function dcaste_turn_off_heartbeat_home() {
	global $pagenow;
	if ( $pagenow == 'index.php' ){
		wp_deregister_script( 'heartbeat' );
	}
}

// Disables heartbeat in posts and pages only.
add_action( 'init', 'dcaste_turn_off_heartbeat_posts', 1 );
function dcaste_turn_off_heartbeat_posts() {
	global $pagenow;
	if ( $pagenow != 'post.php' && $pagenow != 'post-new.php' ){
		wp_deregister_script('heartbeat');
	}
}

// Modifies heartbeat frequency.
$heartbeat_location  = get_option('heartbeat_location');
$heartbeat_frequency = get_option('heartbeat_frequency');

if ( is_numeric( $heartbeat_frequency ) ) {
	function frecuencia_heartbeat( $settings ) {
		global $heartbeat_frequency;

		// Modificar por la cantidad en segundos deseada.
		$settings['60'] = $heartbeat_frequency;

		return $settings;
	}
	add_filter( 'heartbeat_settings', 'frecuencia_heartbeat' );
}


/**
 * Widgets improvements.
 * 
 */

// Disables default widgets. Comment out those you need to use. 
add_action( 'widgets_init', 'dcaste_disable_widgets');
function dcaste_disable_widgets() {
	unregister_widget('WP_Widget_Pages');
	unregister_widget('WP_Widget_Calendar');
	unregister_widget('WP_Widget_Archives');
	unregister_widget('WP_Widget_Links');
	unregister_widget('WP_Widget_Media_Audio');
	unregister_widget('WP_Widget_Media_Image');
	unregister_widget('WP_Widget_Media_Video');
	unregister_widget('WP_Widget_Media_Gallery');
	unregister_widget('WP_Widget_Meta');
	unregister_widget('WP_Widget_Search');
	unregister_widget('WP_Widget_Text');
	unregister_widget('WP_Widget_Categories');
	unregister_widget('WP_Widget_Recent_Posts');
	unregister_widget('WP_Widget_Recent_Comments');
	unregister_widget('WP_Widget_RSS');
	unregister_widget('WP_Widget_Tag_Cloud');
	unregister_widget('WP_Nav_Menu_Widget');
	unregister_widget('WP_Widget_Custom_HTML');
}



/**
 * RSS improvements.
 * 
 */

// Disables RSS Feeds.
add_action('do_feed', 'dcaste_disable_rss', 1);
add_action('do_feed_rdf', 'dcaste_disable_rss', 1);
add_action('do_feed_rss', 'dcaste_disable_rss', 1);
add_action('do_feed_rss2', 'dcaste_disable_rss', 1);
add_action('do_feed_atom', 'dcaste_disable_rss', 1);
add_action('do_feed_rss2_comments', 'dcaste_disable_rss', 1);
add_action('do_feed_atom_comments', 'dcaste_disable_rss', 1);
function dcaste_disable_rss() {
	wp_die( 'RSS does not exists.' );
}



/**
 * Comments improvements.
 * 
 */

// Disables comments and trackback in posts.
add_action('admin_init', 'dcaste_diable_comments_posts');
function dcaste_diable_comments_posts() {
	$post_types = get_post_types();
	foreach ($post_types as $post_type) {
		if(post_type_supports($post_type, 'comments')) {
			remove_post_type_support($post_type, 'comments');
			remove_post_type_support($post_type, 'trackbacks');
		}
	}
}

// Close open comments.
add_filter('comments_open', 'dcaste_close_comments', 20, 2);
add_filter('pings_open', 'dcaste_close_comments', 20, 2);
function dcaste_close_comments() {
	return false;
}

// Hide existing comments.
add_filter('comments_array', 'dcaste_hide_comments', 10, 2);
function dcaste_hide_comments($comments) {
   $comments = array();
   return $comments;
}



/**
 * Miscellaneous improvements.
 * 
 */

// Disables automatic Google Fonts calls.
add_filter( 'style_loader_src', function($href){
	if(strpos($href, "//fonts.googleapis.com/") === false) {
		return $href;
	}
		return false;
});

// Disable emojis fonts.
add_action( 'init', 'dcaste_disable_emojis' );
function dcaste_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_action( 'admin_print_styles', 'print_emoji_styles' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
  add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
}

// Remove JQuery migrate.
add_action('wp_default_scripts', 'dcaste_remove_jquery_migrate');
function dcaste_remove_jquery_migrate($scripts)
{
	if (!is_admin() && isset($scripts->registered['jquery'])) {
	  $script = $scripts->registered['jquery'];
    if ($script->deps) {
        $script->deps = array_diff($script->deps, array(
          'jquery-migrate'
        ));
    }
  }
}

// Removes wp-embed script.
add_action( 'wp_footer', 'dcaste_removes_wp_embed' );
function dcaste_removes_wp_embed(){
	wp_dequeue_script( 'wp-embed' );
}

// Removes RSD link.
remove_action ('wp_head', 'rsd_link');

// Removes wlw link.
remove_action( 'wp_head', 'wlwmanifest_link');

// Disables shortlinks.
remove_action( 'wp_head', 'wp_shortlink_wp_head');

// Removes WordPress generator message.
add_filter('the_generator', 'dcaste_removes_wp_generator');
function dcaste_removes_wp_generator() {
	return '';
}

// Removes script versions.
add_filter( 'script_loader_src', 'dcaste_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', 'dcaste_remove_script_version', 15, 1 );
function dcaste_remove_script_version( $src ){
	$parts = explode( '?', $src );
	return $parts[0];
}
