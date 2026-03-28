<?php
namespace Aesir\v1\Components;

use Aesir\v1\Traits\Filterable;

class Utilities {
    use Filterable;

    /**
     * Strips all punctuation from a string and replaces all spaces with dashes.
     * @param string $text The string you want stripped of punctuation
     * @param string $replace (Optional) replace spaces with text of your choice
     * @return string $regex (Optional) Specify the regular expression to be used
     * when stripping punctuation.
     */
    public function stripPunctuation($text, $replace = '', $regex = '/[^a-zA-Z\s]/') {
        return preg_replace($regex, $replace, $text);
    }

    /**
     * Returns the URL of an attachment image - used for instances when we don't
     * care about the pixel dimensions of the image (most of the time in responsive
     * design) and just want the file URL.
     * @param int $id The attachment ID
     * @param string $size (Optional) WordPress image size (defaults to "medium")
     * @return string
     */
    public function src($id, $size = 'medium')
    {
        $image = wp_get_attachment_image_src($id, $size);
        return $image[0];
    }

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    public function merge( array &$array1, array &$array2 )
    {
        $merged = $array1;

        foreach ( $array2 as $key => &$value )
        {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
            {
                $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
            } else {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * A wrapper for get_stylesheet_directory() and get_template_directory() that shortens and makes it a
     * little more user friendly. This should be used over get_stylesheet_directory(). If the file or folder
     * is found in the child theme then it will be used, otherwise the parent theme will be used.
     *
     * @param string $path (optional) A relative path from the theme directory (and/or file name)
     * @return string
     * @filter theme_parent_path
     * @filter theme_child_path
     */
    public function path($path = '') {
        $base = apply_filters('theme_child_path', str_replace('/', DIRECTORY_SEPARATOR, get_stylesheet_directory().DIRECTORY_SEPARATOR));
        $full = str_replace('/', DIRECTORY_SEPARATOR, $base.$path);

        if(!(file_exists($full) || !is_dir($full))) {
            $base = apply_filters('theme_parent_path', str_replace('/', DIRECTORY_SEPARATOR, get_template_directory().DIRECTORY_SEPARATOR));
            $full = str_replace('/', DIRECTORY_SEPARATOR, $base.$path);
        }

        return $full;
    }

    /**
     * Returns the full URL to a theme file based on the provided path. When using a child
     * theme this will attempt to locate the file in the child theme first and if not
     * found the link will be to the parent theme. If a fully qualified URL is passed
     * in as the path (it must contain "//" near the beginning) then the URL will be
     * passed through unaltered. In this way both absolute and relative URLs may be
     * passed through this function without needing to worry what type you have.
     *
     * @param string|null $path A relative path from the theme directory (and/or file name)
     * @return string
     */
    public function url($path = '') {
        $url = $path;
        $qualified = strpos($path, '//');
        if($qualified === false || $qualified >  10) {
            $url = get_stylesheet_directory_uri().'/'.$path;
            if(!file_exists($this->path($path))) {
                $url = get_template_directory_uri().'/'.$path;
            }
        }

        return $url;
    }

    /**
     * Creates an href link to a page on the site using a relative path,
     * if an absolute link is provided then it is passed through unaltered.
     *
     * @api
     * @param string|null $path
     * @return string
     */
    public function link($path = '') {
        $url = $path;
        $qualified = strpos($path, '//');
        if($qualified === false || $qualified > 10){
            $url = home_url($path);
        }

        return $url;
    }

    /**
     * Converts a string to cammelCase, if the 2nd parameter is true then
     * the PascalCase variant is used, capitalizing the first letter.
     * @param string $string
     * @return string
     */
    public function toCammelCase($string, $pascalCase = false) {
        $text = str_replace(' ', '', ucwords($this->stripPunctuation($string, ' ')));
        return $pascalCase ? $text : lcfirst($text);
    }

    /**
     * Retrieves a list of parameters for the given class method or function
     * @param $class
     * @param $method
     * @return \ReflectionParameter[]
     */
    public function parameters(callable $object) {
        if(is_array($object)) {
            $reflection = new \ReflectionMethod($object[0], $object[1]);
        } else {
            if(is_string($object) && strpos($object, '::') !== false) {
                list($obj, $method) = explode('::', $object);
                $reflection = new \ReflectionMethod($obj, $method);
            } else {
                $reflection = new \ReflectionFunction($object);
            }
        }

        return $reflection->getParameters();
    }

    /**
     * Returns all parameters of a given class type, including any parameters that
     * are a subclass of the passed in type.
     * @param callable $object
     * @param string $type A fully qualified class name
     * @return \ReflectionParameter[]
     */
    public function getParametersOfType(callable $object, $type) {
        $list = [];

        foreach($this->parameters($object) as $parameter) {
            if(is_a($parameter->getClass()->getName(), $type, true)) {
                $list[] = $parameter;
            }
        }

        return $list;
    }
}