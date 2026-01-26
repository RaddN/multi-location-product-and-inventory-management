<?php

/**
 * Location Managers Admin Page
 * 
 * @package Multi Location Product & Inventory Management
 * @since 1.0.7.5
 */

if (!defined('ABSPATH')) exit;

class MULOPIMFWC_Location_Managers
{

    public function __construct()
    {

    }

    /**
     * Get all available capabilities
     */
    private function get_available_capabilities()
    {
        return [
            'manage_inventory' => __('Manage Inventory', 'multi-location-product-and-inventory-management'),
            'view_orders' => __('View Orders', 'multi-location-product-and-inventory-management'),
            'manage_orders' => __('Manage Orders', 'multi-location-product-and-inventory-management'),
            'view_products' => __('View Products', 'multi-location-product-and-inventory-management'),
            'manage_products' => __('Manage Products', 'multi-location-product-and-inventory-management'),
            'run_reports' => __('Run Reports', 'multi-location-product-and-inventory-management'),
        ];
    }

    /**
     * Admin page content
     */
    public function admin_page()
    {
        global $mulopimfwc_locations;

        $managers = [
            (object) [
                'ID' => 1,
                'display_name' => 'John Doe',
                'user_email' => 'john.doe@example.com',
                'capabilities' => ['Manage Products', 'View Reports'],
                'assigned_locations' => ['New York', 'Los Angeles']
            ],
            (object) [
                'ID' => 2,
                'display_name' => 'Jane Smith',
                'user_email' => 'jane.smith@example.com',
                'capabilities' => ['Manage Inventory'],
                'assigned_locations' => ['Chicago']
            ],
            (object) [
                'ID' => 3,
                'display_name' => 'Alice Johnson',
                'user_email' => 'alice.johnson@example.com',
                'capabilities' => ['Manage Orders', 'Edit Products'],
                'assigned_locations' => []
            ],
            (object) [
                'ID' => 4,
                'display_name' => 'Bob Brown',
                'user_email' => 'bob.brown@example.com',
                'capabilities' => ['View Reports'],
                'assigned_locations' => ['Miami', 'Houston']
            ],
            (object) [
                'ID' => 5,
                'display_name' => 'Charlie Davis',
                'user_email' => 'charlie.davis@example.com',
                'capabilities' => ['Manage Products', 'Manage Inventory'],
                'assigned_locations' => ['San Francisco']
            ],
            (object) [
                'ID' => 7,
                'display_name' => 'Ethan Green',
                'user_email' => 'ethan.green@example.com',
                'capabilities' => ['Edit Products'],
                'assigned_locations' => ['Boston']
            ],
            (object) [
                'ID' => 9,
                'display_name' => 'George King',
                'user_email' => 'george.king@example.com',
                'capabilities' => ['Manage Inventory'],
                'assigned_locations' => ['Denver', 'Las Vegas']
            ],
            (object) [
                'ID' => 10,
                'display_name' => 'Hannah Lee',
                'user_email' => 'hannah.lee@example.com',
                'capabilities' => ['Manage Products', 'Edit Products'],
                'assigned_locations' => ['Phoenix']
            ]
        ];
        $locations = $mulopimfwc_locations;
        $capabilities = $this->get_available_capabilities();
        global $mulopimfwc_options;
            $options = is_array($mulopimfwc_options ?? null)
                ? $mulopimfwc_options
                : get_option('mulopimfwc_display_options', []);
        $global_capabilities = isset($options['location_manager_capabilities']) ? $options['location_manager_capabilities'] : [];

?>
        <div class="wrap mulopimfwc-location-managers-main mulopimfwc_pro_only mulopimfwc_pro_only_blur1">


            <div class="mulopimfwc-location-managers">
                <div class="mulopimfwc-manager-header">
                    <h1 class="mlm-settings-heading">
                        <div class="mlm-settings-icon">
                            <svg class="svg-inline--fa fa-users" aria-hidden="true" data-prefix="fas" data-icon="users" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 16" width="20" height="16">
                                <path fill="#ffffff" d="M4.5 0a2.5 2.5 0 1 1 0 5 2.5 2.5 0 1 1 0-5M16 0a2.5 2.5 0 1 1 0 5 2.5 2.5 0 1 1 0-5M0 9.334A3.336 3.336 0 0 1 3.334 6h1.334c.497 0 .969.109 1.394.303A4 4 0 0 0 7.356 10H.666A.67.67 0 0 1 0 9.334M12.666 10h-.022a4 4 0 0 0 1.353-3q-.002-.355-.059-.697A3.3 3.3 0 0 1 15.332 6h1.334A3.335 3.335 0 0 1 20 9.334c0 .369-.3.666-.666.666zM7 7a3 3 0 1 1 6 0 3 3 0 1 1-6 0m-3 8.166C4 12.866 5.866 11 8.166 11h3.669c2.3 0 4.166 1.866 4.166 4.166a.834.834 0 0 1-.834.834H4.834A.834.834 0 0 1 4 15.166"></path>
                            </svg>
                        </div>
                        <span><?php echo esc_html__('Location Managers', 'multi-location-product-and-inventory-management'); ?></span>
                    </h1>
                    <!-- Add New Manager Button -->
                    <button type="button" class="button button-primary" id="mulopimfwc-add-manager-btn">
                        <svg class="svg-inline--fa fa-plus" aria-hidden="true" data-prefix="fas" data-icon="plus" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path fill="currentColor" d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32v144H48c-17.7 0-32 14.3-32 32s14.3 32 32 32h144v144c0 17.7 14.3 32 32 32s32-14.3 32-32V288h144c17.7 0 32-14.3 32-32s-14.3-32-32-32H256z" />
                        </svg> <?php echo esc_html__('Add New Location Manager', 'multi-location-product-and-inventory-management'); ?>
                    </button>
                </div>

                <!-- Managers List -->
                <div class="mulopimfwc-managers-list">
                    <h2><?php echo esc_html__('Current Location Managers', 'multi-location-product-and-inventory-management'); ?></h2>

                    <?php if (empty($managers)): ?>
                        <div class="mulopimfwc-no-managers">
                            <p><?php echo esc_html__('No location managers found. Create your first location manager to get started.', 'multi-location-product-and-inventory-management'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="mulopimfwc-managers-grid">
                            <?php foreach ($managers as $manager): ?>
                                <?php
                                $assigned_locations = get_user_meta($manager->ID, 'mulopimfwc_assigned_locations', true);
                                $manager_capabilities = get_user_meta($manager->ID, 'mulopimfwc_manager_capabilities', true);
                                if (!is_array($assigned_locations)) $assigned_locations = [];
                                if (!is_array($manager_capabilities)) $manager_capabilities = $global_capabilities;
                                ?>
                                <div class="mulopimfwc-manager-card" data-manager-id="<?php echo esc_attr($manager->ID); ?>">
                                    <div class="manager-header">
                                        <div class="manager-info">
                                            <?php echo get_avatar($manager->ID, 50); ?>
                                            <div class="manager-details">
                                                <h3><?php echo esc_html($manager->display_name); ?></h3>
                                                <p class="manager-email"><?php echo esc_html($manager->user_email); ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="manager-locations">
                                        <h4><svg class="svg-inline--fa fa-location-dot" aria-hidden="true" data-prefix="fas" data-icon="location-dot" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                                                <path fill="#9ca3af" d="M215.7 499.2C267 435 384 279.4 384 192 384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2 12.3 15.3 35.1 15.3 47.4 0M192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128" />
                                            </svg><?php echo esc_html__('Assigned Locations:', 'multi-location-product-and-inventory-management'); ?></h4>
                                        <?php if (empty($assigned_locations)): ?>
                                            <p class="no-locations"><?php echo esc_html__('No locations assigned', 'multi-location-product-and-inventory-management'); ?></p>
                                        <?php else: ?>
                                            <ul class="location-list">
                                                <?php foreach ($assigned_locations as $location_slug): ?>
                                                    <?php
                                                    $location = get_term_by('slug', $location_slug, 'mulopimfwc_store_location');
                                                    if ($location):
                                                    ?>
                                                        <li><span class="location-tag"><?php echo esc_html($location->name); ?></span></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>

                                    <div class="manager-capabilities">
                                        <h4><svg class="svg-inline--fa fa-key" aria-hidden="true" data-prefix="fas" data-icon="key" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                <path fill="#9ca3af" d="M336 352c97.2 0 176-78.8 176-176S433.2 0 336 0 160 78.8 160 176c0 18.7 2.9 36.8 8.3 53.7L7 391c-4.5 4.5-7 10.6-7 17v80c0 13.3 10.7 24 24 24h80c13.3 0 24-10.7 24-24v-40h40c13.3 0 24-10.7 24-24v-40h40c6.4 0 12.5-2.5 17-7l33.3-33.3c16.9 5.4 35 8.3 53.7 8.3m40-256a40 40 0 1 1 0 80 40 40 0 1 1 0-80" />
                                            </svg><?php echo esc_html__('Permissions:', 'multi-location-product-and-inventory-management'); ?></h4>
                                        <?php if (empty($manager_capabilities)): ?>
                                            <p class="no-capabilities"><?php echo esc_html__('No specific permissions set (using global defaults)', 'multi-location-product-and-inventory-management'); ?></p>
                                        <?php else: ?>
                                            <ul class="capability-list">
                                                <?php foreach ($manager_capabilities as $capability): ?>
                                                    <?php if (isset($capabilities[$capability])): ?>
                                                        <li><span class="capability-tag"><?php echo esc_html($capabilities[$capability]); ?></span></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>

                                    <div class="manager-actions">
                                        <button type="button" class="button button-small mulopimfwc-edit-manager mulopimfwc-btn-primary" data-manager-id="<?php echo esc_attr($manager->ID); ?>" data-assign-locations=<?php echo wp_json_encode($assigned_locations); ?> data-assign-capabilities=<?php echo wp_json_encode($manager_capabilities); ?>>
                                            <svg class="svg-inline--fa fa-pencil" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="pencil" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                                                <path fill="currentColor" d="M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1v32c0 8.8 7.2 16 16 16h32zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0zm-47.4 168l-144 144c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l144-144c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6z"></path>
                                            </svg> <?php echo esc_html__('Edit', 'multi-location-product-and-inventory-management'); ?>
                                        </button>
                                        <button type="button" class="button button-small button-link-delete mulopimfwc-delete-manager" data-manager-id="<?php echo esc_attr($manager->ID); ?>">
                                            <svg class="svg-inline--fa fa-trash" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg="">
                                                <path fill="currentColor" d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path>
                                            </svg> <?php echo esc_html__('Delete', 'multi-location-product-and-inventory-management'); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php wp_nonce_field('mulopimfwc_location_managers_nonce', 'mulopimfwc_location_managers_nonce'); ?>

        <style>
            /* New start*/
            .wrap.mulopimfwc-location-managers-main {
                border: 2px solid #d1d1d4;
                border-radius: 8px;
                background-color: #f9fafb;
                margin: 20px 20px 0px 0px;
            }

            h1.mlm-settings-heading {
                display: flex;
                gap: 10px;
                align-items: center;
            }

            .mlm-settings-icon {
                color: #ffffff;
                background: #3b82f6;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 10px;
                border-radius: 7px;
            }

            .mulopimfwc-manager-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: rgba(33, 35, 38, 0.1) 0px 10px 10px -10px;
                padding: 20px;
            }

            .mulopimfwc-manager-header button {
                background: #2563eb !important;
                border-color: #2563eb !important;
                padding: 5px 15px !important;
                border-radius: 6px !important;
                font-size: 15px !important;
                font-weight: 600;
                display: flex !important;
                justify-content: center;
                align-items: center;
            }

            .mulopimfwc-manager-header button svg {
                width: 15px;
                height: 15px;
                margin-right: 6px;
            }

            .mulopimfwc-managers-list {
                padding: 10px 25px;
            }

            .manager-info img {
                border-radius: 50%;
            }


            .manager-actions .mulopimfwc-delete-manager {
                background: #ffffff !important;
                border-color: #dc2626 !important;
                padding: 0px 30px !important;
                border-radius: 6px !important;
                font-size: 15px !important;
                color: #dc2626;
                font-weight: 600;
                display: flex !important;
                justify-content: center;
                align-items: center;
                transition: all 0.25s ease !important;
            }

            .manager-actions .mulopimfwc-delete-manager:hover {
                box-shadow: 0 4px 10px rgba(220, 38, 38, 0.4) !important;
                transform: translateY(-2px);
            }

            

            /* New End */

            .mulopimfwc-managers-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 20px;
            }

            .mulopimfwc-manager-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                padding: 25px;
                border-radius: 8px;
                box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            }

            .manager-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 15px;
            }

            .manager-info {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .manager-details h3 {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
            }

            .manager-email {
                margin: 0px 0 0;
                color: #666;
                font-size: 14px;
            }

            .manager-actions {
                display: flex;
                gap: 5px;
            }

            .manager-locations,
            .manager-capabilities {
                margin-bottom: 15px;
            }

            .manager-locations h4,
            .manager-capabilities h4 {
                margin: 0 0 10px;
                font-size: 14px;
                display: flex;
                align-items: center;
            }

            .manager-locations h4 svg,
            .manager-capabilities h4 svg {
                width: auto;
                height: 14px;
                margin-right: 6px;
            }

            .location-list,
            .capability-list {
                list-style: none;
                padding: 0;
                margin: 0;
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }

            .location-tag,
            .capability-tag {
                background: #f3f4f6;
                padding: 4px 8px;
                border-radius: 30px;
                font-size: 12px;
            }

            .capability-tag {
                background: #2563eb1a;
                color: #2563eb;
            }

            .no-locations,
            .no-capabilities {
                color: #666;
                font-style: italic;
                margin: 0;
            }

            .mulopimfwc-no-managers {
                text-align: center;
                padding: 40px;
                background: #f9f9f9;
                border-radius: 8px;
            }

            /* Modal Styles */
            .mulopimfwc-modal {
                position: fixed;
                z-index: 100000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
            }

            .mulopimfwc-modal-content {
                background-color: #fff;
                margin: 5% auto;
                padding: 0;
                border-radius: 8px;
                width: 90%;
                max-width: 600px;
                max-height: 90vh;
                overflow-y: auto;
            }

            .mulopimfwc-modal-header {
                padding: 20px;
                border-bottom: 1px solid #ddd;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .mulopimfwc-modal-header h2 {
                margin: 0;
            }

            .mulopimfwc-modal-close {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                padding: 0;
            }

            .mulopimfwc-modal form {
                padding: 20px;
            }

            .mulopimfwc-form-row {
                margin-bottom: 20px;
            }

            .mulopimfwc-form-row label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
            }

            .mulopimfwc-form-row input[type="text"],
            .mulopimfwc-form-row input[type="email"] {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .location-checkboxes,
            .capability-checkboxes {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 10px;
            }

            .checkbox-label {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                cursor: pointer;
            }

            .checkbox-label:hover {
                background: #f9f9f9;
            }

            .global-default {
                font-size: 11px;
                color: #666;
                font-style: italic;
            }

            .mulopimfwc-modal-footer {
                padding: 20px;
                border-top: 1px solid #ddd;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }

            /* User Search */
            .user-search-container {
                position: relative;
            }

            .search-results {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #fff;
                border: 1px solid #ddd;
                border-top: none;
                max-height: 200px;
                overflow-y: auto;
                z-index: 1000;
            }

            .search-result-item {
                padding: 10px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
            }

            .search-result-item:hover {
                background: #f9f9f9;
            }

            .search-result-item:last-child {
                border-bottom: none;
            }
        </style>
    <?php
    }

    /**
     * Delete location manager AJAX handler
     */
    public function delete_location_manager()
    {
        check_ajax_referer('mulopimfwc_location_managers_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => esc_html_e('Permission denied', 'multi-location-product-and-inventory-management')]);
        }

        $manager_id = intval($_POST['manager_id']);

        if (empty($manager_id)) {
            wp_send_json_error(['message' => esc_html_e('Invalid manager ID', 'multi-location-product-and-inventory-management')]);
        }

        // Remove location manager role and revert to subscriber
        $user = new WP_User($manager_id);
        $user->set_role('subscriber');

        // Remove location manager meta
        delete_user_meta($manager_id, 'mulopimfwc_assigned_locations');
        delete_user_meta($manager_id, 'mulopimfwc_manager_capabilities');

        wp_send_json_success(['message' => esc_html_e('Location manager deleted successfully', 'multi-location-product-and-inventory-management')]);
    }

    /**
     * Check if current user is location manager for specific location
     */
    public static function is_location_manager_for($location_slug = '')
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();
        if (!in_array('mulopimfwc_location_manager', $user->roles)) {
            return false;
        }

        if (empty($location_slug)) {
            return true; // User is a location manager, location not specified
        }

        $assigned_locations = get_user_meta($user->ID, 'mulopimfwc_assigned_locations', true);
        if (!is_array($assigned_locations)) {
            return false;
        }

        return in_array($location_slug, $assigned_locations);
    }

    /**
     * Check if current user has specific capability
     */
    public static function user_has_capability($capability)
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();
        if (!in_array('mulopimfwc_location_manager', $user->roles)) {
            return current_user_can('manage_woocommerce'); // Admin check
        }

        // Get individual capabilities
        $manager_capabilities = get_user_meta($user->ID, 'mulopimfwc_manager_capabilities', true);
        if (!is_array($manager_capabilities) || empty($manager_capabilities)) {
            // Use global capabilities
            global $mulopimfwc_options;
            $options = is_array($mulopimfwc_options ?? null)
                ? $mulopimfwc_options
                : get_option('mulopimfwc_display_options', []);
            $manager_capabilities = isset($options['location_manager_capabilities']) ? $options['location_manager_capabilities'] : [];
        }

        return in_array($capability, $manager_capabilities);
    }

    /**
     * Get assigned locations for current user
     */
    public static function get_user_assigned_locations()
    {
        if (!is_user_logged_in()) {
            return [];
        }

        $user = wp_get_current_user();
        if (!in_array('mulopimfwc_location_manager', $user->roles)) {
            return []; // Not a location manager
        }

        $assigned_locations = get_user_meta($user->ID, 'mulopimfwc_assigned_locations', true);
        return is_array($assigned_locations) ? $assigned_locations : [];
    }
}

// Initialize the class
new MULOPIMFWC_Location_Managers();







/**
 * filter orders based on location manager's assigned locations
 */

class MULOPIMFWC_Order_Filter
{
    public function __construct()
    {
        // Filter orders in admin list table
        add_action('pre_get_posts', [$this, 'filter_orders_by_location_manager']);

        // Filter orders in WooCommerce HPOS (High Performance Order Storage) if enabled
        add_filter('woocommerce_order_query_args', [$this, 'filter_hpos_orders_by_location_manager']);

        // Add location filter dropdown in orders admin
        add_action('restrict_manage_posts', [$this, 'add_location_filter_dropdown']);

        // Handle location filter dropdown
        add_action('pre_get_posts', [$this, 'handle_location_filter_dropdown']);
    }

    /**
     * Filter orders for location managers (Traditional Posts)
     */
    public function filter_orders_by_location_manager($query)
    {
        // Only apply in admin area
        if (!is_admin()) {
            return;
        }

        // Only apply to main query
        if (!$query->is_main_query()) {
            return;
        }

        // Only apply to shop_order post type
        if (!isset($query->query['post_type']) || $query->query['post_type'] !== 'shop_order') {
            return;
        }

        // Check if current user is a location manager
        if (!$this->is_current_user_location_manager()) {
            return;
        }

        // Get assigned locations for current user
        $assigned_locations = MULOPIMFWC_Location_Managers::get_user_assigned_locations();

        if (empty($assigned_locations)) {
            // If no locations assigned, show no orders
            $query->set('post__in', [0]);
            return;
        }

        // Add meta query to filter by store location
        $meta_query = $query->get('meta_query') ?: [];

        $meta_query[] = [
            'key' => '_store_location',
            'value' => $assigned_locations,
            'compare' => 'IN'
        ];

        $query->set('meta_query', $meta_query);
    }

    /**
     * Filter orders for location managers (HPOS - High Performance Order Storage)
     */
    public function filter_hpos_orders_by_location_manager($query_args)
    {
        // Only apply in admin area
        if (!is_admin()) {
            return $query_args;
        }

        // Check if current user is a location manager
        if (!$this->is_current_user_location_manager()) {
            return $query_args;
        }

        // Get assigned locations for current user
        $assigned_locations = MULOPIMFWC_Location_Managers::get_user_assigned_locations();

        if (empty($assigned_locations)) {
            // If no locations assigned, show no orders
            $query_args['post__in'] = [0];
            return $query_args;
        }

        // Add meta query for HPOS
        if (!isset($query_args['meta_query'])) {
            $query_args['meta_query'] = [];
        }

        $query_args['meta_query'][] = [
            'key' => '_store_location',
            'value' => $assigned_locations,
            'compare' => 'IN'
        ];

        return $query_args;
    }

    /**
     * Add location filter dropdown for admins (not location managers)
     */
    public function add_location_filter_dropdown()
    {
        global $typenow;

        // Only show on orders page
        if ($typenow !== 'shop_order') {
            return;
        }

        // Don't show for location managers (they already have filtered view)
        if ($this->is_current_user_location_manager()) {
            return;
        }

        // Only show for users who can manage woocommerce
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        global $mulopimfwc_locations;

        if (empty($mulopimfwc_locations)) {
            return;
        }

        $selected_location = isset($_GET['filter_by_location']) ? sanitize_text_field($_GET['filter_by_location']) : '';

        echo '<select name="filter_by_location" id="filter_by_location">';
        echo '<option value="">' . esc_html__('All Locations', 'multi-location-product-and-inventory-management') . '</option>';

        foreach ($mulopimfwc_locations as $location) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr(rawurldecode($location->slug)),
                selected($selected_location, rawurldecode($location->slug), false),
                esc_html($location->name)
            );
        }

        echo '</select>';
    }

    /**
     * Handle location filter dropdown selection
     */
    public function handle_location_filter_dropdown($query)
    {
        // Only apply in admin area
        if (!is_admin()) {
            return;
        }

        // Only apply to main query
        if (!$query->is_main_query()) {
            return;
        }

        // Only apply to shop_order post type
        if (!isset($query->query['post_type']) || $query->query['post_type'] !== 'shop_order') {
            return;
        }

        // Don't apply if user is location manager (they have their own filtering)
        if ($this->is_current_user_location_manager()) {
            return;
        }

        // Check if location filter is set
        if (empty($_GET['filter_by_location'])) {
            return;
        }

        $filter_location = sanitize_text_field($_GET['filter_by_location']);

        // Add meta query to filter by selected location
        $meta_query = $query->get('meta_query') ?: [];

        $meta_query[] = [
            'key' => '_store_location',
            'value' => $filter_location,
            'compare' => '='
        ];

        $query->set('meta_query', $meta_query);
    }

    /**
     * Check if current user is a location manager
     */
    private function is_current_user_location_manager()
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();
        return in_array('mulopimfwc_location_manager', $user->roles);
    }

    /**
     * Add notice to show current location filtering status
     */
    public function add_location_filter_notice()
    {
        $screen = get_current_screen();

        if (!$screen || $screen->id !== 'edit-shop_order') {
            return;
        }

        if (!$this->is_current_user_location_manager()) {
            return;
        }

        $assigned_locations = MULOPIMFWC_Location_Managers::get_user_assigned_locations();

        if (empty($assigned_locations)) {
            echo '<div class="notice notice-warning"><p>' .
                esc_html__('You are not assigned to any locations. No orders will be displayed.', 'multi-location-product-and-inventory-management') .
                '</p></div>';
            return;
        }

        global $mulopimfwc_locations;
        $location_names = [];

        foreach ($assigned_locations as $location_slug) {
            $location = get_term_by('slug', $location_slug, 'mulopimfwc_store_location');
            if ($location) {
                $location_names[] = $location->name;
            }
        }

        if (!empty($location_names)) {
            
            echo '<div class="notice notice-info"><p>' .
                sprintf(
                    // translators: %s: List of location names (e.g. "Sydney, Melbourne")
                    esc_html__('You are viewing orders for: %s', 'multi-location-product-and-inventory-management'),
                    '<strong>' . implode(', ', esc_attr($location_names)) . '</strong>'
                ) .
                '</p></div>';
        }
    }
}

// Initialize the order filter
add_action('init', function () {
    new MULOPIMFWC_Order_Filter();
});

// Also add the notice functionality
add_action('admin_notices', [new MULOPIMFWC_Order_Filter(), 'add_location_filter_notice']);



/**
 * Filter order count in admin menu for location managers
 * This handles both traditional posts and WooCommerce HPOS
 */

class MULOPIMFWC_Order_Count_Filter
{
    public function __construct()
    {
        // Filter post counts for traditional WordPress posts
        add_filter('wp_count_posts', [$this, 'filter_order_count'], 10, 3);

        // Filter WooCommerce HPOS order counts
        add_filter('woocommerce_order_query', [$this, 'filter_wc_order_count_query'], 10, 2);

        // Alternative approach for WooCommerce admin menu counts
        add_filter('woocommerce_menu_order_count', [$this, 'filter_wc_menu_order_count']);
    }

    /**
     * Filter order count for traditional WordPress posts
     */
    public function filter_order_count($counts, $type, $perm)
    {
        if ($type !== 'shop_order' || !is_admin()) {
            return $counts;
        }

        if (!$this->is_current_user_location_manager()) {
            return $counts;
        }

        $assigned_locations = MULOPIMFWC_Location_Managers::get_user_assigned_locations();

        if (empty($assigned_locations)) {
            // Reset all counts to 0 if no locations assigned
            $empty_counts = new stdClass();
            foreach ($counts as $status => $count) {
                $empty_counts->$status = 0;
            }
            return $empty_counts;
        }

        // Get filtered counts for each status
        $filtered_counts = new stdClass();
        foreach ($counts as $status => $count) {
            $filtered_counts->$status = $this->get_filtered_order_count($status, $assigned_locations);
        }

        return $filtered_counts;
    }

    /**
     * Get filtered order count for specific status and locations
     */
    private function get_filtered_order_count($status, $assigned_locations)
    {
        global $wpdb;

        // Handle WooCommerce HPOS if enabled
        if ($this->is_hpos_enabled()) {

            // HPOS enabled - query orders table
            $orders_table = $wpdb->prefix . 'wc_orders';
            $orders_meta_table = $wpdb->prefix . 'wc_orders_meta';

            $placeholders = implode(',', array_fill(0, count($assigned_locations), '%s'));
            $query_params = array_merge([$status], $assigned_locations);

            $sql = $wpdb->prepare("
                SELECT COUNT(o.id)
                FROM {$orders_table} o
                INNER JOIN {$orders_meta_table} om ON o.id = om.order_id
                WHERE o.status = %s
                AND om.meta_key = '_store_location'
                AND om.meta_value IN ({$placeholders})
            ", $query_params);

            return (int) $wpdb->get_var($sql);
        } else {
            // Traditional posts table
            $placeholders = implode(',', array_fill(0, count($assigned_locations), '%s'));
            $query_params = array_merge([$status], $assigned_locations);

            $sql = $wpdb->prepare("
                SELECT COUNT(p.ID)
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'shop_order'
                AND p.post_status = %s
                AND pm.meta_key = '_store_location'
                AND pm.meta_value IN ({$placeholders})
            ", $query_params);

            return (int) $wpdb->get_var($sql);
        }
    }

    /**
     * Filter WooCommerce HPOS order count query
     */
    public function filter_wc_order_count_query($query, $query_vars)
    {
        if (!is_admin() || !$this->is_current_user_location_manager()) {
            return $query;
        }

        $assigned_locations = MULOPIMFWC_Location_Managers::get_user_assigned_locations();

        if (empty($assigned_locations)) {
            // Force no results
            $query_vars['post__in'] = [0];
            return $query;
        }

        // Add meta query for assigned locations
        if (!isset($query_vars['meta_query'])) {
            $query_vars['meta_query'] = [];
        }

        $query_vars['meta_query'][] = [
            'key' => '_store_location',
            'value' => $assigned_locations,
            'compare' => 'IN'
        ];

        return $query;
    }

    /**
     * Filter WooCommerce menu order count
     */
    public function filter_wc_menu_order_count($count)
    {
        if (!is_admin() || !$this->is_current_user_location_manager()) {
            return $count;
        }

        $assigned_locations = MULOPIMFWC_Location_Managers::get_user_assigned_locations();

        if (empty($assigned_locations)) {
            return 0;
        }

        // Get count of processing orders for assigned locations
        return $this->get_filtered_order_count('wc-processing', $assigned_locations);
    }

    /**
     * Check if WooCommerce HPOS is enabled
     */
    private function is_hpos_enabled()
    {
        // Check if WooCommerce HPOS is available and enabled
        if (!function_exists('wc_get_container')) {
            return false;
        }

        // Multiple ways to check HPOS
        if (function_exists('wc_get_order_datastore')) {
            $datastore = wc_get_order_datastore();
            return is_a($datastore, 'Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore');
        }

        // Alternative check
        if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil')) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }

        // Fallback check
        if (function_exists('wc_get_container')) {
            try {
                $features_controller = wc_get_container()->get(\Automattic\WooCommerce\Internal\Features\FeaturesController::class);
                return $features_controller->feature_is_enabled('custom_order_tables');
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Check if current user is a location manager
     */
    public function is_current_user_location_manager()
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();
        return in_array('mulopimfwc_location_manager', $user->roles);
    }

    /**
     * Get total order count for all statuses (for dashboard widget)
     */
    public function get_location_manager_total_orders()
    {
        if (!$this->is_current_user_location_manager()) {
            return null;
        }

        $assigned_locations = MULOPIMFWC_Location_Managers::get_user_assigned_locations();

        if (empty($assigned_locations)) {
            return 0;
        }

        global $wpdb;

        // Handle WooCommerce HPOS if enabled
        if ($this->is_hpos_enabled()) {

            $orders_table = $wpdb->prefix . 'wc_orders';
            $orders_meta_table = $wpdb->prefix . 'wc_orders_meta';

            $placeholders = implode(',', array_fill(0, count($assigned_locations), '%s'));

            $sql = $wpdb->prepare("
                SELECT COUNT(DISTINCT o.id)
                FROM {$orders_table} o
                INNER JOIN {$orders_meta_table} om ON o.id = om.order_id
                WHERE om.meta_key = '_store_location'
                AND om.meta_value IN ({$placeholders})
            ", $assigned_locations);

            return (int) $wpdb->get_var($sql);
        } else {
            $placeholders = implode(',', array_fill(0, count($assigned_locations), '%s'));

            $sql = $wpdb->prepare("
                SELECT COUNT(DISTINCT p.ID)
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'shop_order'
                AND pm.meta_key = '_store_location'
                AND pm.meta_value IN ({$placeholders})
            ", $assigned_locations);

            return (int) $wpdb->get_var($sql);
        }
    }
}

// Initialize the order count filter
new MULOPIMFWC_Order_Count_Filter();

/**
 * Alternative approach: Hook into WooCommerce reports and dashboard widgets
 */
add_filter('woocommerce_reports_order_statuses', function ($order_statuses) {
    $filter = new MULOPIMFWC_Order_Count_Filter();
    if (
        method_exists($filter, 'is_current_user_location_manager') &&
        $filter->is_current_user_location_manager()
    ) {
        // This ensures reports also respect location filtering
        add_filter('woocommerce_reports_get_order_report_query', function ($query) {
            $assigned_locations = MULOPIMFWC_Location_Managers::get_user_assigned_locations();
            if (!empty($assigned_locations)) {
                $query['meta_query'] = $query['meta_query'] ?? [];
                $query['meta_query'][] = [
                    'key' => '_store_location',
                    'value' => $assigned_locations,
                    'compare' => 'IN'
                ];
            }
            return $query;
        });
    }
    return $order_statuses;
});
?>