<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 9/28/2017
 * Time: 2:43 PM
 */

namespace Aesir\v1\Factories;

use Aesir\v1\Components\Template\Partial;
use Aesir\v1\Components\Template\TemplateLocator;
use Aesir\v1\Exceptions\MissingDependency;

class PartFactory
{
    /** @var TemplateLocator */
    protected $locator = null;

    public function __construct(TemplateLocator $locator = null)
    {
        $this->setLocator($locator);
    }

    public function setLocator(TemplateLocator $locator) {
        $this->locator = $locator;
    }

    public function make($slug, $name = null, $subtype = null, $data = []) {

        if(!is_null($this->locator)) {
            $template = $this->locator->find($slug, $name, $subtype);
        } else {
            throw new MissingDependency("TemplateLocator not provided");
        }

        $part = new Partial($template);
        $part->setData($data);

        return $part;
    }
}