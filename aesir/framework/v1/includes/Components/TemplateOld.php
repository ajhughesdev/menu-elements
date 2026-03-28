<?php
/**
 * Created by PhpStorm.
 * User: finle
 * Date: 6/9/2016
 * Time: 3:06 AM
 */

namespace Aesir\v1\Components;

use Aesir\v1\Exceptions\AesirException;

class TemplateOld {
    protected $part_path = 'parts';

    /**
     * An expanded version of get_template_part() that will except up to three parameters
     * and attempt to load files in the format "$path/$slug/$name-$subtype.php". This will
     * act like get_template_part() and check the child theme first, then the parent. If
     * the first filename is not found in either theme then fallback checking will occur
     * just like with get_template_part(). Name formats to be checked are as follows and
     * in this specific order starting from highest to lowest:
     *
     * "$path/$slug/$name-$subtype.php"
     * "$path/$slug/$name.php"
     * "$path/$slug/any-$subtype.php"
     * "$path/$slug/$slug.php"
     *
     * Note that unlike get_template_part() this method specifically looks in the theme
     * parts folder (by default /parts), and if you need to override this you can use the
     * provided aesir_parts_path filter to return a different path. You may also modify
     * the checked path formats using the aesir_parts_formats filter.
     *
     * This will also allow you to include an array of data that should be passed to the
     * template part as the $args[] variable.
     *
     * @api
     * @param string $slug
     * @param string|null $name
     * @param string|null $subtype
     * @param array|null $args
     * @filter aesir_parts_formats
     * @return string
     */
    public function part($slug, $name = null, $subtype = null, array $args = null) {
        $path = $this->part_path;
        $search = apply_filters('aesir_parts_formats', array(
            "$path/$slug/$name-$subtype.php",
            "$path/$slug/$name.php",
            "$path/$slug/any-$subtype.php",
            "$path/$slug/$slug.php"
        ));

        $part_info = $this->locateTemplate($search, false, false);

        ob_start();
        if(!empty($part_info)) {
            $part = $part_info['path'];
            $theme = $part_info['theme'];

            if(WP_DEBUG) { // If debugging is on print out fully qualified part names as comments
                $full_part_name = "$theme/$slug/".str_ireplace('.php', '', basename($part));

                echo "<!-- [START $full_part_name] -->";
                $this->loadTemplate($part, false, $args);
                echo "<!-- [END $full_part_name] -->";

            } else {
                $this->loadTemplate($part, false, $args);
            }
        }
        return ob_get_clean();
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
     * @param bool $load If true the template file will be loaded if it is found.
     * @param bool $require_once Whether to require_once or require. Default true. Has no effect if $load is false.
     * @return array('theme'=>string, 'path'=>string, 'status'=>mixed) The template filename and containing theme if
     * one is located. Also any return value provided by the included template (status).
     */
    protected function locateTemplate($template_names, $load = false, $require_once = true ) {
        $located = '';
        $theme = '';

        foreach ( (array) $template_names as $template_name ) {
            if ( !$template_name )
                continue;
            if ( file_exists(STYLESHEETPATH . '/' . $template_name)) {
                $theme = 'child';
                $located = STYLESHEETPATH . '/' . $template_name;
                break;
            } else if ( file_exists(TEMPLATEPATH . '/' . $template_name) ) {
                $theme = 'parent';
                $located = TEMPLATEPATH . '/' . $template_name;
                break;
            }
        }

        if(STYLESHEETPATH === TEMPLATEPATH) {
            $theme = 'theme';
        }

        $status = null;
        if ( $load && '' != $located )
            $status = $this->loadTemplate( $located, $require_once );

        if($located != '') {
            return array('theme'=>$theme, 'path'=>$located, 'status'=> $status);
        }

        return null;
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
     * @param string $_template_file Path to template file.
     * @param bool $require_once Whether to require_once or require. Default true.
     * @param array|null $args An array of data that you wish to be available to this template file
     * @see TemplateVariable
     * @return mixed Return value of included file
     */
    protected function loadTemplate($_template_file, $require_once = true , $args = array()) {
        global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

        if ( is_array( $wp_query->query_vars ) )
            extract( $wp_query->query_vars, EXTR_SKIP );

        if(!empty($args)) {
            foreach($args as $key => $arg) {
                if(array_key_exists($key, get_defined_vars())) {
                    throw new AesirException("Template variable [$key] already set in scope, cannot redefine");
                }
                if((!is_array($arg) && !is_null($arg) && !is_bool($arg)
                    && (!is_object($arg) && settype($arg, 'string') !== false ))) {
                    $args[$key] = new SanitizedVariable($arg);
                }
            }

            extract($args, EXTR_SKIP);
        }

        if ( $require_once )
            return require_once( $_template_file );
        else
            return require( $_template_file );
    }

    /**
     * Returns the template slug of the currently active template.
     * @api
     * @return string
     * @filter aesir_template_name
     */
    public function templateName() {
        $template = '';

        if(is_home()) {
            $template = 'index';
        } elseif(is_author()) {
            $template = 'author';
        } elseif(is_post_type_archive()) {
            $template = 'archive-'.get_query_var('post_type');
        } elseif (is_tax()) {
            $template = 'archive-'.get_query_var('taxonomy');
        } elseif(is_archive()) {
            $template = 'archive';
        } elseif(is_404()) {
            $template = '404';
        } elseif(is_single()) {
            $template = get_post_type();
        } else {
            $template = str_replace('.php', '', basename(get_page_template()));
        }

        return apply_filters('aesir_template_name', $template);
    }

    /**
     * Checks to see if a post is being displayed as in an archive type view or as a
     * single post. Returns the text single or archive to indicate the display mode.
     * @filter aesir_post_display_mode
     * @return string
     */
    public function post_display_mode() {
        $template = 'single';

        if(!is_single()) {
            $template = 'archive';
        }

        return apply_filters('aesir_post_display_mode', $template);
    }

    /**
     * Returns the slug of the current page, if the page is an automatically generated page
     * the slug will be the name of the template that is generating it (such as "archive", "index", etc).
     * @api
     * @return string
     * @filter aesir_page_slug
     */
    public function page_slug() {
        global $post;
        $slug = '';

        if(is_home()) {
            $slug = 'index';
        } elseif(is_author()) {
            $slug = 'author';
        } elseif(is_post_type_archive()) {
            $slug = 'archive-'.get_query_var('post_type');
        } elseif (is_tax()) {
            $slug = 'archive-'.get_query_var('taxonomy');
        } elseif(is_archive()) {
            $slug = 'archive';
        } elseif(is_404()) {
            $slug = '404';
        } else {
            $slug = $post->post_name;
        }

        return apply_filters('aesir_page_slug', $slug);
    }

    /**
     * Attempts to display the correct title based on what type of page
     * or archive is being displayed.
     *
     * @api
     * @filter aesir_search_title
     */
    public function the_page_title() {
        if(is_category()) {
            single_cat_title();
        } elseif(is_tag()) {
            single_tag_title();
        } elseif(is_author()) {
            echo 'Posts by '.get_the_author();
        } elseif(is_tax()) {
            single_term_title();
        } elseif(is_search()) {
            echo apply_filters('aesir_search_title', 'Search Results');
        } elseif(is_post_type_archive()) {
            post_type_archive_title();
        } elseif(is_home()) {
            echo get_the_title(get_option('page_for_posts' ));
        } else {
            the_title();
        }
    }
}