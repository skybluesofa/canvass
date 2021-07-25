<?php

namespace SkyBlueSofa\Canvass\Support;

/*
Included in this class are a bunch of static methods that are wrappers for Wordpress
functions.

The provided methods are named similarly to their Wordpress partners using this
naming convention:
* Underscores are removed
* Method names are camelCased
* The 'wp_' prefix is removed

If you call a static method on the Wordpress class and that method does not exist,
the method name will be converted to Wordpress standards and will attempt to be
called. For instance, if you run this function:
    Wordpress::addPostMeta(...);

The Wordpress class will convert that to the original WP function named:
    \add_post_meta(...);

There are also some additional helper functions defined:
    Wordpress::isDashboard()
    Wordpress::logNotice(...)
    Wordpress::logWarning(...);
    Wordpress::db();
*/

class Wordpress
{
    private static $instance;
    private $scriptAttributes = [];

    private function __construct()
    {
    }

    private static function instance()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function pluginBasename($plugin)
    {
        return \plugin_basename($plugin);
    }

    public static function pluginDirPath($path)
    {
        return \plugin_dir_path($path);
    }

    public static function pluginsURL($path, $plugin)
    {
        return \plugins_url($path, $plugin);
    }

    public static function pluginData($file, $markup = true, $translate = true)
    {
        return \get_plugin_data($file, $markup, $translate);
    }

    public static function doingAjax()
    {
        return \wp_doing_ajax();
    }

    public static function getCurrentUserId()
    {
        return \get_current_user_id();
    }

    public static function addRole()
    {
        return \add_role($role, $display_name, $capabilities = array());
    }

    public static function isDashboard()
    {
        return (Wordpress::isAdmin() && !Wordpress::doingAjax());
    }

    public static function scriptIs($handle, $list = 'enqueued')
    {
        \wp_script_is($handle, $list='enqueued');
    }

    public static function registerScript($handle, $src, $deps = array(), $ver = false, $in_footer = false)
    {
        \wp_register_script($handle, $src, $deps, $ver, $in_footer);
    }

    public static function deferScript($handle)
    {
        Wordpress::addScriptAttribute($handle, 'defer', 'defer');
    }

    public static function asyncScript($handle)
    {
        Wordpress::addScriptAttribute($handle, 'async', 'async');
    }

    public static function addScriptAttribute($handle, $attributeKey, $attributeValue)
    {
        if (count(Wordpress::instance()->scriptAttributes)==0) {
            Wordpress::addFilter('script_loader_tag', function ($tag, $handle) {
                if (key_exists($handle, Wordpress::instance()->scriptAttributes)) {
                    foreach (Wordpress::instance()->scriptAttributes[$handle] as $key => $value) {
                        $tag = str_replace(' src', ' '.$key.'="'.$value.'" src', $tag);
                    }
                }
                return $tag;
            }, 10, 2);
        }

        if (!isset(Wordpress::instance()->scriptAttributes[$handle])) {
            Wordpress::instance()->scriptAttributes[$handle] = [];
        }
        Wordpress::instance()->scriptAttributes[$handle][$attributeKey] = $attributeValue;
    }

    public static function localizeScript($handle, $object_name, $l10n)
    {
        \wp_localize_script($handle, $object_name, $l10n);
    }

    public static function addInlineScript($handle, $data, $position = 'after')
    {
        \wp_add_inline_script($handle, $data, $position);
    }

    public static function enqueueScript($handle, $src = false, $deps = array(), $ver = false, $in_footer = false)
    {
        \wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
    }

    public static function addInlineStyle($handle, $data)
    {
        \wp_add_inline_style($handle, $data);
    }

    public static function enqueueStyle($handle, $src = false, $deps = array(), $ver = false, $media = 'all')
    {
        \wp_enqueue_style($handle, $src, $deps, $ver, $media);
    }

    public static function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        \add_action($tag, $function_to_add, $priority, $accepted_args);
    }

    public static function removeFilter($tag, $function_to_remove, $priority = 10)
    {
        \remove_filter($tag, $function_to_remove, $priority);
    }

    public static function addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        \add_filter($tag, $function_to_add, $priority, $accepted_args);
    }

    public static function query($args = [])
    {
        \remove_all_filters('posts_orderby');
        return new \WP_Query($args);
    }

    public static function addQueryArg()
    {
        return call_user_func_array("\add_query_arg", func_get_args());
    }

    public static function getPermalink($post = 0, $leavename = false)
    {
        return \get_permalink($post, $leavename);
    }

    public static function getPostTitle($post = 0)
    {
        return \get_the_title($post);
    }

    public static function getListTable($class, $args = array())
    {
        return \_get_list_table($class, $args);
    }

    public static function redirect($location, $status = 302)
    {
        return \wp_redirect($location, $status);
    }

    public static function checkAdminReferer($action = -1, $query_arg = '_wpnonce')
    {
        return \check_admin_referer($action, $query_arg);
    }

    public static function addSubmenuPage(
        $parent_slug,
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $function = ''
    ) {
        \add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
    }

    public static function addMenuPage(
        string $page_title,
        string $menu_title,
        string $capability,
        string $menu_slug,
        callable $function = null,
        string $icon_url = '',
        int $position = null
    ) {
        \add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
    }

    public static function adminURL($path = '', $scheme = 'admin')
    {
        return \admin_url($path, $scheme);
    }

    public static function siteURL($path = '', $scheme = null)
    {
        return \site_url($path, $scheme);
    }

    public static function homeURL($path = '', $scheme = null)
    {
        return \home_url($path, $scheme);
    }

    public static function uploadDir($time = null, $create_dir = true, $refresh_cache = false)
    {
        return \wp_upload_dir($time, $create_dir, $refresh_cache);
    }

    public static function uniqueFilename($dir, $filename, $unique_filename_callback = null)
    {
        return \wp_unique_filename($dir, $filename, $unique_filename_callback);
    }

    public static function checkFileType($filename, $mimes = null)
    {
        return \wp_check_filetype($filename, $mimes);
    }

    public static function getPostMeta($post_id, $key = '', $single = false)
    {
        return \get_post_meta($post_id, $key, $single);
    }

    public static function updatePostMeta($post_id, $meta_key, $meta_value, $prev_value = '')
    {
        return \update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
    }

    public static function deletePostMeta($post_id, $meta_key, $meta_value = '')
    {
        return \delete_post_meta($post_id, $meta_key, $meta_value);
    }

    public static function getObjectTaxonomies($object, $output = 'names')
    {
        return \get_object_taxonomies($object, $output);
    }

    public static function getObjectTerms($object_ids, $taxonomies, $args = array())
    {
        return \wp_get_object_terms($object_ids, $taxonomies, $args);
    }

    public static function getAttachmentImageSrc($attachment_id, $size = 'thumbnail', $icon = false)
    {
        return \wp_get_attachment_image_src($attachment_id, $size, $icon);
    }

    public static function getFieldObject($field_key, $post_id = false, $options = array())
    {
        return \get_field_object($field_key, $post_id, $options);
    }

    public static function getPostStatus($ID = '')
    {
        return \get_post_status($ID);
    }

    public static function remotePost($url, $args = array())
    {
        return \wp_remote_post($url, $args);
    }

    public static function remoteRetreiveResponseCode($response)
    {
        return \wp_remote_retrieve_response_code($response);
    }

    public static function remoteRetrieveBody($response)
    {
        return \wp_remote_retrieve_body($response);
    }

    public static function remoteGet($url, $args = array())
    {
        return \wp_remote_get($url, $args);
    }

    public static function insertAttachment($args, $file = false, $parent = 0)
    {
        return \wp_insert_attachment($args, $file, $parent);
    }

    public static function generateAttachmentMetaData($attachment_id, $file)
    {
        return \wp_generate_attachment_metadata($attachment_id, $file);
    }

    public static function updateAttachmentMetaData($post_id, $data)
    {
        return \wp_update_attachment_metadata($post_id, $data);
    }

    public static function setObjectTerms($object_id, $terms, $taxonomy, $append = false)
    {
        return \wp_set_object_terms($object_id, $terms, $taxonomy, $append);
    }

    public static function getPost($post = null, $output = OBJECT, $filter = 'raw')
    {
        return \get_post($post, $output, $filter);
    }

    public static function getPosts($args = null, $order_by = "date", $post_mime_type = "")
    {
        return \get_posts($args, $order_by, $post_mime_type);
    }

    public static function isUserLoggedIn()
    {
        return \is_user_logged_in();
    }

    public static function hash($data, $scheme = 'auth')
    {
        return \wp_hash($data, $scheme);
    }

    public static function getOption($option, $default = false)
    {
        return \get_option($option, $default);
    }

    public static function currentTime($type, $gmt = 0)
    {
        return \current_time($type, $gmt);
    }

    public static function updatePost($postarr = array(), $wp_error = false)
    {
        return \wp_update_post($postarr, $wp_error);
    }

    public static function insertPost($postarr, $wp_error = false)
    {
        return \wp_insert_post($postarr, $wp_error);
    }

    public static function setOption($option, $value, $autoload = null)
    {
        self::updateOption($option, $value, $autoload);
    }

    public static function updateOption($option, $value, $autoload = null)
    {
        \update_option($option, $value, $autoload);
    }

    public static function getTaxonomy($taxonomy)
    {
        return \get_taxonomy($taxonomy);
    }

    public static function currentUserCan($capability)
    {
        return \current_user_can($capability);
    }

    public static function registerActivationHook($file, $function)
    {
        return \register_activation_hook($file, $function);
    }

    public static function registerDeactivationHook($file, $function)
    {
        return \register_deactivation_hook($file, $function);
    }

    public static function getPostType($post = null)
    {
        return \get_post_type($post);
    }

    public static function getPostField($field, $post_id = '', $context = 'display')
    {
        return \get_post_field($field, $post_id, $context);
    }

    public static function shortcodeAtts($pairs, $atts, $shortcode = '')
    {
        return \shortcode_atts($pairs, $atts, $shortcode);
    }
    
    public static function logNotice($message, $prefix = null)
    {
        return self::logMessage('notice', $message, $prefix);
    }

    public static function logError($message, $prefix = null)
    {
        return self::logMessage('error', $message, $prefix);
    }

    public static function logMessage($type, $message, $prefix = null)
    {
        return error_log(($prefix?$prefix.': ':'').ucwords(strtolower($type)).': '.$message);
    }

    public static function doingAutoSave()
    {
        return defined('DOING_AUTOSAVE') && DOING_AUTOSAVE;
    }

    public static function dbDelta($queries, $execute = true)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        return \dbDelta($queries, $execute);
    }
    
    public static function db()
    {
        global $wpdb;

        return $wpdb;
    }

    /**
    * Add support for Wordpress functions that are not explicitly defined above.
    * @param string $functionName
    * @param array $args
    * @return mixed
    */
    public static function __callStatic($functionName, $args)
    {
        $functionName = "\\".self::camelCaseToSnakeCase($functionName);
        if (function_exists($functionName)) {
            return call_user_func_array($functionName, $args);
        }
        throw new \Exception("The function '".$functionName."' does not exist.");
    }

    /**
    * Turns camelCasedFunctionName into snake_cased_function_name
    * @param string $input
    * @return string
    */
    private static function camelCaseToSnakeCase($input)
    {
        $matches = [];
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        foreach ($matches[0] as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $matches[0]);
    }
    public static function nextScheduled($hook, $args = [])
    {
        return wp_next_scheduled($hook, $args);
    }

    public static function scheduleEvent($timestamp, $recurrance, $hook, $args = [])
    {
        return wp_schedule_event($timestamp, $recurrance, $hook, $args);
    }
    
    public static function scheduleEventIfNotAlready($timestamp, $recurrance, $hook, $args = [])
    {
        if (!Wordpress::nextScheduled($hook, $args)) {
            Wordpress::scheduleEvent($timestamp, $recurrance, $hook, $args);
        }
    }

    public static function clearScheduledHook($hook, $args = [])
    {
        return wp_clear_scheduled_hook($hook, $args);
    }
    
    public static function clearScheduledEvent($hook, $args = [])
    {
        return Wordpress::clearScheduledHook($hook, $args);
    }
}
