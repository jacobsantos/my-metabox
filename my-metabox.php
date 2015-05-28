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

interface MyMetaBoxInterface
{
	/**
	 * Register my metabox
	 *
	 * @return void
	 */
	public function register();
}

interface MyMetaBoxFormDisplayInterface
{
	/**
	 * Retrieve name of textarea.
	 * 
	 * @return string name field of textarea.
	 */
	public function metabox_form_name();
	/**
	 * Get the HTML for my Metabox
	 * @param  WP_Post $post The post that is being used
	 * @return void
	 */
	public function metabox_display($post);
}

interface MyMetaBoxVideoDisplayInterface
{
	/**
	 * Output the embed code at the top of your post if it has a code.
	 * @param  string   $content  The contents of your post
	 * @return string   $content  The updated contents of your post
	 */
	public function video_display($content);
}

interface MyMetaBoxSaveVerificationInterface
{
	/**
	 * Verify metabox should be saved.
	 * 
	 * @param type $post
	 * @return bool
	 */
	public function verify($post);
}

interface MyMetaBoxSaveInterface
{
	/**
	 * Update metabox video link value for the post.
	 * 
	 * @param int $post_id
	 * @param string $metabox_title
	 * @param string $value
	 * @return void
	 */
	public function update_metabox($post_id, $metabox_title, $value);
	
	/**
	 * Remove metabox video link for the post.
	 * 
	 * @param int $post_id
	 * @param string $metabox_title
	 * @return void
	 */
	public function delete_metabox($post_id, $metabox_title);
}

class MyMetaBox implements 
		MyMetaBoxInterface,
		MyMetaBoxFormDisplayInterface,
		MyMetaBoxVideoDisplayInterface,
		MyMetaBoxSaveVerificationInterface,
		MyMetaBoxSaveInterface {
	public function register()
	{
		add_meta_box('my_mb-video', 'Video Embed Code', 'my_mb_html', 'post', 'normal');
	}
	
	public function metabox_form_name()
	{
		return 'my_mb_embed_code';
	}
	
	public function metabox_display($post)
	{
		$embed = get_post_meta($post->ID, 'my_mb_embed_code', true);
		ob_start();
		require_once(dirname(__FILE__) . '/views/video-mb.php');
		$html = ob_get_clean();
		echo $html;
	}
	
	public function video_display($content)
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
	
	public function verify($post)
	{
		if ($post->post_type != 'post') {
			return false;
		}

		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
				|| (defined('DOING_AJAX') && DOING_AJAX)
				|| (defined('DOING_CRON') && DOING_CRON)) {
			return false;
		}
		
		return true;
	}
	
	public function update_metabox($post_id, $metabox_title, $value)
	{
		update_post_meta($post_id, $metabox_title, $value);
	}
	
	public function delete_metabox($post_id, $metabox_title)
	{
		delete_post_meta($post_id, $metabox_title);
	}
}

function my_metabox_instance(MyMetaBoxInterface $instance = null)
{
	static $_instance = null;
	
	if (is_null($_instance) && is_null($instance)) {
		$_instance = new MyMetaBox;
	}
	
	if ( is_object($instance) ) {
		$_instance = $instance;
	}
	
	return $_instance;
}

function my_metabox_display_instance(MyMetaBoxFormDisplayInterface $instance = null)
{
	static $_instance = null;
	
	if (is_null($_instance) && is_null($instance)) {
		$_instance = new MyMetaBox;
	}
	
	if ( is_object($instance) ) {
		$_instance = $instance;
	}
	
	return $_instance;
}

function my_metabox_video_display_instance(MyMetaBoxVideoDisplayInterface $instance = null)
{
	static $_instance = null;
	
	if (is_null($_instance) && is_null($instance)) {
		$_instance = new MyMetaBox;
	}
	
	if ( is_object($instance) ) {
		$_instance = $instance;
	}
	
	return $_instance;
}

function my_metabox_verify_instance(MyMetaBoxSaveVerificationInterface $instance = null)
{
	static $_instance = null;
	
	if (is_null($_instance) && is_null($instance)) {
		$_instance = new MyMetaBox;
	}
	
	if ( is_object($instance) ) {
		$_instance = $instance;
	}
	
	return $_instance;
}

function my_metabox_save_instance(MyMetaBoxSaveInterface $instance = null)
{
	static $_instance = null;
	
	if (is_null($_instance) && is_null($instance)) {
		$_instance = new MyMetaBox;
	}
	
	if ( is_object($instance) ) {
		$_instance = $instance;
	}
	
	return $_instance;
}

/**
 * Register my metabox
 *
 * @return void
 */
function my_mb_register()
{
	my_metabox_instance()->register();
}
add_action('add_meta_boxes', 'my_mb_register');

/**
 * Get the HTML for my Metabox
 * @param  WP_Post $post The post that is being used
 * @return void
 */
function my_mb_html($post)
{
	my_metabox_display_instance()->metabox_display($post);
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
	if ( ! my_metabox_verify_instance()->verify($post) ) {
		return;
	}

    if (isset($_POST[my_metabox_display_instance()->metabox_form_name()])) {
		$value = $_POST[my_metabox_display_instance()->metabox_form_name()];
		my_metabox_save_instance()->update_metabox($post_id, 'my_mb_embed_code', $value);
    } else {
		my_metabox_save_instance()->delete_metabox($post_id, 'my_mb_embed_code');
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
    return my_metabox_video_display_instance()->video_display($content);
}
add_filter('the_content', 'my_mb_show_video');
