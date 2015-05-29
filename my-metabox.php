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

final class MyMetaBox implements 
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

final class MyMetaBoxStorage
{
	private static $_storage = array(
		'register' => null,
		'form_display' => null,
		'video_display' => null,
		'verify' => null,
		'save' => null,
	);
	
	private static $_instance = null;
	
	private function __construct()
	{
		$instance = new MyMetaBox;
		self::$_storage['register'] = $instance;
		self::$_storage['form_display'] = $instance;
		self::$_storage['video_display'] = $instance;
		self::$_storage['verify'] = $instance;
		self::$_storage['save'] = $instance;
	}
	
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new static;
		}
		return self::$_instance;
	}
	
	public function register($instance)
	{
		switch (true) {
			case ($instance instanceof MyMetaBoxInterface):
				self::$_storage['register'] = $instance;
				break;
			case ($instance instanceof MyMetaBoxFormDisplayInterface):
				self::$_storage['form_display'] = $instance;
				break;
			case ($instance instanceof MyMetaBoxVideoDisplayInterface):
				self::$_storage['video_display'] = $instance;
				break;
			case ($instance instanceof MyMetaBoxSaveVerificationInterface):
				self::$_storage['verify'] = $instance;
				break;
			case ($instance instanceof MyMetaBoxSaveInterface):
				self::$_storage['save'] = $instance;
				break;
			default:
				throw new Exception("instance object type is not supported.");
		}
	}
	
	public function metabox()
	{
		return self::$_storage['register'];
	}
	
	public function display($type = 'form')
	{
		$name = $type.'_display';
		if ( array_key_exists($name, self::$_storage) ) {
			return self::$_storage[$name];
		}
		return null;
	}
	
	public function verify()
	{
		return self::$_storage['verify'];
	}
	
	public function save()
	{
		return self::$_storage['save'];
	}
}

/**
 * Register my metabox
 *
 * @return void
 */
function my_mb_register()
{
	MyMetaBoxStorage::instance()->metabox()->register();
}
add_action('add_meta_boxes', 'my_mb_register');

/**
 * Get the HTML for my Metabox
 * @param  WP_Post $post The post that is being used
 * @return void
 */
function my_mb_html($post)
{
	MyMetaBoxStorage::instance()->display('form')->metabox_display($post);
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
	if ( ! MyMetaBoxStorage::instance()->verify()->verify($post) ) {
		return;
	}

	$field_name = MyMetaBoxStorage::instance()->display('form')->metabox_form_name();
    if (isset($_POST[$field_name])) {
		$value = $_POST[$field_name];
		MyMetaBoxStorage::instance()->save()->update_metabox($post_id, 'my_mb_embed_code', $value);
    } else {
		MyMetaBoxStorage::instance()->save()->delete_metabox($post_id, 'my_mb_embed_code');
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
    return MyMetaBoxStorage::instance()->display('video')->video_display($content);
}
add_filter('the_content', 'my_mb_show_video');
