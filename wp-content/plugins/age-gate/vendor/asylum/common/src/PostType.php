<?php

namespace Asylum\Common;

class PostType
{
    /**
     * Default Post type data
     *
     * @var array
     */
    private $data = [
        'labels'                => [],
        'supports'              => ['title', 'editor'],
        'taxonomies'            => [],
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'delete_with_user'      => false,
        'rewrite'               => false,
        'show_in_rest'          => true,
    ];

    /**
     * Singular post type name
     *
     * @var string
     */
    private $singular;

    /**
     * Plural post type name
     *
     * @var string
     */
    private $plural;

    /**
     * post type slug
     *
     * @var string
     */
    private $slug;

    /**
     * Hold if setLabels has been called
     *
     * @var boolean
     */
    private $labelled = false;

    public function __construct(string $singular, string $plural = '')
    {
        $this->singular = $singular;
        $this->plural = ($plural ?: $singular . 's');

        // Default label / description
        $this->data['label'] = $this->singular;
		$this->data['description'] = sprintf('Post Type for %s', $this->plural);
    }

    /**
     * Set internal slug for post type
     *
     * @param string $slug
     * @return self
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;
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
     * Set the supported UI elements
     *
     * @param array $options
     * @return self
     */
    public function setSupports(array $options)
    {
        $this->data['supports'] = $options;
        return $this;
    }

    /**
     * Set taxonomies for post type
     *
     * @param array $taxonomies
     * @return self
     */
    public function setTaxonomies(array $taxonomies = [])
    {
        $this->data['taxonomies'] = $taxonomies;
        return $this;
    }

    /**
     * Set the heirarchical status of the post type
     *
     * @param boolean $hierarchical
     * @return self
     */
    public function setHierarchical(bool $hierarchical)
    {
        $this->data['hierarchical'] = $hierarchical;
        return $this;
    }

    /**
     * Set public status
     *
     * @param boolean $public
     * @return self
     */
    public function setPublic(bool $public)
    {
        $this->data['public'] = $public;
        return $this;
    }

    /**
     * Show the ui in admin
     *
     * @param boolean $showUi
     * @return self
     */
    public function setShowUi(bool $showUi)
    {
        $this->data['show_ui'] = $showUi;
        return $this;
    }

    /**
     * Set menu visibility/location
     *
     * @param bool|string $showInMenu Bool for no parent, string to nest under another menu item
     * @return self
     */
    public function setShowInMenu($showInMenu)
    {
        $this->data['show_in_menu'] = $showInMenu;
        return $this;
    }

    /**
     * Set the menu Icon
     *
     * @param string $icon
     * @return self
     */
    public function setIcon($icon)
    {
        if (strpos($icon, '<svg') === 0) {
            $icon = 'data:image/svg+xml;base64,' . base64_encode($icon);
        }

        $this->data['menu_icon'] = $icon;
        return $this;
    }

    /**
     * Set the admin menu position
     *
     * @param integer $position
     * @return self
     */
    public function setMenuPosition(int $position)
    {
        $this->data['menu_position'] = $position;
        return $this;
    }

    /**
     * Show post type in admin menu
     *
     * @param boolean $show
     * @return self
     */
    public function setShowAdminBar(bool $show)
    {
        $this->data['show_in_admin_bar'] = $show;
        return $this;
    }

    /**
     * Display post type in navigation menus
     *
     * @param boolean $show
     * @return self
     */
    public function setShowInNavMenus(bool $show)
    {
        $this->data['show_in_nav_menus'] = $show;
        return $this;
    }

    /**
     * Set id pst type should appear in WP exporter
     *
     * @param boolean $exportable
     * @return self
     */
    public function setCanExport(bool $exportable)
    {
        $this->data['can_export'] = $exportable;
        return $this;
    }

    /**
     * Set if post type has an archive
     *
     * @param boolean $archive
     * @return self
     */
    public function setHasArchive(bool $archive)
    {
        $this->data['has_archive'] = $archive;
        return $this;
    }

    /**
     * Set post types search status
     *
     * @param boolean $excludeSearch
     * @return self
     */
    public function setExcludeFromSearch(bool $excludeSearch)
    {
        $this->data['exclude_from_search'] = $excludeSearch;
        return $this;
    }

    /**
     * Should post type be publicly queryable
     *
     * @param boolean $queryable
     * @return self
     */
    public function setPubliclyQueryable(bool $queryable)
    {
        $this->data['publicly_queryable'] = $queryable;
        return $this;
    }

    /**
     * Set capability type
     *
     * @param string $capability
     * @return self
     */
    public function setCapabilityType(string $capability)
    {
        $this->data['capability_type'] = $capability;
        return $this;
    }

    /**
     * Set custom caps
     *
     * @param array $capabilities
     * @param boolean $mapMeta
     * @return self
     */
    public function setCapabilities(array $capabilities, bool $mapMeta = true)
    {
        $this->data['capabilities'] = $capabilities;
        $this->data['map_meta_cap'] = $mapMeta;
        return $this;
    }

    /**
     * Set CPT rewrite
     *
     * @param string $slug
     * @param boolean $front
     * @param array $args
     * @return self
     */
    public function setRewrite(string $slug = '', bool $front = false, array $args = [])
    {
        $this->data['rewrite'] = array_merge(
            [
                'slug' => $slug ?: $this->slug,
                'with_front' => $front
            ],
            $args
        );

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

        $defaults = [
            'name'                  => ucfirst($this->plural),
            'singular_name'         => ucfirst($this->singular),
            'menu_name'             => ucfirst($this->plural),
            'name_admin_bar'        => ucfirst($this->singular),
            'add_new'               => "Add New",
            'add_new_item'          => "Add New " . ucfirst($this->singular),
            'new_item'              => "New " . ucfirst($this->singular),
            'edit_item'             => "Edit " . ucfirst($this->singular),
            'view_item'             => "View " . ucfirst($this->singular),
            'all_items'             => "All " . ucfirst($this->plural),
            'search_items'          => "Search " . ucfirst($this->plural),
            'parent_item_colon'     => "Parent " . ucfirst($this->plural) . ":",
            'not_found'             => "No " . $this->plural . " found.",
            'not_found_in_trash'    => "No " . $this->plural . " found in Trash.",
            'featured_image'        => ucfirst($this->singular) . " Cover Image",
            'set_featured_image'    => "Set cover image",
            'remove_featured_image' => "Remove cover image",
            'use_featured_image'    => "Use as cover image",
            'archives'              => ucfirst($this->singular) . " archives",
            'insert_into_item'      => "Insert into " . $this->singular,
            'uploaded_to_this_item' => "Uploaded to this " . $this->singular,
            'filter_items_list'     => "Filter " . $this->singular . " list",
            'items_list_navigation' => ucfirst($this->plural) . " list navigation",
            'items_list'            => ucfirst($this->plural) . " list",
        ];

        $this->labelled = true;
        $this->data['labels'] = wp_parse_args($labels, $defaults);

        return $this;
    }

    /**
     * Register the post type
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

            register_post_type($slug, $this->data);

            do_action('asylum/post_type/registered/' . $slug, $this->data);
        }, $priority);
    }
}