<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 8/28/2017
 * Time: 2:56 PM
 */

namespace Aesir\v1\Components;


use Aesir\v1\Traits\Filterable;

abstract class Taxonomy
{
    use Filterable;

    protected $textdomain = 'aesir';
    protected $postType;

    public function __construct(PostType $postType)
    {
        $this->postType = $postType;
        register_taxonomy($this->getSlug(), $postType->slug(), $this->buildArgs());

        $this->addAction('restrict_manage_posts', 'create_admin_filter', 10, 2);

        $this->addAction('pre_get_posts', 'disable_archives', 10, 1);
    }

    /**
     * Returns the slug of the post type
     *
     * @return string
     */
    private function getSlug() {
        return static::slug();
    }

    /**
     * Returns the slug of the post type
     *
     * @return string
     */
    abstract public static function slug();


    /**
     * Returns the singular readable name of the post type
     *
     * @return string
     */
    abstract public function singular();

    /**
     * Returns the plural readable name of the post type
     *
     * @return string
     */
    abstract public function plural();

    /**
     * Include a description of the taxonomy
     *
     * @return string
     */
    abstract protected function description();

    /**
     * Whether a taxonomy is intended for use publicly either via the admin interface or by front-end users.
     *
     * @return bool
     */
    abstract protected function isPublic();

    /**
     * Whether the taxonomy is publicly queryable.
     *
     * @return bool
     */
    protected function publiclyQueryable() {
        return $this->isPublic();
    }

    /**
     * Whether to generate a default UI for managing this taxonomy.
     *
     * @return bool
     */
    protected function showUI() {
        return $this->isPublic();
    }

    /**
     * Controls if an archive view is generated on the front end.
     * @return bool
     */
    protected function hasArchives() {
        return$this->isPublic();
    }

    /**
     * Where to show the taxonomy in the admin menu. showUI() must be true.
     *
     * @return bool
     */
    protected function showInMenu() {
        return $this->showUI();
    }

    /**
     * True makes this taxonomy available for selection in navigation menus.
     *
     * @return bool
     */
    protected function showInNavMenus() {
        return $this->isPublic();
    }

    /**
     * Whether to include the taxonomy in the REST API.
     *
     * @return bool
     */
    abstract protected function showInRest();

    /**
     * Used to change the base url of REST API route.
     *
     * @return string
     */
    protected function restBase() {
        return $this->getSlug();
    }

    /**
     * REST API Controller class name.
     *
     * @return string
     */
    protected function restControllerClass() {
        return 'WP_REST_Terms_Controller';
    }

    /**
     * Whether to allow the Tag Cloud widget to use this taxonomy.
     *
     * @return bool
     */
    abstract protected function showTagcloud();

    /**
     * Whether to show the taxonomy in the quick/bulk edit panel.
     *
     * @return bool
     */
    protected function showInQuickEdit() {
        return $this->showUI();
    }

    /**
     * Provide a callback function name for the meta box display
     *
     * @return callable|null
     */
    protected function registerMetaBox() {
        return null;
    }

    /**
     * Is this taxonomy hierarchical (have descendants) like categories or not hierarchical like tags.
     *
     * @return bool
     */
    abstract protected function isHierarchical();

    /**
     * Whether to allow automatic creation of taxonomy columns on associated post-types table.
     *
     * @return bool
     */
    abstract protected function showAdminColumn();

    /**
     * If true then a filter will be created on the backend post listing, allowing an admin to filter out posts not
     * in a particular taxonomy term.
     *
     * Defaults to false
     *
     * @return bool
     */
    protected function showAdminFilter() {
        return false;
    }

    /**
     * A function name that will be called when the count of an associated $object_type, such as post, is updated.
     * Works much like a hook.
     *
     * @return string|callable|null
     */
    protected function updateCountCallback() {
        return null;
    }

    /**
     * Set to false to prevent automatic URL rewriting a.k.a. "pretty permalinks". Pass an array to override default
     * URL settings for permalinks as outlined below:
     *
     * 'slug' - Used as pretty permalink text (i.e. /tag/) - defaults to $taxonomy (taxonomy's name slug)
     * 'with_front' - allowing permalinks to be prepended with front base - defaults to true
     * 'hierarchical' - true or false allow hierarchical urls (implemented in Version 3.1) - defaults to false
     * 'ep_mask' - (Required for pretty permalinks) Assign an endpoint mask for this taxonomy - defaults to EP_NONE.
     *              If you do not specify the EP_MASK, pretty permalinks will not work. For more info see this Make
     *              WordPress Plugins summary of endpoints.
     *
     * Note: You may need to flush the rewrite rules after changing this. You can do it manually by going to the
     * Permalink Settings page and re-saving the rules -- you don't need to change them -- or by
     * calling $wp_rewrite->flush_rules(). You should only flush the rules once after the taxonomy has been created,
     * not every time the plugin/theme loads.
     *
     * @return bool|array
     */
    protected function rewrite() {
        return true;
    }

    /**
     * False to disable the query_var, set as string to use custom query_var instead of default which is slug(),
     * the taxonomy's "name". True is not seen as a valid entry and will result in 404 issues.
     *
     * @return bool|string
     */
    protected function queryVar() {
        return $this->getSlug();
    }

    /**
     * An array of capabilities for the taxonomy
     *
     * @return array
     */
    protected function capabilities() {
        return [
            'manage_terms'  => 'manage_categories',
            'edit_terms'    => 'manage_categories',
            'delete_terms'  => 'manage_categories',
            'assign_terms'  => 'edit_posts'
        ];
    }

    /**
     * Whether this taxonomy should remember the order in which terms are added to objects.
     *
     * @return bool
     */
    abstract protected function sort();

    protected function buildArgs() {
        return [
            'labels'                => $this->buildLables(),
            'public'                => $this->isPublic(),
            'publicly_queryable'    => $this->publiclyQueryable(),
            'show_ui'               => $this->showUI(),
            'show_in_menu'          => $this->showInMenu(),
            'show_in_nav_menus'     => $this->showInNavMenus(),
            'show_in_rest'          => $this->showInRest(),
            'rest_base'             => $this->restBase(),
            'rest_controller_class' => $this->restControllerClass(),
            'show_tagcloud'         => $this->showTagcloud(),
            'show_in_quick_edit'    => $this->showInQuickEdit(),
            'meta_box_cb'           => $this->registerMetaBox(),
            'show_admin_column'     => $this->showAdminColumn(),
            'description'           => $this->description(),
            'hierarchical'          => $this->isHierarchical(),
            'update_count_callback' => $this->updateCountCallback(),
            'query_var'             => $this->queryVar(),
            'rewrite'               => $this->rewrite(),
            'capabilities'          => $this->capabilities(),
            'sort'                  => $this->sort()
        ];
    }

    protected function buildLables() {
        $singular = $this->singular();
        $plural = $this->plural();
        $lcPlural = strtolower($plural);

        return [
            'name'                       => __( $plural, $this->textdomain ),
            'singular_name'              => __( $singular, $this->textdomain ),
            'menu_name'                  => __( $plural, $this->textdomain ),
            'all_items'                  => __( "All {$plural}", $this->textdomain ),
            'edit_item'                  => __( "Edit {$singular}", $this->textdomain ),
            'view_item'                  => __( "Update {$singular}", $this->textdomain),
            'update_item'                => __( "Update {$singular}", $this->textdomain ),
            'add_new_item'               => __( "Add New {$singular}", $this->textdomain ),
            'new_item_name'              => __( "New {$singular} Name", $this->textdomain ),
            'parent_item'                => __( "Parent {$singular}", $this->textdomain),
            'parent_item_colon'          => __( "Parent {$singular}:"),
            'search_items'               => __( "Search {$plural}", $this->textdomain ),
            'popular_items'              => __( "Popular {$plural}", $this->textdomain ),
            'separate_items_with_commas' => __( "Separate {$lcPlural} with commas", $this->textdomain ),
            'add_or_remove_items'        => __( "Add or remove {$lcPlural}", $this->textdomain ),
            'choose_from_most_used'      => __( "Choose from the most used {$lcPlural}", $this->textdomain ),
            'not_found'                  => __( "No {$lcPlural} found.", $this->textdomain ),
        ];
    }

    private function __create_admin_filter($postType, $which) {
        if($this->postType->slug() == $postType && $this->showAdminFilter()) {
            $slug = $this->getSlug();

            // Retrieve taxonomy data
            $taxObj = get_taxonomy($slug);
            $taxName = $taxObj->labels->name;

            // Retrieve taxonomy terms
            $terms = get_terms($slug);

            // Display filter HTML
            echo "<select name='{$slug}' id='{$slug}' class='postform'>";
            echo '<option value="">' . sprintf( esc_html__( 'Show All %s', 'text_domain' ), $taxName ) . '</option>';
            foreach ( $terms as $term ) {
                printf(
                    '<option value="%1$s" %2$s>%3$s (%4$s)</option>',
                    $term->slug,
                    ( ( isset( $_GET[$slug] ) && ( $_GET[$slug] == $term->slug ) ) ? ' selected="selected"' : '' ),
                    $term->name,
                    $term->count
                );
            }
            echo '</select>';
        }
    }

    /**
     * This hook disables the taxonomy archive pages by causing them to 404 if the hasArchive method returns true
     * @param \WP_Query $query
     */
    protected function __disable_archives(\WP_Query $query) {
        if (!$this->hasArchives() && !is_admin() && is_tax($this->getSlug())){
            $query->set_404();
        }
    }
}