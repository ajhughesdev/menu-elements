<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 8/28/2017
 * Time: 1:59 PM
 */

namespace Aesir\v1\Components;


use Aesir\v1\Exceptions\AesirException;

abstract class PostType
{
    protected $textdomain = 'aesir';
    protected $taxonomies = [];
    protected $wpPostType = null;
    protected $defaultSlug;
    protected $isRegistered = false;

    /**
     * PostType constructor. You may specify a slug on initialization or by overriding PostType::slug()
     *
     * @param null $defaultSlug
     * @param bool $delayRegistration
     *
     * @throws AesirException
     */
    public function __construct($defaultSlug = null, $delayRegistration = true)
    {
        $this->defaultSlug = $defaultSlug;

        if(!$delayRegistration) {
            $this->init();
        }
    }

    public function init() {
        if(!$this->isRegistered) {
            $this->isRegistered = true;
            $this->taxonomies = $this->registerTaxonomies();
            $this->wpPostType = register_post_type($this->slug(), $this->buildArgs());

            if($this->wpPostType instanceof \WP_Error) {
                throw new AesirException("Error Creating Post Type: " . print_r($this->wpPostType->errors, true));
            }
        } else {
            throw new AesirException("Error Creating Post Type: [".$this->slug()."] is already registered.");
        }
    }

    protected function buildArgs() {
        return [
            'labels'                => $this->buildLabels(),
            'description'           => $this->description(),
            'public'                => $this->isPublic(),
            'hierarchical'          => $this->isHierarchical(),
            'exclude_from_search'   => $this->isExcludedFromSearch(),
            'publicly_queryable'    => $this->isPubliclyQueryable(),
            'show_ui'               => $this->showUI(),
            'show_in_menu'          => $this->showInMenu(),
            'show_in_nav_menus'     => $this->showInNavMenus(),
            'show_in_admin_bar'     => $this->showInAdminBar(),
            'show_in_rest'          => $this->showInRest(),
            'rest_base'             => $this->restBase(),
            'rest_controller_class' => $this->restControllerClass(),
            'menu_position'         => $this->menuPosition(),
            'menu_icon'             => $this->menuPosition(),
            'capability_type'       => $this->capabilityType(),
            'capabilities'          => $this->capabilities(),
            'map_meta_cap'          => $this->mapMetaCap(),
            'supports'              => $this->supports(),
            'register_meta_box_cb'  => $this->registerMetaBox(),
            'taxonomies'            => $this->getTaxonomySlugs(),
            'has_archive'           => $this->hasArchive(),
            'rewrite'               => $this->rewrite(),
            'query_var'             => $this->queryVar(),
            'can_export'            => $this->canExport(),
            'delete_with_user'      => $this->deleteWithUser()
        ];
    }

    protected function buildLabels() {
        $singular = $this->singularName();
        $plural = $this->pluralName();
        $menu = $this->menuName();

        return [
            'name'                  => __($menu, $this->textdomain),
            'singluar'              => __($plural, $this->textdomain),
            'add_new'               => __("Add New", $this->textdomain),
            'add_new_item '         => __("Add New {$singular}", $this->textdomain),
            'edit_item'             => __("Edit {$singular}", $this->textdomain),
            'new_item'              => __("New {$singular}", $this->textdomain),
            'view_item'             => __("View {$singular}", $this->textdomain),
            'view_items'            => __("View {$plural}", $this->textdomain),
            'search_items'          => __("Search {$plural}", $this->textdomain),
            'not_found'             => __("No {$plural} found", $this->textdomain),
            'not_found_in_trash'    => __("No {$plural} found in Trash", $this->textdomain),
            'parent_item_colon'     => __("Parent {$singular}:", $this->textdomain),
            'all_items'             => __("All {$plural}", $this->textdomain),
            'archives'              => __("{$singular} Archives", $this->textdomain),
            'attributes'            => __("{$singular} Attributes", $this->textdomain),
            'insert_into_item'      => __("Insert into {$singular}", $this->textdomain),
            'uploaded_to_this_item' => __("Uploaded to this {$singular}", $this->textdomain),
            'featured_image'        => __("Featured Image", $this->textdomain),
            'set_featured_image'    => __("Set featured image", $this->textdomain),
            'remove_featured_image' => __("Remove featured image", $this->textdomain),
            'use_featured_image'    => __("Use as featured image", $this->textdomain),
            'menu_name'             => __($menu, $this->textdomain),
            'filter_items_list'     => __("Filter {$plural} list", $this->textdomain),
            'items_list_navigation' => __("{$plural} list navigation", $this->textdomain),
            'items_list'            => __("{$plural} list", $this->textdomain)
        ];
    }

    /**
     * Provides an array of registered taxonomy slugs.
     *
     * @return string[]
     */
    public function getTaxonomySlugs() {
        $taxonomies = [];

        if(!empty($this->taxonomies)) {
            foreach($this->taxonomies as $taxonomy) {
                $taxonomies[] = $taxonomy->slug();
            }
        }

        return $taxonomies;
    }

    /**
     * @param string $slug
     *
     * @return Taxonomy
     */
    public function getTaxonomy($slug) {
        foreach ($this->taxonomies as $taxonomy) {
            if($taxonomy->slug() == $slug) {
                return $taxonomy;
            }
        }

        return null;
    }

    /**
     * Returns the post type slug
     * @return string
     * @throws AesirException
     */
    public function slug() {
        if(is_null($this->defaultSlug)) {
            throw new AesirException('Post Type created without passing a slug to the constructor or overriding PostType::slug()');
        }

        return $this->defaultSlug;
    }

    /**
     * Returns the singular display name of the post type
     *
     * @return string
     */
    abstract public function singularName();

    /**
     * Returns the plural display name of the post type
     *
     * @return string
     */
    abstract public function pluralName();

    /**
     * Returns the name of the post type for how it should be displayed in the menu
     *
     * @return string
     */
    protected function menuName() {
        return $this->singularName();
    }

    /**
     * Returns the post type description
     *
     * @return string
     */
    abstract protected function description();

    /**
     * Whether a post type is intended for use publicly either via the admin interface
     * or by front-end users. While the default settings of $exclude_from_search,
     * $publicly_queryable, $show_ui, and $show_in_nav_menus are inherited from public,
     * each does not rely on this relationship and controls a very specific intention.
     *
     * @return bool
     */
    abstract protected function isPublic();

    /**
     * Whether the post type is hierarchical (e.g. page).
     *
     * @return bool
     */
    abstract protected function isHierarchical();

    /**
     * Whether to exclude posts with this post type from front end search results. Default is the opposite value of $public.
     *
     * @return bool
     */
    protected function isExcludedFromSearch() {
        return !$this->isPublic();
    }

    /**
     * Whether queries can be performed on the front end for the post type as part of parse_request(). Endpoints would include:
     * ?post_type={post_type_key}
     * ?{post_type_key}={single_post_slug}
     * ?{post_type_query_var}={single_post_slug}
     *
     * @return bool
     */
    protected function isPubliclyQueryable() {
        return $this->isPublic();
    }

    /**
     * Whether to generate and allow a UI for managing this post type in the admin.
     *
     * @return bool
     */
    protected function showUI() {
        return $this->isPublic();
    }

    /**
     * Where to show the post type in the admin menu. To work, $show_ui must be true.
     * If true, the post type is shown in its own top level menu. If false, no menu is shown.
     * If a string of an existing top level menu (eg. 'tools.php' or 'edit.php?post_type=page'),
     * the post type will be placed as a sub-menu of that.
     *
     * @return bool
     */
    protected function showInMenu() {
        return $this->showUI();
    }

    /**
     * Makes this post type available for selection in navigation menus.
     *
     * @return bool
     */
    protected function showInNavMenus() {
        return $this->isPublic();
    }

    /**
     * Makes this post type available via the admin bar.
     *
     * @return bool
     */
    protected function showInAdminBar() {
        return $this->showInMenu();
    }

    /**
     * Whether to add the post type route in the REST API 'wp/v2' namespace.
     *
     * @return bool
     */
    abstract protected function showInRest();

    /**
     * The base url (slug) of REST API route
     *
     * @return string
     */
    protected function restBase() {
        return $this->slug();
    }

    /**
     * REST API Controller class name
     *
     * @return string
     */
    protected function restControllerClass() {
        return "WP_REST_Posts_Controller";
    }

    /**
     * The position in the menu order the post type should appear.
     * To work, showInMenu() must return true.
     *
     * @return int
     */
    abstract protected function menuPosition();

    /**
     * The url to the icon to be used for this menu. Pass a base64-encoded SVG using a data URI,
     * which will be colored to match the color scheme -- this should begin with 'data:image/svg+xml;base64,'.
     * Pass the name of a Dashicons helper class to use a font icon, e.g. 'dashicons-chart-pie'. Pass 'none'
     * to leave div.wp-menu-image empty so an icon can be added via CSS.
     *
     * @return string
     */
    protected function menuIcon() {
        return 'dashicons-admin-page';
    }

    /**
     * The string to use to build the read, edit, and delete capabilities. May be passed as
     * an array to allow for alternative plurals when using this argument as a base to construct
     * the capabilities, e.g. array('story', 'stories').
     *
     * @return string
     */
    protected function capabilityType() {
        return 'post';
    }

    /**
     * Array of capabilities for this post type. capabilityType() is used as a base to construct
     * capabilities by default.
     *
     * @return array|null
     */
    protected function capabilities() {
        $capability_type = $this->capabilityType();

        return [
            "edit_post"		        => "edit_{$capability_type}",
            "read_post"		        => "read_{$capability_type}",
            "delete_post"		    => "delete_{$capability_type}",

                                     // Primitive capabilities used outside of map_meta_cap():

            "edit_posts"		    => "edit_{$capability_type}s",
            "edit_others_posts"	    => "edit_others_{$capability_type}s",
            "publish_posts"		    => "publish_{$capability_type}s",
            "read_private_posts"	=> "read_private_{$capability_type}s",

                                        // Primitive capabilities used within map_meta_cap():

            "read"                   => "read",
            "delete_posts"           => "delete_{$capability_type}s",
            "delete_private_posts"   => "delete_private_{$capability_type}s",
            "delete_published_posts" => "delete_published_{$capability_type}s",
            "delete_others_posts"    => "delete_others_{$capability_type}s",
            "edit_private_posts"     => "edit_private_{$capability_type}s",
            "edit_published_posts"   => "edit_published_{$capability_type}s",
            "create_posts"           => "edit_{$capability_type}s"
        ];
    }

    /**
     * Whether to use the internal default meta capability handling.
     *
     * @return bool
     */
    protected function mapMetaCap() {
        return true;
    }

    /**
     * Core feature(s) the post type supports. Serves as an alias for calling add_post_type_support() directly.
     * Core features include 'title', 'editor', 'comments', 'revisions', 'trackbacks', 'author', 'excerpt',
     * 'page-attributes', 'thumbnail', 'custom-fields', and 'post-formats'. Additionally, the 'revisions'
     * feature dictates whether the post type will store revisions, and the 'comments' feature dictates whether
     * the comments count will show on the edit screen.
     *
     * @return array
     */
    abstract protected function supports();

    /**
     * Provide a callback function that sets up the meta boxes for the edit form. Do remove_meta_box()
     * and add_meta_box() calls in the callback.
     *
     * @return callable|null
     */
    protected function registerMetaBox() {
        return null;
    }

    /**
     * An array of Taxonomy objects that will be registered for the post type.
     *
     * @return null|Taxonomy[]
     */
    abstract protected function registerTaxonomies();

    /**
     * Whether there should be post type archives, or if a string, the archive slug to use.
     * Will generate the proper rewrite rules if $rewrite is enabled.
     *
     * @return bool|string
     */
    abstract protected function hasArchive();

    /**
     * Triggers the handling of rewrites for this post type. To prevent rewrite, set to false. Defaults to true, using
     * slug(). To specify rewrite rules, an array can be passed with any of these keys:
     * 'slug': (string) Customize the permastruct slug. Defaults to slug() key.
     * 'with_front': (bool) Whether the permastruct should be prepended with WP_Rewrite::$front. Default true.
     * 'feeds': (bool) Whether the feed permastruct should be built for this post type. Default is value of hasArchive().
     * 'pages': (bool) Whether the permastruct should provide for pagination. Default true.
     * 'ep_mask': (const) Endpoint mask to assign. If not specified and permalink_epmask is set, inherits from
     *            $permalink_epmask. If not specified and permalink_epmask is not set, defaults to EP_PERMALINK.
     *
     * @return mixed
     */
    protected function rewrite() {
        return true;
    }

    /**
     * Sets the query_var key for this post type. Defaults to slug() key. If false, a post type cannot
     * be loaded at ?{query_var}={post_slug}. If specified as a string, the query ?{query_var_string}={post_slug}
     * will be valid.
     *
     * @return string
     */
    protected function queryVar() {
        return $this->slug();
    }

    /**
     * Whether to allow this post type to be exported
     * @return bool
     */
    protected function canExport() {
        return true;
    }

    /**
     * Whether to delete posts of this type when deleting a user. If true, posts of this type belonging to the user
     * will be moved to trash when then user is deleted. If false, posts of this type belonging to the user will *not*
     * be trashed or deleted. If not set (the default), posts are trashed if post_type_supports('author'). Otherwise
     * posts are not trashed or deleted.
     *
     * @return null
     */
    protected function deleteWithUser() {
        return false;
    }
}