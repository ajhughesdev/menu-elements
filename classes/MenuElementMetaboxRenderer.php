<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 3/29/2026
 * Time: 2:15 AM
 */

namespace KMDG\MenuElements;


class MenuElementMetaboxRenderer
{
    public function render(array $menuItems, $navMenuSelectedId)
    {
        $walker = new \Walker_Nav_Menu_Checklist(false);

        $removedArgs = array(
            'action',
            'customlink-tab',
            'edit-menu-item',
            'menu-item',
            'page-tab',
            '_wpnonce',
        );

        ob_start();
        ?>
        <div id="kmdg-menu-elements">
            <div id="tabs-panel-kmdg-menu-elements-all" class="tabs-panel tabs-panel-active">
                <ul id="kmdg-menu-elements-checklist-pop" class="categorychecklist form-no-clear" >
                    <?php echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $menuItems), 0, (object) ['walker' => $walker]); ?>
                </ul>

                <p class="button-controls">
                <span class="list-controls">
                    <a href="<?php
                    echo esc_url(add_query_arg(
                        array(
                            'kmdg-menu-elements-all' => 'all',
                            'selectall' => 1,
                        ),
                        remove_query_arg($removedArgs)
                    ));
                    ?>#kmdg-menu-metabox" class="select-all"><?php _e('Select All'); ?></a>
                </span>

                    <span class="add-to-menu">
                    <input type="submit"<?php wp_nav_menu_disabled_check($navMenuSelectedId); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-kmdg-menu-elements-menu-item" id="submit-kmdg-menu-elements" />
                    <span class="spinner"></span>
                </span>
                </p>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }
}
