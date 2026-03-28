<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 10/8/2018
 * Time: 10:42 AM
 */

namespace Aesir\v1\Components\Template;


class TemplateLocator
{
    protected $slug;
    protected $name;
    protected $subtype;
    protected $location;
    protected $path;
    protected $pathMap;

    public function __construct()
    {
        $this->pathMap = [];
    }

    public function find($slug, $name = null, $subtype = null) {

        $search = $this->getSearchPaths($slug, $name, $subtype);

        $this->slug = $slug;
        $this->locateTemplate($search);

        if($this->exists()) {
            return new Template($this->location, $this->path);
        } else {
            return null;
        }
    }

    /**
     * Adds a path to the list of areas to be searched. $path should be an absolute path, and $location should be a slug
     * that identifies the source of the template. Priority works along the same lines as a wordpress filter with lower
     * numbers taking precident, and things added at the same priority applying in the order they are added.
     *
     * @param string $location
     * @param string $path
     * @param int $priority
     */
    public function addPath($location, $path, $priority = 10) {
        if(empty($this->pathMap[$priority])) {
            $this->pathMap[$priority] = [];
        }

        $this->pathMap[$priority] = [$path => $location] + $this->pathMap[$priority];

        ksort($this->pathMap);
    }

    /**
     * Adds both parent and child theme paths to list of areas to be searched.
     *
     * @see TemplateLocator::addPath()
     * @param string $relPath
     * @param int $priority
     */
    public function addThemePath($relPath, $priority = 10) {
        $this->addPath('parent-theme', trailingslashit(get_template_directory()).$relPath, $priority);
        $this->addPath('theme', trailingslashit(get_stylesheet_directory()).$relPath, $priority);
    }

    /**
     * Returns true if the template was found.
     *
     * @return bool
     */
    public function exists() {
        return !empty($this->path);
    }

    /**
     * This function returns paths that should be searched within a theme, plugin, or other location as
     * specified by getPathMap().
     * @param $slug
     * @param $name
     * @param $subtype
     *
     * @return array
     */
    protected function getSearchPaths($slug, $name, $subtype) {
        return [
            "$slug/$name-$subtype.php",
            "$slug/$name.php",
            "$slug/$slug.php",
            "$slug-$name-$subtype.php",
            "$slug-$name.php",
            "$slug.php",
        ];
    }

    /**
     * Retrieve the name of the highest priority template file that exists.
     *
     * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
     * inherit from a parent theme can just overload one file.
     *
     * This is a modified version of the locate_template function included in the WordPress core,
     * it returns an array consisting of the template and the theme it is found in (parent
     * or child).
     *
     * @since 2.7.0
     * @see https://core.trac.wordpress.org/browser/tags/4.2.1/src/wp-includes/template.php#L0
     * @param string|array $template_names Template file(s) to search for, in order.
     */
    protected function locateTemplate($template_names) {

        $located = false;
        $location = null;

        $map = $this->pathMap;

        foreach ( (array) $template_names as $template_name ) {
            if ( !$template_name )
                continue;

            foreach($map as $priority) {
                foreach($priority as $path=>$type) {
                    if ( file_exists($path . '/' . $template_name)) {
                        $location = $type;
                        $located = $path . '/' . $template_name;
                        break 3;
                    }
                }
            }
        }

        $this->location = $location;
        $this->path = $located;
    }
}