<?php

class MyMetabox
{
    /**
     * A list of post types that the metabox will be available for
     * @var array
     */
    protected $post_types = array();

    /**
     * A list of fields that need to be saved for the metabox
     * @var array
     */
    protected $fields = array();

    /**
     * The CSS ID of the metabox
     * @var string
     */
    protected $id;

    /**
     * The visitble title of the metabox
     * @var string
     */
    protected $title;

    /**
     * A file path used for rendering a view
     * @var string
     */
    protected $view;

    /**
     * Construct our metabox
     * @param string $id    CSS ID for the Metabox
     * @param string $title Title of the Metabox
     * @param string $view  Path to the view file
     */
    public function __construct($id, $title, $view)
    {
        $this->id = $id;
        $this->title = $title;
        $this->view = $view;
    }

    /**
     * Initialize this metabox for particular post types
     * @param  array  $post_types an array of post types
     * @return self
     */
    public function initialize(array $post_types = array('post'))
    {
        $this->post_types = $post_types;

        add_action('save_post', array($this, 'save'), 10, 3);
        add_action('add_meta_boxes', array($this, 'register'));

        return $this;
    }

    /**
     * Register the metabox with WordPress so that it renders
     * @return self
     */
    public function register()
    {
        foreach ($this->post_types as $post_type) {
            add_meta_box($this->getId(), $this->getTitle(), array($this, 'render'), $post_type);
        }

        return $this;
    }

    /**
     * Getter for the title
     * @return string Title of Metabox
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Getter for the Id
     * @return string CSS Id of Metabox
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add a field for the metabox to save and render
     * @param string $name Name for the field to be saved/rendered
     * @return self
     */
    public function addField($name)
    {
        $this->fields[] = $name;
        return $this;
    }

    /**
     * Get the fields of the metabox
     * @return array An array of all of the fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Render the metabox in the admin area, including field data
     * @param  WP_Post $post The WP Post object for the Metabox
     * @return string        The HTML of the metabox view
     */
    public function render(WP_Post $post)
    {
        // First, make sure that the view is valid and readable.  If not, throw an exception.
        if (!is_readable($this->view)) {
            throw new Exception('View file is not readable.');
        }

        // We want to prepare our fields for our view
        foreach ($this->getFields() as $field) {
            ${$field} = get_post_meta($post->ID, $field, true);
        }

        ob_start();
            require_once($this->view);
        echo ob_get_clean();
    }

    /**
     * Save the fields of the Metabox
     * @param  int|string $post_id ID of the post
     * @param  WP_Post    $post    WordPress post object
     * @param  bool       $update  Is this an update?
     * @return self
     */
    public function save($post_id, $post, $update)
    {
        if ($this->isAutoSave() || !$this->isRegisteredPostType($post)) {
            return;
        }

        foreach ($this->getFields() as $field) {
            $this->saveField($post, $field);
        }

        return $this;
    }

    /**
     * Save an individual field for a post
     * @param  WP_Post $post  WordPress post object
     * @param  string  $field Name of the field being saved
     * @return self
     */
    private function saveField(WP_Post $post, $field)
    {
        if (isset($_POST[$field])) {
            update_post_meta($post->ID, $field, $_POST[$field]);
        } else {
            delete_post_meta($post->ID, $field);
        }

        return $this;
    }

    /**
     * Determine whether we need to save our field for this post type
     * @param  WP_Post $post WordPress post object
     * @return boolean       Is this a post type we need to save on?
     */
    private function isRegisteredPostType(WP_Post $post)
    {
        return in_array($post->post_type, $this->post_types);
    }

    /**
     * Determine whether it is the right time to save, so that we're not saving "empty" date
     * during autosaves and CRON
     * @return boolean Should we save?
     */
    private function isAutoSave()
    {
        return ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || (defined('DOING_CRON') && DOING_CRON));
    }
}
