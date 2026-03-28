<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 6/7/2019
 * Time: 3:07 PM
 */

namespace KMDG\MenuElements;


class Walker extends \Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        $item = apply_filters('KMDG/MenuElements/Item/pre-render', $item);

        parent::start_el($output, $item, $depth, $args, $id);
    }

    public function end_el(&$output, $item, $depth = 0, $args = array())
    {
        $output .= apply_filters('walker_nav_menu_end_el', '', $item, $depth , $args);

        parent::end_el($output, $item, $depth, $args);
    }
}