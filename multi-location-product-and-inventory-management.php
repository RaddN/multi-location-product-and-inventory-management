<?php

/**
 * Plugin Name: Multi Location Product & Inventory Management for WooCommerce
 * Plugin URI: https://plugincy.com/multi-location-product-and-inventory-management
 * Description: Filter WooCommerce products by store locations with a location selector for customers.
 * Version: 1.0.2
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

if (!defined('mulopimfwc_VERSION')) {
    define("mulopimfwc_VERSION", "1.0.2");
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

    class mulopimfwc_Location_Wise_Products
    {
        public function __construct()
        {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
            add_action('pre_get_posts', [$this, 'filter_products_by_location']);
            add_filter('woocommerce_shortcode_products_query', [$this, 'filter_shortcode_products']);
            add_filter('woocommerce_products_widget_query_args', [$this, 'filter_widget_products']);
            add_filter('woocommerce_related_products_args', [$this, 'filter_related_products']);
            add_action('init', [$this, 'clear_cart_on_location_change']);

            add_shortcode('mulopimfwc_store_location_selector', [$this, 'location_selector_shortcode']);

            add_filter('the_title', [$this, 'add_location_to_product_title'], 10, 2);
            add_filter('woocommerce_product_title', [$this, 'add_location_to_wc_product_title'], 10, 2);
            new MULOPIMFWC_Admin();
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

            add_action('wp_ajax_clear_cart', [$this, 'clear_cart']);
            add_action('wp_ajax_nopriv_clear_cart', [$this, 'clear_cart']);

            add_action('wp_ajax_check_cart_products', [$this, 'check_cart_products']);
            add_action('wp_ajax_nopriv_check_cart_products', [$this, 'check_cart_products']);

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

            add_action('admin_enqueue_scripts', [$this, 'cymulopimfwc_enqueue_admin_scripts']);
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
            $location_selected = wp_get_object_terms($product_id, 'mulopimfwc_store_location', array('fields' => 'slugs'));
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
                    'selected' => in_array($location->slug, $location_selected),
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
                // Deactivate location - add disabled meta
                update_post_meta($product_id, '_location_disabled_' . $location_id, 1);
                $message = __('Location deactivated successfully.', 'multi-location-product-and-inventory-management');
            }

            wp_send_json_success(['message' => $message]);
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
                '1.0.2',
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
                '1.0.2'
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
            $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=multi-location-product-and-inventory-management')) . '">' . esc_html__('Settings', 'multi-location-product-and-inventory-management') . '</a>';
            $pro_link = '<a href="https://plugincy.com/multi-location-product-and-inventory-management" style="color: #ff5722; font-weight: bold;" target="_blank">' . esc_html__('Get Pro', 'multi-location-product-and-inventory-management') . '</a>';
            $links[] = $settings_link;
            $links[] = $pro_link;
            return $links;
        }

        public function enqueue_scripts()
        {
            global $mulopimfwc_options;

            $cookie_expiry = isset($mulopimfwc_options["location_cookie_expiry"]) && is_numeric($mulopimfwc_options["location_cookie_expiry"])
                ? (int)$mulopimfwc_options["location_cookie_expiry"]
                : 30;

            wp_enqueue_style('mulopimfwc_style', plugins_url('assets/css/style.css', __FILE__), [], '1.0.2');
            wp_enqueue_style('mulopimfwc_select2', plugins_url('assets/css/select2.min.css', __FILE__), [], '4.1.0');
            wp_enqueue_script('mulopimfwc_script', plugins_url('assets/js/script.js', __FILE__), ['jquery'], '1.0.2', true);
            wp_enqueue_script('mulopimfwc_select2', plugins_url('assets/js/select2.min.js', __FILE__), ['jquery'], '4.1.0', true);

            wp_localize_script('mulopimfwc_script', 'mulopimfwc_locationWiseProducts', [
                'cartHasProducts' => !WC()->cart->is_empty(),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'location_change_notification' => isset($mulopimfwc_options["location_change_notification"]),
                'nonce' => wp_create_nonce('multi-location-product-and-inventory-management'),
                'cookie_expiry' => $cookie_expiry
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
            $location = $this->get_current_location();
            global $mulopimfwc_options;
            $enable_all_locations = isset($mulopimfwc_options['enable_all_locations']) ? $mulopimfwc_options['enable_all_locations'] : 'off';
            if (!$location || $location === 'all-products' || $enable_all_locations === 'on') {
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
            $options = get_option('mulopimfwc_display_options', []);
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


        private function get_display_options()
        {
            $options = get_option('mulopimfwc_display_options', []);
            return $options;
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

        private function product_belongs_to_location($product_id)
        {
            $location = $this->get_current_location();
            global $mulopimfwc_options;
            $enable_all_locations = isset($mulopimfwc_options['enable_all_locations']) ? $mulopimfwc_options['enable_all_locations'] : 'off';

            if (!$location || $location === 'all-products') {
                return true;
            }

            $terms = wp_get_object_terms($product_id, 'mulopimfwc_store_location', ['fields' => 'slugs']);
            if (empty($terms) && $enable_all_locations === 'on') {
                return true; // Product is available in all locations
            }
            return (!is_wp_error($terms) && in_array($location, $terms));
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
            $location = $this->get_current_location();
            global $mulopimfwc_options;
            $enable_all_locations = isset($mulopimfwc_options['enable_all_locations']) ? $mulopimfwc_options['enable_all_locations'] : 'off';

            if (!$location || $location === 'all-products') {
                return $products;
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
            $location = $this->get_current_location();

            if (!$location || $location === 'all-products') {
                return $args;
            }

            // if (!isset($args['tax_query'])) {
            //     $args['tax_query'] = [];
            // }

            $args['tax_query'][] = [
                'taxonomy' => 'mulopimfwc_store_location',
                'field' => 'slug',
                'terms' => $location,
            ];

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

            foreach ($cart_contents as $key => $item) {
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
            if (is_admin() || !$query->is_main_query()) {
                return;
            }

            $section = '';
            if (is_shop() || is_product_category() || is_product_tag() || is_post_type_archive('product')) {
                $section = 'shop';
            } elseif (is_search()) {
                $section = 'search';
            } else {
                return;
            }

            $location = $this->get_filtered_location($section);
            if (!$location) {
                return;
            }

            $tax_query = (array) $query->get('tax_query');
            global $mulopimfwc_options;
            $enable_all_locations = isset($mulopimfwc_options['enable_all_locations']) ? $mulopimfwc_options['enable_all_locations'] : 'off';

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



        public function filter_related_products_by_location($related_products, $product_id, $args)
        {
            $location = $this->get_filtered_location('related');

            if (!$location) {
                return $related_products;
            }

            return array_filter($related_products, [$this, 'product_belongs_to_location']);
        }

        public function filter_cross_sells_by_location($cross_sells)
        {
            $location = $this->get_filtered_location('cross_sells');

            if (!$location) {
                return $cross_sells;
            }

            return array_filter($cross_sells, [$this, 'product_belongs_to_location']);
        }

        public function filter_upsells_by_location($upsell_ids, $product_id)
        {
            $location = $this->get_filtered_location('upsells');

            if (!$location) {
                return $upsell_ids;
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
        function custom_admin_styles()
        {
            wp_enqueue_style('mulopimfwc-custom-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', array(), "1.0.2");
        }
    }

    function mulopimfwc_location_wise_products_init()
    {
        new mulopimfwc_Location_Wise_Products();
    }

    add_action('plugins_loaded', 'mulopimfwc_location_wise_products_init');



    register_uninstall_hook(__FILE__, 'mulopimfwc_settings_remove');

    register_activation_hook(__FILE__, 'mulopimfwc_settings_remove');

    function mulopimfwc_settings_remove()
    {
        // Check if the option exists and delete it
        if (get_option('mulopimfwc_display_options') !== false) {
            delete_option('mulopimfwc_display_options');
        }
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
                "1.0.2",
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
            add_action('wp_ajax_send_deactivation_feedback', array($this, 'handle_deactivation_feedback'));
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
