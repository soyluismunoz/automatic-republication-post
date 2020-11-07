<?php 
/*
Plugin Name: Automatic Republication Post
Description: Sencillo plugin para republicacion automatica de post
Version:     1.0
Author:      Luis Munoz
Author URI:  https://github.com/soyluismunoz
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Activación del Plugin

register_activation_hook( __FILE__, 'arp_plugin_activation' );

add_action('my_daily_event', 'getAndUpdatePost');

// The action will trigger when someone visits your WordPress site
function arp_plugin_activation() {
    if ( !wp_next_scheduled( 'my_daily_event' ) ) {
        wp_schedule_event( current_time( 'timestamp' ), 'daily', 'my_daily_event');
    }
}

add_action('wp', 'arp_plugin_activation');

function getAndUpdatePost(){
    
    $currentDate = date("Y-m-d H:i:s");

    $cat = [5, 33];

    $postHoroscopo = getPostByCategory($cat[0]);
    $postArticle = getPostByCategory($cat[1]);


    $is_future_post = false;

    $queue              = new Rop_Admin();
    $services_model     = new Rop_Services_Model();

    $accounts_data = $services_model->get_active_accounts();

    if ($postHoroscopo) {
    	foreach ($postHoroscopo as $p) {
            $post = get_post( $p->ID );
            $post->post_date = $currentDate;
            $post->post_modified = $currentDate;
            wp_update_post( $post );
	    }
    }

    if (postArticle) {
    	foreach ($postArticle as $p) {
	       $post = get_post( $p->ID );
            $post->post_date = $currentDate;
            $post->post_modified = $currentDate;
            wp_update_post( $post );
	    }
    }
}

function getPostByCategory($cat){
    global $wpdb;

    $date = date("m-d");
    $lastYear = date("Y") - 1;

    $postTable = $wpdb->prefix . 'posts';
    $termRelationships = $wpdb->prefix . 'term_relationships';
    $termTaxonomy = $wpdb->prefix . 'term_taxonomy';

    $posts = "SELECT `ID`, `post_date`, `post_date_gmt`, `post_title`, `post_status`, `post_modified`, `post_modified_gmt`, `post_type` FROM $postTable LEFT JOIN $termRelationships ON $postTable.ID = $termRelationships.object_id LEFT JOIN $termTaxonomy ON $termRelationships.term_taxonomy_id = $termTaxonomy.term_taxonomy_id WHERE $postTable.post_status = 'publish' AND `post_type` = 'post' AND `post_date` LIKE '%$lastYear-$date%' AND $termTaxonomy.taxonomy = 'category' AND $termTaxonomy.term_id = $cat ORDER BY post_date DESC ";

    $posts = $wpdb->get_results($posts);

    return $posts;
}


// Desactivación del plugin
register_deactivation_hook( __FILE__,"remove_schedule");
function remove_schedule() {
    wp_clear_scheduled_hook("my_daily_event");
}