<?php
/*
Plugin Name: My MetaBox
Description: Add a custom metabox to your Posts page
Version:     0.1
Author:      Jonathan Wondrusch
Author URI:  https://jonathanwondrusch.com
Text Domain: my-mb
Domain Path: /lang
 */

/**
 * Register my metabox
 *
 * @return void
 */

function my_mb_register()
{
    add_meta_box('my_mb-video', 'Video Embed Code', 'my_mb_html', 'post', 'normal');
}
add_action('add_meta_boxes', 'my_mb_register');

/**
 * Get the HTML for my Metabox
 * @param  WP_Post $post The post that is being used
 * @return void
 */
function my_mb_html($post)
{
    $embed = get_post_meta($post->ID, 'my_mb_embed_code', true);

    ob_start();
        require_once(dirname(__FILE__) . '/views/video-mb.php');
    
    $html = ob_get_clean();

    echo $html;
}

/**
 * Save the fields for your metabox
 * @param  int      $post_id ID of the post that we're saving info for
 * @param  WP_Post  $post The post that is being saved
 * @param  bool     $update Whether the post is being updated or created
 * @return void
 */
function my_mb_save($post_id, $post, $update)
{

    if ($post->post_type != 'post') {
        return;
    }

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || (defined('DOING_CRON') && DOING_CRON)) {
        return;
    }

    if (isset($_POST['my_mb_embed_code'])) {
        update_post_meta($post_id, 'my_mb_embed_code', $_POST['my_mb_embed_code']);
    } else {
        delete_post_meta($post_id, 'my_mb_embed_code');
    }
}
add_action('save_post', 'my_mb_save', 10, 3);

/**
 * Output the embed code at the top of your post if it has a code.
 * @param  string   $content  The contents of your post
 * @return string   $content  The updated contents of your post
 */
function my_mb_show_video($content)
{
    global $post;

    $embed = get_post_meta($post->ID, 'my_mb_embed_code', true);
    $html = '';

    if ($embed) {
        $html = '<div class="my-mb-embed">' . $embed . '</div>';
        $content = $html . $content;
    }

    return $content;
}
add_filter('the_content', 'my_mb_show_video');
