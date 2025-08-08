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

