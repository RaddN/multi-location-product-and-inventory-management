<?php

/**
 * Plugin Name: Multi Location Product & Inventory Management for WooCommerce
 * Plugin URI: https://plugincy.com/multi-location-product-and-inventory-management
 * Description: Filter WooCommerce products by store locations with a location selector for customers.
 * Version: 1.0.7.5
 * Author: plugincy
 * Author URI: https://plugincy.com/
 * Text Domain: multi-location-product-and-inventory-management
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 4.0
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) exit;

if (!defined('MULTI_LOCATION_PLUGIN_URL')) {
    define('MULTI_LOCATION_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('MULTI_LOCATION_PLUGIN_BASE_NAME')) {
    define('MULTI_LOCATION_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
}

if (!defined('mulopimfwc_VERSION')) {
    define("mulopimfwc_VERSION", "1.0.7.5");
}


if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>' . esc_html_e('Location Wise Products requires WooCommerce to be installed and active.', 'multi-location-product-and-inventory-management') . '</p></div>';
    });
    return;
}

if (!function_exists('mulopimfwc_get_values')) {

    global $mulopimfwc_locations, $mulopimfwc_allowed_tags, $mulopimfwc_options;

    function mulopimfwc_get_values()
    {
        global $mulopimfwc_locations, $mulopimfwc_allowed_tags, $mulopimfwc_options;

        // Check if taxonomy exists
        if (!taxonomy_exists('mulopimfwc_store_location')) {
            error_log('Taxonomy mulopimfwc_store_location does not exist');
            return;
        }

        $mulopimfwc_locations = get_terms([
            'taxonomy' => 'mulopimfwc_store_location',
            'hide_empty' => false,
        ]);

        $mulopimfwc_options = get_option('mulopimfwc_display_options') ?:
            [
                'enable_location_stock' => 'on',
                'enable_location_price' => 'on',
                'enable_location_backorder' => 'on',
                'enable_all_locations' => 'on',
                'location_change_notification' => 'on',
                'display_location_single_product' => 'on',
                'allow_data_share' => 'on',
                'strict_filtering' => 'enabled',
                'location_require_selection' => 'on',

            ];
        $mulopimfwc_allowed_tags = array(
            'a' => array(
                'href' => array(),
                'title' => array(),
                'class' => array(),
                'target' => array(), // Allow target attribute for links
                'style' => array(),
                'id' => array(),
            ),
            'strong' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ),
            'em' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ),
            'li' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ),
            'div' => array(
                'class' => array(),
                'id' => array(), // Allow id for divs
                'style' => array(),
            ),
            'img' => array(
                'src' => array(),
                'alt' => array(),
                'class' => array(),
                'width' => array(), // Allow width attribute
                'height' => array(), // Allow height attribute
                'style' => array(),
                'id' => array(),
                'data-src' => array(),
            ),
            'h1' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ), // Allow h1
            'h2' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ),
            'h3' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ), // Allow h3
            'h4' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ), // Allow h4
            'h5' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ), // Allow h5
            'h6' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ), // Allow h6
            'span' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ),
            'p' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ),
            'br' => array(
                'style' => array(),
                'class' => array(),
            ), // Allow line breaks
            'blockquote' => array(
                'cite' => array(), // Allow cite attribute for blockquotes
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ),
            'table' => array(
                'class' => array(),
                'style' => array(), // Allow inline styles
                'id' => array(),
            ),
            'tr' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ),
            'td' => array(
                'class' => array(),
                'colspan' => array(), // Allow colspan attribute
                'rowspan' => array(), // Allow rowspan attribute
                'style' => array(),
                'id' => array(),
            ),
            'th' => array(
                'class' => array(),
                'colspan' => array(),
                'rowspan' => array(),
                'style' => array(),
                'id' => array(),
            ),
            'ul' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ), // Allow unordered lists
            'ol' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
            ), // Allow ordered lists
            'script' => array(
                'type' => array(),
                'src' => array(),
                'async' => array(),
                'defer' => array(),
                'charset' => array(),
            ), // Be cautious with scripts

            // Style and Meta Tags
            'style' => array(
                'type' => array(),
                'media' => array(),
                'scoped' => array(),
            ),
            'link' => array(
                'rel' => array(),
                'href' => array(),
                'type' => array(),
                'media' => array(),
                'sizes' => array(),
                'hreflang' => array(),
                'crossorigin' => array(),
            ),
            'meta' => array(
                'name' => array(),
                'content' => array(),
                'http-equiv' => array(),
                'charset' => array(),
                'property' => array(), // For Open Graph
            ),
            'title' => array(),
            'base' => array(
                'href' => array(),
                'target' => array(),
            ),

            // Document Structure
            'html' => array(
                'lang' => array(),
                'dir' => array(),
                'class' => array(),
                'style' => array(),
            ),
            'head' => array(),
            'body' => array(
                'class' => array(),
                'id' => array(),
                'style' => array(),
                'onload' => array(),
            ),
            'header' => array(
                'class' => array(),
                'id' => array(),
                'style' => array(),
                'role' => array(),
            ),
            'footer' => array(
                'class' => array(),
                'id' => array(),
                'style' => array(),
                'role' => array(),
            ),
            'nav' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
                'role' => array(),
            ),
            'main' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
                'role' => array(),
            ),
            'section' => array(
                'class' => array(),
                'id' => array(),
                'style' => array(),
                'role' => array(),
            ),
            'article' => array(
                'class' => array(),
                'style' => array(),
                'id' => array(),
                'role' => array(),
            ),
            'aside' => array(
                'class' => array(),
                'id' => array(),
                'style' => array(),
                'role' => array(),
            ),

            // Form Elements
            'form' => array(
                'action' => array(),
                'method' => array(),
                'style' => array(),
                'enctype' => array(),
                'target' => array(),
                'name' => array(),
                'id' => array(),
                'class' => array(),
                'autocomplete' => array(),
                'novalidate' => array(),
                'data-mobile-style' => array(),
                'data-product_show_settings' => array(),
                'data-product_selector' => array(),
                'data-pagination_selector' => array(),
                'data-layout' => array(),
            ),
            'input' => array(
                'type' => array(),
                'name' => array(),
                'value' => array(),
                'style' => array(),
                'placeholder' => array(),
                'id' => array(),
                'class' => array(),
                'required' => array(),
                'disabled' => array(),
                'readonly' => array(),
                'checked' => array(),
                'selected' => array(),
                'multiple' => array(),
                'min' => array(),
                'max' => array(),
                'step' => array(),
                'pattern' => array(),
                'maxlength' => array(),
                'minlength' => array(),
                'size' => array(),
                'autocomplete' => array(),
                'autofocus' => array(),
                'form' => array(),
                'formaction' => array(),
                'formmethod' => array(),
                'formtarget' => array(),
                'formnovalidate' => array(),
                'accept' => array(),
                'alt' => array(),
                'src' => array(),
                'width' => array(),
                'height' => array(),
            ),
            'textarea' => array(
                'name' => array(),
                'id' => array(),
                'class' => array(),
                'placeholder' => array(),
                'rows' => array(),
                'style' => array(),
                'cols' => array(),
                'required' => array(),
                'disabled' => array(),
                'readonly' => array(),
                'maxlength' => array(),
                'minlength' => array(),
                'wrap' => array(),
                'autocomplete' => array(),
                'autofocus' => array(),
                'form' => array(),
            ),
            'select' => array(
                'name' => array(),
                'id' => array(),
                'class' => array(),
                'multiple' => array(),
                'size' => array(),
                'required' => array(),
                'style' => array(),
                'disabled' => array(),
                'autofocus' => array(),
                'form' => array(),
            ),
            'option' => array(
                'value' => array(),
                'selected' => array(),
                'style' => array(),
                'disabled' => array(),
                'label' => array(),
            ),
            'optgroup' => array(
                'label' => array(),
                'style' => array(),
                'disabled' => array(),
            ),
            'button' => array(
                'type' => array(),
                'name' => array(),
                'value' => array(),
                'id' => array(),
                'style' => array(),
                'class' => array(),
                'disabled' => array(),
                'form' => array(),
                'formaction' => array(),
                'formmethod' => array(),
                'formtarget' => array(),
                'formnovalidate' => array(),
                'autofocus' => array(),
            ),
            'label' => array(
                'for' => array(),
                'form' => array(),
                'id' => array(),
                'class' => array(),
                'style' => array(),
            ),
            'fieldset' => array(
                'disabled' => array(),
                'form' => array(),
                'style' => array(),
                'name' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'legend' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'datalist' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'output' => array(
                'for' => array(),
                'form' => array(),
                'name' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'plugrogress' => array(
                'value' => array(),
                'max' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'meter' => array(
                'value' => array(),
                'min' => array(),
                'max' => array(),
                'low' => array(),
                'style' => array(),
                'high' => array(),
                'optimum' => array(),
                'id' => array(),
                'class' => array(),
            ),

            // Media Elements
            'audio' => array(
                'src' => array(),
                'controls' => array(),
                'autoplay' => array(),
                'style' => array(),
                'loop' => array(),
                'muted' => array(),
                'preload' => array(),
                'crossorigin' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'video' => array(
                'src' => array(),
                'controls' => array(),
                'autoplay' => array(),
                'loop' => array(),
                'muted' => array(),
                'preload' => array(),
                'style' => array(),
                'poster' => array(),
                'width' => array(),
                'height' => array(),
                'crossorigin' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'source' => array(
                'src' => array(),
                'style' => array(),
                'type' => array(),
                'media' => array(),
                'sizes' => array(),
                'srcset' => array(),
            ),
            'track' => array(
                'kind' => array(),
                'src' => array(),
                'style' => array(),
                'srclang' => array(),
                'label' => array(),
                'default' => array(),
            ),
            'embed' => array(
                'src' => array(),
                'type' => array(),
                'width' => array(),
                'height' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'object' => array(
                'data' => array(),
                'type' => array(),
                'style' => array(),
                'name' => array(),
                'width' => array(),
                'height' => array(),
                'form' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'param' => array(
                'name' => array(),
                'value' => array(),
                'style' => array(),
            ),
            'iframe' => array(
                'src' => array(),
                'srcdoc' => array(),
                'name' => array(),
                'width' => array(),
                'style' => array(),
                'height' => array(),
                'sandbox' => array(),
                'allow' => array(),
                'allowfullscreen' => array(),
                'loading' => array(),
                'id' => array(),
                'class' => array(),
            ),

            // Interactive Elements
            'details' => array(
                'open' => array(),
                'id' => array(),
                'class' => array(),
                'style' => array(),
            ),
            'summary' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'dialog' => array(
                'open' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),

            // Text Content Elements
            'pre' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'code' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'kbd' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'samp' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'var' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'small' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'sub' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'sup' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'mark' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'del' => array(
                'datetime' => array(),
                'style' => array(),
                'cite' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'ins' => array(
                'datetime' => array(),
                'style' => array(),
                'cite' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'q' => array(
                'cite' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'cite' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'abbr' => array(
                'title' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'dfn' => array(
                'title' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'time' => array(
                'datetime' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'data' => array(
                'value' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'address' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),

            // Table Elements (Enhanced)
            'caption' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'thead' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'tbody' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'tfoot' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'colgroup' => array(
                'span' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),
            'col' => array(
                'span' => array(),
                'style' => array(),
                'id' => array(),
                'class' => array(),
            ),

            // Definition Lists
            'dl' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'dt' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'dd' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),

            // Ruby Annotations
            'ruby' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'rt' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'rp' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),

            // Bidirectional Text
            'bdi' => array(
                'dir' => array(),
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'bdo' => array(
                'dir' => array(),
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),

            // Web Components
            'template' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'slot' => array(
                'name' => array(),
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),

            // Math and Science
            'math' => array(
                'display' => array(),
                'xmlns' => array(),
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),

            // Canvas and Graphics
            'canvas' => array(
                'width' => array(),
                'height' => array(),
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),

            // Obsolete but sometimes needed
            'center' => array(
                'id' => array(),
                'style' => array(),
                'class' => array(),
            ),
            'font' => array(
                'size' => array(),
                'style' => array(),
                'color' => array(),
                'face' => array(),
                'id' => array(),
                'class' => array(),
            ),

            // SVG Tags
            'svg' => array(
                'xmlns' => array(),
                'viewbox' => array(), // lowercase
                'viewBox' => array(), // camelCase (standard)
                'width' => array(),
                'height' => array(),
                'class' => array(),
                'id' => array(),
                'style' => array(),
                'preserveAspectRatio' => array(),
                'version' => array(),
                'x' => array(),
                'y' => array(),
                'fill' => array(),
            ),
            'g' => array(
                'class' => array(),
                'id' => array(),
                'transform' => array(),
                'style' => array(),
                'fill' => array(),
                'stroke' => array(),
                'opacity' => array(),
            ),
            'path' => array(
                'd' => array(),
                'class' => array(),
                'id' => array(),
                'fill' => array(),
                'stroke' => array(),
                'stroke-width' => array(),
                'stroke-dasharray' => array(),
                'stroke-linecap' => array(),
                'stroke-linejoin' => array(),
                'opacity' => array(),
                'transform' => array(),
                'style' => array(),
            ),
            'circle' => array(
                'cx' => array(),
                'cy' => array(),
                'r' => array(),
                'class' => array(),
                'id' => array(),
                'fill' => array(),
                'stroke' => array(),
                'stroke-width' => array(),
                'opacity' => array(),
                'transform' => array(),
                'style' => array(),
            ),
            'ellipse' => array(
                'cx' => array(),
                'cy' => array(),
                'rx' => array(),
                'ry' => array(),
                'class' => array(),
                'id' => array(),
                'fill' => array(),
                'stroke' => array(),
                'stroke-width' => array(),
                'opacity' => array(),
                'transform' => array(),
                'style' => array(),
            ),
            'rect' => array(
                'x' => array(),
                'y' => array(),
                'width' => array(),
                'height' => array(),
                'rx' => array(),
                'ry' => array(),
                'class' => array(),
                'id' => array(),
                'fill' => array(),
                'stroke' => array(),
                'stroke-width' => array(),
                'opacity' => array(),
                'transform' => array(),
                'style' => array(),
            ),
            'line' => array(
                'x1' => array(),
                'y1' => array(),
                'x2' => array(),
                'y2' => array(),
                'class' => array(),
                'id' => array(),
                'stroke' => array(),
                'stroke-width' => array(),
                'stroke-dasharray' => array(),
                'stroke-linecap' => array(),
                'opacity' => array(),
                'transform' => array(),
                'style' => array(),
            ),
            'polyline' => array(
                'points' => array(),
                'class' => array(),
                'id' => array(),
                'fill' => array(),
                'stroke' => array(),
                'stroke-width' => array(),
                'stroke-dasharray' => array(),
                'stroke-linecap' => array(),
                'stroke-linejoin' => array(),
                'opacity' => array(),
                'transform' => array(),
                'style' => array(),
            ),
            'polygon' => array(
                'points' => array(),
                'class' => array(),
                'id' => array(),
                'fill' => array(),
                'stroke' => array(),
                'stroke-width' => array(),
                'stroke-dasharray' => array(),
                'stroke-linecap' => array(),
                'stroke-linejoin' => array(),
                'opacity' => array(),
                'transform' => array(),
                'style' => array(),
            ),
            'text' => array(
                'x' => array(),
                'y' => array(),
                'dx' => array(),
                'dy' => array(),
                'class' => array(),
                'id' => array(),
                'fill' => array(),
                'stroke' => array(),
                'font-family' => array(),
                'font-size' => array(),
                'font-weight' => array(),
                'text-anchor' => array(),
                'dominant-baseline' => array(),
                'opacity' => array(),
                'transform' => array(),
                'style' => array(),
            ),
            'tspan' => array(
                'x' => array(),
                'y' => array(),
                'dx' => array(),
                'dy' => array(),
                'class' => array(),
                'id' => array(),
                'fill' => array(),
                'stroke' => array(),
                'font-family' => array(),
                'font-size' => array(),
                'font-weight' => array(),
                'text-anchor' => array(),
                'dominant-baseline' => array(),
                'opacity' => array(),
                'style' => array(),
            ),
            'use' => array(
                'href' => array(),
                'xlink:href' => array(),
                'x' => array(),
                'y' => array(),
                'width' => array(),
                'height' => array(),
                'class' => array(),
                'id' => array(),
                'transform' => array(),
                'style' => array(),
            ),
            'defs' => array(
                'class' => array(),
                'id' => array(),
                'style' => array(),
            ),
            'symbol' => array(
                'id' => array(),
                'viewBox' => array(),
                'class' => array(),
                'style' => array(),
                'preserveAspectRatio' => array(),
            ),
            'marker' => array(
                'id' => array(),
                'markerWidth' => array(),
                'markerHeight' => array(),
                'refX' => array(),
                'refY' => array(),
                'style' => array(),
                'orient' => array(),
                'markerUnits' => array(),
                'class' => array(),
            ),
            'linearGradient' => array(
                'id' => array(),
                'x1' => array(),
                'y1' => array(),
                'style' => array(),
                'x2' => array(),
                'y2' => array(),
                'gradientUnits' => array(),
                'gradientTransform' => array(),
                'class' => array(),
            ),
            'lineargradient' => array(
                'id' => array(),
                'x1' => array(),
                'y1' => array(),
                'style' => array(),
                'x2' => array(),
                'y2' => array(),
                'gradientUnits' => array(),
                'gradientTransform' => array(),
                'class' => array(),
            ),
            'radialGradient' => array(
                'id' => array(),
                'cx' => array(),
                'cy' => array(),
                'style' => array(),
                'r' => array(),
                'fx' => array(),
                'fy' => array(),
                'gradientUnits' => array(),
                'gradientTransform' => array(),
                'class' => array(),
            ),
            'radialgradient' => array(
                'id' => array(),
                'cx' => array(),
                'cy' => array(),
                'r' => array(),
                'style' => array(),
                'fx' => array(),
                'fy' => array(),
                'gradientUnits' => array(),
                'gradientTransform' => array(),
                'class' => array(),
            ),
            'stop' => array(
                'offset' => array(),
                'stop-color' => array(),
                'stop-opacity' => array(),
                'class' => array(),
                'style' => array(),
            ),
            'clipPath' => array(
                'id' => array(),
                'class' => array(),
                'style' => array(),
                'clipPathUnits' => array(),
            ),
            'mask' => array(
                'id' => array(),
                'class' => array(),
                'style' => array(),
                'maskUnits' => array(),
                'maskContentUnits' => array(),
                'x' => array(),
                'y' => array(),
                'width' => array(),
                'height' => array(),
            ),
            'pattern' => array(
                'id' => array(),
                'x' => array(),
                'y' => array(),
                'width' => array(),
                'style' => array(),
                'height' => array(),
                'patternUnits' => array(),
                'patternContentUnits' => array(),
                'patternTransform' => array(),
                'viewBox' => array(),
                'class' => array(),
            ),
            'filter' => array(
                'id' => array(),
                'x' => array(),
                'y' => array(),
                'style' => array(),
                'width' => array(),
                'height' => array(),
                'filterUnits' => array(),
                'primitiveUnits' => array(),
                'class' => array(),
            ),
            'feGaussianBlur' => array(
                'in' => array(),
                'style' => array(),
                'stdDeviation' => array(),
                'result' => array(),
            ),
            'feOffset' => array(
                'in' => array(),
                'dx' => array(),
                'style' => array(),
                'dy' => array(),
                'result' => array(),
            ),
            'feDropShadow' => array(
                'dx' => array(),
                'dy' => array(),
                'style' => array(),
                'stdDeviation' => array(),
                'flood-color' => array(),
                'flood-opacity' => array(),
            ),
            'image' => array(
                'x' => array(),
                'y' => array(),
                'width' => array(),
                'style' => array(),
                'height' => array(),
                'href' => array(),
                'xlink:href' => array(),
                'preserveAspectRatio' => array(),
                'class' => array(),
                'id' => array(),
                'opacity' => array(),
                'transform' => array(),
            ),
        );
    }

    add_action('init', 'mulopimfwc_get_values', 20);

    require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
    require_once plugin_dir_path(__FILE__) . 'admin/dashboard.php';
    require_once plugin_dir_path(__FILE__) . 'admin/license-page.php';
    require_once plugin_dir_path(__FILE__) . 'admin/stock-central.php';
    require_once plugin_dir_path(__FILE__) . 'admin/admin.php';
    require_once plugin_dir_path(__FILE__) . 'includes/product-display.php';
    require_once plugin_dir_path(__FILE__) . 'admin/location-based-everythings.php';
    require_once plugin_dir_path(__FILE__) . 'admin/location-managers.php';
    require_once plugin_dir_path(__FILE__) . 'includes/product-location-selector-single.php';
    require_once plugin_dir_path(__FILE__) . 'includes/customer-location-insights.php';

    class mulopimfwc_Location_Wise_Products
    {

        private $cart_items_cache = null;
        
        /**
         * Request-level cache for product location term relationships
         * Prevents N+1 query problems by batch loading term relationships
         * 
         * @var array|null Cache structure: [product_id => [location_slug1, location_slug2, ...]]
         */
        private static $product_locations_cache = null;
        
        /**
         * Track which product IDs have been batch loaded to avoid redundant queries
         * 
         * @var array Array of product IDs that have been loaded
         */
        private static $batch_loaded_products = [];
        
        /**
         * Request-level cache for display options
         * Prevents repeated get_option() calls that hit the database
         * 
         * @var array|null Cached display options
         */
        private static $cached_display_options = null;
        
        /**
         * Request-level cache for location terms
         * Prevents repeated get_term_by() calls that hit the database
         * Cache structure: [location_slug => WP_Term object]
         * 
         * @var array Cached location terms
         */
        private static $cached_location_terms = [];
        
        public function __construct()
        {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
            // Clear cache when display options are updated
            add_action('update_option_mulopimfwc_display_options', [__CLASS__, 'clear_display_options_cache']);
            add_action('add_option_mulopimfwc_display_options', [__CLASS__, 'clear_display_options_cache']);
            add_action('delete_option_mulopimfwc_display_options', [__CLASS__, 'clear_display_options_cache']);
            add_action('pre_get_posts', [$this, 'filter_products_by_location']);
            add_filter('posts_clauses', [$this, 'filter_products_by_location_clauses'], 10, 2);
            add_filter('woocommerce_shortcode_products_query', [$this, 'filter_shortcode_products']);
            add_filter('woocommerce_products_widget_query_args', [$this, 'filter_widget_products']);
            add_filter('woocommerce_related_products_args', [$this, 'filter_related_products']);
            add_action('init', [$this, 'clear_cart_on_location_change']);

            add_shortcode('mulopimfwc_store_location_selector', [$this, 'location_selector_shortcode']);

            add_filter('the_title', [$this, 'add_location_to_product_title'], 10, 2);
            add_filter('woocommerce_product_title', [$this, 'add_location_to_wc_product_title'], 10, 2);
            global $MULOPIMFWC_Admin;
            $MULOPIMFWC_Admin = new MULOPIMFWC_Admin();
            add_filter('woocommerce_related_products', [$this, 'filter_related_products_by_location'], 10, 3);
            add_filter('woocommerce_recently_viewed_products_widget_query_args', [$this, 'filter_widget_products_by_location']);
            add_filter('woocommerce_cross_sells_products', [$this, 'filter_cross_sells_by_location'], 10, 1);
            add_filter('woocommerce_upsells_products', [$this, 'filter_upsells_by_location'], 10, 2);
            add_filter('woocommerce_blocks_product_grid_item_html', [$this, 'filter_product_blocks'], 10, 3);
            add_filter('woocommerce_json_search_found_products', [$this, 'filter_ajax_searched_products']);
            add_filter('woocommerce_rest_product_object_query', [$this, 'filter_rest_api_products'], 10, 2);
            add_filter('woocommerce_rest_prepare_product_object', [$this, 'modify_product_rest_response'], 10, 3);
            add_filter('woocommerce_cart_contents', [$this, 'filter_cart_contents'], 10, 1);
            add_action('template_redirect', [$this, 'filter_recently_viewed_products']);
            add_action('template_redirect', [$this, 'handle_unavailable_single_product'], 5);
            add_filter('woocommerce_add_to_cart_validation', [$this, 'validate_location_selection_before_add_to_cart'], 10, 5);

            add_action('wp_ajax_clear_cart', [$this, 'clear_cart']);
            add_action('wp_ajax_nopriv_clear_cart', [$this, 'clear_cart']);

            add_action('wp_ajax_check_cart_products', [$this, 'check_cart_products']);
            add_action('wp_ajax_nopriv_check_cart_products', [$this, 'check_cart_products']);
            add_action('wp_ajax_mulopimfwc_switch_location', [$this, 'ajax_switch_location']);
            add_action('wp_ajax_nopriv_mulopimfwc_switch_location', [$this, 'ajax_switch_location']);

            add_action('admin_enqueue_scripts', [$this, 'custom_admin_styles']);

            // add settings button after deactivate button in plugins page

            add_action('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link']);
            add_action('admin_init', [$this, 'add_settings_link']);

            // Save location to order meta
            add_action('woocommerce_thankyou', array($this, 'save_location_to_order_meta'), 10, 2);

            // Use these specific hooks for HPOS orders table
            add_action('woocommerce_order_list_table_restrict_manage_orders', array($this, 'add_store_location_filter'));
            add_filter('woocommerce_order_query_args', array($this, 'filter_orders_by_location'));

            require_once plugin_dir_path(__FILE__) . 'includes/stock-price-backorder-manage.php';

            add_action('wp_ajax_update_product_location_status', [$this, 'cymulopimfwc_update_product_location_status']);
            add_action('wp_ajax_get_available_locations', [$this, 'cymulopimfwc_get_available_locations']);
            add_action('wp_ajax_save_product_locations', [$this, 'cymulopimfwc_save_product_locations']);
            add_action('wp_ajax_get_product_quick_edit_data', [$this, 'cymulopimfwc_get_product_quick_edit_data']);
            add_action('wp_ajax_save_product_quick_edit_data', [$this, 'cymulopimfwc_save_product_quick_edit_data']);
            add_action('wp_ajax_remove_product_location', [$this, 'cymulopimfwc_remove_product_location']);


            add_action('admin_enqueue_scripts', [$this, 'cymulopimfwc_enqueue_admin_scripts']);

            add_filter('plugin_row_meta', array(__CLASS__, 'plugin_row_meta'), 10, 2);
        }

        /**
         * Clear cart items cache
         */
        public function clear_cart_cache()
        {
            $this->cart_items_cache = null;
        }


        /**
         * Prevent adding location-bound products to the cart when no location is selected.
         *
         * @param bool $passed Whether WooCommerce validations passed.
         * @param int $product_id Product ID being added.
         * @param int $quantity Quantity requested.
         * @param int $variation_id Variation ID if applicable.
         * @param array $variations Variation attributes (unused).
         * @return bool
         */
        public function validate_location_selection_before_add_to_cart($passed, $product_id, $quantity, $variation_id = 0, $variations = [])
        {
            if (!$passed) {
                return $passed;
            }

            if (is_admin() && !wp_doing_ajax()) {
                return $passed;
            }

            global $mulopimfwc_options;
            $options = is_array($mulopimfwc_options ?? null)
                ? $mulopimfwc_options
                : get_option('mulopimfwc_display_options', []);

            $require_selection = isset($options['location_require_selection']) ? $options['location_require_selection'] : 'off';

            if ($require_selection !== 'on') {
                return $passed;
            }

            $primary_product = $variation_id ? $variation_id : $product_id;

            if (!$this->product_has_assigned_locations($primary_product, $product_id)) {
                return $passed;
            }

            $selected_location = isset($_COOKIE['mulopimfwc_store_location']) ? sanitize_text_field(wp_unslash($_COOKIE['mulopimfwc_store_location'])) : '';

            if ($selected_location === '' || $selected_location === 'all-products') {
                if (function_exists('wc_add_notice')) {
                    wc_add_notice(__('Please select a store location before adding this product to your cart.', 'multi-location-product-and-inventory-management'), 'error');
                }
                return false;
            }

            return $passed;
        }

        /**
         * Determine if a product (or its parent) is assigned to any store location.
         *
         * @param int $product_id Primary product/variation ID to inspect.
         * @param int $fallback_product_id Optional fallback (usually parent product).
         * @return bool
         */
        private function product_has_assigned_locations($product_id, $fallback_product_id = 0)
        {
            $product_ids = array_unique(array_filter(array_map('absint', [$product_id, $fallback_product_id])));

            foreach ($product_ids as $id) {
                $terms = wp_get_object_terms($id, 'mulopimfwc_store_location', ['fields' => 'ids']);

                if (!is_wp_error($terms) && !empty($terms)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Get available locations for a product via AJAX
         */
        public function cymulopimfwc_get_available_locations()
        {
            global $mulopimfwc_locations;
            // Check nonce
            check_ajax_referer('location_wise_products_nonce', 'security');

            // Check permissions
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'multi-location-product-and-inventory-management')]);
            }

            // Get product ID
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $location_selected = array_map('rawurldecode', wp_get_object_terms($product_id, 'mulopimfwc_store_location', ['fields' => 'slugs']));
            if (!$product_id) {
                wp_send_json_error(['message' => __('Invalid product ID.', 'multi-location-product-and-inventory-management')]);
            }

            if (is_wp_error($mulopimfwc_locations)) {
                wp_send_json_error(['message' => $mulopimfwc_locations->get_error_message()]);
            }

            // Format locations for output
            $location_data = [];
            foreach ($mulopimfwc_locations as $location) {
                $location_data[] = [
                    'id' => $location->term_id,
                    'name' => $location->name,
                    'parent' => $location->parent,
                    'selected' => in_array(rawurldecode($location->slug), $location_selected),
                ];
            }

            wp_send_json_success(['locations' => $location_data]);
        }

        /**
         * Save product locations via AJAX
         */
        public function cymulopimfwc_save_product_locations()
        {
            // Check nonce
            check_ajax_referer('location_wise_products_nonce', 'security');

            // Check permissions
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'multi-location-product-and-inventory-management')]);
            }

            // Get product ID and location IDs
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $location_ids = isset($_POST['location_ids']) ? array_map('intval', (array) $_POST['location_ids']) : [];

            if (!$product_id) {
                wp_send_json_error(['message' => __('Invalid product ID.', 'multi-location-product-and-inventory-management')]);
            }

            // Set product locations
            wp_set_object_terms($product_id, $location_ids, 'mulopimfwc_store_location');

            wp_send_json_success([
                'message' => __('Product locations saved successfully.', 'multi-location-product-and-inventory-management'),
            ]);
        }

        /**
         * Update product location status via AJAX
         */
        public function cymulopimfwc_update_product_location_status()
        {
            // Check nonce
            check_ajax_referer('location_wise_products_nonce', 'security');

            // Check permissions
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'multi-location-product-and-inventory-management')]);
            }

            // Get parameters
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
            $action = isset($_POST['status_action']) ? sanitize_text_field(wp_unslash($_POST['status_action'])) : '';

            if (!$product_id || !$location_id || !in_array($action, ['activate', 'deactivate'])) {
                wp_send_json_error(['message' => __('Invalid parameters.', 'multi-location-product-and-inventory-management')]);
            }

            // Update location status
            if ($action === 'activate') {
                // Activate location - remove disabled meta
                delete_post_meta($product_id, '_location_disabled_' . $location_id);
                $message = __('Location activated successfully.', 'multi-location-product-and-inventory-management');
            } else {
                // Deactivate location - remove location from product
                // Remove the taxonomy term relationship
                wp_remove_object_terms($product_id, $location_id, 'mulopimfwc_store_location');

                // Clean up location-specific meta data
                delete_post_meta($product_id, '_location_disabled_' . $location_id);
                delete_post_meta($product_id, '_location_stock_' . $location_id);
                delete_post_meta($product_id, '_location_regular_price_' . $location_id);
                delete_post_meta($product_id, '_location_sale_price_' . $location_id);
                delete_post_meta($product_id, '_location_backorders_' . $location_id);

                // Also clean up for variations if it's a variable product
                $product = wc_get_product($product_id);
                if ($product && $product->is_type('variable')) {
                    $variation_ids = $product->get_children();
                    foreach ($variation_ids as $variation_id) {
                        delete_post_meta($variation_id, '_location_stock_' . $location_id);
                        delete_post_meta($variation_id, '_location_regular_price_' . $location_id);
                        delete_post_meta($variation_id, '_location_sale_price_' . $location_id);
                        delete_post_meta($variation_id, '_location_backorders_' . $location_id);
                    }
                }

                $message = __('Location removed from product successfully.', 'multi-location-product-and-inventory-management');
            }

            wp_send_json_success(['message' => $message]);
        }

        /**
         * Get product data for quick edit popup via AJAX
         */
        public function cymulopimfwc_get_product_quick_edit_data()
        {
            // Check nonce
            check_ajax_referer('location_wise_products_nonce', 'security');

            // Check permissions
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'multi-location-product-and-inventory-management')]);
            }

            // Get product ID
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            if (!$product_id) {
                wp_send_json_error(['message' => __('Invalid product ID.', 'multi-location-product-and-inventory-management')]);
            }

            $product = wc_get_product($product_id);
            if (!$product) {
                wp_send_json_error(['message' => __('Product not found.', 'multi-location-product-and-inventory-management')]);
            }

            global $mulopimfwc_locations;
            $product_type = $product->get_type();
            $data = [
                'product_id' => $product_id,
                'product_name' => $product->get_name(),
                'product_type' => $product_type,
                'default' => [],
                'locations' => [],
                'variations' => [],
            ];

            // Get default product data
            $data['default'] = [
                'stock_quantity' => $product->get_stock_quantity(),
                'regular_price' => $product->get_regular_price(),
                'sale_price' => $product->get_sale_price(),
                'backorders' => $product->get_backorders(),
                'purchase_price' => get_post_meta($product_id, '_purchase_price', true),
                'purchase_quantity' => get_post_meta($product_id, '_purchase_quantity', true),
            ];

            // Get location data - batch meta queries for better performance
            if (!is_wp_error($mulopimfwc_locations) && !empty($mulopimfwc_locations)) {
                $location_ids = array_map(function ($loc) {
                    return $loc->term_id;
                }, $mulopimfwc_locations);

                // Batch fetch all meta keys at once
                $meta_keys = [];
                foreach ($location_ids as $loc_id) {
                    $meta_keys[] = '_location_stock_' . $loc_id;
                    $meta_keys[] = '_location_regular_price_' . $loc_id;
                    $meta_keys[] = '_location_sale_price_' . $loc_id;
                    $meta_keys[] = '_location_backorders_' . $loc_id;
                }

                // Get all meta in one query - optimized batch query
                global $wpdb;
                if (!empty($meta_keys)) {
                    $escaped_keys = array_map(function ($key) use ($wpdb) {
                        return $wpdb->prepare('%s', $key);
                    }, $meta_keys);
                    $meta_query = $wpdb->prepare(
                        "SELECT meta_key, meta_value FROM {$wpdb->postmeta} 
                        WHERE post_id = %d AND meta_key IN (" . implode(',', $escaped_keys) . ")",
                        $product_id
                    );
                    $meta_results = $wpdb->get_results($meta_query);
                    $meta_values = [];
                    foreach ($meta_results as $row) {
                        $meta_values[$row->meta_key] = $row->meta_value;
                    }
                } else {
                    $meta_values = [];
                }

                // Build location data from cached meta
                foreach ($mulopimfwc_locations as $location) {
                    $location_data = [
                        'id' => $location->term_id,
                        'name' => $location->name,
                        'stock' => isset($meta_values['_location_stock_' . $location->term_id]) ? $meta_values['_location_stock_' . $location->term_id] : '',
                        'regular_price' => isset($meta_values['_location_regular_price_' . $location->term_id]) ? $meta_values['_location_regular_price_' . $location->term_id] : '',
                        'sale_price' => isset($meta_values['_location_sale_price_' . $location->term_id]) ? $meta_values['_location_sale_price_' . $location->term_id] : '',
                        'backorders' => isset($meta_values['_location_backorders_' . $location->term_id]) ? $meta_values['_location_backorders_' . $location->term_id] : '',
                    ];
                    $data['locations'][] = $location_data;
                }
            }

            // Get variation data for variable products - optimized
            if ($product_type === 'variable') {
                // Get variation IDs directly without loading full objects
                $variation_ids = $product->get_children();

                if (!empty($variation_ids)) {
                    // Get location IDs if available
                    $loc_ids_for_variations = [];
                    if (!is_wp_error($mulopimfwc_locations) && !empty($mulopimfwc_locations)) {
                        $loc_ids_for_variations = array_map(function ($loc) {
                            return $loc->term_id;
                        }, $mulopimfwc_locations);
                    }

                    // Batch load all variation meta at once
                    global $wpdb;
                    $variation_meta_keys = ['_stock', '_regular_price', '_sale_price', '_backorders', '_purchase_price'];
                    foreach ($loc_ids_for_variations as $loc_id) {
                        $variation_meta_keys[] = '_location_stock_' . $loc_id;
                        $variation_meta_keys[] = '_location_regular_price_' . $loc_id;
                        $variation_meta_keys[] = '_location_sale_price_' . $loc_id;
                        $variation_meta_keys[] = '_location_backorders_' . $loc_id;
                    }

                    if (!empty($variation_meta_keys)) {
                        $id_placeholders = implode(',', array_map('intval', $variation_ids));
                        $prepared_meta_keys = array_map(function ($key) use ($wpdb) {
                            return $wpdb->prepare('%s', $key);
                        }, $variation_meta_keys);
                        $meta_query = $wpdb->prepare(
                            "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} 
                            WHERE post_id IN ($id_placeholders) AND meta_key IN (" . implode(',', $prepared_meta_keys) . ")"
                        );
                        $all_variation_meta = $wpdb->get_results($meta_query);

                        // Organize meta by variation ID
                        $variation_meta_map = [];
                        foreach ($all_variation_meta as $meta) {
                            if (!isset($variation_meta_map[$meta->post_id])) {
                                $variation_meta_map[$meta->post_id] = [];
                            }
                            $variation_meta_map[$meta->post_id][$meta->meta_key] = $meta->meta_value;
                        }
                    } else {
                        $variation_meta_map = [];
                    }

                    // Get variation attributes efficiently
                    foreach ($variation_ids as $variation_id) {
                        $variation = wc_get_product($variation_id);
                        if (!$variation) {
                            continue;
                        }

                        $meta = isset($variation_meta_map[$variation_id]) ? $variation_meta_map[$variation_id] : [];

                        // Get attributes
                        $attributes = [];
                        foreach ($variation->get_attributes() as $key => $value) {
                            $attributes[$key] = $value;
                        }

                        $variation_info = [
                            'id' => $variation_id,
                            'attributes' => $attributes,
                            'default' => [
                                'stock_quantity' => isset($meta['_stock']) ? $meta['_stock'] : $variation->get_stock_quantity(),
                                'regular_price' => isset($meta['_regular_price']) ? $meta['_regular_price'] : $variation->get_regular_price(),
                                'sale_price' => isset($meta['_sale_price']) ? $meta['_sale_price'] : $variation->get_sale_price(),
                                'backorders' => isset($meta['_backorders']) ? $meta['_backorders'] : $variation->get_backorders(),
                                'purchase_price' => isset($meta['_purchase_price']) ? $meta['_purchase_price'] : '',
                            ],
                            'locations' => [],
                        ];

                        // Get location data for variation from cached meta
                        if (!is_wp_error($mulopimfwc_locations) && !empty($mulopimfwc_locations)) {
                            foreach ($mulopimfwc_locations as $location) {
                                $variation_info['locations'][] = [
                                    'id' => $location->term_id,
                                    'name' => $location->name,
                                    'stock' => isset($meta['_location_stock_' . $location->term_id]) ? $meta['_location_stock_' . $location->term_id] : '',
                                    'regular_price' => isset($meta['_location_regular_price_' . $location->term_id]) ? $meta['_location_regular_price_' . $location->term_id] : '',
                                    'sale_price' => isset($meta['_location_sale_price_' . $location->term_id]) ? $meta['_location_sale_price_' . $location->term_id] : '',
                                    'backorders' => isset($meta['_location_backorders_' . $location->term_id]) ? $meta['_location_backorders_' . $location->term_id] : '',
                                ];
                            }
                        }

                        $data['variations'][] = $variation_info;
                    }
                }
            }

            wp_send_json_success($data);
        }

        /**
         * Save product quick edit data via AJAX
         */
        public function cymulopimfwc_save_product_quick_edit_data()
        {
            // Check nonce
            check_ajax_referer('location_wise_products_nonce', 'security');

            // Check permissions
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'multi-location-product-and-inventory-management')]);
            }

            // Get product ID
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            if (!$product_id) {
                wp_send_json_error(['message' => __('Invalid product ID.', 'multi-location-product-and-inventory-management')]);
            }

            $product = wc_get_product($product_id);
            if (!$product) {
                wp_send_json_error(['message' => __('Product not found.', 'multi-location-product-and-inventory-management')]);
            }

            // Save default product data
            if (isset($_POST['default'])) {
                $default = $_POST['default'];
                if (isset($default['stock_quantity'])) {
                    $product->set_stock_quantity(intval($default['stock_quantity']));
                }
                if (isset($default['regular_price'])) {
                    $product->set_regular_price(wc_format_decimal($default['regular_price']));
                }
                if (isset($default['sale_price'])) {
                    $product->set_sale_price(wc_format_decimal($default['sale_price']));
                }
                if (isset($default['backorders'])) {
                    $product->set_backorders(sanitize_text_field($default['backorders']));
                }
                if (isset($default['purchase_price'])) {
                    update_post_meta($product_id, '_purchase_price', wc_format_decimal($default['purchase_price']));
                }
                if (isset($default['purchase_quantity'])) {
                    update_post_meta($product_id, '_purchase_quantity', intval($default['purchase_quantity']));
                }
                $product->save();
            }

            // Save location data
            if (isset($_POST['locations']) && is_array($_POST['locations'])) {
                foreach ($_POST['locations'] as $location_data) {
                    $location_id = isset($location_data['id']) ? intval($location_data['id']) : 0;
                    if (!$location_id) {
                        continue;
                    }

                    $old_stock = get_post_meta($product_id, '_location_stock_' . $location_id, true);

                    if (isset($location_data['stock'])) {
                        $new_stock = intval($location_data['stock']);
                        update_post_meta($product_id, '_location_stock_' . $location_id, $new_stock);
                    }
                    if (isset($location_data['regular_price'])) {
                        update_post_meta($product_id, '_location_regular_price_' . $location_id, wc_format_decimal($location_data['regular_price']));
                    }
                    if (isset($location_data['sale_price'])) {
                        update_post_meta($product_id, '_location_sale_price_' . $location_id, wc_format_decimal($location_data['sale_price']));
                    }
                    if (isset($location_data['backorders'])) {
                        update_post_meta($product_id, '_location_backorders_' . $location_id, sanitize_text_field($location_data['backorders']));
                    }
                }
            }

            // Set assigned locations (queue add/remove)
            if (isset($_POST['location_ids'])) {
                $location_ids = array_map('intval', (array) $_POST['location_ids']);
                wp_set_object_terms($product_id, $location_ids, 'mulopimfwc_store_location');

                $removed_location_ids = isset($_POST['removed_location_ids']) ? array_map('intval', (array) $_POST['removed_location_ids']) : [];
                if (!empty($removed_location_ids)) {
                    $removed_location_ids = array_diff($removed_location_ids, $location_ids);
                    foreach ($removed_location_ids as $location_id) {
                        delete_post_meta($product_id, '_location_stock_' . $location_id);
                        delete_post_meta($product_id, '_location_regular_price_' . $location_id);
                        delete_post_meta($product_id, '_location_sale_price_' . $location_id);
                        delete_post_meta($product_id, '_location_backorders_' . $location_id);
                        delete_post_meta($product_id, '_location_disabled_' . $location_id);

                        if ($product->get_type() === 'variable') {
                            $variation_ids = $product->get_children();
                            foreach ($variation_ids as $variation_id) {
                                delete_post_meta($variation_id, '_location_stock_' . $location_id);
                                delete_post_meta($variation_id, '_location_regular_price_' . $location_id);
                                delete_post_meta($variation_id, '_location_sale_price_' . $location_id);
                                delete_post_meta($variation_id, '_location_backorders_' . $location_id);
                            }
                        }
                    }
                }
            }

            // Save variation data
            if (isset($_POST['variations']) && is_array($_POST['variations'])) {
                foreach ($_POST['variations'] as $variation_data) {
                    $variation_id = isset($variation_data['id']) ? intval($variation_data['id']) : 0;
                    if (!$variation_id) {
                        continue;
                    }

                    $variation = wc_get_product($variation_id);
                    if (!$variation) {
                        continue;
                    }

                    // Save default variation data
                    if (isset($variation_data['default'])) {
                        $default = $variation_data['default'];
                        if (isset($default['stock_quantity'])) {
                            $variation->set_stock_quantity(intval($default['stock_quantity']));
                        }
                        if (isset($default['regular_price'])) {
                            $variation->set_regular_price(wc_format_decimal($default['regular_price']));
                        }
                        if (isset($default['sale_price'])) {
                            $variation->set_sale_price(wc_format_decimal($default['sale_price']));
                        }
                        if (isset($default['backorders'])) {
                            $variation->set_backorders(sanitize_text_field($default['backorders']));
                        }
                        if (isset($default['purchase_price'])) {
                            update_post_meta($variation_id, '_purchase_price', wc_format_decimal($default['purchase_price']));
                        }
                        $variation->save();
                    }

                    // Save location data for variation
                    if (isset($variation_data['locations']) && is_array($variation_data['locations'])) {
                        foreach ($variation_data['locations'] as $location_data) {
                            $location_id = isset($location_data['id']) ? intval($location_data['id']) : 0;
                            if (!$location_id) {
                                continue;
                            }

                            if (isset($location_data['stock'])) {
                                update_post_meta($variation_id, '_location_stock_' . $location_id, intval($location_data['stock']));
                            }
                            if (isset($location_data['regular_price'])) {
                                update_post_meta($variation_id, '_location_regular_price_' . $location_id, wc_format_decimal($location_data['regular_price']));
                            }
                            if (isset($location_data['sale_price'])) {
                                update_post_meta($variation_id, '_location_sale_price_' . $location_id, wc_format_decimal($location_data['sale_price']));
                            }
                            if (isset($location_data['backorders'])) {
                                update_post_meta($variation_id, '_location_backorders_' . $location_id, sanitize_text_field($location_data['backorders']));
                            }
                        }
                    }
                }
            }

            wp_send_json_success(['message' => __('Product data saved successfully.', 'multi-location-product-and-inventory-management')]);
        }

        /**
         * Remove location from product via AJAX
         */
        public function cymulopimfwc_remove_product_location()
        {
            // Check nonce
            check_ajax_referer('location_wise_products_nonce', 'security');

            // Check permissions
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'multi-location-product-and-inventory-management')]);
            }

            // Get product ID and location ID
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;

            if (!$product_id || !$location_id) {
                wp_send_json_error(['message' => __('Invalid parameters.', 'multi-location-product-and-inventory-management')]);
            }

            // Remove location from product
            wp_remove_object_terms($product_id, $location_id, 'mulopimfwc_store_location');

            // Delete location-specific meta
            delete_post_meta($product_id, '_location_stock_' . $location_id);
            delete_post_meta($product_id, '_location_regular_price_' . $location_id);
            delete_post_meta($product_id, '_location_sale_price_' . $location_id);
            delete_post_meta($product_id, '_location_backorders_' . $location_id);
            delete_post_meta($product_id, '_location_disabled_' . $location_id);

            // If variable product, remove from variations too
            $product = wc_get_product($product_id);
            if ($product && $product->get_type() === 'variable') {
                $variation_ids = $product->get_children();
                foreach ($variation_ids as $variation_id) {
                    delete_post_meta($variation_id, '_location_stock_' . $location_id);
                    delete_post_meta($variation_id, '_location_regular_price_' . $location_id);
                    delete_post_meta($variation_id, '_location_sale_price_' . $location_id);
                    delete_post_meta($variation_id, '_location_backorders_' . $location_id);
                }
            }

            wp_send_json_success(['message' => __('Location removed successfully.', 'multi-location-product-and-inventory-management')]);
        }

        /**
         * Enqueue admin scripts
         */
        public function cymulopimfwc_enqueue_admin_scripts($hook)
        {
            // Only on product location page
            // if ($hook !== 'multi-location-product-and-inventory-management-settings') {
            //     return;
            // }

            wp_enqueue_script(
                'mulopimfwc-multi-location-product-and-inventory-managements-admin',
                plugin_dir_url(__FILE__) . 'assets/js/admin.js',
                ['jquery'],
                '1.0.7.5',
                true
            );

            wp_localize_script('mulopimfwc-multi-location-product-and-inventory-managements-admin', 'mulopimfwc_locationWiseProducts', [
                'nonce' => wp_create_nonce('location_wise_products_nonce'),
                'i18n' => [
                    'activate' => __('Activate', 'multi-location-product-and-inventory-management'),
                    'deactivate' => __('Deactivate', 'multi-location-product-and-inventory-management'),
                    'selectLocations' => __('Select Locations', 'multi-location-product-and-inventory-management'),
                    'saveLocations' => __('Save Locations', 'multi-location-product-and-inventory-management'),
                    'ajaxError' => __('An error occurred. Please try again.', 'multi-location-product-and-inventory-management'),
                ],
            ]);

            // Add modal styles
            wp_enqueue_style(
                'mulopimfwc-multi-location-product-and-inventory-managements-admin',
                plugin_dir_url(__FILE__) . 'assets/css/admin.css',
                [],
                '1.0.7.5'
            );
        }

        /**
         * Save location from cookie to order meta
         *
         * @param WC_Order $order Order object
         * @param array $data Order data
         */
        public function save_location_to_order_meta($order_id)
        {
            $location = isset($_COOKIE['mulopimfwc_store_location']) ? sanitize_text_field(wp_unslash($_COOKIE['mulopimfwc_store_location'])) : '';

            if (!empty($location)) {
                $order = wc_get_order($order_id);
                if ($order) {
                    $order->update_meta_data('_store_location', $location);
                    $order->save();
                }
            }
        }
        private function get_all_store_locations()
        {
            global $mulopimfwc_locations;

            if (is_wp_error($mulopimfwc_locations)) {
                return array();
            }

            return wp_list_pluck($mulopimfwc_locations, 'slug');
        }

        /**
         * Add filter dropdown in the WooCommerce orders list table
         */
        public function add_store_location_filter()
        {

            $locations = $this->get_all_store_locations();

            if (!isset($_GET['store_location_filter_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['store_location_filter_nonce'])), 'store_location_filter_nonce')) {
                $selected_location = '';
            } else {
                $selected_location = isset($_GET['mulopimfwc_store_location']) ? sanitize_text_field(wp_unslash($_GET['mulopimfwc_store_location'])) : '';
            }

            // add nonce for security
            wp_nonce_field('store_location_filter_nonce', 'store_location_filter_nonce');

            echo '<select name="mulopimfwc_store_location" id="mulopimfwc_store_location">';
            echo '<option value="">' . esc_html__('All Locations', 'multi-location-product-and-inventory-management') . '</option>';

            foreach ($locations as $location) {
                $selected = ($location === $selected_location) ? 'selected' : '';
                echo '<option value="' . esc_attr($location) . '" ' . esc_attr($selected) . '>' . esc_html(ucfirst(strtolower($location))) . '</option>';
            }

            echo '</select>';
        }

        /**
         * Filter orders by store location
         * 
         * @param array $query_args Query arguments
         * @return array Modified query arguments
         */
        public function filter_orders_by_location($query_args)
        {
            if (!isset($_GET['store_location_filter_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['store_location_filter_nonce'])), 'store_location_filter_nonce')) {
                $selected_location = '';
            } else {
                $selected_location = isset($_GET['mulopimfwc_store_location']) ? sanitize_text_field(wp_unslash($_GET['mulopimfwc_store_location'])) : '';
            }

            if (!empty($selected_location)) {
                $query_args['meta_query'][] = [
                    'key' => '_store_location',
                    'value' => $selected_location,
                    'compare' => '='
                ];
            }

            return $query_args;
        }

        // add_settings_link

        public function add_settings_link($links)
        {
            if (!is_array($links)) {
                $links = [];
            }
            $old_links = $links;
            $links = [];
            $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=multi-location-product-and-inventory-management-settings')) . '">' . esc_html__('Settings', 'multi-location-product-and-inventory-management') . '</a>';
            $create_location = '<a href="' . esc_url(admin_url('edit-tags.php?taxonomy=mulopimfwc_store_location&post_type=product')) . '">' . esc_html__('Manage Locations', 'multi-location-product-and-inventory-management') . '</a>';
            $support_link = '<a href="' . esc_url("https://www.plugincy.com/support/") . '">' . esc_html__('Support', 'multi-location-product-and-inventory-management') . '</a>';
            $documentation_link = '<a href="' . esc_url("https://plugincy.com/documentations/multi-location-product-inventory-management-for-woocommerce/") . '">' . esc_html__('Documentation', 'multi-location-product-and-inventory-management') . '</a>';
            $our_plugins_link = '<a href="' . esc_url(admin_url('admin.php?page=plugincy-plugins')) . '">' . esc_html__('Our Plugins', 'multi-location-product-and-inventory-management') . '</a>';
            $pro_link = '<a href="https://plugincy.com/multi-location-product-and-inventory-management" style="color: #ff5722; font-weight: bold;" target="_blank">' . esc_html__('Get Pro', 'multi-location-product-and-inventory-management') . '</a>';

            $links[] = $pro_link;
            $links[] = $create_location;
            $links[] = $settings_link;
            $links[] = $support_link;
            $links[] = $documentation_link;
            $links[] = $our_plugins_link;

            $links = array_merge($links, $old_links);
            return array_filter($links);
        }

        /**
         * Show row meta on the plugin screen.
         *
         * @param mixed $links Plugin Row Meta.
         * @param mixed $file  Plugin Base file.
         *
         * @return array
         */
        public static function plugin_row_meta($links, $file)
        {
            if (MULTI_LOCATION_PLUGIN_BASE_NAME !== $file) {
                return $links;
            }

            $docs_url = 'https://plugincy.com/documentations/multi-location-product-inventory-management-for-woocommerce/';

            $community_support_url = 'https://wordpress.org/support/plugin/multi-location-product-and-inventory-management/';

            $support_url = 'https://www.plugincy.com/support/';

            $row_meta = array(
                'docs'    => '<a href="' . esc_url($docs_url) . '" aria-label="' . esc_attr__('View documentation', 'multi-location-product-and-inventory-management') . '">' . esc_html__('Docs', 'multi-location-product-and-inventory-management') . '</a>',
                'support' => '<a href="' . esc_url($support_url) . '" aria-label="' . esc_attr__('Support', 'multi-location-product-and-inventory-management') . '">' . esc_html__('Support', 'multi-location-product-and-inventory-management') . '</a>',
                'community_support' => '<a href="' . esc_url($community_support_url) . '" aria-label="' . esc_attr__('Visit community forums', 'multi-location-product-and-inventory-management') . '">' . esc_html__('Community support', 'multi-location-product-and-inventory-management') . '</a>',
            );

            return array_merge($links, $row_meta);
        }

        public function enqueue_scripts()
        {
            global $mulopimfwc_options;

            $cookie_expiry = isset($mulopimfwc_options["location_cookie_expiry"]) && is_numeric($mulopimfwc_options["location_cookie_expiry"])
                ? (int)$mulopimfwc_options["location_cookie_expiry"]
                : 30;

            wp_enqueue_style('mulopimfwc_style', plugins_url('assets/css/style.css', __FILE__), [], '1.0.7.5');
            wp_enqueue_style('mulopimfwc_select2', plugins_url('assets/css/select2.min.css', __FILE__), [], '4.1.0');
            wp_enqueue_script('mulopimfwc_script', plugins_url('assets/js/script.js', __FILE__), ['jquery'], '1.0.7.5', true);
            wp_enqueue_script('mulopimfwc_select2', plugins_url('assets/js/select2.min.js', __FILE__), ['jquery'], '4.1.0', true);

            $location_require_selection = isset($mulopimfwc_options['location_require_selection']) ? $mulopimfwc_options['location_require_selection'] : 'off';
            $single_product_requires_location = false;

            if ($location_require_selection === 'on' && function_exists('is_product') && is_product()) {
                $product_id = get_queried_object_id();
                if ($product_id) {
                    $single_product_requires_location = $this->product_has_assigned_locations($product_id);
                }
            }

            wp_localize_script('mulopimfwc_script', 'mulopimfwc_locationWiseProducts', [
                'cartHasProducts' => !WC()->cart->is_empty(),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'location_change_notification' => isset($mulopimfwc_options["location_change_notification"]),
                'nonce' => wp_create_nonce('multi-location-product-and-inventory-management'),
                'cookie_expiry' => $cookie_expiry,
                'singleProductRequiresLocation' => $single_product_requires_location,
                'selectLocationPrompt' => __('Please select a store location before adding this product to your cart.', 'multi-location-product-and-inventory-management'),
                'locationSelectionEnforced' => ($location_require_selection === 'on')
            ]);

            wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), '1.7.1');
            wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array('jquery'), '1.7.1', true);
        }

        private function get_current_location()
        {
            return isset($_COOKIE['mulopimfwc_store_location']) ? sanitize_text_field(wp_unslash($_COOKIE['mulopimfwc_store_location'])) : '';
        }

        public function filter_shortcode_products($query_args)
        {
            // Check if strict_filtering is enabled
            $options = $this->get_display_options();
            if (isset($options['strict_filtering']) && $options['strict_filtering'] === 'disabled') {
                return $query_args;
            }

            $location = $this->get_current_location();
            global $mulopimfwc_options;
            $enable_all_locations = isset($mulopimfwc_options['enable_all_locations']) ? $mulopimfwc_options['enable_all_locations'] : 'off';
            
            if (!$location || $location === 'all-products') {
                return $query_args;
            }

            // Check if location filter already exists to avoid duplicates
            if (!isset($query_args['tax_query'])) {
                $query_args['tax_query'] = [];
            }

            $has_location_filter = false;
            foreach ($query_args['tax_query'] as $tax) {
                if (isset($tax['taxonomy']) && $tax['taxonomy'] === 'mulopimfwc_store_location') {
                    $has_location_filter = true;
                    break;
                }
            }

            if (!$has_location_filter) {
                if ($enable_all_locations === 'on') {
                    $query_args['tax_query'][] = [
                        'relation' => 'OR',
                        [
                            'taxonomy' => 'mulopimfwc_store_location',
                            'field' => 'slug',
                            'terms' => $location,
                        ],
                        [
                            'taxonomy' => 'mulopimfwc_store_location',
                            'operator' => 'NOT EXISTS',
                        ],
                    ];
                } else {
                    $query_args['tax_query'][] = [
                        'taxonomy' => 'mulopimfwc_store_location',
                        'field' => 'slug',
                        'terms' => $location,
                    ];
                }
            }

            return $query_args;
        }

        public function filter_widget_products($query_args)
        {
            return $this->filter_shortcode_products($query_args);
        }

        public function filter_related_products($args)
        {
            return $this->filter_shortcode_products($args);
        }

        public function clear_cart_on_location_change()
        {
            if (!isset($_POST['mulopimfwc_shortcode_selector_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mulopimfwc_shortcode_selector_nonce'])), 'mulopimfwc_shortcode_selector')) {
                return;
            }
            if (isset($_POST['clear_cart_on_store_change']) && $_POST['clear_cart_on_store_change'] == '1') {
                if (function_exists('WC')) {
                    WC()->cart->empty_cart();
                    WC()->session->set('cart', []);
                }
            }
        }

        public function location_selector_shortcode($atts)
        {
            global $mulopimfwc_locations;
            $atts = shortcode_atts([
                'title' => __('Select Store Location', 'multi-location-product-and-inventory-management'),
                'show_title' => 'on',
                'class' => '',
                'show_button' => '',
                'use_select2' => '',
                'herichical' => '',
                'show_count' => '',
                'enable_user_locations' => 'off', // New attribute
                'max_width' => '300',
                'multi_line' => 'off'
            ], $atts);

            $is_user_logged_in = is_user_logged_in();
            $current_user = wp_get_current_user();
            // mulopimfwc_display_options[show_all_products_admin]
            global $mulopimfwc_options;
            $options = is_array($mulopimfwc_options ?? null)
                ? $mulopimfwc_options
                : get_option('mulopimfwc_display_options', []);
            $show_all_products_admin = isset($options['show_all_products_admin']) ? $options['show_all_products_admin'] : 'off';
            $is_admin_or_manager = in_array('administrator', $current_user->roles) || in_array('shop_manager', $current_user->roles);
            $selected_location = $this->get_current_location();

            $locations = $mulopimfwc_locations;

            ob_start();
            include plugin_dir_path(__FILE__) . 'templates/shortcode-selector.php';
            return ob_get_clean();
        }

        public function add_location_to_product_title($title, $post_id = 0)
        {
            if (!$post_id || get_post_type($post_id) !== 'product') {
                return $title;
            }
            return $this->get_title_with_location($title, $post_id);
        }

        public function add_location_to_wc_product_title($title, $product = null)
        {
            if (!$product) {
                return $title;
            }
            return $this->get_title_with_location($title, $product->get_id());
        }


        /**
         * Get display options with request-level caching
         * Loads options once per request to prevent N+1 database queries
         * 
         * @return array Display options array
         */
        private function get_display_options()
        {
            // Return cached value if available
            if (self::$cached_display_options !== null) {
                return self::$cached_display_options;
            }

            // Load from database and cache
            self::$cached_display_options = get_option('mulopimfwc_display_options', []);
            
            return self::$cached_display_options;
        }

        /**
         * Clear the display options cache
         * Should be called when options are updated
         * 
         * @return void
         */
        public static function clear_display_options_cache()
        {
            self::$cached_display_options = null;
        }

        /**
         * Get location term with request-level caching
         * Prevents repeated get_term_by() calls that hit the database
         * 
         * @param string $location_slug Location slug to get term for
         * @return WP_Term|false Location term object or false if not found
         */
        private function get_cached_location_term($location_slug)
        {
            // Sanitize location slug
            $location_slug = sanitize_text_field($location_slug);
            
            // Return cached value if available
            if (isset(self::$cached_location_terms[$location_slug])) {
                return self::$cached_location_terms[$location_slug];
            }

            // Load from database and cache
            $location_term = get_term_by('slug', $location_slug, 'mulopimfwc_store_location');
            
            // Cache the result (even if false, to avoid repeated queries)
            self::$cached_location_terms[$location_slug] = $location_term;
            
            return $location_term;
        }

        private function get_title_with_location($title, $product_id)
        {
            $locations = get_the_terms($product_id, 'mulopimfwc_store_location');
            if (!$locations || is_wp_error($locations)) {
                return $title;
            }

            $options = $this->get_display_options();
            $enabled_pages = isset($options['enabled_pages']) ? $options['enabled_pages'] : ['shop', 'single', 'cart'];
            $should_display = false;

            // Check standard WooCommerce pages
            if (in_array('shop', $enabled_pages) && (is_shop() || is_product_category() || is_product_tag() || is_post_type_archive('product'))) {
                $should_display = true;
            } elseif (in_array('single', $enabled_pages) && is_singular('product')) {
                $should_display = true;
            } elseif (in_array('cart', $enabled_pages) && (is_cart() || is_checkout())) {
                $should_display = true;
            } elseif (in_array('search', $enabled_pages) && is_search()) {
                $should_display = true;
            } elseif (in_array('widgets', $enabled_pages) && (is_active_widget(false, false, 'woocommerce_products', true) || is_active_widget(false, false, 'woocommerce_recent_products', true))) {
                $should_display = true;
            } elseif (
                in_array('Shortcode', $enabled_pages) && !is_shop() && !is_product_category() && !is_product_tag() &&
                !is_product() && !is_cart() && !is_checkout() && !is_account_page() && !is_admin()
            ) {
                $should_display = true;
            }

            if (!$should_display) {
                return $title;
            }

            $location_names = [];
            foreach ($locations as $location) {
                $location_names[] = $location->name;
            }

            $location_text = count($location_names) === 1 ? $location_names[0] : implode(', ', $location_names);
            $separator = isset($options['separator']) ? $options['separator'] : ' - ';
            $format = isset($options['display_format']) ? $options['display_format'] : 'none';

            switch ($format) {
                case 'prepend':
                    return $location_text . $separator . $title;
                case 'brackets':
                    return $title . ' [' . $location_text . ']';
                case 'none':
                    return $title;
                case 'append':
                default:
                    return $title . $separator . $location_text;
            }
        }

        /**
         * Batch load term relationships for multiple products in a single query
         * This eliminates N+1 query problems when checking multiple products
         * 
         * @param array $product_ids Array of product IDs to load
         * @return void
         */
        private function batch_load_product_locations($product_ids)
        {
            // Filter out already loaded products
            $product_ids = array_diff(array_map('intval', $product_ids), self::$batch_loaded_products);
            
            if (empty($product_ids)) {
                return; // All products already loaded
            }
            
            // Initialize cache if needed
            if (self::$product_locations_cache === null) {
                self::$product_locations_cache = [];
            }
            
            // Load all term relationships in one query
            global $wpdb;
            
            // Sanitize product IDs and create safe IN clause
            $product_ids = array_map('intval', $product_ids);
            $ids_placeholder = implode(',', $product_ids);
            
            // Use prepare for taxonomy, but IDs are already sanitized as integers
            $query = $wpdb->prepare(
                "SELECT tr.object_id, t.slug 
                 FROM {$wpdb->term_relationships} tr
                 INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                 INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                 WHERE tr.object_id IN ($ids_placeholder) 
                 AND tt.taxonomy = %s",
                'mulopimfwc_store_location'
            );
            
            $results = $wpdb->get_results($query);
            
            // Organize results by product_id
            foreach ($results as $row) {
                $product_id = (int) $row->object_id;
                $location_slug = rawurldecode($row->slug);
                
                if (!isset(self::$product_locations_cache[$product_id])) {
                    self::$product_locations_cache[$product_id] = [];
                }
                
                self::$product_locations_cache[$product_id][] = $location_slug;
            }
            
            // Mark products with no locations (empty array) to avoid re-querying
            foreach ($product_ids as $product_id) {
                if (!isset(self::$product_locations_cache[$product_id])) {
                    self::$product_locations_cache[$product_id] = []; // Empty = no locations
                }
                self::$batch_loaded_products[] = $product_id;
            }
        }
        
        /**
         * Check if a product belongs to the current location
         * Uses request-level cache to prevent N+1 query problems
         * 
         * @param int $product_id Product ID to check
         * @return bool True if product belongs to current location
         */
        private function product_belongs_to_location($product_id)
        {
            $location = $this->get_current_location();
            global $mulopimfwc_options;
            $enable_all_locations = isset($mulopimfwc_options['enable_all_locations']) ? $mulopimfwc_options['enable_all_locations'] : 'off';

            if (!$location || $location === 'all-products') {
                return true;
            }

            $product_id = (int) $product_id;
            
            // Check cache first
            if (self::$product_locations_cache !== null && isset(self::$product_locations_cache[$product_id])) {
                $terms = self::$product_locations_cache[$product_id];
            } else {
                // Not in cache, load it (fallback for single calls)
                $this->batch_load_product_locations([$product_id]);
                $terms = isset(self::$product_locations_cache[$product_id]) 
                    ? self::$product_locations_cache[$product_id] 
                    : [];
            }
            
            // If product has no locations and enable_all_locations is on, it's available everywhere
            if (empty($terms) && $enable_all_locations === 'on') {
                return true; // Product is available in all locations
            }
            
            // Check if current location is in the product's locations
            return in_array($location, $terms, true);
        }

        public function filter_product_blocks($html, $data, $product)
        {
            if (!$this->product_belongs_to_location($product->get_id())) {
                return '';
            }
            return $html;
        }

        public function filter_ajax_searched_products($products)
        {
            // Check if strict_filtering is enabled
            $options = $this->get_display_options();
            if (isset($options['strict_filtering']) && $options['strict_filtering'] === 'disabled') {
                return $products;
            }

            $location = $this->get_current_location();
            if (!$location || $location === 'all-products') {
                return $products;
            }

            // Batch load all product locations in one query (prevents N+1)
            $product_ids = array_map('intval', array_keys($products));
            if (!empty($product_ids)) {
                $this->batch_load_product_locations($product_ids);
            }

            foreach ($products as $id => $product) {
                if (!$this->product_belongs_to_location($id)) {
                    unset($products[$id]);
                }
            }

            return $products;
        }

        public function filter_rest_api_products($args, $request)
        {
            // Check if strict_filtering is enabled
            $options = $this->get_display_options();
            if (isset($options['strict_filtering']) && $options['strict_filtering'] === 'disabled') {
                return $args;
            }

            $location = $this->get_current_location();
            if (!$location || $location === 'all-products') {
                return $args;
            }

            // Check if location filter already exists to avoid duplicates
            if (!isset($args['tax_query'])) {
                $args['tax_query'] = [];
            }

            $has_location_filter = false;
            foreach ($args['tax_query'] as $tax) {
                if (isset($tax['taxonomy']) && $tax['taxonomy'] === 'mulopimfwc_store_location') {
                    $has_location_filter = true;
                    break;
                }
            }

            if (!$has_location_filter) {
                global $mulopimfwc_options;
                $enable_all_locations = isset($mulopimfwc_options['enable_all_locations']) ? $mulopimfwc_options['enable_all_locations'] : 'off';

                if ($enable_all_locations === 'on') {
                    $args['tax_query'][] = [
                        'relation' => 'OR',
                        [
                            'taxonomy' => 'mulopimfwc_store_location',
                            'field' => 'slug',
                            'terms' => $location,
                        ],
                        [
                            'taxonomy' => 'mulopimfwc_store_location',
                            'operator' => 'NOT EXISTS',
                        ],
                    ];
                } else {
                    $args['tax_query'][] = [
                        'taxonomy' => 'mulopimfwc_store_location',
                        'field' => 'slug',
                        'terms' => $location,
                    ];
                }
            }

            return $args;
        }

        public function modify_product_rest_response($response, $product, $request)
        {
            if (!$this->product_belongs_to_location($product->get_id())) {
                $data = $response->get_data();
                $data['hidden_by_location'] = true;
                $response->set_data($data);
            }
            return $response;
        }

        public function filter_cart_contents($cart_contents)
        {
            $location = $this->get_current_location();

            if (!$location || $location === 'all-products') {
                return $cart_contents;
            }

            // Ensure $cart_contents is an array
            if (!is_array($cart_contents)) {
                return $cart_contents;
            }

            // Batch load all product locations in one query (prevents N+1)
            $product_ids = [];
            foreach ($cart_contents as $item) {
                if (is_array($item) && isset($item['product_id'])) {
                    $product_ids[] = (int) $item['product_id'];
                }
            }
            
            if (!empty($product_ids)) {
                $this->batch_load_product_locations($product_ids);
            }

            foreach ($cart_contents as $key => $item) {
                // Ensure $item is an array and has product_id
                if (!is_array($item) || !isset($item['product_id'])) {
                    continue;
                }

                if (!$this->product_belongs_to_location($item['product_id'])) {
                    $cart_contents[$key]['hidden_by_location'] = true;
                }
            }

            return $cart_contents;
        }

        public function filter_recently_viewed_products()
        {
            $location = $this->get_filtered_location('recently_viewed');

            if (!$location) {
                return;
            }

            $viewed_products = isset($_COOKIE['woocommerce_recently_viewed']) ? (array) explode('|', sanitize_text_field(wp_unslash($_COOKIE['woocommerce_recently_viewed']))) : [];

            if (empty($viewed_products)) {
                return;
            }

            // Batch load all product locations in one query (prevents N+1)
            $product_ids = array_map('intval', array_filter($viewed_products));
            if (!empty($product_ids)) {
                $this->batch_load_product_locations($product_ids);
            }

            $filtered_products = [];
            foreach ($viewed_products as $product_id) {
                if ($this->product_belongs_to_location($product_id)) {
                    $filtered_products[] = $product_id;
                }
            }

            if (count($filtered_products) !== count($viewed_products)) {
                $filtered_cookie = implode('|', $filtered_products);
                wc_setcookie('woocommerce_recently_viewed', $filtered_cookie, time() + 60 * 60 * 24 * 30);
            }
        }

        /**
         * Handle unavailable single product pages based on settings
         * Shows 404 or allows page to load with message
         */
        public function handle_unavailable_single_product()
        {
            // Only on single product pages
            if (!is_singular('product')) {
                return;
            }

            // Check if strict_filtering is enabled
            $options = $this->get_display_options();
            if (isset($options['strict_filtering']) && $options['strict_filtering'] === 'disabled') {
                return;
            }

            // Get current location
            $location = $this->get_current_location();
            if (!$location || $location === 'all-products') {
                return;
            }

            // Get the product
            global $post;
            if (!$post) {
                return;
            }

            $product_id = $post->ID;
            
            // Check if product belongs to location
            if ($this->product_belongs_to_location($product_id)) {
                return; // Product is available, no action needed
            }

            // Product is not available - check behavior setting
            $unavailable_behavior = isset($options['unavailable_product_behavior']) ? $options['unavailable_product_behavior'] : 'show_404';

            if ($unavailable_behavior === 'show_404') {
                // Show 404 page
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                nocache_headers();
            } else {
                // show_with_message - allow page to load, message will be shown by existing hooks
                // Add a notice at the top of the product page
                add_action('woocommerce_before_single_product', function() {
                    echo '<div class="woocommerce-info woocommerce-message" style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-bottom: 20px;">';
                    echo '<strong>' . esc_html__('Product Not Available', 'multi-location-product-and-inventory-management') . '</strong><br>';
                    echo esc_html__('This product is not available in your currently selected location.', 'multi-location-product-and-inventory-management');
                    echo '</div>';
                }, 5);
            }
        }


        private function should_apply_filtering($section)
        {
            $options = $this->get_display_options();
            $location = $this->get_current_location();
            if (!$location || $location === 'all-products') {
                return false;
            }

            if (isset($options['strict_filtering']) && $options['strict_filtering'] === 'disabled') {
                return false;
            }

            $filtered_sections = isset($options['filtered_sections']) ? $options['filtered_sections'] : [];
            return in_array($section, $filtered_sections);
        }

        private function get_filtered_location($section)
        {
            if (!$this->should_apply_filtering($section)) {
                return false;
            }
            return $this->get_current_location();
        }

        public function filter_products_by_location($query)
        {
            // Skip admin queries
            if (is_admin()) {
                return;
            }

            // Check if this is a product query
            $post_type = $query->get('post_type');
            if (empty($post_type)) {
                // If no post_type is set, check if it's a product archive or single product page
                if (!is_product() && !is_shop() && !is_product_category() && !is_product_tag() && !is_product_taxonomy() && !is_post_type_archive('product')) {
                    return;
                }
                // Assume it's a product query if we're on a product-related page
                $post_type = 'product';
            } elseif (!in_array('product', (array) $post_type)) {
                // Not a product query, skip
                return;
            }

            // Check if strict_filtering is enabled
            $options = $this->get_display_options();
            if (isset($options['strict_filtering']) && $options['strict_filtering'] === 'disabled') {
                return;
            }

            // Get current location
            $location = $this->get_current_location();
            if (!$location || $location === 'all-products') {
                return;
            }

            // Check if we should skip filtering for single product pages when setting is "show_with_message"
            $unavailable_behavior = isset($options['unavailable_product_behavior']) ? $options['unavailable_product_behavior'] : 'show_404';
            if ($unavailable_behavior === 'show_with_message') {
                // Check if this is a single product query
                // In pre_get_posts, we need to check the query parameters directly
                $p = $query->get('p');
                $page_id = $query->get('page_id');
                $name = $query->get('name');
                $post_name = $query->get('post_name');
                
                // Check if it's the main query and looks like a single product page
                if ($query->is_main_query()) {
                    // Check request URI pattern
                    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                    if ($request_uri && preg_match('#/product/[^/]+/?$#', $request_uri)) {
                        // This looks like a single product page, skip filtering
                        return;
                    }
                }
                
                // Also check direct query parameters
                if (($p || $page_id || $name || $post_name) && $post_type === 'product') {
                    // This looks like a single product query, skip filtering
                    return;
                }
            }

            // Apply location filtering to ALL product queries when strict_filtering is enabled
            $tax_query = (array) $query->get('tax_query');
            global $mulopimfwc_options;
            $enable_all_locations = isset($mulopimfwc_options['enable_all_locations']) ? $mulopimfwc_options['enable_all_locations'] : 'off';

            // Check if location filter already exists to avoid duplicates
            $has_location_filter = false;
            foreach ($tax_query as $tax) {
                if (isset($tax['taxonomy']) && $tax['taxonomy'] === 'mulopimfwc_store_location') {
                    $has_location_filter = true;
                    break;
                }
            }

            if (!$has_location_filter) {
                if ($enable_all_locations === 'on') {
                    $tax_query[] = [
                        'relation' => 'OR',
                        [
                            'taxonomy' => 'mulopimfwc_store_location',
                            'field' => 'slug',
                            'terms' => $location,
                        ],
                        [
                            'taxonomy' => 'mulopimfwc_store_location',
                            'operator' => 'NOT EXISTS',
                        ],
                    ];
                } else {
                    $tax_query[] = [
                        'taxonomy' => 'mulopimfwc_store_location',
                        'field' => 'slug',
                        'terms' => $location,
                    ];
                }
                $query->set('tax_query', $tax_query);
            }
        }

        /**
         * Filter product queries using posts_clauses to catch queries that bypass pre_get_posts
         * This ensures ALL database product queries are filtered when strict_filtering is enabled
         */
        public function filter_products_by_location_clauses($clauses, $query)
        {
            // Skip admin queries
            if (is_admin()) {
                return $clauses;
            }

            // Check if this is a product query
            $post_type = $query->get('post_type');
            if (empty($post_type)) {
                // If no post_type is set, check if it's a product-related page
                if (!is_product() && !is_shop() && !is_product_category() && !is_product_tag() && !is_product_taxonomy() && !is_post_type_archive('product')) {
                    return $clauses;
                }
            } elseif (!in_array('product', (array) $post_type)) {
                // Not a product query, skip
                return $clauses;
            }

            // Check if strict_filtering is enabled
            $options = $this->get_display_options();
            if (isset($options['strict_filtering']) && $options['strict_filtering'] === 'disabled') {
                return $clauses;
            }

            // Get current location
            $location = $this->get_current_location();
            if (!$location || $location === 'all-products') {
                return $clauses;
            }

            // Check if we should skip filtering for single product pages when setting is "show_with_message"
            $unavailable_behavior = isset($options['unavailable_product_behavior']) ? $options['unavailable_product_behavior'] : 'show_404';
            if ($unavailable_behavior === 'show_with_message') {
                // Check if this is a single product query
                // For main queries, check multiple ways to detect single product pages
                if ($query->is_main_query()) {
                    // Method 1: Check global $wp query_vars
                    global $wp;
                    if (isset($wp->query_vars['post_type']) && $wp->query_vars['post_type'] === 'product' && 
                        (isset($wp->query_vars['p']) || isset($wp->query_vars['name']) || isset($wp->query_vars['page_id']))) {
                        // This is a single product page query, skip filtering
                        return $clauses;
                    }
                    
                    // Method 2: Check request URI pattern (product slug in URL)
                    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                    if ($request_uri && preg_match('#/product/[^/]+/?$#', $request_uri)) {
                        // This looks like a single product page URL, skip filtering
                        return $clauses;
                    }
                }
                
                // Method 3: Check direct query parameters (for any query)
                $p = $query->get('p');
                $page_id = $query->get('page_id');
                $name = $query->get('name');
                $post_name = $query->get('post_name');
                if (($p || $page_id || $name || $post_name) && $post_type === 'product') {
                    // This looks like a single product query, skip filtering
                    return $clauses;
                }
            }

            // Get location term (cached)
            $location_term = $this->get_cached_location_term($location);
            if (!$location_term || is_wp_error($location_term)) {
                return $clauses;
            }

            global $wpdb, $mulopimfwc_options;
            $enable_all_locations = isset($mulopimfwc_options['enable_all_locations']) ? $mulopimfwc_options['enable_all_locations'] : 'off';

            // Check if location filter is already in tax_query (handled by pre_get_posts)
            $tax_query = $query->get('tax_query');
            $has_location_filter = false;
            if (is_array($tax_query)) {
                foreach ($tax_query as $tax) {
                    if (isset($tax['taxonomy']) && $tax['taxonomy'] === 'mulopimfwc_store_location') {
                        $has_location_filter = true;
                        break;
                    }
                }
            }

            // Only apply if not already filtered via tax_query
            if (!$has_location_filter) {
                if ($enable_all_locations === 'on') {
                    // Include products with location OR products with no location assigned
                    // Use a subquery approach to avoid JOIN conflicts
                    $clauses['where'] .= $wpdb->prepare(
                        " AND (
                            {$wpdb->posts}.ID IN (
                                SELECT object_id FROM {$wpdb->term_relationships} 
                                INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id
                                WHERE {$wpdb->term_taxonomy}.taxonomy = 'mulopimfwc_store_location'
                                AND {$wpdb->term_relationships}.term_taxonomy_id = %d
                            )
                            OR {$wpdb->posts}.ID NOT IN (
                                SELECT object_id FROM {$wpdb->term_relationships} 
                                INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id
                                WHERE {$wpdb->term_taxonomy}.taxonomy = 'mulopimfwc_store_location'
                            )
                        )",
                        $location_term->term_taxonomy_id
                    );
                } else {
                    // Only include products with the selected location
                    // Add JOIN for term relationships only if not already present
                    if (strpos($clauses['join'], 'INNER JOIN ' . $wpdb->term_relationships . ' AS mulopimfwc_tr') === false) {
                        $clauses['join'] .= " INNER JOIN {$wpdb->term_relationships} AS mulopimfwc_tr ON ({$wpdb->posts}.ID = mulopimfwc_tr.object_id)";
                        $clauses['join'] .= $wpdb->prepare(
                            " INNER JOIN {$wpdb->term_taxonomy} AS mulopimfwc_tt ON (mulopimfwc_tr.term_taxonomy_id = mulopimfwc_tt.term_taxonomy_id AND mulopimfwc_tt.taxonomy = 'mulopimfwc_store_location')"
                        );
                    }
                    $clauses['where'] .= $wpdb->prepare(
                        " AND mulopimfwc_tr.term_taxonomy_id = %d",
                        $location_term->term_taxonomy_id
                    );
                }

                // Add GROUP BY to handle multiple term relationships
                if (empty($clauses['groupby'])) {
                    $clauses['groupby'] = "{$wpdb->posts}.ID";
                }
            }

            return $clauses;
        }



        public function filter_related_products_by_location($related_products, $product_id, $args)
        {
            $location = $this->get_filtered_location('related');

            if (!$location) {
                return $related_products;
            }

            // Batch load all product locations in one query (prevents N+1)
            if (!empty($related_products)) {
                $this->batch_load_product_locations(array_map('intval', $related_products));
            }

            return array_filter($related_products, [$this, 'product_belongs_to_location']);
        }

        public function filter_cross_sells_by_location($cross_sells)
        {
            $location = $this->get_filtered_location('cross_sells');

            if (!$location) {
                return $cross_sells;
            }

            // Batch load all product locations in one query (prevents N+1)
            if (!empty($cross_sells)) {
                $product_ids = [];
                foreach ($cross_sells as $product) {
                    if (is_object($product) && method_exists($product, 'get_id')) {
                        $product_ids[] = $product->get_id();
                    } elseif (is_numeric($product)) {
                        $product_ids[] = (int) $product;
                    }
                }
                if (!empty($product_ids)) {
                    $this->batch_load_product_locations($product_ids);
                }
            }

            return array_filter($cross_sells, [$this, 'product_belongs_to_location']);
        }

        public function filter_upsells_by_location($upsell_ids, $product_id)
        {
            $location = $this->get_filtered_location('upsells');

            if (!$location) {
                return $upsell_ids;
            }

            // Batch load all product locations in one query (prevents N+1)
            if (!empty($upsell_ids)) {
                $this->batch_load_product_locations(array_map('intval', $upsell_ids));
            }

            return array_filter($upsell_ids, [$this, 'product_belongs_to_location']);
        }

        public function filter_widget_products_by_location($query_args)
        {
            $location = $this->get_filtered_location('widgets');

            if (!$location) {
                return $query_args;
            }

            // if (!isset($query_args['tax_query'])) {
            //     $query_args['tax_query'] = [];
            // }

            $query_args['tax_query'][] = [
                'taxonomy' => 'mulopimfwc_store_location',
                'field' => 'slug',
                'terms' => $location,
            ];

            return $query_args;
        }

        function clear_cart()
        {
            // Check if WooCommerce is active
            if (class_exists('WooCommerce')) {
                WC()->cart->empty_cart(); // Clear the cart
                wp_send_json_success(); // Send a success response
            } else {
                wp_send_json_error(); // Send an error response
            }

            wp_die(); // Always call wp_die() at the end of AJAX functions
        }

        function check_cart_products()
        {
            // Check if WooCommerce is active
            if (!class_exists('WooCommerce')) {
                wp_send_json_error('WooCommerce is not active.');
            }

            // Check if the cart has products
            $cart_has_products = !WC()->cart->is_empty();

            // Return response
            wp_send_json_success(array('cartHasProducts' => $cart_has_products));
        }

        public function ajax_switch_location()
        {
            // Validate request
            check_ajax_referer('multi-location-product-and-inventory-management', 'nonce');

            $location = isset($_POST['location']) ? sanitize_text_field(wp_unslash(rawurldecode($_POST['location']))) : '';

            if (empty($location)) {
                wp_send_json_error(['message' => __('Invalid location.', 'multi-location-product-and-inventory-management')]);
            }

            global $mulopimfwc_options;
            $options = is_array($mulopimfwc_options ?? null)
                ? $mulopimfwc_options
                : get_option('mulopimfwc_display_options', []);

            $allow_mixed = isset($options['allow_mixed_location_cart']) && mulopimfwc_premium_feature()
                ? $options['allow_mixed_location_cart']
                : 'off';

            $behavior = 'update_cart';

            // Store the selected location cookie immediately
            $this->set_store_location_cookie($location);

            $removed_items = [];

            if ($allow_mixed !== 'on' && $behavior !== 'preserve_cart') {
                $removed_items = $this->remove_unavailable_cart_items($location);
            }

            wp_send_json_success([
                'location' => $location,
                'removed_items' => $removed_items,
                'removed_count' => count($removed_items),
                'behavior' => $behavior,
                'allow_mixed' => $allow_mixed,
            ]);
        }

        function custom_admin_styles()
        {
            wp_enqueue_style('mulopimfwc-custom-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', array(), "1.0.7.5");
        }

        private function set_store_location_cookie($location)
        {
            $expiry = time() + mulopimfwc_get_location_cookie_expiry_seconds();

            if (function_exists('wc_setcookie')) {
                wc_setcookie('mulopimfwc_store_location', $location, $expiry);
            } else {
                setcookie('mulopimfwc_store_location', $location, $expiry, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl());
            }

            $_COOKIE['mulopimfwc_store_location'] = $location;
        }

        private function remove_unavailable_cart_items(string $location_slug): array
        {
            if (!function_exists('WC') || !WC()->cart) {
                return [];
            }

            $removed_items = [];
            $cart = WC()->cart;

            foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                if (!$this->cart_item_available_for_location($cart_item, $location_slug)) {
                    $product_name = '';

                    if (isset($cart_item['data']) && is_object($cart_item['data'])) {
                        $product_name = $cart_item['data']->get_name();
                    } elseif (isset($cart_item['product_id'])) {
                        $product_name = get_the_title($cart_item['product_id']);
                    }

                    $removed_items[] = $product_name;
                    $cart->remove_cart_item($cart_item_key);
                }
            }

            if (!empty($removed_items)) {
                $cart->calculate_totals();
                $this->clear_cart_cache();
            }

            return array_filter($removed_items);
        }

        private function cart_item_available_for_location(array $cart_item, string $location_slug): bool
        {
            if (empty($location_slug) || $location_slug === 'all-products') {
                return true;
            }

            $product_ids_to_check = [];

            if (!empty($cart_item['variation_id'])) {
                $product_ids_to_check[] = (int) $cart_item['variation_id'];
            }

            if (!empty($cart_item['product_id'])) {
                $product_ids_to_check[] = (int) $cart_item['product_id'];
            }

            foreach ($product_ids_to_check as $product_id) {
                if ($this->product_available_for_location($product_id, $location_slug)) {
                    return true;
                }
            }

            return false;
        }

        private function product_available_for_location(int $product_id, string $location_slug): bool
        {
            if (empty($location_slug) || $location_slug === 'all-products') {
                return true;
            }

            global $mulopimfwc_options;

            if (!isset($mulopimfwc_options) || !is_array($mulopimfwc_options)) {
                $mulopimfwc_options = get_option('mulopimfwc_display_options', []);
            }

            $enable_all_locations = isset($mulopimfwc_options['enable_all_locations'])
                ? $mulopimfwc_options['enable_all_locations']
                : 'off';

            $terms = array_map('rawurldecode', wp_get_object_terms($product_id, 'mulopimfwc_store_location', ['fields' => 'slugs']));

            if (empty($terms)) {
                return $enable_all_locations === 'on';
            }

            if (is_wp_error($terms) || !in_array($location_slug, $terms, true)) {
                return false;
            }

            $location_term = $this->get_cached_location_term($location_slug);
            if ($location_term && !is_wp_error($location_term)) {
                $is_disabled = get_post_meta($product_id, '_location_disabled_' . $location_term->term_id, true);
                if (!empty($is_disabled)) {
                    return false;
                }
            }

            return true;
        }
    }

    function mulopimfwc_location_wise_products_init()
    {
        new mulopimfwc_Location_Wise_Products();
    }

    add_action('plugins_loaded', 'mulopimfwc_location_wise_products_init');



    register_uninstall_hook(__FILE__, 'mulopimfwc_settings_remove');

    register_activation_hook(__FILE__, 'mulopimfwc_activate_plugin');

    /**
     * Plugin activation hook
     * Sets up database indexes for optimal performance
     */
    function mulopimfwc_activate_plugin()
    {
        mulopimfwc_add_database_indexes();
    }

    /**
     * Add admin action to manually create indexes for existing installations
     */
    add_action('admin_init', function() {
        // Allow manual index creation via admin action
        if (isset($_GET['mulopimfwc_create_indexes']) && current_user_can('manage_options')) {
            check_admin_referer('mulopimfwc_create_indexes');
            mulopimfwc_add_database_indexes();
            wp_redirect(add_query_arg(['mulopimfwc_indexes_created' => '1'], remove_query_arg('mulopimfwc_create_indexes')));
            exit;
        }
    });

    /**
     * Show admin notice if indexes haven't been created yet
     */
    add_action('admin_notices', function() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $indexes_added = get_option('mulopimfwc_database_indexes_added', false);
        if ($indexes_added) {
            // Show success message if just created
            if (isset($_GET['mulopimfwc_indexes_created'])) {
                echo '<div class="notice notice-success is-dismissible"><p>';
                echo esc_html__('Database indexes have been created successfully. Query performance should be improved.', 'multi-location-product-and-inventory-management');
                echo '</p></div>';
            }
            return;
        }

        // Show notice to create indexes for existing installations
        $create_url = wp_nonce_url(
            add_query_arg('mulopimfwc_create_indexes', '1'),
            'mulopimfwc_create_indexes'
        );
        echo '<div class="notice notice-info is-dismissible"><p>';
        echo esc_html__('Multi Location Plugin: For optimal performance with large datasets, please create database indexes.', 'multi-location-product-and-inventory-management');
        echo ' <a href="' . esc_url($create_url) . '" class="button button-primary">';
        echo esc_html__('Create Indexes Now', 'multi-location-product-and-inventory-management');
        echo '</a></p></div>';
    });

    /**
     * Add database indexes for optimal query performance
     * Improves performance on large datasets (10,000+ products)
     * 
     * Note: WordPress core tables already have some indexes, but we add composite
     * indexes that are specifically optimized for our location-based queries.
     */
    function mulopimfwc_add_database_indexes()
    {
        global $wpdb;

        // Check if indexes have already been added
        $indexes_added = get_option('mulopimfwc_database_indexes_added', false);
        if ($indexes_added) {
            return; // Indexes already exist
        }

        $indexes_created = [];
        $errors = [];

        // 1. Check if wp_term_relationships already has composite index
        // WordPress core has PRIMARY KEY on (object_id, term_taxonomy_id), which serves as composite index
        // So we don't need to add another one - the PRIMARY KEY already covers our use case
        $table_1 = $wpdb->term_relationships;
        $has_primary_key = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.table_constraints 
             WHERE table_schema = %s 
             AND table_name = %s 
             AND constraint_type = 'PRIMARY KEY'",
            DB_NAME,
            $table_1
        ));

        // WordPress core already has optimal indexing for term_relationships via PRIMARY KEY
        // No additional index needed

        // 2. Composite index on wp_postmeta for faster location meta queries
        // WordPress core has separate indexes on post_id and meta_key, but composite (post_id, meta_key)
        // is more efficient for queries filtering by both (which we do frequently)
        $index_name_2 = 'mulopimfwc_pm_post_meta';
        $table_2 = $wpdb->postmeta;
        
        // Check if composite index already exists
        $index_exists_2 = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.statistics 
             WHERE table_schema = %s 
             AND table_name = %s 
             AND index_name = %s",
            DB_NAME,
            $table_2,
            $index_name_2
        ));

        // Also check if there's already a composite index with different name
        // Look for any index that has both post_id (seq 1) and meta_key (seq 2)
        $has_composite = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT s1.index_name) 
             FROM information_schema.statistics s1
             INNER JOIN information_schema.statistics s2 
             ON s1.table_schema = s2.table_schema 
             AND s1.table_name = s2.table_name 
             AND s1.index_name = s2.index_name
             WHERE s1.table_schema = %s 
             AND s1.table_name = %s 
             AND s1.column_name = 'post_id' 
             AND s1.seq_in_index = 1
             AND s2.column_name = 'meta_key' 
             AND s2.seq_in_index = 2",
            DB_NAME,
            $table_2
        ));

        if (!$index_exists_2 && !$has_composite) {
            // Only create if it doesn't exist and no other composite index exists
            $sql_2 = "CREATE INDEX {$index_name_2} ON {$table_2} (post_id, meta_key)";
            $result_2 = $wpdb->query($sql_2);
            if ($result_2 !== false) {
                $indexes_created[] = $index_name_2;
            } else {
                $errors[] = sprintf(
                    __('Failed to create index %s: %s', 'multi-location-product-and-inventory-management'),
                    $index_name_2,
                    $wpdb->last_error
                );
            }
        }

        // 3. Additional index on wp_postmeta.meta_key for location-specific meta lookups
        // This helps when querying by meta_key pattern (e.g., _location_stock_*)
        $index_name_3 = 'mulopimfwc_pm_meta_key';
        
        // Check if meta_key index already exists (WordPress core may have it)
        $meta_key_index_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.statistics 
             WHERE table_schema = %s 
             AND table_name = %s 
             AND column_name = 'meta_key' 
             AND seq_in_index = 1",
            DB_NAME,
            $table_2
        ));

        if (!$meta_key_index_exists && !$index_exists_2) {
            // Only add if no meta_key index exists and we're not using composite
            $sql_3 = "CREATE INDEX {$index_name_3} ON {$table_2} (meta_key)";
            $result_3 = $wpdb->query($sql_3);
            if ($result_3 !== false) {
                $indexes_created[] = $index_name_3;
            } else {
                $errors[] = sprintf(
                    __('Failed to create index %s: %s', 'multi-location-product-and-inventory-management'),
                    $index_name_3,
                    $wpdb->last_error
                );
            }
        }

        // Mark indexes as added if any were created or if optimal indexes already exist
        if (!empty($indexes_created) || ($has_primary_key && ($index_exists_2 || $has_composite || $meta_key_index_exists))) {
            update_option('mulopimfwc_database_indexes_added', true);
            
            // Log errors if any occurred (but don't fail activation)
            if (!empty($errors)) {
                error_log('Multi Location Plugin: Index creation errors: ' . implode(', ', $errors));
            }
        }
    }

    /**
     * Remove database indexes on plugin uninstall (optional)
     * Note: We keep indexes on deactivation as they improve performance
     * and don't cause issues. Only remove on uninstall if explicitly requested.
     */
    function mulopimfwc_remove_database_indexes()
    {
        global $wpdb;

        $indexes_removed = [];

        // Remove postmeta composite index
        $index_name_2 = 'mulopimfwc_pm_post_meta';
        $table_2 = $wpdb->postmeta;
        
        $index_exists_2 = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.statistics 
             WHERE table_schema = %s 
             AND table_name = %s 
             AND index_name = %s",
            DB_NAME,
            $table_2,
            $index_name_2
        ));

        if ($index_exists_2) {
            $sql_2 = "DROP INDEX {$index_name_2} ON {$table_2}";
            $result_2 = $wpdb->query($sql_2);
            if ($result_2 !== false) {
                $indexes_removed[] = $index_name_2;
            }
        }

        // Remove postmeta meta_key index
        $index_name_3 = 'mulopimfwc_pm_meta_key';
        
        $index_exists_3 = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.statistics 
             WHERE table_schema = %s 
             AND table_name = %s 
             AND index_name = %s",
            DB_NAME,
            $table_2,
            $index_name_3
        ));

        if ($index_exists_3) {
            $sql_3 = "DROP INDEX {$index_name_3} ON {$table_2}";
            $result_3 = $wpdb->query($sql_3);
            if ($result_3 !== false) {
                $indexes_removed[] = $index_name_3;
            }
        }

        if (!empty($indexes_removed)) {
            delete_option('mulopimfwc_database_indexes_added');
        }
    }

    function mulopimfwc_settings_remove()
    {
        // Check if the option exists and delete it
        if (get_option('mulopimfwc_display_options') !== false) {
            delete_option('mulopimfwc_display_options');
        }
        
        // Optionally remove indexes on uninstall (commented out to preserve performance)
        // mulopimfwc_remove_database_indexes();
    }


    // Add this to the main plugin file after the class definition

    // AJAX handler for saving user location
    add_action('wp_ajax_mulopimfwc_save_user_location', 'mulopimfwc_save_user_location');
    add_action('wp_ajax_nopriv_mulopimfwc_save_user_location', 'mulopimfwc_save_user_location');

    function mulopimfwc_save_user_location()
    {
        // Check nonce
        check_ajax_referer('mulopimfwc_save_user_location', 'mulopimfwc_save_user_location_nonce');

        // Get form data
        $label = isset($_POST['label']) ? sanitize_text_field($_POST['label']) : '';
        $street = isset($_POST['street']) ? sanitize_text_field($_POST['street']) : '';
        $apartment = isset($_POST['apartment']) ? sanitize_text_field($_POST['apartment']) : '';
        $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
        $state = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '';
        $postal = isset($_POST['postal']) ? sanitize_text_field($_POST['postal']) : '';
        $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
        $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';
        $lat = isset($_POST['lat']) ? floatval($_POST['lat']) : 0;
        $lng = isset($_POST['lng']) ? floatval($_POST['lng']) : 0;

        // Check if we're editing an existing location
        $location_id = isset($_POST['location_id']) && !empty($_POST['location_id']) ? sanitize_text_field($_POST['location_id']) : uniqid();

        // Prepare location data
        $location_data = array(
            'id' => $location_id,
            'label' => $label,
            'street' => $street,
            'apartment' => $apartment,
            'city' => $city,
            'state' => $state,
            'postal' => $postal,
            'country' => $country,
            'note' => $note,
            'lat' => $lat,
            'lng' => $lng,
            'address' => $street . ', ' . $city . ', ' . $state . ' ' . $postal . ', ' . $country
        );

        $is_logged_in = is_user_logged_in();

        if ($is_logged_in) {
            $user_id = get_current_user_id();
            $user_locations = get_user_meta($user_id, 'mulopimfwc_user_locations', true);
            if (!is_array($user_locations)) {
                $user_locations = array();
            }

            // If editing an existing location, find and update it
            $found = false;
            foreach ($user_locations as $key => $location) {
                if ($location['id'] === $location_id) {
                    $user_locations[$key] = $location_data;
                    $found = true;
                    break;
                }
            }

            // If not found, add new location
            if (!$found) {
                $user_locations[] = $location_data;
            }

            // Update user meta
            update_user_meta($user_id, 'mulopimfwc_user_locations', $user_locations);

            wp_send_json_success(array(
                'logged_in' => true,
                'location_id' => $location_id,
                'label' => $label,
                'address' => $location_data['address']
            ));
        } else {
            // For non-logged-in users, we can't save the location permanently.
            wc_setcookie('mulopimfwc_user_location', $location_data['address'], time() + 60 * 60 * 24 * 30);
            wp_send_json_success(array(
                'logged_in' => false,
                'location_id' => $location_id,
                'label' => $label,
                'address' => $location_data['address']
            ));
        }
    }



    // AJAX handler for deleting user location
    add_action('wp_ajax_mulopimfwc_delete_user_location', 'mulopimfwc_delete_user_location');

    function mulopimfwc_delete_user_location()
    {

        // Get location ID
        $location_id = isset($_POST['location_id']) ? sanitize_text_field($_POST['location_id']) : '';

        error_log("You are deleting: " . $location_id);

        if (empty($location_id)) {
            wp_send_json_error(array('message' => 'Invalid location ID'));
        }

        $user_id = get_current_user_id();
        $user_locations = get_user_meta($user_id, 'mulopimfwc_user_locations', true);

        if (!is_array($user_locations)) {
            wp_send_json_error(array('message' => 'No saved locations found'));
        }

        // Find and remove the location
        $found = false;
        foreach ($user_locations as $key => $location) {
            if ($location['id'] === $location_id) {
                unset($user_locations[$key]);
                $found = true;
                break;
            }
        }

        if (!$found) {
            wp_send_json_error(array('message' => 'Location not found'));
        }

        // Re-index array
        $user_locations = array_values($user_locations);

        // Update user meta
        update_user_meta($user_id, 'mulopimfwc_user_locations', $user_locations);

        wp_send_json_success(array('message' => 'Location deleted successfully'));
    }










    require_once plugin_dir_path(__FILE__) . 'includes/analytics.php';

    class mulopimfwc_analytics_main
    {
        private $analytics;

        public function __construct()
        {
            global $mulopimfwc_options;
            // Initialize analytics with the correct plugin file path
            $this->analytics = new mulopimfwc_anaylytics(
                '04',
                'https://plugincy.com/wp-json/product-analytics/v1',
                "1.0.7.5",
                'Multi Location Product & Inventory Management for WooCommerce',
                __FILE__ // Pass the main plugin file
            );

            add_action('admin_footer',  array($this->analytics, "add_deactivation_feedback_form"));

            // Plugin hooks
            add_action('init', array($this, 'init'));
            if (!isset($mulopimfwc_options["allow_data_share"]) || (isset($mulopimfwc_options["allow_data_share"])  && $mulopimfwc_options["allow_data_share"] === 'on')) {
                add_action('admin_init', array($this, 'admin_init'));
            }

            // Handle deactivation feedback AJAX
            add_action('wp_ajax_mulopimfwc_send_deactivation_feedback', array($this, 'handle_deactivation_feedback'));
        }

        public function init()
        {
            // Any initialization code
        }

        public function admin_init()
        {
            // Send analytics data on first activation or weekly
            $this->maybe_send_analytics();
        }

        private function maybe_send_analytics()
        {
            $last_sent = get_option('onepaquc_analytics_last_sent', 0);
            $week_ago = strtotime('-1 week');

            if ($last_sent < $week_ago) {
                $this->analytics->send_tracking_data();
                update_option('onepaquc_analytics_last_sent', time());
            }
        }

        public function handle_deactivation_feedback()
        {
            check_ajax_referer('deactivation_feedback', 'nonce');

            $reason = sanitize_text_field(wp_unslash($_POST['reason'] ?? ''));
            $this->analytics->send_deactivation_data($reason);

            wp_die();
        }
    }

    new mulopimfwc_analytics_main();
}

if (!function_exists('mulopimfwc_premium_feature')) {
    function mulopimfwc_premium_feature()
    {
        return false;
    }
}

if (!function_exists('mulopimfwc_get_location_cookie_expiry_days')) {
    /**
     * Return the configured number of days for location cookies (default: 30).
     *
     * @return int
     */
    function mulopimfwc_get_location_cookie_expiry_days(): int
    {
        global $mulopimfwc_options;
            $options = is_array($mulopimfwc_options ?? null)
                ? $mulopimfwc_options
                : get_option('mulopimfwc_display_options', []);
        $value = isset($options['location_cookie_expiry']) && is_numeric($options['location_cookie_expiry'])
            ? (int)$options['location_cookie_expiry']
            : 30;

        if ($value < 1) {
            $value = 1;
        }

        return $value;
    }
}

if (!function_exists('mulopimfwc_get_location_cookie_expiry_seconds')) {
    /**
     * Return the configured cookie expiry interval in seconds, honoring WP's DAY_IN_SECONDS.
     *
     * @return int
     */
    function mulopimfwc_get_location_cookie_expiry_seconds(): int
    {
        $day_in_seconds = defined('DAY_IN_SECONDS') ? DAY_IN_SECONDS : 86400;
        return mulopimfwc_get_location_cookie_expiry_days() * $day_in_seconds;
    }
}
