<?php

// Require the 
require_once(MY_MB_PATH . 'classes/MyMetabox.php');

class MyMetaboxPlugin
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_init', array($this, 'setup'));
        add_action('the_content', array($this, 'showVideo'));
    }

    public function setup()
    {
        // Instantiate the Metabox
        $view = MY_MB_PATH . '/views/video-mb.php';
        $mb = new MyMetabox('video', 'Video Embed Code', $view);

        // Set up fields we'll need
        $mb->addField('embed_code');

        // Register the metabox with WordPress for registering and saving
        $mb->initialize(array('post'));
    }

    public function showVideo($content)
    {
        global $post;

        $embed = get_post_meta($post->ID, 'embed_code', true);
        $html = '';

        if ($embed) {
            $html = '<div class="my-mb-embed">' . $embed . '</div>';
            $content = $html . $content;
        }

        return $content;
    }
}
