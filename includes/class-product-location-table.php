<?php

/**
 * Product Location Table Class
 *
 * @package Location_Wise_Products
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product Location Table Class
 * Extends the WP_List_Table class to create a custom table for showing products with location data
 */
class mulopimfwc_Product_Location_Table extends WP_List_Table
{

    /**
     * Flag to track if we're ordering by location
     */
    private $ordering_by_location = false;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            'singular' => 'product',
            'plural'   => 'products',
            'ajax'     => false,
        ]);
        
        // Add screen options for per_page
        add_filter('set_screen_option_' . $this->get_screen_option_name(), [$this, 'set_screen_option'], 10, 3);
    }

    /**
     * Get the screen option name for this table
     * Used for storing user's per_page preference
     * 
     * @return string Screen option name
     */
    protected function get_screen_option_name()
    {
        return 'mulopimfwc_products_per_page';
    }

    /**
     * Validate and sanitize screen option value
     * Enforces maximum limit to prevent memory issues
     * 
     * @param mixed $status Current status
     * @param string $option Option name
     * @param mixed $value Option value
     * @return mixed Validated value
     */
    public function set_screen_option($status, $option, $value)
    {
        if ($option === $this->get_screen_option_name()) {
            $max_per_page = $this->get_max_per_page();
            $value = (int) $value;
            // Enforce maximum limit
            return min($value, $max_per_page);
        }
        return $status;
    }

    /**
     * Get the maximum allowed items per page
     * Prevents memory exhaustion and timeouts on large datasets
     * 
     * @return int Maximum items per page (default: 100)
     */
    private function get_max_per_page()
    {
        /**
         * Filter the maximum items per page for the product location table
         * 
         * @param int $max_per_page Maximum items per page (default: 100)
         */
        return apply_filters('mulopimfwc_max_per_page', 100);
    }

    /**
     * Get the default items per page
     * 
     * @return int Default items per page (default: 20)
     */
    private function get_default_per_page()
    {
        /**
         * Filter the default items per page for the product location table
         * 
         * @param int $default_per_page Default items per page (default: 20)
         */
        return apply_filters('mulopimfwc_default_per_page', 20);
    }

    /**
     * Get table columns
     *
     * @return array
     */
    public function get_columns()
    {
        return [
            'cb'            => '<input type="checkbox" />',
            'image'         => __('Image', 'multi-location-product-and-inventory-management'),
            'title'         => __('Product', 'multi-location-product-and-inventory-management'),
            'stock'         => __('Stock by Location', 'multi-location-product-and-inventory-management'),
            'price'         => __('Price by Location', 'multi-location-product-and-inventory-management'),
            'purchase_price' => __('Purchase Info', 'multi-location-product-and-inventory-management'),
            'gross_profit'   => __('Gross Profit', 'multi-location-product-and-inventory-management'),
            'actions'       => __('Actions', 'multi-location-product-and-inventory-management'),
        ];
    }

     /**
     * Get bulk actions
     *
     * @return array
     */
    protected function get_bulk_actions()
    {
        return [
            'bulk_assign_location' => __('Assign to Location', 'multi-location-product-and-inventory-management'),
            'bulk_remove_location' => __('Remove from Location', 'multi-location-product-and-inventory-management'),
            'trash' => __('Move to Trash', 'multi-location-product-and-inventory-management'),
        ];
    }

    /**
     * Default column rendering
     *
     * @param array $item Item data
     * @param string $column_name Column name
     * @return string
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'title':
                return $item['title'];
            case 'stock':
                return $this->get_location_stock_display($item);
            case 'price':
                return $this->get_location_price_display($item);
            case 'purchase_price':
                return $this->get_purchase_price_display($item);
            case 'gross_profit':
                return $this->get_gross_profit_display($item);
            case 'actions':
                return $this->get_actions_display($item);
            default:
                return '';
        }
    }

    /**
     * Checkbox column
     *
     * @param array $item Item data
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="product[]" value="%s" />', $item['id']);
    }

    /**
     * Image column
     *
     * @param array $item Item data
     * @return string
     */
    public function column_image($item)
    {
        return $item['image'];
    }

    /**
     * Title column with action links
     *
     * @param array $item Item data
     * @return string
     */
    public function column_title($item)
    {
        $edit_link = get_edit_post_link($item['id']);
        $view_link = get_permalink($item['id']);
        $trash_link = get_delete_post_link($item['id'], '', true);
        $duplicate_link = wp_nonce_url(
            admin_url('edit.php?post_type=product&action=duplicate_product&post=' . $item['id']),
            'woocommerce-duplicate-product_' . $item['id']
        );

        // Prepare data for quick edit (Manage Stock) popup
        $nonce = wp_create_nonce('location_product_action_nonce');
        $locations = $item['location_terms'];
        $product_location_slugs = wp_list_pluck($locations, 'slug');

        global $mulopimfwc_locations;
        $all_locations_data = [];
        if (!is_wp_error($mulopimfwc_locations) && !empty($mulopimfwc_locations)) {
            foreach ($mulopimfwc_locations as $location) {
                $all_locations_data[] = [
                    'id' => $location->term_id,
                    'name' => $location->name,
                    'parent' => $location->parent,
                    'selected' => in_array(rawurldecode($location->slug), $product_location_slugs),
                ];
            }
        }

        $quick_edit_data = isset($item['quick_edit_data']) ? $item['quick_edit_data'] : null;

        $title = '<strong><a target="_blank" rel="noopener noreferrer" href="' . esc_url($edit_link) . '">' . esc_html($item['title']) . '</a></strong>';
        $title .= '<div class="row-actions">';
        $title .= '<span class="edit"><a target="_blank" rel="noopener noreferrer" href="' . esc_url($edit_link) . '">' . __('Edit', 'multi-location-product-and-inventory-management') . '</a> | </span>';
        $title .= '<span class="view"><a target="_blank" rel="noopener noreferrer" href="' . esc_url($view_link) . '">' . __('View', 'multi-location-product-and-inventory-management') . '</a> | </span>';

        $manage_attrs = 'class="row-quick-edit manage-product-location" data-product-id="' . esc_attr($item['id']) . '" data-product-type="' . esc_attr($item['type']) . '" data-nonce="' . esc_attr($nonce) . '"';
        if ($quick_edit_data) {
            $manage_attrs .= ' data-product-data="' . esc_attr(wp_json_encode($quick_edit_data)) . '"';
        }
        if (!empty($all_locations_data)) {
            $manage_attrs .= ' data-locations="' . esc_attr(wp_json_encode($all_locations_data)) . '"';
        }

        $title .= '<span class="quick-edit"><a href="#" ' . $manage_attrs . '>' . __('Quick Edit', 'multi-location-product-and-inventory-management') . '</a> | </span>';
        $title .= '<span class="trash"><a href="' . esc_url($trash_link) . '">' . __('Trash', 'multi-location-product-and-inventory-management') . '</a> | </span>';
        $title .= '<span class="duplicate"><a href="' . esc_url($duplicate_link) . '">' . __('Duplicate', 'multi-location-product-and-inventory-management') . '</a></span>';
        $title .= '</div>';
        return $title;
    }

    /**
     * Get stock display for each location
     *
     * @param array $item Item data
     * @return string
     */
    private function get_location_stock_display($item)
    {
        // Show -- for affiliate, external, and grouped products (they don't have stock management)
        if ($item['type'] === 'affiliate' || $item['type'] === 'external' || $item['type'] === 'grouped') {
            return '<span style="color: #9ca3af;">--</span>';
        }

        $output = '<div class="location-stock-container">';
        if ($item['type'] === 'variable' && !empty($item['variations'])) {
            $variation_index = 0;
            foreach ($item['variations'] as $variation) {
                $variation_title = implode(', ', array_map(function ($key, $value) {
                    return ucfirst(str_replace('attribute_pa_', '', $key)) . ': ' . $value;
                }, array_keys($variation['attributes']), $variation['attributes']));
                $is_first = $variation_index === 0;
                $accordion_id = 'variation-stock-' . $item['id'] . '-' . $variation_index;
                $output .= '<div class="variation-stock-item accordion-item' . ($is_first ? ' accordion-expanded' : '') . '">';
                $output .= '<div class="accordion-header" data-accordion-target="' . esc_attr($accordion_id) . '">';
                $output .= '<strong>' . esc_html($variation_title) . '</strong>';
                $output .= '<span class="accordion-icon">' . ($is_first ? '−' : '+') . '</span>';
                $output .= '</div>';
                $output .= '<div class="accordion-content' . ($is_first ? ' accordion-open' : '') . '" id="' . esc_attr($accordion_id) . '">';
                
                // Get variation product to check stock management setting
                $variation_product = wc_get_product($variation['id']);
                $manage_stock = $variation_product ? $variation_product->get_manage_stock() : false;
                
                $output .= '<div class="location-stock-item">';
                $output .= '<span class="location-name">' . __('Default', 'multi-location-product-and-inventory-management') . ':</span> ';
                if ($manage_stock) {
                    // Stock management is enabled - use stock quantity
                    $default_stock = get_post_meta($variation['id'], '_stock', true);
                    $output .= '<span class="stock-value">' . ($default_stock ? __('In stock', 'multi-location-product-and-inventory-management') . ' (' . esc_html($default_stock) . ')' : __('Out of stock', 'multi-location-product-and-inventory-management')) . '</span>';
                } else {
                    // Stock management is disabled - check stock status
                    $stock_status = get_post_meta($variation['id'], '_stock_status', true);
                    $stock_status_text = ($stock_status === 'instock') ? __('In stock', 'multi-location-product-and-inventory-management') : __('Out of stock', 'multi-location-product-and-inventory-management');
                    $output .= '<span class="stock-value">' . esc_html($stock_status_text) . '</span>';
                }
                $output .= '</div>';
                
                if (!empty($item['location_terms'])) {
                    foreach ($item['location_terms'] as $location) {
                        $location_stock = get_post_meta($variation['id'], '_location_stock_' . $location->term_id, true);
                        $output .= '<div class="location-stock-item">';
                        $output .= '<span class="location-name">' . esc_html($location->name) . ':</span> ';
                        
                        // For location stock, check if stock is set (empty string means not set)
                        if ($location_stock !== '') {
                            // Location stock is set - use it
                            $output .= '<span class="stock-value">' . ($location_stock > 0 ? __('In stock', 'multi-location-product-and-inventory-management') . ' (' . esc_html($location_stock) . ')' : __('Out of stock', 'multi-location-product-and-inventory-management')) . '</span>';
                        } else {
                            // Location stock not set - check default stock status
                            if ($manage_stock) {
                                // Use default stock quantity
                                $default_stock = get_post_meta($variation['id'], '_stock', true);
                                $output .= '<span class="stock-value">' . ($default_stock ? __('In stock', 'multi-location-product-and-inventory-management') . ' (' . esc_html($default_stock) . ')' : __('Out of stock', 'multi-location-product-and-inventory-management')) . '</span>';
                            } else {
                                // Use default stock status
                                $stock_status = get_post_meta($variation['id'], '_stock_status', true);
                                $stock_status_text = ($stock_status === 'instock') ? __('In stock', 'multi-location-product-and-inventory-management') : __('Out of stock', 'multi-location-product-and-inventory-management');
                                $output .= '<span class="stock-value">' . esc_html($stock_status_text) . '</span>';
                            }
                        }
                        $output .= '</div>';
                    }
                }
                $output .= '</div>';
                $output .= '</div>';
                $variation_index++;
            }
        } else {
            // Get product to check stock management setting
            $product = wc_get_product($item['id']);
            $manage_stock = $product ? $product->get_manage_stock() : false;
            
            if ($manage_stock) {
                // Stock management is enabled - use stock quantity
                $default_stock = get_post_meta($item['id'], "_stock", true);
                $output .= '<div class="location-stock-item">';
                $output .= '<span class="location-name">' . __('Default', 'multi-location-product-and-inventory-management') . ':</span> ';
                $output .= '<span class="stock-value">' . ($default_stock ? __('In stock', 'multi-location-product-and-inventory-management') . ' (' . esc_html($default_stock) . ')' : __('Out of stock', 'multi-location-product-and-inventory-management')) . '</span>';
                $output .= '</div>';
            } else {
                // Stock management is disabled - check stock status
                $stock_status = get_post_meta($item['id'], "_stock_status", true);
                $stock_status_text = ($stock_status === 'instock') ? __('In stock', 'multi-location-product-and-inventory-management') : __('Out of stock', 'multi-location-product-and-inventory-management');
                $output .= '<div class="location-stock-item">';
                $output .= '<span class="location-name">' . __('Default', 'multi-location-product-and-inventory-management') . ':</span> ';
                $output .= '<span class="stock-value">' . esc_html($stock_status_text) . '</span>';
                $output .= '</div>';
            }
            if (!empty($item['location_terms'])) {
                foreach ($item['location_terms'] as $location) {
                    $location_stock = get_post_meta($item['id'], '_location_stock_' . $location->term_id, true);
                    $output .= '<div class="location-stock-item">';
                    $output .= '<span class="location-name">' . esc_html($location->name) . ':</span> ';
                    
                    // For location stock, check if stock is set (empty string means not set)
                    if ($location_stock !== '') {
                        // Location stock is set - use it
                        $output .= '<span class="stock-value">' . ($location_stock > 0 ? __('In stock', 'multi-location-product-and-inventory-management') . ' (' . esc_html($location_stock) . ')' : __('Out of stock', 'multi-location-product-and-inventory-management')) . '</span>';
                    } else {
                        // Location stock not set - check default stock status
                        if ($manage_stock) {
                            // Use default stock quantity
                            $default_stock = get_post_meta($item['id'], '_stock', true);
                            $output .= '<span class="stock-value">' . ($default_stock ? __('In stock', 'multi-location-product-and-inventory-management') . ' (' . esc_html($default_stock) . ')' : __('Out of stock', 'multi-location-product-and-inventory-management')) . '</span>';
                        } else {
                            // Use default stock status
                            $stock_status = get_post_meta($item['id'], '_stock_status', true);
                            $stock_status_text = ($stock_status === 'instock') ? __('In stock', 'multi-location-product-and-inventory-management') : __('Out of stock', 'multi-location-product-and-inventory-management');
                            $output .= '<span class="stock-value">' . esc_html($stock_status_text) . '</span>';
                        }
                    }
                    $output .= '</div>';
                }
            }
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Get price display for each location
     *
     * @param array $item Item data
     * @return string
     */
    private function get_location_price_display($item)
    {
        // Show -- for grouped products
        if ($item['type'] === 'grouped') {
            return '<span style="color: #9ca3af;">--</span>';
        }
        $output = '<div class="location-price-container">';
        if ($item['type'] === 'variable' && !empty($item['variations'])) {
            $variation_index = 0;
            foreach ($item['variations'] as $variation) {
                $variation_title = implode(', ', array_map(function ($key, $value) {
                    return ucfirst(str_replace('attribute_pa_', '', $key)) . ': ' . $value;
                }, array_keys($variation['attributes']), $variation['attributes']));
                $is_first = $variation_index === 0;
                $accordion_id = 'variation-price-' . $item['id'] . '-' . $variation_index;
                $output .= '<div class="variation-price-item accordion-item' . ($is_first ? ' accordion-expanded' : '') . '">';
                $output .= '<div class="accordion-header" data-accordion-target="' . esc_attr($accordion_id) . '">';
                $output .= '<strong>' . esc_html($variation_title) . '</strong>';
                $output .= '<span class="accordion-icon">' . ($is_first ? '−' : '+') . '</span>';
                $output .= '</div>';
                $output .= '<div class="accordion-content' . ($is_first ? ' accordion-open' : '') . '" id="' . esc_attr($accordion_id) . '">';
                $output .= '<div class="location-price-item">';
                $output .= '<span class="location-name">' . __('Default', 'multi-location-product-and-inventory-management') . ':</span> ';
                $output .= '<span class="price-value">' . wc_price($variation['price']) . '</span>';
                $output .= '</div>';
                if (!empty($item['location_terms'])) {
                    foreach ($item['location_terms'] as $location) {
                        $price = get_post_meta($variation['id'], '_location_sale_price_' . $location->term_id, true);
                        $output .= '<div class="location-price-item">';
                        $output .= '<span class="location-name">' . esc_html($location->name) . ':</span> ';
                        $output .= '<span class="price-value">' . (!empty($price) ? wc_price($price) : wc_price($variation['price'])) . '</span>';
                        $output .= '</div>';
                    }
                }
                $output .= '</div>';
                $output .= '</div>';
                $variation_index++;
            }
        } else {
            $default_price = get_post_meta($item['id'], "_price", true);
            $output .= '<div class="location-price-item">';
            $output .= '<span class="location-name">' . __('Default', 'multi-location-product-and-inventory-management') . ':</span> ';
            $output .= '<span class="price-value">' . wc_price($default_price) . '</span>';
            $output .= '</div>';
            if (!empty($item['location_terms'])) {
                foreach ($item['location_terms'] as $location) {
                    $price = get_post_meta($item['id'], '_location_sale_price_' . $location->term_id, true);
                    $output .= '<div class="location-price-item">';
                    $output .= '<span class="location-name">' . esc_html($location->name) . ':</span> ';
                    $output .= '<span class="price-value">' . (!empty($price) ? wc_price($price) : wc_price($default_price)) . '</span>';
                    $output .= '</div>';
                }
            }
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Get purchase price display
     *
     * @param array $item Item data
     * @return string
     */
    private function get_purchase_price_display($item)
    {
        // Show -- for grouped products
        if ($item['type'] === 'grouped') {
            return '<span style="color: #9ca3af;">--</span>';
        }

        $output = '<div class="purchase-price-container">';

        if ($item['type'] === 'variable' && !empty($item['variations'])) {
            foreach ($item['variations'] as $variation) {
                $variation_title = implode(', ', array_map(function ($key, $value) {
                    return ucfirst(str_replace('attribute_pa_', '', $key)) . ': ' . $value;
                }, array_keys($variation['attributes']), $variation['attributes']));

                $purchase_price = get_post_meta($variation['id'], '_purchase_price', true);

                $output .= '<div class="variation-purchase-price-item">';
                $output .= '<strong>' . esc_html($variation_title) . '</strong>';
                $output .= '<div class="purchase-price-item">';
                $output .= '<span class="purchase-price-value">' . (!empty($purchase_price) ? wc_price($purchase_price) : __('Not set', 'multi-location-product-and-inventory-management')) . '</span>';
                $output .= '</div>';
                $output .= '</div>';
            }
        } else {
            $purchase_price = get_post_meta($item['id'], '_purchase_price', true);
            $purchase_quantity = get_post_meta($item['id'], '_purchase_quantity', true);
            $output .= '<div class="purchase-price-item">';
            $output .= '<span class="purchase-price-value"> Price: ' . (!empty($purchase_price) ? wc_price($purchase_price) : __('Not set', 'multi-location-product-and-inventory-management')) . '</span>';
            $output .= '</div>';
            $output .= '<div class="purchase-price-item">';
            $output .= '<span class="purchase-price-value"> Quantity: ' . (!empty($purchase_quantity) ? $purchase_quantity : __('Not set', 'multi-location-product-and-inventory-management')) . '</span>';
            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Get gross profit display
     *
     * @param array $item Item data
     * @return string
     */
    private function get_gross_profit_display($item)
    {
        // Show -- for grouped products
        if ($item['type'] === 'grouped') {
            return '<span style="color: #9ca3af;">--</span>';
        }

        $output = '<div class="gross-profit-container mulopimfwc_pro_only mulopimfwc_pro_only_blur">';

        if ($item['type'] === 'variable' && !empty($item['variations'])) {
            $variation_index = 0;
            foreach ($item['variations'] as $variation) {
                $variation_title = implode(', ', array_map(function ($key, $value) {
                    return ucfirst(str_replace('attribute_pa_', '', $key)) . ': ' . $value;
                }, array_keys($variation['attributes']), $variation['attributes']));

                $purchase_price = get_post_meta($variation['id'], '_purchase_price', true);
                $default_price = $variation['price'];

                $is_first = $variation_index === 0;
                $accordion_id = 'variation-profit-' . $item['id'] . '-' . $variation_index;
                $output .= '<div class="variation-gross-profit-item accordion-item' . ($is_first ? ' accordion-expanded' : '') . '">';
                $output .= '<div class="accordion-header" data-accordion-target="' . esc_attr($accordion_id) . '">';
                $output .= '<strong>' . esc_html($variation_title) . '</strong>';
                $output .= '<span class="accordion-icon">' . ($is_first ? '−' : '+') . '</span>';
                $output .= '</div>';
                $output .= '<div class="accordion-content' . ($is_first ? ' accordion-open' : '') . '" id="' . esc_attr($accordion_id) . '">';

                // Default gross profit
                $output .= '<div class="location-gross-profit-item">';
                $output .= '<span class="location-name">' . __('Default', 'multi-location-product-and-inventory-management') . ':</span> ';
                $output .= $this->calculate_profit_display($default_price, $purchase_price);
                $output .= '</div>';

                // Location-specific gross profit
                if (!empty($item['location_terms'])) {
                    foreach ($item['location_terms'] as $location) {
                        $location_price = get_post_meta($variation['id'], '_location_sale_price_' . $location->term_id, true);
                        $price_to_use = !empty($location_price) ? $location_price : $default_price;

                        $output .= '<div class="location-gross-profit-item">';
                        $output .= '<span class="location-name">' . esc_html($location->name) . ':</span> ';
                        $output .= $this->calculate_profit_display($price_to_use, $purchase_price);
                        $output .= '</div>';
                    }
                }

                $output .= '</div>';
                $output .= '</div>';
                $variation_index++;
            }
        } else {
            $purchase_price = get_post_meta($item['id'], '_purchase_price', true);
            $default_price = get_post_meta($item['id'], "_price", true);

            // Default gross profit
            $output .= '<div class="location-gross-profit-item">';
            $output .= '<span class="location-name">' . __('Default', 'multi-location-product-and-inventory-management') . ':</span> ';
            $output .= $this->calculate_profit_display($default_price, $purchase_price);
            $output .= '</div>';

            // Location-specific gross profit
            if (!empty($item['location_terms'])) {
                foreach ($item['location_terms'] as $location) {
                    $location_price = get_post_meta($item['id'], '_location_sale_price_' . $location->term_id, true);
                    $price_to_use = !empty($location_price) ? $location_price : $default_price;

                    $output .= '<div class="location-gross-profit-item">';
                    $output .= '<span class="location-name">' . esc_html($location->name) . ':</span> ';
                    $output .= $this->calculate_profit_display($price_to_use, $purchase_price);
                    $output .= '</div>';
                }
            }
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Calculate and format profit display
     *
     * @param float $sale_price Sale price
     * @param float $purchase_price Purchase price
     * @return string Formatted profit display
     */
    private function calculate_profit_display($sale_price, $purchase_price)
    {
        // Generate random profit instead of calculating
        $profit = rand(-100, 100); // Random profit between -100 and 100
        $gross_profit = wc_price($profit);

        // Generate random percentage
        $percentage = rand(-100, 100); // Random percentage between -100% and 100%
        $gross_profit_percentage = round($percentage, 2) . '%';

        // Determine color based on profit
        $profit_class = $profit > 0 ? 'positive-profit' : ($profit < 0 ? 'negative-profit' : 'zero-profit');

        return '<span class="gross-profit-value ' . $profit_class . '">' .
            $gross_profit . ' <span class="profit-percentage">(' . $gross_profit_percentage . ')</span></span>';
    }

    /**
     * Get locations display
     *
     * @param array $item Item data
     * @return string
     */
    private function get_locations_display($item)
    {
        $locations = $item['location_terms'];
        if (empty($locations)) {
            return '<span class="no-locations">' . __('N/A', 'multi-location-product-and-inventory-management') . '</span>';
        }
        $output = '<div class="product-locations">';
        foreach ($locations as $location) {
            $output .= '<span class="location-tag">' . esc_html($location->name) . '</span>';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Get actions display
     *
     * @param array $item Item data
     * @return string
     */
    private function get_actions_display($item)
    {
        // Create nonce for action buttons
        $nonce = wp_create_nonce('location_product_action_nonce');

        $locations = $item['location_terms'];
        $product_location_slugs = wp_list_pluck($locations, 'slug');

        global $mulopimfwc_locations;
        $all_locations_data = [];
        if (!is_wp_error($mulopimfwc_locations) && !empty($mulopimfwc_locations)) {
            foreach ($mulopimfwc_locations as $location) {
                $all_locations_data[] = [
                    'id' => $location->term_id,
                    'name' => $location->name,
                    'parent' => $location->parent,
                    'selected' => in_array(rawurldecode($location->slug), $product_location_slugs),
                ];
            }
        }

        if (empty($locations)) {
            $quick_edit_data = isset($item['quick_edit_data']) ? $item['quick_edit_data'] : null;
            $button = '<a href="#" class="button button-small add-location" data-product-id="' . esc_attr($item['id']) . '" data-product-type="' . esc_attr($item['type']) . '" data-nonce="' . esc_attr($nonce) . '"';
            if ($quick_edit_data) {
                $button .= ' data-product-data="' . esc_attr(wp_json_encode($quick_edit_data)) . '"';
            }
            if (!empty($all_locations_data)) {
                $button .= ' data-locations="' . esc_attr(wp_json_encode($all_locations_data)) . '"';
            }
            $button .= '>' . __('Add to Location', 'multi-location-product-and-inventory-management') . '</a>';
            return $button;
        }
        $output = '<div class="location-actions">';
        foreach ($locations as $location) {
            $is_active = !get_post_meta($item['id'], '_location_disabled_' . $location->term_id, true);
            $action_class = $is_active ? 'activate-location' : 'deactivate-location';
            $action_text = $is_active ? __('Activated', 'multi-location-product-and-inventory-management') : __('Deactivated', 'multi-location-product-and-inventory-management');
            $button_class = $is_active ? 'button-primary' : 'button-secondary';

            $output .= '<div class="location-action-item">';
            $output .= '<span class="location-name">' . esc_html($location->name) . ':</span> ';
            $output .= '<a href="#" class="button button-small ' . esc_attr($button_class) . ' ' . esc_attr($action_class) . '" ' .
                'data-product-id="' . esc_attr($item['id']) . '" ' .
                'data-location-id="' . esc_attr($location->term_id) . '" ' .
                'data-action="' . ($is_active ? 'deactivate' : 'activate') . '" ' .
                'data-nonce="' . esc_attr($nonce) . '">' .
                esc_html($action_text) . '</a>';
            $output .= '</div>';
        }
        // Prepare location data for Edit Location popup
        // Store complete product data in data attribute for instant popup
        $quick_edit_data = isset($item['quick_edit_data']) ? $item['quick_edit_data'] : null;

        // Single button for both edit location and quick edit
        if ($quick_edit_data) {
            $output .= '<a href="#" class="button button-small manage-product-location" style="margin-top: 5px; display: block;" data-product-id="' . esc_attr($item['id']) . '" data-product-type="' . esc_attr($item['type']) . '" data-product-data="' . esc_attr(wp_json_encode($quick_edit_data)) . '" data-locations="' . esc_attr(wp_json_encode($all_locations_data)) . '" data-nonce="' . esc_attr($nonce) . '">' . __('Manage Stock', 'multi-location-product-and-inventory-management') . '</a>';
        } else {
            $output .= '<a href="#" class="button button-small manage-product-location" style="margin-top: 5px; display: block;" data-product-id="' . esc_attr($item['id']) . '" data-product-type="' . esc_attr($item['type']) . '" data-locations="' . esc_attr(wp_json_encode($all_locations_data)) . '" data-nonce="' . esc_attr($nonce) . '">' . __('Manage Stock', 'multi-location-product-and-inventory-management') . '</a>';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Process bulk actions
     */
    public function process_bulk_action()
    {
        // Check if bulk action is set
        if (!isset($_REQUEST['action']) && !isset($_REQUEST['action2'])) {
            return;
        }

        $action = isset($_REQUEST['action']) && $_REQUEST['action'] != '-1' 
            ? sanitize_text_field(wp_unslash($_REQUEST['action'])) 
            : (isset($_REQUEST['action2']) && $_REQUEST['action2'] != '-1' 
                ? sanitize_text_field(wp_unslash($_REQUEST['action2'])) 
                : '');

        if (empty($action)) {
            return;
        }

        // Verify nonce
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'bulk-' . $this->_args['plural'])) {
            wp_die(__('Security check failed', 'multi-location-product-and-inventory-management'));
        }

        // Check if products are selected
        if (!isset($_REQUEST['product']) || !is_array($_REQUEST['product'])) {
            return;
        }

        $product_ids = array_map('intval', $_REQUEST['product']);
        $count = 0;

        switch ($action) {
            case 'bulk_assign_location':
                if (isset($_REQUEST['bulk_location_id']) && !empty($_REQUEST['bulk_location_id'])) {
                    $location_id = intval($_REQUEST['bulk_location_id']);
                    foreach ($product_ids as $product_id) {
                        $term = get_term($location_id, 'mulopimfwc_store_location');
                        if ($term && !is_wp_error($term)) {
                            wp_set_object_terms($product_id, [$location_id], 'mulopimfwc_store_location', true);
                            $count++;
                        }
                    }
                    add_action('admin_notices', function() use ($count) {
                        echo '<div class="notice notice-success is-dismissible"><p>';
                        printf(
                            esc_html__('Successfully assigned %d products to location.', 'multi-location-product-and-inventory-management'),
                            $count
                        );
                        echo '</p></div>';
                    });
                } else {
                    // Store for modal selection
                    set_transient('mulopimfwc_bulk_action_assign_location', $product_ids, 300);
                }
                break;

            case 'bulk_remove_location':
                if (isset($_REQUEST['bulk_location_id']) && !empty($_REQUEST['bulk_location_id'])) {
                    $location_id = intval($_REQUEST['bulk_location_id']);
                    foreach ($product_ids as $product_id) {
                        wp_remove_object_terms($product_id, $location_id, 'mulopimfwc_store_location');
                        $count++;
                    }
                    add_action('admin_notices', function() use ($count) {
                        echo '<div class="notice notice-success is-dismissible"><p>';
                        printf(
                            esc_html__('Successfully removed %d products from location.', 'multi-location-product-and-inventory-management'),
                            $count
                        );
                        echo '</p></div>';
                    });
                } else {
                    // Store for modal selection
                    set_transient('mulopimfwc_bulk_action_remove_location', $product_ids, 300);
                }
                break;

            case 'trash':
                foreach ($product_ids as $product_id) {
                    if (current_user_can('delete_post', $product_id)) {
                        wp_trash_post($product_id);
                        $count++;
                    }
                }
                if ($count > 0) {
                    add_action('admin_notices', function() use ($count) {
                        echo '<div class="notice notice-success is-dismissible"><p>';
                        printf(
                            esc_html__('Successfully moved %d product(s) to trash.', 'multi-location-product-and-inventory-management'),
                            $count
                        );
                        echo '</p></div>';
                    });
                }
                break;
        }
    }

    /**
     * Batch load location meta for multiple products/variations
     * Prevents N+1 query problem by loading all meta in a single query
     * 
     * @param array $product_ids Array of product IDs
     * @param array $variation_ids Array of variation IDs
     * @param array $location_ids Array of location IDs
     * @return array Cache structure: [post_id][location_id][meta_key] => meta_value
     */
    private function batch_load_location_meta($product_ids, $variation_ids, $location_ids)
    {
        $cache = [];
        $all_post_ids = array_unique(array_merge($product_ids, $variation_ids));
        
        if (empty($all_post_ids) || empty($location_ids)) {
            return $cache;
        }

        global $wpdb;
        
        // Build meta keys for all locations
        $meta_keys = [];
        foreach ($location_ids as $location_id) {
            $meta_keys[] = '_location_stock_' . $location_id;
            $meta_keys[] = '_location_regular_price_' . $location_id;
            $meta_keys[] = '_location_sale_price_' . $location_id;
            $meta_keys[] = '_location_backorders_' . $location_id;
        }

        if (empty($meta_keys)) {
            return $cache;
        }

        // Prepare placeholders for IN clauses (sanitize as integers for post_ids, strings for meta_keys)
        $post_ids_placeholder = implode(',', array_map('intval', $all_post_ids));
        $meta_keys_escaped = array_map(function($key) use ($wpdb) {
            return $wpdb->prepare('%s', $key);
        }, $meta_keys);
        $meta_keys_placeholder = implode(',', $meta_keys_escaped);

        // Single query to get all location meta
        $query = "SELECT post_id, meta_key, meta_value 
                  FROM {$wpdb->postmeta} 
                  WHERE post_id IN ($post_ids_placeholder) 
                  AND meta_key IN ($meta_keys_placeholder)";

        $results = $wpdb->get_results($query);

        // Organize results by post_id and location_id
        foreach ($results as $row) {
            $post_id = (int) $row->post_id;
            $meta_key = $row->meta_key;
            $meta_value = $row->meta_value;

            // Extract location_id from meta_key (e.g., '_location_stock_123' => 123)
            // Meta keys format: '_location_stock_{location_id}', '_location_regular_price_{location_id}', etc.
            foreach ($location_ids as $location_id) {
                $suffix = '_' . $location_id;
                if (strpos($meta_key, '_location_') === 0 && substr($meta_key, -strlen($suffix)) === $suffix) {
                    if (!isset($cache[$post_id])) {
                        $cache[$post_id] = [];
                    }
                    if (!isset($cache[$post_id][$location_id])) {
                        $cache[$post_id][$location_id] = [];
                    }
                    $cache[$post_id][$location_id][$meta_key] = $meta_value;
                    break;
                }
            }
        }

        return $cache;
    }

    /**
     * Prepare table items
     */
    public function prepare_items()
    {
        // Process bulk actions first
        $this->process_bulk_action();

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        // Get per_page from user preference, screen option, or default
        // WP_List_Table's get_items_per_page() handles screen options automatically
        $default_per_page = $this->get_default_per_page();
        $user_per_page = $this->get_items_per_page($this->get_screen_option_name(), $default_per_page);
        
        // Enforce maximum limit to prevent memory exhaustion and timeouts
        // This is critical for sites with 10,000+ products
        $max_per_page = $this->get_max_per_page();
        $per_page = min((int) $user_per_page, $max_per_page);
        
        // Ensure per_page is at least 1
        $per_page = max(1, $per_page);
        
        // If user tried to set a value higher than max, show a notice
        if ((int) $user_per_page > $max_per_page) {
            add_action('admin_notices', function() use ($max_per_page) {
                echo '<div class="notice notice-warning is-dismissible"><p>';
                printf(
                    esc_html__('Maximum items per page is limited to %d for performance reasons. Large datasets require pagination.', 'multi-location-product-and-inventory-management'),
                    $max_per_page
                );
                echo '</p></div>';
            });
        }
        
        $current_page = $this->get_pagenum();
        
        // Ensure current_page is valid (at least 1)
        $current_page = max(1, (int) $current_page);

        $args = [
            'post_type' => 'product',
            'posts_per_page' => $per_page,
            'paged' => $current_page,
            'post_status' => 'publish',
            'no_found_rows' => false, // We need found_posts for pagination
            'nopaging' => false, // Explicitly disable loading all posts - CRITICAL for memory management
        ];
        
        // Additional safeguard: Ensure posts_per_page is never -1 or 0
        // WordPress allows -1 to load all posts, which we must prevent
        if ($args['posts_per_page'] <= 0) {
            $args['posts_per_page'] = $this->get_default_per_page();
        }

        // Add search if set
        if (isset($_REQUEST['s']) && !empty($_REQUEST['s'])) {
            $args['s'] = sanitize_text_field(wp_unslash($_REQUEST['s']));
        }

        // Build tax_query array for multiple filters
        $tax_queries = [];

        // Add location filter if set
        if (isset($_REQUEST['filter-by-location']) && !empty($_REQUEST['filter-by-location'])) {
            $tax_queries[] = [
                'taxonomy' => 'mulopimfwc_store_location',
                'field'    => 'slug',
                'terms'    => sanitize_text_field(wp_unslash($_REQUEST['filter-by-location'])),
            ];
        }

        // Add category filter if set
        if (isset($_REQUEST['filter-by-category']) && !empty($_REQUEST['filter-by-category'])) {
            $tax_queries[] = [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => intval($_REQUEST['filter-by-category']),
            ];
        }

        // Add brand filter if set (check for common brand taxonomies)
        $brand_taxonomies = ['product_brand', 'pa_brand', 'pwb-brand'];
        foreach ($brand_taxonomies as $brand_tax) {
            if (taxonomy_exists($brand_tax) && isset($_REQUEST['filter-by-brand']) && !empty($_REQUEST['filter-by-brand'])) {
                $tax_queries[] = [
                    'taxonomy' => $brand_tax,
                    'field'    => 'term_id',
                    'terms'    => intval($_REQUEST['filter-by-brand']),
                ];
                break; // Only use first available brand taxonomy
            }
        }

        

        // Add product type filter
        if (isset($_REQUEST['filter-by-type']) && !empty($_REQUEST['filter-by-type'])) {
            $product_type = sanitize_text_field(wp_unslash($_REQUEST['filter-by-type']));
            if (in_array($product_type, ['simple', 'variable', 'grouped', 'external', 'affiliate'])) {
                // WooCommerce stores product type in taxonomy
                $tax_queries[] = [
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => $product_type,
                ];
            }
        }

        // Add tax_query if we have any tax queries
        if (!empty($tax_queries)) {
            if (count($tax_queries) > 1) {
                $args['tax_query'] = [
                    'relation' => 'AND',
                ];
                $args['tax_query'] = array_merge($args['tax_query'], $tax_queries);
            } else {
                $args['tax_query'] = $tax_queries;
            }
        }

        // Add stock status filter
        if (isset($_REQUEST['filter-by-stock-status']) && !empty($_REQUEST['filter-by-stock-status'])) {
            $stock_status = sanitize_text_field(wp_unslash($_REQUEST['filter-by-stock-status']));
            if (in_array($stock_status, ['instock', 'outofstock', 'onbackorder'])) {
                $args['meta_query'][] = [
                    'key' => '_stock_status',
                    'value' => $stock_status,
                    'compare' => '=',
                ];
            }
        }

        // Handle meta_query relation if we have multiple meta queries
        if (isset($args['meta_query']) && count($args['meta_query']) > 1) {
            $args['meta_query']['relation'] = 'AND';
        }

        // Enable ordering by location assignment
        $this->ordering_by_location = true;
        add_filter('posts_clauses', [$this, 'order_by_location_assignment'], 10, 2);

        $query = new WP_Query($args);

        // Remove the filter after query
        remove_filter('posts_clauses', [$this, 'order_by_location_assignment'], 10);
        $this->ordering_by_location = false;

        // Safety check: Ensure query didn't accidentally load all posts
        // This prevents memory exhaustion if something goes wrong
        $max_per_page = $this->get_max_per_page();
        if ($query->post_count > $max_per_page) {
            // Something went wrong - log error and limit results
            error_log('Multi Location Plugin: Query returned more posts than allowed. Limiting to ' . $max_per_page);
            // Truncate the posts array to prevent memory issues
            $query->posts = array_slice($query->posts, 0, $max_per_page);
            $query->post_count = count($query->posts);
        }

        $this->items = [];

        if ($query->have_posts()) {
            // First pass: collect all product IDs, variation IDs, and location IDs for batch loading
            $all_product_ids = [];
            $all_variation_ids = [];
            $all_location_ids = [];
            $products_data = [];

            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);
                if (!$product) {
                    continue;
                }

                $all_product_ids[] = $product_id;
                $product_type = $product->get_type();

                // Get product locations
                $location_terms = wp_get_object_terms($product_id, 'mulopimfwc_store_location');
                $assigned_location_ids = [];
                if (!is_wp_error($location_terms) && !empty($location_terms)) {
                    $assigned_location_ids = array_map(function($term) {
                        return $term->term_id;
                    }, $location_terms);
                    $all_location_ids = array_merge($all_location_ids, $assigned_location_ids);
                }

                // Collect variation IDs for variable products
                if ($product_type === 'variable') {
                    $variation_ids = $product->get_children();
                    if (!empty($variation_ids)) {
                        $all_variation_ids = array_merge($all_variation_ids, $variation_ids);
                    }
                }

                // Store product data for second pass
                $products_data[$product_id] = [
                    'product' => $product,
                    'product_type' => $product_type,
                    'location_terms' => $location_terms,
                    'assigned_location_ids' => $assigned_location_ids,
                ];
            }
            wp_reset_postdata();

            // Batch load all location meta in a single query
            // Limit the number of items we process to prevent memory issues
            $max_per_page = $this->get_max_per_page();
            if (count($all_product_ids) > $max_per_page) {
                // Safety limit: Only process up to max_per_page products
                $all_product_ids = array_slice($all_product_ids, 0, $max_per_page);
                $all_variation_ids = array_slice($all_variation_ids, 0, $max_per_page * 10); // Allow more variations
            }
            
            $all_location_ids = array_unique($all_location_ids);
            $location_meta_cache = $this->batch_load_location_meta($all_product_ids, $all_variation_ids, $all_location_ids);

            // Second pass: build items using cached meta data
            foreach ($products_data as $product_id => $data) {
                $product = $data['product'];
                $product_type = $data['product_type'];
                $location_terms = $data['location_terms'];
                $assigned_location_ids = $data['assigned_location_ids'];

                // Get product thumbnail
                $thumbnail = $product->get_image('thumbnail', ['class' => 'product-thumbnail']);

                // Handle variable products
                if ($product_type === 'variable') {
                    $variations = [];
                    $available_variations = $product->get_available_variations();
                    foreach ($available_variations as $variation) {
                        $variations[] = [
                            'id' => $variation['variation_id'],
                            'attributes' => $variation['attributes'],
                            'price' => $variation['display_price'],
                            'stock' => $variation['is_in_stock'] ? $variation['max_qty'] : 0,
                        ];
                    }
                }

                // Collect all product data for quick edit popup
                $product_data = [
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'type' => $product_type,
                    'product_type' => $product_type, // Also include as product_type for JavaScript compatibility
                    'default' => [
                        'stock_quantity' => $product->get_stock_quantity(),
                        'regular_price' => $product->get_regular_price(),
                        'sale_price' => $product->get_sale_price(),
                        'backorders' => $product->get_backorders(),
                        'purchase_price' => get_post_meta($product_id, '_purchase_price', true),
                        'purchase_quantity' => get_post_meta($product_id, '_purchase_quantity', true),
                    ],
                    'locations' => [],
                    'variations' => [],
                ];

                // Get location data using cached meta
                if (!empty($assigned_location_ids)) {
                    foreach ($assigned_location_ids as $location_id) {
                        $location_term = get_term($location_id, 'mulopimfwc_store_location');
                        if (!$location_term || is_wp_error($location_term)) {
                            continue;
                        }
                        
                        // Use cached meta data
                        $cached_meta = isset($location_meta_cache[$product_id][$location_id]) 
                            ? $location_meta_cache[$product_id][$location_id] 
                            : [];
                        
                        $product_data['locations'][] = [
                            'id' => $location_id,
                            'name' => $location_term->name,
                            'stock' => isset($cached_meta['_location_stock_' . $location_id]) 
                                ? $cached_meta['_location_stock_' . $location_id] 
                                : '',
                            'regular_price' => isset($cached_meta['_location_regular_price_' . $location_id]) 
                                ? $cached_meta['_location_regular_price_' . $location_id] 
                                : '',
                            'sale_price' => isset($cached_meta['_location_sale_price_' . $location_id]) 
                                ? $cached_meta['_location_sale_price_' . $location_id] 
                                : '',
                            'backorders' => isset($cached_meta['_location_backorders_' . $location_id]) 
                                ? $cached_meta['_location_backorders_' . $location_id] 
                                : '',
                        ];
                    }
                }

                // Get variation data for variable products
                if ($product_type === 'variable') {
                    $variation_ids = $product->get_children();
                    if (!empty($variation_ids)) {
                        foreach ($variation_ids as $variation_id) {
                            $variation = wc_get_product($variation_id);
                            if (!$variation) {
                                continue;
                            }

                            // Format attributes for display
                            $attributes = [];
                            foreach ($variation->get_attributes() as $key => $value) {
                                $attributes[$key] = $value;
                            }

                            $variation_data = [
                                'id' => $variation_id,
                                'attributes' => $attributes,
                                'default' => [
                                    'stock_quantity' => $variation->get_stock_quantity(),
                                    'regular_price' => $variation->get_regular_price(),
                                    'sale_price' => $variation->get_sale_price(),
                                    'backorders' => $variation->get_backorders(),
                                    'purchase_price' => get_post_meta($variation_id, '_purchase_price', true),
                                ],
                                'locations' => [],
                            ];

                            // Get location data for variation using cached meta
                            if (!empty($assigned_location_ids)) {
                                foreach ($assigned_location_ids as $location_id) {
                                    $location_term = get_term($location_id, 'mulopimfwc_store_location');
                                    if (!$location_term || is_wp_error($location_term)) {
                                        continue;
                                    }
                                    
                                    // Use cached meta data
                                    $cached_meta = isset($location_meta_cache[$variation_id][$location_id]) 
                                        ? $location_meta_cache[$variation_id][$location_id] 
                                        : [];
                                    
                                    $variation_data['locations'][] = [
                                        'id' => $location_id,
                                        'name' => $location_term->name,
                                        'stock' => isset($cached_meta['_location_stock_' . $location_id]) 
                                            ? $cached_meta['_location_stock_' . $location_id] 
                                            : '',
                                        'regular_price' => isset($cached_meta['_location_regular_price_' . $location_id]) 
                                            ? $cached_meta['_location_regular_price_' . $location_id] 
                                            : '',
                                        'sale_price' => isset($cached_meta['_location_sale_price_' . $location_id]) 
                                            ? $cached_meta['_location_sale_price_' . $location_id] 
                                            : '',
                                        'backorders' => isset($cached_meta['_location_backorders_' . $location_id]) 
                                            ? $cached_meta['_location_backorders_' . $location_id] 
                                            : '',
                                    ];
                                }
                            }

                            $product_data['variations'][] = $variation_data;
                        }
                    }
                }

                $this->items[] = [
                    'id' => $product_id,
                    'title' => $product->get_name(),
                    'image' => $thumbnail,
                    'location_terms' => is_wp_error($location_terms) ? [] : $location_terms,
                    'type' => $product_type,
                    'variations' => $product_type === 'variable' ? $variations : [],
                    'purchase_price' => get_post_meta($product_id, '_purchase_price', true),
                    'quick_edit_data' => $product_data, // Store complete data for popup
                ];
            }
            
            // Clear memory: Unset large arrays that are no longer needed
            unset($products_data, $location_meta_cache, $all_product_ids, $all_variation_ids, $all_location_ids);
            
            wp_reset_postdata();
        }

        $total_items = $query->found_posts;
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        return [
            'title' => ['title', false],
        ];
    }

    /**
     * Extra controls to be displayed between bulk actions and pagination
     *
     * @param string $which Position (top or bottom)
     */
    protected function extra_tablenav($which)
    {
        global $mulopimfwc_locations;
        if ($which == 'top') {
            // Filters section
            echo '<div class="alignleft actions filters-section">';
            
            // Location filter
            if (!is_wp_error($mulopimfwc_locations) && !empty($mulopimfwc_locations)) {
                $selected_location = isset($_REQUEST['filter-by-location']) ? sanitize_text_field(wp_unslash($_REQUEST['filter-by-location'])) : '';
                echo '<select name="filter-by-location" id="filter-by-location">';
                echo '<option value="">' . esc_html__('All Locations', 'multi-location-product-and-inventory-management') . '</option>';
                foreach ($mulopimfwc_locations as $location) {
                    $location_slug = rawurldecode($location->slug);
                    $selected = ($selected_location == $location_slug) ? 'selected="selected"' : '';
                    echo '<option value="' . esc_attr($location_slug) . '" ' . esc_attr($selected) . '>' . esc_html($location->name) . '</option>';
                }
                echo '</select>';
            }

            // Category filter
            $categories = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
            ]);
            if (!is_wp_error($categories) && !empty($categories)) {
                $selected_category = isset($_REQUEST['filter-by-category']) ? intval($_REQUEST['filter-by-category']) : '';
                echo '<select name="filter-by-category" id="filter-by-category">';
                echo '<option value="">' . esc_html__('All Categories', 'multi-location-product-and-inventory-management') . '</option>';
                foreach ($categories as $category) {
                    $selected = ($selected_category == $category->term_id) ? 'selected="selected"' : '';
                    echo '<option value="' . esc_attr($category->term_id) . '" ' . esc_attr($selected) . '>' . esc_html($category->name) . '</option>';
                }
                echo '</select>';
            }

            // Product type filter
            $selected_type = isset($_REQUEST['filter-by-type']) ? sanitize_text_field(wp_unslash($_REQUEST['filter-by-type'])) : '';
            echo '<select name="filter-by-type" id="filter-by-type">';
            echo '<option value="">' . esc_html__('All Product Types', 'multi-location-product-and-inventory-management') . '</option>';
            echo '<option value="simple" ' . selected($selected_type, 'simple', false) . '>' . esc_html__('Simple', 'multi-location-product-and-inventory-management') . '</option>';
            echo '<option value="variable" ' . selected($selected_type, 'variable', false) . '>' . esc_html__('Variable', 'multi-location-product-and-inventory-management') . '</option>';
            echo '<option value="grouped" ' . selected($selected_type, 'grouped', false) . '>' . esc_html__('Grouped', 'multi-location-product-and-inventory-management') . '</option>';
            echo '<option value="external" ' . selected($selected_type, 'external', false) . '>' . esc_html__('External', 'multi-location-product-and-inventory-management') . '</option>';
            echo '</select>';

            // Stock status filter
            $selected_stock = isset($_REQUEST['filter-by-stock-status']) ? sanitize_text_field(wp_unslash($_REQUEST['filter-by-stock-status'])) : '';
            echo '<select name="filter-by-stock-status" id="filter-by-stock-status">';
            echo '<option value="">' . esc_html__('All Stock Statuses', 'multi-location-product-and-inventory-management') . '</option>';
            echo '<option value="instock" ' . selected($selected_stock, 'instock', false) . '>' . esc_html__('In Stock', 'multi-location-product-and-inventory-management') . '</option>';
            echo '<option value="outofstock" ' . selected($selected_stock, 'outofstock', false) . '>' . esc_html__('Out of Stock', 'multi-location-product-and-inventory-management') . '</option>';
            echo '<option value="onbackorder" ' . selected($selected_stock, 'onbackorder', false) . '>' . esc_html__('On Backorder', 'multi-location-product-and-inventory-management') . '</option>';
            echo '</select>';

            // Brand filter (check for common brand taxonomies)
            $brand_taxonomies = ['product_brand', 'pa_brand', 'pwb-brand'];
            $brand_taxonomy = null;
            foreach ($brand_taxonomies as $tax) {
                if (taxonomy_exists($tax)) {
                    $brand_taxonomy = $tax;
                    break;
                }
            }
            if ($brand_taxonomy) {
                $brands = get_terms([
                    'taxonomy' => $brand_taxonomy,
                    'hide_empty' => false,
                ]);
                if (!is_wp_error($brands) && !empty($brands)) {
                    $selected_brand = isset($_REQUEST['filter-by-brand']) ? intval($_REQUEST['filter-by-brand']) : '';
                    echo '<select name="filter-by-brand" id="filter-by-brand">';
                    echo '<option value="">' . esc_html__('All Brands', 'multi-location-product-and-inventory-management') . '</option>';
                    foreach ($brands as $brand) {
                        $selected = ($selected_brand == $brand->term_id) ? 'selected="selected"' : '';
                        echo '<option value="' . esc_attr($brand->term_id) . '" ' . esc_attr($selected) . '>' . esc_html($brand->name) . '</option>';
                    }
                    echo '</select>';
                }
            }

            // Add nonce field for the filter form
            wp_nonce_field('bulk-' . $this->_args['plural']);

            echo '<input type="submit" name="filter_action" id="filter-submit" class="button" value="' . esc_attr__('Filter', 'multi-location-product-and-inventory-management') . '">';
            echo '</div>';

            // Bulk actions section - location selector for bulk actions
            echo '<div class="alignleft actions bulk-actions-section">';
            if (!is_wp_error($mulopimfwc_locations) && !empty($mulopimfwc_locations)) {
                $selected_bulk_location = isset($_REQUEST['bulk_location_id']) ? intval($_REQUEST['bulk_location_id']) : '';
                echo '<label for="bulk-location-id" style="margin-right: 5px;">' . esc_html__('Location for Bulk Actions:', 'multi-location-product-and-inventory-management') . '</label>';
                echo '<select name="bulk_location_id" id="bulk-location-id">';
                echo '<option value="">' . esc_html__('Select Location', 'multi-location-product-and-inventory-management') . '</option>';
                foreach ($mulopimfwc_locations as $location) {
                    $selected = ($selected_bulk_location == $location->term_id) ? 'selected="selected"' : '';
                    echo '<option value="' . esc_attr($location->term_id) . '" ' . esc_attr($selected) . '>' . esc_html($location->name) . '</option>';
                }
                echo '</select>';
            }
            echo '</div>';
        }
    }
    
    /**
     * Modify SQL query to order by location assignment count
     *
     * @param array $clauses Query clauses
     * @param WP_Query $query The query object
     * @return array Modified clauses
     */
    public function order_by_location_assignment($clauses, $query)
    {
        // Only apply to our specific query
        if (!$this->ordering_by_location) {
            return $clauses;
        }

        global $wpdb;

        // Ensure join clause exists
        if (!isset($clauses['join'])) {
            $clauses['join'] = '';
        }

        // Add LEFT JOIN to count location assignments
        $clauses['join'] .= " LEFT JOIN (
            SELECT tr.object_id, COUNT(tr.term_taxonomy_id) as location_count
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE tt.taxonomy = 'mulopimfwc_store_location'
            GROUP BY tr.object_id
        ) as location_counts ON {$wpdb->posts}.ID = location_counts.object_id";

        // Modify ORDER BY to sort by location count (DESC) then by post title (ASC)
        $orderby = "COALESCE(location_counts.location_count, 0) DESC, {$wpdb->posts}.post_title ASC";
        
        if (!empty($clauses['orderby'])) {
            $clauses['orderby'] = $orderby . ', ' . $clauses['orderby'];
        } else {
            $clauses['orderby'] = $orderby;
        }

        return $clauses;
    }
}
