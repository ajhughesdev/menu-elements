<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 10/25/2018
 * Time: 3:08 PM
 */

namespace Aesir\v1\Components\Template;

use Aesir\v1\Components\View;
use Aesir\v1\Traits\ArrayMerge;

class Partial extends View
{
    public function setData($data) {
        $this->templateData = $data;
    }

    public function make(array $templateData = [])
    {
        $this->extend($templateData);

        return $this;
    }

    public function __toString()
    {
        ob_start();

        $this->render();

        return ob_get_clean();
    }

    public function __invoke($templateData = [])
    {
        ob_start();

        $this->render($templateData);

        return ob_get_clean();
    }
}