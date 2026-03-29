<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 3/29/2026
 * Time: 12:00 AM
 */

namespace KMDG\MenuElements;


class BuiltInTypeProvider
{
    public function register(Plugin $plugin)
    {
        $plugin->addItem('Row', 'row',
            apply_filters('KMDG/MenuElements/Row/callback', [$plugin, 'rowCallback'])
        );

        $plugin->addItem('Column', 'column',
            apply_filters('KMDG/MenuElements/Column/callback', [$plugin, 'columnCallback']),
            apply_filters('KMDG/MenuElements/Column/after', [$plugin, 'columnCallbackAfter']),
            apply_filters('KMDG/MenuElements/Column/before', [$plugin, 'columnCallbackBefore'])
        );

        $plugin->addItem('Spacer', 'spacer', [$plugin, 'spacerCallback']);

        $plugin->addItem('Title', 'title', [$plugin, 'titleCallback']);
    }
}
