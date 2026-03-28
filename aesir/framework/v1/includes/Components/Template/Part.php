<?php
/** @deprecated  */

namespace Aesir\v1\Components\Template;

use Aesir\v1\Interfaces\IStringlike;
use Aesir\v1\Exceptions\AesirException;

class Part implements IStringlike
{
    protected $base;
    protected $slug;
    protected $name;
    protected $subtype;
    protected $location;
    protected $path;
    protected $status;
    protected $args;
    protected $require_once = false;
    protected $pathMap;


    public function __construct($base, $slug, $name = null, $subtype = null) {
        $this->base = $base;

        $search = $this->getSearchPaths($base, $slug, $name, $subtype);

        $this->pathMap = [
            STYLESHEETPATH => 'child',
            TEMPLATEPATH => 'parent'
        ];

        $this->slug = $slug;
        $this->args = [];
        $this->locateTemplate($search);
    }

    /**
     * Adds arguments to the template, these will be extracted and available as SanitizedVaribles
     * within the template.
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function args(array $arguments) {
        $this->args = $arguments;

        return $this;
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

            foreach($map as $path=>$type) {
                if ( file_exists($path . '/' . $template_name)) {
                    $location = $type;
                    $located = $path . '/' . $template_name;
                    break 2;
                }
            }
        }

        $this->location = $location;
        $this->path = $located;
    }

    /**
     * This function returns paths that should be searched within a theme, plugin, or other location as
     * specified by getPathMap().
     * @param $path
     * @param $slug
     * @param $name
     * @param $subtype
     *
     * @return array
     */
    protected function getSearchPaths($path, $slug, $name, $subtype) {
        return [
            "$path/$slug/$name-$subtype.php",
            "$path/$slug/$name.php",
            "$path/$slug/any-$subtype.php",
            "$path/$slug/$slug.php"
        ];
    }

    public function addPluginPath($location, $path, $prepend = true) {
        if($prepend) {
            $this->pathMap = [$location => $path] + $this->pathMap;
        } else {
            $this->pathMap = $this->pathMap + [$location => $path];
        }
    }

    /**
     * Require the template file with WordPress environment.
     *
     * The globals are set up for the template file to ensure that the WordPress
     * environment is available from within the function. The query variables are
     * also available.
     *
     * This is a modified version of load_template() from the WordPress core, which also
     * passes in arbitrary arguments by way of the $args[] variable. Arguments passed
     * in in this way will be encapsulated in a TemplateVariable object if they are a
     * non-object/array that can be cast as a string. TemplateVariable objects use
     * __toString() to return the original value passed into them passed through esc_html(),
     * and so can be echoed directly. To retrieve the value without sanitization use
     * $variable->raw(). See the TemplateVariable documentation for additional information.
     *
     * This will also return any value returned
     * by the included file.
     *
     * @since 1.5.0
     * @see https://core.trac.wordpress.org/browser/tags/4.2.1/src/wp-includes/template.php#L0
     *
     * @param string $_template_file Path to template file.
     * @param array|null $args An array of data that you wish to be available to this template file
     *
     * @return mixed Return value of included file
     * @throws AesirException
     * @internal param bool $require_once Whether to require_once or require. Default true.
     * @see SanitizedVariable
     */
    protected function loadTemplate($_template_file, array $args = array()) {

        $templateVars = [];

        if(!empty($args)) {
            foreach($args as $key => $arg) {
                if(array_key_exists($key, get_defined_vars())) {
                    throw new AesirException("Variable [$key] already set in scope, cannot redefine.\n<br />".
                        "Part: [location => '{$this->location}', slug => '{$this->slug}', name => '{$this->name}', subtype => '{$this->subtype}']");
                }
                if((!is_array($arg) && !is_null($arg) && !is_bool($arg)
                    && (!is_object($arg) && settype($arg, 'string') !== false ))) {
                    $templateVars[$key] = new SanitizedVariable($arg);
                }
            }
        }

        // Separate context for template inclusion
        $template = function($unique) use ($_template_file, $templateVars) {
            global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

            if ( is_array( $wp_query->query_vars ) )
                extract( $wp_query->query_vars, EXTR_SKIP );

            extract($templateVars, EXTR_SKIP);

            if($unique)
                require_once($_template_file);
            else
                return require($_template_file);
        };

        $template($this->isUnique());
    }

    public function render() {
        if($this->exists()) {
            ob_start();

            $part = $this->path;

            if(defined('WP_DEBUG') && WP_DEBUG) { // If debugging is on print out fully qualified part names as comments
                $full_part_name = "{$this->location}/{$this->slug}/".str_ireplace('.php', '', basename($part));

                echo "<!-- [START $full_part_name] -->";
                $this->loadTemplate($part, $this->args);
                echo "<!-- [END $full_part_name] -->";

            } else {
                $this->loadTemplate($part, $this->args);
            }

            return ob_get_clean();
        }
        return null;
    }

    public function isUnique() {
        return $this->require_once;
    }

    public function unique($require_once = true) {
        $this->require_once = $require_once;

        return $this;
    }

    public function exists() {
        return !empty($this->path);
    }

    public function __toString()
    {
        return $this->render();
    }

    function jsonSerialize()
    {
        return $this->render();
    }
}