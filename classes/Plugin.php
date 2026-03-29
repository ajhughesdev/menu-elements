<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 6/11/2019
 * Time: 3:20 PM
 */

namespace KMDG\MenuElements;


use Aesir\v1\Exceptions\AesirException;
use Aesir\v1\Traits\Filterable;
use Aesir\v1\Traits\Singleton;
use mysql_xdevapi\Exception;

class Plugin
{
    use Filterable;
    use Singleton;

    protected $types = [];

    protected $menu_items = [];

    /** @var Environment */
    protected $environment;

    /**
     * Pseudo-constructor to be overwritten by implementing classes,
     * this will be ran once upon creation of the singleton's instance.
     */
    protected function initialize()
    {
        $this->environment = new Environment();

        add_action('current_screen', function() {
            add_meta_box('kmdg-menu-metabox', 'Custom Elements', [$this, 'renderMetabox'], 'nav-menus', 'side', 'low');
        });

        $this->addFilter('wp_nav_menu_args', 'changeDefaultWalker', 10, 1);
        $this->addFilter('wp_edit_nav_menu_walker', 'replaceEditWalker', 20, 2);

        $this->addFilter('walker_nav_menu_start_el', 'getItemContent', 10, 4);
        $this->addFilter('walker_nav_menu_end_el', 'getItemContentAfter', 10, 4);
        $this->addFilter('KMDG/MenuElements/Item/pre-render', 'getItemPreRender', 10, 1);

        $this->addFilter('acf/location/rule_types', 'addACFLocations', 10 , 1);
        $this->addFilter('acf/location/rule_values/menu_element', 'addACFLocVals', 10 , 1);
        $this->addFilter('acf/location/rule_match/menu_element', 'acfRuleMatch', 10, 3);
        $this->addFilter('acf/settings/load_json', 'loadACF', 10, 1);

        $this->addAction('admin_enqueue_scripts', 'registerAdminAssets');
        $this->addAction('wp_enqueue_scripts', 'registerAssets');

        $this->addItem('Row', 'row',
            apply_filters('KMDG/MenuElements/Row/callback', [$this, 'rowCallback'])
        );

        $this->addItem('Column', 'column',
            apply_filters('KMDG/MenuElements/Column/callback', [$this, 'columnCallback']),
            apply_filters('KMDG/MenuElements/Column/after', [$this, 'columnCallbackAfter']),
            apply_filters('KMDG/MenuElements/Column/before', [$this, 'columnCallbackBefore'])
        );

        $this->addItem('Spacer', 'spacer', [$this, 'spacerCallback']);

        $this->addItem('Title', 'title', [$this, 'titleCallback']);
    }

    public function addItem($name, $slug, callable $callback, callable $after = null, callable $preRender = null) {
        $this->types[$slug] = [
            'title' => $name,
            'before' => $preRender,
            'main' => $callback,
            'after' => $after
        ];

        $this->menu_items[$slug] = (object) [
            'ID' => 1,
            'db_id' => 0,
            'menu_item_parent' => 0,
            'object_id' => "column",
            'post_parent' => 0,
            'type' => $slug,
            'object' => $slug,
            'type_label' => $name,
            'title' => $name,
            'url' => null,
            'target' => '',
            'attr_title' => '',
            'description' => '',
            'classes' => ["menu-elements__$slug"],
            'xfn' => ''
        ];
    }

    public function getTypes() {
        return array_keys($this->types);
    }

    public function getTypeLabel($slug) {
        if(empty($this->types[$slug])) {
            throw new AesirException("Cannot get label for undefined type [$slug].");
        }

        return $this->types[$slug]['title'];
    }

    protected function __getItemContent($default, $item, $depth, $args) {
        if(!empty($this->types[$item->type]) && is_callable($this->types[$item->type]['main'])) {
            return call_user_func($this->types[$item->type]['main'], $item, $depth, $args);
        }

        return $default;
    }

    protected function __getItemContentAfter($default, $item, $depth, $args) {
        if(!empty($this->types[$item->type]) && is_callable($this->types[$item->type]['after'])) {
            return call_user_func($this->types[$item->type]['after'], $item, $depth, $args);
        }

        return $default;
    }

    protected function __getItemPreRender($item) {
        if(!empty($this->types[$item->type]) && is_callable($this->types[$item->type]['before'])) {
            return call_user_func($this->types[$item->type]['before'], $item);
        }

        return $item;
    }

    protected function __rowCallback($item, $depth, $args) {
        return "";
    }

    protected function __columnCallback($item, $depth, $args) {
        $lineClass = get_field('enable_line', $item) ? 'menu-elements__column-wrap--line' : '';

        return "<div class='menu-elements__column-wrap {$lineClass}'>";
    }

    protected function __columnCallbackAfter($item, $depth, $args) {
        return "</div>";
    }

    protected function __columnCallbackBefore($item) {
        $item->classes[] = 'menu-elements__column--'.get_field('column_size', $item);

        return $item;
    }

    protected function __spacerCallback($item, $depth, $args) {
        $line = get_field('enable_line', $item);
        $lineClass = $line ? 'menu-elements__spacer--has-line' : '';
        $size = get_field('size', $item) / 2;
        $border = $line ? 'border: 1px solid;' : '';

        return apply_filters('KMDG/MenuElements/Spacer/html',
            "<div style='overflow: hidden;'><div class='menu-elements__spacer {$lineClass}' style='padding-top: {$size}em;margin-bottom: {$size}em;{$border}'></div></div>",
            $item, $depth, $args, $line, $lineClass, $size, $border
        );
    }

    protected function __titleCallback($item, $depth, $args) {
        $title = get_the_title($item);
        return apply_filters('KMDG/MenuElements/Title/html', "<div class='menu-elements__title'>{$title}</div>", $item, $depth, $args, $title);
    }

    protected function __addACFLocations($choices) {
        $choices['Custom']['menu_element'] = "Custom Menu Element";

        return $choices;
    }

    protected function __addACFLocVals($choices) {

        foreach ($this->menu_items as $slug => $element) {
            $choices[$slug] = $element->title;
        }

        return $choices;
    }

    protected function __acfRuleMatch($match, $rule, $options) {

        if(!empty($options['nav_menu_item'])) {
            if($rule['operator'] == "==") {
                $match = $options['nav_menu_item'] == $rule['value'];
            } else {
                $match = $options['nav_menu_item'] != $rule['value'];
            }
        }

        return $match;
    }

    protected function __changeDefaultWalker($args) {

        if($args['walker'] == '') {
            $args['walker'] = new Walker();
        }

        return $args;
    }

    protected function __loadACF($paths) {
        $paths[] = $this->environment->getAcfJsonPath();
        return $paths;
    }

    /**
     * Displays a menu metabox
     *
     * @param string $object Not used.
     * @param array $args Parameters and arguments. If you passed custom params to add_meta_box(),
     * they will be in $args['args']
     */
    protected function __renderMetabox($object, $args) {
        global $nav_menu_selected_id;
        // Create an array of objects that imitate Post objects

        $db_fields = false;

        // If your links will be hieararchical, adjust the $db_fields array bellow
        if ( false ) {
            $db_fields = array( 'parent' => 'parent', 'id' => 'post_parent' );
        }

        $walker = new \Walker_Nav_Menu_Checklist( $db_fields );

        $removed_args = array(
            'action',
            'customlink-tab',
            'edit-menu-item',
            'menu-item',
            'page-tab',
            '_wpnonce',
        ); ?>

        <div id="kmdg-menu-elements">
            <div id="tabs-panel-kmdg-menu-elements-all" class="tabs-panel tabs-panel-active">
                <ul id="kmdg-menu-elements-checklist-pop" class="categorychecklist form-no-clear" >
                    <?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $this->menu_items ), 0, (object) ['walker' => $walker] ); ?>
                </ul>

                <p class="button-controls">
                <span class="list-controls">
                    <a href="<?php
                    echo esc_url(add_query_arg(
                        array(
                            'kmdg-menu-elements-all' => 'all',
                            'selectall' => 1,
                        ),
                        remove_query_arg( $removed_args )
                    ));
                    ?>#kmdg-menu-metabox" class="select-all"><?php _e( 'Select All' ); ?></a>
                </span>

                    <span class="add-to-menu">
                    <input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-kmdg-menu-elements-menu-item" id="submit-kmdg-menu-elements" />
                    <span class="spinner"></span>
                </span>
                </p>
            </div>
        </div>
        <?php
    }

    public function __replaceEditWalker( $class, $menu_id = 0 ) {
        return 'KMDG\\MenuElements\\EditWalker';
    }

    /**
     * @hook admin_enqueue_scripts
     */
    protected function __registerAdminAssets() {
        $this->environment->enqueueStyle('kmdg-menu-elements-admin', 'dist/css/admin.css');
    }

    /**
     * @hook enqueue_scripts
     */
    protected function __registerAssets() {
        $this->environment->enqueueStyle('kmdg-menu-elements', 'dist/css/menu-elements.css');
    }
}
