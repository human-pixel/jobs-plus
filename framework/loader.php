<?php
/**
 * Author: Hoang Ngo
 */
if (!function_exists('ig_loader')) {
    /**
     * @param $class
     */
    function ig_loader($class)
    {
        $classes = array(
            'IG_Model' => dirname(__FILE__) . '/database/ig-model.php',
            'IG_Post_Model' => dirname(__FILE__) . '/database/ig-post-model.php',
            'IG_DB_Model' => dirname(__FILE__) . '/database/ig-db-model.php',
            'IG_DB_Model_Ex' => dirname(__FILE__) . '/database/ig-db-model-ex.php',
            'IG_Option_Model' => dirname(__FILE__) . '/database/ig-option-model.php',
            'IG_Grid' => dirname(__FILE__) . '/database/ig-grid.php',
            'IG_Form' => dirname(__FILE__) . '/form/ig-form.php',
            'IG_Active_Form' => dirname(__FILE__) . '/form/ig-active-form.php',
            'IG_Form_Generator' => dirname(__FILE__) . '/generator/ig-form-generator.php',
            'IG_Request' => dirname(__FILE__) . '/request/ig-request.php',
            'IG_Logger' => dirname(__FILE__) . '/logger/ig-logger.php',
        );

        if (isset($classes[$class])) {
            require_once $classes[$class];
        }
    }

    spl_autoload_register('ig_loader');

    if (!function_exists('ig_enqueue_scripts')) {
        add_action('wp_enqueue_scripts', 'ig_enqueue_scripts');
        add_action('admin_enqueue_scripts', 'ig_enqueue_scripts');
        function ig_enqueue_scripts()
        {
            $url = plugin_dir_url(__FILE__);
            if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG == true) {
                wp_register_style('ig-packed', $url . 'assets/ig-packed.css');
            } else {
                wp_register_style('ig-packed', $url . 'assets/ig-packed.css');
            }
            /*wp_register_style('ig-bootstrap-lumen', $url . 'assets/lumen.css');
            wp_register_style('ig-bootstrap-flaty', $url . 'assets/flaty.css');
            wp_register_style('ig-bootstrap-paper', $url . 'assets/paper.css');
            wp_register_style('ig-bootstrap-united', $url . 'assets/united.css');
            wp_register_script('ig-bootstrap', $url . 'assets/bootstrap.min.js', array('jquery'));
            wp_register_style('ig-fontawesome', $url . 'assets/fa/css/font-awesome.css');*/
        }
    }
}