<?php

if (!defined('ABSPATH')) exit;

class MULOPIMFWC_Admin
{
    public function __construct()
    {
        add_action('init', [$this, 'register_store_location_taxonomy']);
        // Hook to add the settings page
        add_action('admin_menu', [$this, 'add_settings_page']);
        // Add custom column to orders table
        add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'add_location_column'), 20);
        add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'display_location_column_content'), 20, 2);
        // Add metabox to order details
        add_action('add_meta_boxes', array($this, 'add_location_metabox'));

        // Add custom fields to location taxonomy
        add_action('mulopimfwc_store_location_add_form_fields', array($this, 'add_location_fields'));
        add_action('mulopimfwc_store_location_edit_form_fields', array($this, 'edit_location_fields'), 10, 2);
        add_action('created_mulopimfwc_store_location', array($this, 'save_location_fields'), 10, 2);
        add_action('edited_mulopimfwc_store_location', array($this, 'save_location_fields'), 10, 2);

        // Add custom columns to location taxonomy table
        add_filter('manage_edit-mulopimfwc_store_location_columns', array($this, 'add_location_taxonomy_columns'));
        add_filter('manage_mulopimfwc_store_location_custom_column', array($this, 'add_location_taxonomy_column_content'), 10, 3);
        add_filter('manage_edit-mulopimfwc_store_location_sortable_columns', array($this, 'add_location_taxonomy_sortable_columns'));
    }

    public function add_location_fields()
    {
?>
        <div class="form-field">
            <label for="street_address"><?php _e('Street Address', 'multi-location-product-and-inventory-management'); ?></label>
            <input type="text" name="street_address" id="street_address" value="" />
            <p class="description"><?php _e('Enter street address for this location', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <div class="form-field">
            <label for="city"><?php _e('City', 'multi-location-product-and-inventory-management'); ?></label>
            <input type="text" name="city" id="city" value="" />
            <p class="description"><?php _e('Enter city for this location', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <div class="form-field">
            <label for="state"><?php _e('State', 'multi-location-product-and-inventory-management'); ?></label>
            <input type="text" name="state" id="state" value="" />
            <p class="description"><?php _e('Enter state for this location', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <div class="form-field">
            <label for="postal_code"><?php _e('Postal Code', 'multi-location-product-and-inventory-management'); ?></label>
            <input type="text" name="postal_code" id="postal_code" value="" />
            <p class="description"><?php _e('Enter postal code for this location', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <div class="form-field">
            <label for="country"><?php _e('Country', 'multi-location-product-and-inventory-management'); ?></label>
            <input type="text" name="country" id="country" value="" />
            <p class="description"><?php _e('Enter country for this location', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <div class="form-field">
            <label for="email"><?php _e('Email', 'multi-location-product-and-inventory-management'); ?></label>
            <input type="email" name="email" id="email" value="" />
            <p class="description"><?php _e('Enter email for this location', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <div class="form-field">
            <label for="phone"><?php _e('Phone', 'multi-location-product-and-inventory-management'); ?></label>
            <input type="tel" name="phone" id="phone" value="" />
            <p class="description"><?php _e('Enter phone for this location', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <div class="form-field">
            <label for="display_order"><?php _e('Display Order', 'multi-location-product-and-inventory-management'); ?></label>
            <input type="number" name="display_order" id="display_order" value="" min="0" step="1" />
            <p class="description"><?php _e('Enter a number to control the order of this location (smaller numbers appear first)', 'multi-location-product-and-inventory-management'); ?></p>
        </div>
    <?php
    }
    /**
     * Add custom fields when editing a location
     */
    public function edit_location_fields($term, $taxonomy)
    {
        // Get existing values
        $street_address = get_term_meta($term->term_id, 'street_address', true);
        $city = get_term_meta($term->term_id, 'city', true);
        $state = get_term_meta($term->term_id, 'state', true);
        $postal_code = get_term_meta($term->term_id, 'postal_code', true);
        $country = get_term_meta($term->term_id, 'country', true);
        $email = get_term_meta($term->term_id, 'email', true);
        $phone = get_term_meta($term->term_id, 'phone', true);
        $display_order = get_term_meta($term->term_id, 'display_order', true);

    ?>
        <tr class="form-field">
            <th scope="row"><label for="street_address"><?php _e('Street Address', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td>
                <input type="text" name="street_address" id="street_address" value="<?php echo esc_attr($street_address); ?>" />
                <p class="description"><?php _e('Enter street address for this location', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="city"><?php _e('City', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td>
                <input type="text" name="city" id="city" value="<?php echo esc_attr($city); ?>" />
                <p class="description"><?php _e('Enter city for this location', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="state"><?php _e('State', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td>
                <input type="text" name="state" id="state" value="<?php echo esc_attr($state); ?>" />
                <p class="description"><?php _e('Enter state for this location', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="postal_code"><?php _e('Postal Code', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td>
                <input type="text" name="postal_code" id="postal_code" value="<?php echo esc_attr($postal_code); ?>" />
                <p class="description"><?php _e('Enter postal code for this location', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="country"><?php _e('Country', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td>
                <input type="text" name="country" id="country" value="<?php echo esc_attr($country); ?>" />
                <p class="description"><?php _e('Enter country for this location', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="email"><?php _e('Email', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td>
                <input type="email" name="email" id="email" value="<?php echo esc_attr($email); ?>" />
                <p class="description"><?php _e('Enter email for this location', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="phone"><?php _e('Phone', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td>
                <input type="tel" name="phone" id="phone" value="<?php echo esc_attr($phone); ?>" />
                <p class="description"><?php _e('Enter phone for this location', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="display_order"><?php _e('Display Order', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td>
                <input type="number" name="display_order" id="display_order" value="<?php echo esc_attr($display_order); ?>" min="0" step="1" />
                <p class="description"><?php _e('Enter a number to control the order of this location (smaller numbers appear first)', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>
<?php
    }

    /**
     * Save custom fields when location is created or edited
     */
    public function save_location_fields($term_id, $tt_id)
    {
        if (isset($_POST['street_address'])) {
            update_term_meta($term_id, 'street_address', sanitize_text_field($_POST['street_address']));
        }

        if (isset($_POST['city'])) {
            update_term_meta($term_id, 'city', sanitize_text_field($_POST['city']));
        }

        if (isset($_POST['state'])) {
            update_term_meta($term_id, 'state', sanitize_text_field($_POST['state']));
        }

        if (isset($_POST['postal_code'])) {
            update_term_meta($term_id, 'postal_code', sanitize_text_field($_POST['postal_code']));
        }

        if (isset($_POST['country'])) {
            update_term_meta($term_id, 'country', sanitize_text_field($_POST['country']));
        }

        if (isset($_POST['email'])) {
            update_term_meta($term_id, 'email', sanitize_email($_POST['email']));
        }

        if (isset($_POST['phone'])) {
            update_term_meta($term_id, 'phone', sanitize_text_field($_POST['phone']));
        }

        if (isset($_POST['display_order'])) {
            $display_order = intval($_POST['display_order']);
            update_term_meta($term_id, 'display_order', $display_order);
        }
    }

    /**
     * Add custom columns to location taxonomy table
     */
    public function add_location_taxonomy_columns($columns)
    {
        $new_columns = array();

        // Add columns before the 'slug' column
        foreach ($columns as $key => $value) {
            if ($key === 'slug') {
                $new_columns['display_order'] = __('Order', 'multi-location-product-and-inventory-management');
                $new_columns['city'] = __('City', 'multi-location-product-and-inventory-management');
                $new_columns['country'] = __('Country', 'multi-location-product-and-inventory-management');
            }
            $new_columns[$key] = $value;
        }

        return $new_columns;
    }

    /**
     * Add content to custom columns in location taxonomy table
     */
    public function add_location_taxonomy_column_content($content, $column_name, $term_id)
    {
        switch ($column_name) {
            case 'display_order':
                $display_order = get_term_meta($term_id, 'display_order', true);
                echo $display_order ? esc_html($display_order) : '—';
                break;

            case 'city':
                $city = get_term_meta($term_id, 'city', true);
                echo $city ? esc_html($city) : '—';
                break;

            case 'country':
                $country = get_term_meta($term_id, 'country', true);
                echo $country ? esc_html($country) : '—';
                break;
        }

        return $content;
    }

    /**
     * Make display order column sortable
     */
    public function add_location_taxonomy_sortable_columns($columns)
    {
        $columns['display_order'] = 'display_order';
        return $columns;
    }






    public function add_settings_page()
    {
        // Add main menu page
        add_menu_page(
            __('Location Manage', 'multi-location-product-and-inventory-management'),
            __('Location Manage', 'multi-location-product-and-inventory-management'),
            'manage_options',
            'multi-location-product-and-inventory-management',
            [new MULOPIMFWC_Dashboard(), 'dashboard_page_content'],
            'dashicons-location-alt',
            56
        );

        // Add Dashboard submenu (just label, points to same page, no callback)
        add_submenu_page(
            'multi-location-product-and-inventory-management',
            __('Dashboard', 'multi-location-product-and-inventory-management'),
            __('Dashboard', 'multi-location-product-and-inventory-management'),
            'manage_options',
            'multi-location-product-and-inventory-management'
            // No callback here, so it won't render twice
        );

        // Add Locations submenu
        add_submenu_page(
            'multi-location-product-and-inventory-management',
            __('Locations', 'multi-location-product-and-inventory-management'),
            __('Locations', 'multi-location-product-and-inventory-management'),
            'manage_options',
            'edit-tags.php?taxonomy=mulopimfwc_store_location&post_type=product',
            null,
            56
        );

        // Ensure the menu is expanded and active when this page is active
        add_filter('parent_file', function ($parent_file) {
            global $pagenow, $taxonomy;

            if ($pagenow === 'edit-tags.php' && $taxonomy === 'mulopimfwc_store_location') {
                $parent_file = 'multi-location-product-and-inventory-management';
            }

            return $parent_file;
        });

        // Add current class to the active menu item
        add_filter('submenu_file', function ($submenu_file) {
            global $pagenow, $taxonomy;

            if ($pagenow === 'edit-tags.php' && $taxonomy === 'mulopimfwc_store_location') {
                $submenu_file = 'edit-tags.php?taxonomy=mulopimfwc_store_location&post_type=product';
            }

            return $submenu_file;
        });


        // add Stock Central submenu
        add_submenu_page(
            'multi-location-product-and-inventory-management',
            __('Stock Central', 'multi-location-product-and-inventory-management'),
            __('Stock Central', 'multi-location-product-and-inventory-management'),
            'manage_options',
            'location-stock-management',
            [new mulopimfwc_Stock_Central(), 'location_stock_page_content']
        );

        add_submenu_page(
            'multi-location-product-and-inventory-management',
            __('Location Managers', 'multi-location-product-and-inventory-management'),
            __('Location Managers', 'multi-location-product-and-inventory-management'),
            'manage_options',
            'location-managers',
            array(new MULOPIMFWC_Location_Managers(), 'admin_page')
        );

        // Add Settings submenu
        add_submenu_page(
            'multi-location-product-and-inventory-management',
            __('Settings', 'multi-location-product-and-inventory-management'),
            __('Settings', 'multi-location-product-and-inventory-management'),
            'manage_options',
            'multi-location-product-and-inventory-management-settings',
            [new mulopimfwc_settings(), 'settings_page_content']
        );

        if (class_exists('Mulopimfwc_Customer_Location_Insights')) {
            add_submenu_page(
                'multi-location-product-and-inventory-management',
                __('Location Analytics', 'multi-location-product-and-inventory-management'),
                __('Analytics', 'multi-location-product-and-inventory-management'),
                'manage_woocommerce',
                'mulopimfwc-analytics',
                [$this, 'mulopimfwc_render_analytics_page_wrapper'] // Use a wrapper function instead
            );
        }


        // Add "Get Pro" submenu (external link)
        add_submenu_page(
            'multi-location-product-and-inventory-management',
            __('Get Pro', 'multi-location-product-and-inventory-management'),
            __('⭐ Get Pro', 'multi-location-product-and-inventory-management'),
            'manage_options',
            'https://plugincy.com/multi-location-product-and-inventory-management/'
        );
    }


    public function mulopimfwc_render_analytics_page_wrapper()
    {
        if (!class_exists('Mulopimfwc_Customer_Location_Insights')) {
            echo '<div class="wrap"><h1>' . esc_html__('Analytics Not Available', 'multi-location-product-and-inventory-management') . '</h1>';
            echo '<p>' . esc_html__('The analytics feature is not available. Please ensure all plugin files are properly loaded.', 'multi-location-product-and-inventory-management') . '</p></div>';
            return;
        }

        $instance = Mulopimfwc_Customer_Location_Insights::get_instance();

        if (!$instance || !method_exists($instance, 'render_analytics_page')) {
            echo '<div class="wrap"><h1>' . esc_html__('Analytics Error', 'multi-location-product-and-inventory-management') . '</h1>';
            echo '<p>' . esc_html__('Unable to load analytics page. Please contact support.', 'multi-location-product-and-inventory-management') . '</p></div>';
            return;
        }

        $instance->render_analytics_page();
    }


    /**
     * Add location column to orders table
     *
     * @param array $columns Order list columns
     * @return array Modified columns
     */
    public function add_location_column($columns)
    {
        $new_columns = array();

        foreach ($columns as $column_name => $column_info) {
            $new_columns[$column_name] = $column_info;

            if ('order_status' === $column_name) {
                $new_columns['mulopimfwc_store_location'] = __('Store Location', 'multi-location-product-and-inventory-management');
            }
        }

        return $new_columns;
    }
    /**
     * Display location in orders table column
     *
     * @param string $column Column name
     * @param WC_Order $order Order object
     */
    public function display_location_column_content($column, $order)
    {
        if ($column == 'mulopimfwc_store_location') {
            $location = $order->get_meta('_store_location');
            echo esc_html($location ? ucfirst(strtolower($location)) : '—');
        }
    }
    /**
     * Add location metabox to order details
     */
    public function add_location_metabox()
    {
        $screen = $this->get_order_screen_id();

        add_meta_box(
            'wc_store_location_metabox',
            __('Store Location', 'multi-location-product-and-inventory-management'),
            array($this, 'render_location_metabox'),
            $screen,
            'side',
            'high'
        );
    }

    /**
     * Get appropriate screen ID based on WooCommerce version
     *
     * @return string Screen ID
     */
    private function get_order_screen_id()
    {
        // Check if we're using the HPOS (High-Performance Order Storage)
        if (class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')) {
            $controller = wc_get_container()->get(\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class);

            if (
                method_exists($controller, 'custom_orders_table_usage_is_enabled') &&
                $controller->custom_orders_table_usage_is_enabled()
            ) {
                return wc_get_page_screen_id('shop-order');
            }
        }

        return 'shop_order';
    }
    /**
     * Render location metabox content
     *
     * @param mixed $object Post or order object
     */
    public function render_location_metabox($object)
    {
        // Get the WC_Order object
        $order = is_a($object, 'WP_Post') ? wc_get_order($object->ID) : $object;

        if (!$order) {
            return;
        }

        $location = $order->get_meta('_store_location');

        echo '<div class="wc-store-location-container">';

        if (!empty($location)) {
            echo '<p>' . esc_html(ucfirst(strtolower($location))) . '</p>';
        } else {
            echo '<p>' . esc_html__('No location data available', 'multi-location-product-and-inventory-management') . '</p>';
        }

        echo '</div>';
    }
    public function register_store_location_taxonomy()
    {
        register_taxonomy('mulopimfwc_store_location', 'product', [
            'labels' => [
                'name' => __('Locations', 'multi-location-product-and-inventory-management'),
                'singular_name' => __('Location', 'multi-location-product-and-inventory-management'),
                'search_items' => __('Search Location', 'multi-location-product-and-inventory-management'),
                'all_items' => __('All Locations', 'multi-location-product-and-inventory-management'),
                'parent_item' => __('Parent Location', 'multi-location-product-and-inventory-management'),
                'parent_item_colon' => __('Parent Location:', 'multi-location-product-and-inventory-management'),
                'edit_item' => __('Edit Location', 'multi-location-product-and-inventory-management'),
                'view_item' => __('View Location', 'multi-location-product-and-inventory-management'),
                'update_item' => __('Update Location', 'multi-location-product-and-inventory-management'),
                'add_new_item' => __('Add New Location', 'multi-location-product-and-inventory-management'),
                'new_item_name' => __('New Location Name', 'multi-location-product-and-inventory-management'),
                'separate_items_with_commas' => __('Separate locations with commas', 'multi-location-product-and-inventory-management'),
                'add_or_remove_items' => __('Add or remove locations', 'multi-location-product-and-inventory-management'),
                'choose_from_most_used' => __('Choose from most used locations', 'multi-location-product-and-inventory-management'),
                'not_found' => __('No locations found', 'multi-location-product-and-inventory-management'),
                'no_terms' => __('No locations', 'multi-location-product-and-inventory-management'),
                'menu_name' => __('Locations', 'multi-location-product-and-inventory-management'),
                'popular_items' => __('Popular Locations', 'multi-location-product-and-inventory-management'),
                'back_to_items' => __('Back to Locations', 'multi-location-product-and-inventory-management'),
            ],
            'description' => __('Manage locations for products and inventory tracking', 'multi-location-product-and-inventory-management'),
            'public' => true,
            'publicly_queryable' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_tagcloud' => false,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [
                'slug' => 'store-location',
                'with_front' => false,
                'hierarchical' => true,
            ],
            'capabilities' => [
                'manage_terms' => 'manage_woocommerce',
                'edit_terms' => 'manage_woocommerce',
                'delete_terms' => 'manage_woocommerce',
                'assign_terms' => 'edit_products',
            ],
            'sort' => true,
        ]);
    }
}



if (!function_exists('mulopimfwc_is_cyber_monday_deal_active')) {
    /**
     * Check if Cyber Monday deal is still active.
     *
     * Ends automatically at 11:59 PM, December 2, 2025 (WordPress local time).
     */
    function mulopimfwc_is_cyber_monday_deal_active()
    {
        $expiry_timestamp = strtotime('2025-12-02 23:59:00');
        return current_time('timestamp') <= $expiry_timestamp;
    }
}

add_action('admin_notices', 'mulopimfwc_show_cyber_monday_notice');

function mulopimfwc_show_cyber_monday_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!function_exists('mulopimfwc_is_cyber_monday_deal_active') || !mulopimfwc_is_cyber_monday_deal_active()) {
        return;
    }

    echo '<div class="notice notice-info is-dismissible" style="padding:0;border:none;background:transparent;box-shadow:none;">';
    echo '  <div style="padding:18px 20px;border-radius:14px;background:linear-gradient(120deg,#5d87ff 0%,#764ba2 55%,#fcd34d 105%);color:#0b132b;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;box-shadow:0 16px 34px rgba(0,0,0,0.15);border:1px solid rgba(255,255,255,0.18);position:relative;overflow:hidden;">';
    echo '      <span class="dashicons dashicons-megaphone" style="font-size:24px;height:24px;width:24px;color:#0b132b;background:#fdfdfd;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;padding:10px;"></span>';
    echo '      <div style="flex:1;min-width:260px;position:relative;z-index:1;">';
    echo '          <div style="display:inline-flex;align-items:center;gap:8px;margin-bottom:6px;font-weight:700;text-transform:uppercase;font-size:12px;letter-spacing:0.5px;color:#0b132b;background:rgba(255,255,255,0.88);padding:4px 12px;border-radius:999px;">Cyber Monday • Ends Dec 2, 2025</div>';
    echo '          <div style="font-size:15px;line-height:1.6;color:#fff;font-weight:600;display: flex;gap:5px;">Save 30% on Multi Location Product & Inventory Management for WooCommerce Pro with code <code style="background:#0b132b;color:#fefce8;padding:3px 8px;border-radius:8px;">CMP30</code>. Valid until 11:59 PM, December 2, 2025.</div>';
    echo '      </div>';
    echo '      <a class="button button-primary" target="_blank" href="https://plugincy.com/best-cyber-monday-plugin-deals-in-2025/" style="background:#fcd34d;border-color:#fcd34d;color:#0b132b;box-shadow:0 12px 22px rgba(0,0,0,0.18);padding:11px 20px;font-weight:800;border-radius:12px;text-transform:uppercase;letter-spacing:0.3px;">Claim 30% Off</a>';
    echo '  </div>';
    echo '</div>';
}