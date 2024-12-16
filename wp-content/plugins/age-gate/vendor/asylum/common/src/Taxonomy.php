<?php

namespace Asylum\Common;

class Taxonomy
{

    /**
     * Default taxonomy data
     *
     * @var array
     */
    private $data = [
        'labels'                => [],
        'hierarchical'          => true,
		'public'                => true,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'show_in_nav_menus'     => false,
		'show_tagcloud'         => false,
    ];

    /**
     * Post types to register against
     *
     * @var array
     */
    private $postTypes = ['post'];

    /**
     * Singular taxonomy name
     *
     * @var string
     */
    private $singular;

    /**
     * Plural taxonomy name
     *
     * @var string
     */
    private $plural;

    /**
     * Hold if setLabels has been called
     *
     * @var boolean
     */
    private $labelled = false;

    /**
     * post type slug
     *
     * @var string
     */
    private $slug;

    public function __construct(string $singular, string $plural = '')
    {
        $this->singular = $singular;
        $this->plural = ($plural ?: $singular . 's');
    }

    /**
     * Set if taxonomy should be hierarchical
     *
     * @param boolean $value
     * @return self
     */
    public function setHierarchical(bool $value) {
        $this->data['hierarchical'] = $value;
        return $this;

    }

    /**
     * Set publicity of taxonomy
     *
     * @param boolean $value
     * @return self
     */
    public function setPublic(bool $value) {
        $this->data['public'] = $value;
        return $this;

    }

    /**
     * Show taxonomy UI
     *
     * @param boolean $value
     * @return self
     */
    public function setShowUi(bool $value) {
        $this->data['show_ui'] = $value;
        return $this;

    }

    /**
     * Show admin column for new taxonomy
     *
     * @param boolean $value
     * @return self
     */
    public function setShowAdminColumn(bool $value) {
        $this->data['show_admin_column'] = $value;
        return $this;

    }

    /**
     * Show taxonomy in nav menus
     *
     * @param boolean $value
     * @return self
     */
    public function setShowInNavMenus(bool $value) {
        $this->data['show_in_nav_menus'] = $value;
        return $this;

    }

    /**
     * Set tag cloud visibility
     *
     * @param boolean $value
     * @return self
     */
    public function setShowTagcloud(bool $value) {
        $this->data['show_tagcloud'] = $value;
        return $this;
    }

    /**
     * Set the callback for the metabox
     *
     * @param string|boolean|array $value Callback method or false for no metabox
     * @return self
     */
    public function setMetaBox($value) {
        $this->data['meta_box_cb'] = $value;
        return $this;
    }

    /**
     * Set default term
     *
     * @param string $name
     * @param string $slug
     * @param string $description
     * @return self
     */
    public function setDefaultTerm(string $name, string $slug = '', string $description = '')
    {
        $this->data['default_term'] = [ //(string|array) Default term to be used for the taxonomy.
            'name' => $name, //(string) Name of default term.
            'slug' => $slug ?: sanitize_title($name), //(string) Slug for default term.
            'description' => $description, //(string) Description for default term.
        ];
        return $this;
    }

    /**
     * Set an arbitary option
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setOption(string $key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }


    /**
     * Generate labels
     *
     * @param array $labels
     * @return self
     */
    public function setLabels(array $labels = [])
    {
        $this->data['labels'] = wp_parse_args(
            $labels,
            [
                'name'                       => $this->plural,
                'singular_name'              => $this->singular,
                'menu_name'                  => $this->singular,
                'all_items'                  => __( 'All Items', 'text_domain' ),
                'parent_item'                => __( 'Parent Item', 'text_domain' ),
                'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
                'new_item_name'              => __( 'New Item Name', 'text_domain' ),
                'add_new_item'               => __( 'Add New Item', 'text_domain' ),
                'edit_item'                  => __( 'Edit Item', 'text_domain' ),
                'update_item'                => __( 'Update Item', 'text_domain' ),
                'view_item'                  => __( 'View Item', 'text_domain' ),
                'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
                'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
                'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
                'popular_items'              => __( 'Popular Items', 'text_domain' ),
                'search_items'               => __( 'Search Items', 'text_domain' ),
                'not_found'                  => __( 'Not Found', 'text_domain' ),
                'no_terms'                   => __( 'No items', 'text_domain' ),
                'items_list'                 => __( 'Items list', 'text_domain' ),
                'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
            ]
        );

        $this->labelled = true;

        return $this;
    }

    /**
     * Set the post types for the taxonomy
     *
     * @param array $postTypes
     * @return self
     */
    public function setPostTypes(array $postTypes)
    {
        $this->postTypes = $postTypes;
        return $this;
    }

    /**
     * Set the slug
     *
     * @param string $slug
     * @return self
     */
    public function setSlug($slug)
    {
        $this->slug = sanitize_title($slug);
        return $this;
    }

    /**
     * Register taxonomy
     *
     * @return void
     */
    public function register($priority = 10)
    {
        add_action('init', function() {

            // force labels if not set
            if (!$this->labelled) {
                $this->setLabels();
            }

            $slug = $this->slug ?: sanitize_title($this->singular);

            register_taxonomy($slug, $this->postTypes, $this->data);

            do_action('asylum/taxonomy/registered/' . $slug, $this->data, $this->postTypes);
        }, $priority);
    }
}