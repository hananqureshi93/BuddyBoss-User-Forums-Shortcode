<?php
/**
 * Plugin Name: BuddyBoss User Forums Shortcode
 * Description: Displays a list of forums the current user can view (via bbPress/BuddyBoss). Shortcode: [bb_user_forums].
 * Version: 1.0.0
 * Author: Hanan Qureshi
 * License: MIT
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function bbufs_is_bbpress_active() {
    return function_exists('bbp_is_forum') || class_exists('BuddyBoss_Theme');
}

function bbufs_user_can_view_forum( $forum_id ) {
    if ( function_exists('bbp_user_can_view_forum') ) {
        return bbp_user_can_view_forum( $forum_id );
    }
    // Fallback: check published & public
    return get_post_status( $forum_id ) === 'publish';
}

function bbufs_get_user_forums( $user_id ) {
    $args = array(
        'post_type'      => 'forum',
        'post_status'    => 'publish',
        'posts_per_page' => 200,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'fields'         => 'ids'
    );
    $forums = get_posts( $args );
    $visible = array();
    foreach ( $forums as $fid ) {
        if ( bbufs_user_can_view_forum( $fid ) ) {
            $visible[] = $fid;
        }
    }
    return $visible;
}

function bbufs_shortcode( $atts ) {
    if ( ! is_user_logged_in() ) {
        return '<div class="bbufs-notice">Please log in to view your forums.</div>';
    }
    if ( ! bbufs_is_bbpress_active() ) {
        return '<div class="bbufs-notice">bbPress / BuddyBoss Forums not detected.</div>';
    }
    $user_id = get_current_user_id();
    $forums = bbufs_get_user_forums( $user_id );

    if ( empty( $forums ) ) {
        return '<div class="bbufs-empty">No forums available.</div>';
    }

    ob_start();
    echo '<ul class="bbufs-list">';
    foreach ( $forums as $fid ) {
        $title = get_the_title( $fid );
        $link  = get_permalink( $fid );
        printf( '<li><a href="%s">%s</a></li>', esc_url( $link ), esc_html( $title ) );
    }
    echo '</ul>';
    return ob_get_clean();
}
add_shortcode( 'bb_user_forums', 'bbufs_shortcode' );

// Optional: enqueue minimal CSS
function bbufs_enqueue_styles() {
    wp_register_style( 'bbufs-css', plugins_url( 'assets/style.css', __FILE__ ), array(), '1.0.0' );
    wp_enqueue_style( 'bbufs-css' );
}
add_action( 'wp_enqueue_scripts', 'bbufs_enqueue_styles' );

