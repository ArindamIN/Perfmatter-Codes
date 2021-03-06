#Disable File Editing
  
There are two editing options provided by WordPress for Themes and Plugins. Once your site is live you need to disable the file editing method.

Paste the following code in your wp-config.php file.
define('DISALLOW_FILE_EDIT', true);

#Disable Emoji
Emoji are loaded on every page of your website. It’s actually loaded by a javascript file ( wp-emoji-release.min.js). You can remove Emoji with the following code. Should be added in functions.php
add_action('init', 'disable_emojis');

function disable_emojis() {
     remove_action('wp_head', 'print_emoji_detection_script', 7);
     remove_action('admin_print_scripts', 'print_emoji_detection_script');
     remove_action('wp_print_styles', 'print_emoji_styles');
     remove_action('admin_print_styles', 'print_emoji_styles');  
     remove_filter('the_content_feed', 'wp_staticize_emoji');
     remove_filter('comment_text_rss', 'wp_staticize_emoji');    
     remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
     add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
     add_filter('wp_resource_hints', 'disable_emojis_dns_prefetch', 10, 2);
     add_filter('emoji_svg_url', '__return_false');
 }
 function disable_emojis_tinymce($plugins) {
     if(is_array($plugins)) {
         return array_diff($plugins, array('wpemoji'));
     } else {
         return array();
     }
 }
 function disable_emojis_dns_prefetch( $urls, $relation_type ) {
     if('dns-prefetch' == $relation_type) {
         $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2.2.1/svg/');
         $urls = array_diff($urls, array($emoji_svg_url));
     }
     return $urls;
 }
 ```
///Remove Query Strings
Query strings such as ? or & are added by WordPress to every CSS and js files for versioning (?ver=5.0.2). You may get a warning about removing this while you do a speed test. Remove the query strings with the following code.
add_action('init', 'remove_query_strings');

function remove_query_strings() {
     if(!is_admin()) {
         add_filter('script_loader_src', 'remove_query_strings_split', 15);
         add_filter('style_loader_src', 'remove_query_strings_split', 15);
     }
 }
 function remove_query_strings_split($src){
     $output = preg_split("/(&ver|\?ver)/", $src);
     return $output[0];
 }

#WP REST API
If you want to disable the WP REST API then add this. Keep in mind that this feature can’t be disabled completely because many plugins like Yoast SEO and Gutenberg Editor use REST API so we can only restrict it to logged-in Users only…


add_filter( 'rest_authentication_errors', function( $result ) {
    if ( ! empty( $result ) ) {
        return $result;
    }
    if ( ! is_user_logged_in() ) {
        return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array( 'status' => 401 ) );
    }
    return $result;
});





#Disable XML-RPC
#XML-RPC is used for remote connections. For better security, you can remove this from your WordPress.
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_headers', 'remove_x_pingback');
add_filter('pings_open', '__return_false', 9999);

function remove_x_pingback($headers) {
     unset($headers['X-Pingback'], $headers['x-pingback']);
     return $headers;
}

#Remove jQuery Migrate
#Most latest WordPress themes and plugins won’t use jQuery migrate. In most cases, it’s an unnecessary load to your website#

add_filter('wp_default_scripts', 'remove_jquery_migrate');

function remove_jquery_migrate(&$scripts) {
     if(!is_admin()) {
         $scripts->remove('jquery');
         $scripts->add('jquery', false, array( 'jquery-core' ), '1.12.4');
     }
 }

7. Remove Meta Generator Tags
By default, a meta tag is added by WordPress with the version you are using. Why would you show your version of WP to everyone? Just remove it.

remove_action('wp_head', 'wp_generator');
add_filter('the_generator', 'hide_wp_version');

function hide_wp_version() {
     return '';
 }

8. Remove Manifest, RSD, and Shortlinks
You can safely remove the manifest link if you are not using Windows Live Writer.
remove_action('wp_head', 'wlwmanifest_link');
 


RSD links are mostly unnecessary code.
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action ('template_redirect', 'wp_shortlink_header', 11, 0);

If you are already using permalinks, then you don’t need the short links.
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action ('template_redirect', 'wp_shortlink_header', 11, 0);

9. Disable Pingbacks
add_action('pre_ping', 'disable_self_pingbacks');

function disable_self_pingbacks(&$links) {
     $home = get_option('home');
     foreach($links as $l => $link) {
         if(strpos($link, $home) === 0) {
             unset($links[$l]);
         }
     }
 }

10.Disable Dashicons
Dashicons is the official font used for icons by WordPress. If you don’t need dash icons you can remove this from your front end except for all admin pages.
add_action('wp_enqueue_scripts', 'disable_dashicons');

function disable_dashicons() {
     if(!is_admin()) {
         wp_dequeue_style('dashicons');
         wp_deregister_style('dashicons');
     }
 }



11. Disable Embeds in WordPress
OEmbed provides an easy way to embed content from one site to another. Many popular websites like Flickr, YouTube, Twitter, and others use it. But to do so it adds a js file. So if you are not using it then you may disable it by adding 


function disable_embeds_code_init() {

 // Remove the REST API endpoint.
 remove_action( 'rest_api_init', 'wp_oembed_register_route' );

 // Turn off oEmbed auto discovery.
 add_filter( 'embed_oembed_discover', '__return_false' );

 // Don't filter oEmbed results.
 remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

 // Remove oEmbed discovery links.
 remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

 // Remove oEmbed-specific JavaScript from the front-end and back-end.
 remove_action( 'wp_head', 'wp_oembed_add_host_js' );
 add_filter( 'tiny_mce_plugins', 'disable_embeds_tiny_mce_plugin' );

 // Remove all embeds rewrite rules.
 add_filter( 'rewrite_rules_array', 'disable_embeds_rewrites' );

 // Remove filter of the oEmbed result before any HTTP requests are made.
 remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
}

add_action( 'init', 'disable_embeds_code_init', 9999 );

function disable_embeds_tiny_mce_plugin($plugins) {
    return array_diff($plugins, array('wpembed'));
}

function disable_embeds_rewrites($rules) {
    foreach($rules as $rule => $rewrite) {
        if(false !== strpos($rewrite, 'embed=true')) {
            unset($rules[$rule]);
        }
    }
    return $rules;
}

Or you could also use the wp_dequeue_script function. Keep in mind just use one option. Its safe to use above one. This may break your theme….
function my_deregister_scripts(){
 wp_dequeue_script( 'wp-embed' );
}
add_action( 'wp_footer', 'my_deregister_scripts' );

12. Disable RSS Feed

function itsme_disable_feed() {
 wp_die( __( 'No feed available, please visit the <a href="'. esc_url( home_url( '/' ) ) .'">homepage</a>!' ) );
}

add_action('do_feed', 'itsme_disable_feed', 1);
add_action('do_feed_rdf', 'itsme_disable_feed', 1);
add_action('do_feed_rss', 'itsme_disable_feed', 1);
add_action('do_feed_rss2', 'itsme_disable_feed', 1);
add_action('do_feed_atom', 'itsme_disable_feed', 1);
add_action('do_feed_rss2_comments', 'itsme_disable_feed', 1);
add_action('do_feed_atom_comments', 'itsme_disable_feed', 1);

WordPress also generates links to the RSS feeds within your webpage’s header. You can go one step further and remove these links from within your pages HTML code.
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );




Add all the codes in functions.php file of your Child Theme except the first one which you have to add in the wp-config.php file. 
