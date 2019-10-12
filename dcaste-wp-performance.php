<?php
/**
 * Plugin Name: Tactic Center - Performance
 * Author: Tactic-Center
 * Author URI: http://tactic-center.com
 * Description: Mejoras en el performance de Wordpress.
 * Version: 1.0.0

 */

defined( 'ABSPATH' ) || exit;
$url = plugin_dir_url( __FILE__ );


/* REST API *******************************************************************/

// Deshabilita el sistema de REST API completamente.
// Utilizar con precaución pues aLgunos plugins pueden depender de esta funcionalidad.
add_filter( 'rest_authentication_errors', 'deshabilitar_rest_api' );
function deshabilitar_rest_api( $access ) {
	return new WP_Error( 'rest_disabled', 'El REST API en este sitio ha sido deshabilitado', array( 'status' => rest_authorization_required_code() ) );
}

// Inhabilita el acceso al REST API si no es un usuario conectado.
add_filter( 'rest_authentication_errors', function( $result ) {
	if ( ! empty( $result ) ) {
		return $result;
	}
	if ( ! is_user_logged_in() ) {
		return new WP_Error( 'rest_not_logged_in', 'No está conectado.', array( 'status' => 401 ) );
	}
	return $result;
});

// Deshabilita el enlace de REST API en el head.
remove_action('wp_head', 'rest_output_link_wp_head', 10);

// Deshabilita enlaces de oEmbeds en el head.
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);

// Deshabilita el enlace a REST API en los HTTP headers.
remove_action('template_redirect', 'rest_output_link_header', 11, 0);

// Deshabilita la carga de JSON del REST API.
add_filter('json_enabled', '__return_false');
add_filter('json_jsonp_enabled', '__return_false');



/* HEARTBEAT ******************************************************************/

// Deshabilita completamente la funcionalidad de heartbeat.
add_action( 'init', 'apaga_heartbeat', 1 );
function apaga_heartbeat() {
	wp_deregister_script('heartbeat');
}

// Deshabilita heartbeat en homepage solamente.
add_action( 'init', 'apaga_heartbeat_home', 1 );
function apaga_heartbeat_home() {
	global $pagenow;
	if ( $pagenow == 'index.php' ){
		wp_deregister_script( 'heartbeat' );
	}
}

// Deshabilita heartbeat en posts y páginas solamente.
add_action( 'init', 'apaga_heartbeat_posts', 1 );
function apaga_heartbeat_posts() {
	global $pagenow;
	if ( $pagenow != 'post.php' && $pagenow != 'post-new.php' ){
		wp_deregister_script('heartbeat');
	}
}

// Modifica la frecuencia del heartbeat.
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


/* WIDGETS ********************************************************************/

//Desactiva todos los widgets que trae consigo WP por defecto. Comentar los que no se desean desactivar.
add_action( 'widgets_init', 'remover_widgets');
function remover_widgets() {
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



/* RSS ************************************************************************/

// Deshabilita los feeds de RSS.
add_action('do_feed', 'deshabilitar_rss', 1);
add_action('do_feed_rdf', 'deshabilitar_rss', 1);
add_action('do_feed_rss', 'deshabilitar_rss', 1);
add_action('do_feed_rss2', 'deshabilitar_rss', 1);
add_action('do_feed_atom', 'deshabilitar_rss', 1);
add_action('do_feed_rss2_comments', 'deshabilitar_rss', 1);
add_action('do_feed_atom_comments', 'deshabilitar_rss', 1);
function deshabilitar_rss() {
	wp_die( 'No existe RSS' );
}



// Desactiva la llamada automática de fuentes de Google.
add_filter( 'style_loader_src', function($href){
	if(strpos($href, "//fonts.googleapis.com/") === false) {
		return $href;
	}
		return false;
});


/* COMENTARIOS ****************************************************************/

// Deshabilita comentarios y trackbacks en los posts.
add_action('admin_init', 'deshabilita_comentarios_posts');
function deshabilita_comentarios_posts() {
	$post_types = get_post_types();
	foreach ($post_types as $post_type) {
		if(post_type_supports($post_type, 'comments')) {
			remove_post_type_support($post_type, 'comments');
			remove_post_type_support($post_type, 'trackbacks');
		}
	}
}

// Cierra los comentarios abiertos.
add_filter('comments_open', 'cierra_comentarios_abiertos', 20, 2);
add_filter('pings_open', 'cierra_comentarios_abiertos', 20, 2);
function cierra_comentarios_abiertos() {
	return false;
}

// Ocultar los comnetarios existentes.
add_filter('comments_array', 'ocultar_comentarios', 10, 2);
function ocultar_comentarios($comments) {
   $comments = array();
   return $comments;
}



/* LIMPIEZA DE SCRIPTS VARIOS *************************************************/

// Deshabilita el script de emojis.
add_action( 'init', 'deshabilitar_emojis' );
function deshabilitar_emojis() {
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

// Remueve JQuery migrate
add_action('wp_default_scripts', 'remueve_jquery_migrate');
function remueve_jquery_migrate($scripts)
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

// Remueve el script wp-embed.
add_action( 'wp_footer', 'remueve_wp_embed' );
function remueve_wp_embed(){
	wp_dequeue_script( 'wp-embed' );
}



/* VARIOS *********************************************************************/

// Deshabilita el enlace  a RSD.
remove_action ('wp_head', 'rsd_link');

// Deshabilita el enlace de wlw.
remove_action( 'wp_head', 'wlwmanifest_link');

// Deshabilita los 'shortlinks'.
remove_action( 'wp_head', 'wp_shortlink_wp_head');

// Remueve el mensaje de generador de Wordpress.
add_filter('the_generator', 'remueve_generador_wp');
function remueve_generador_wp() {
	return '';
}

// Desactiva el llamado a pings del cron de WP.
if (isset($_GET['doing_wp_cron'])) {
	remove_action('do_pings', 'do_all_pings');
	wp_clear_scheduled_hook('do_pings');
}

// Remueve las versiones de los scripts.
add_filter( 'script_loader_src', 'remueve_version_script', 15, 1 );
add_filter( 'style_loader_src', 'remueve_version_script', 15, 1 );
function remueve_version_script( $src ){
	$parts = explode( '?', $src );
	return $parts[0];
}
