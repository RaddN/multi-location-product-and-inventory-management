<?php

/**
 * Location Managers Admin Page
 * 
 * @package Multi Location Product & Inventory Management
 * @since 1.0.1
 */

if (!defined('ABSPATH')) exit;

class MULOPIMFWC_Location_Managers
{

    public function __construct()
    {
        // Add location manager meta box to user profile
        add_action('show_user_profile', [$this, 'add_user_profile_fields']);
        add_action('edit_user_profile', [$this, 'add_user_profile_fields']);
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
        $options = get_option('mulopimfwc_display_options', []);
        $global_capabilities = isset($options['location_manager_capabilities']) ? $options['location_manager_capabilities'] : [];

?>
        <div class="wrap mulopimfwc_pro_only mulopimfwc_pro_only_blur1">
            <h1><?php echo esc_html__('Location Managers', 'multi-location-product-and-inventory-management'); ?></h1>

            <div class="mulopimfwc-location-managers">
                <!-- Add New Manager Button -->
                <div class="mulopimfwc-manager-header">
                    <button type="button" class="button button-primary" id="mulopimfwc-add-manager-btn">
                        <?php echo esc_html__('Add New Location Manager', 'multi-location-product-and-inventory-management'); ?>
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
                                $assigned_locations = $manager->assigned_locations;
                                $manager_capabilities = $manager->capabilities;
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
                                        <div class="manager-actions">
                                            <button type="button" class="button button-small mulopimfwc-edit-manager" data-manager-id="<?php echo esc_attr($manager->ID); ?>">
                                                <?php echo esc_html__('Edit', 'multi-location-product-and-inventory-management'); ?>
                                            </button>
                                            <button type="button" class="button button-small button-link-delete mulopimfwc-delete-manager" data-manager-id="<?php echo esc_attr($manager->ID); ?>">
                                                <?php echo esc_html__('Delete', 'multi-location-product-and-inventory-management'); ?>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="manager-locations">
                                        <h4><?php echo esc_html__('Assigned Locations:', 'multi-location-product-and-inventory-management'); ?></h4>
                                        <?php if (empty($assigned_locations)): ?>
                                            <p class="no-locations"><?php echo esc_html__('No locations assigned', 'multi-location-product-and-inventory-management'); ?></p>
                                        <?php else: ?>
                                            <ul class="location-list">
                                                <?php foreach ($assigned_locations as $location_slug): ?>
                                                    <?php
                                                    if ($location_slug):
                                                    ?>
                                                        <li><span class="location-tag"><?php echo esc_html($location_slug); ?></span></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>

                                    <div class="manager-capabilities">
                                        <h4><?php echo esc_html__('Permissions:', 'multi-location-product-and-inventory-management'); ?></h4>
                                        <?php if (empty($manager_capabilities)): ?>
                                            <p class="no-capabilities"><?php echo esc_html__('No specific permissions set (using global defaults)', 'multi-location-product-and-inventory-management'); ?></p>
                                        <?php else: ?>
                                            <ul class="capability-list">
                                                <?php foreach ($manager_capabilities as $capability): ?>
                                                    <li><span class="capability-tag"><?php echo esc_html($capability); ?></span></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Add/Edit Manager Modal -->
        <div id="mulopimfwc-manager-modal" class="mulopimfwc-modal" style="display: none;">
            <div class="mulopimfwc-modal-content mulopimfwc_pro_only mulopimfwc_pro_only_blur">
                <div class="mulopimfwc-modal-header">
                    <h2 id="mulopimfwc-modal-title"><?php echo esc_html__('Add New Location Manager', 'multi-location-product-and-inventory-management'); ?></h2>
                    <button type="button" class="mulopimfwc-modal-close">&times;</button>
                </div>

                <form id="mulopimfwc-manager-form" method="post">
                    <input type="hidden" id="manager-id" name="manager_id" value="">
                    <input type="hidden" id="action-type" name="action_type" value="create">

                    <div class="mulopimfwc-form-row">
                        <label for="user-search"><?php echo esc_html__('Select User:', 'multi-location-product-and-inventory-management'); ?></label>
                        <div class="user-search-container">
                            <input type="text" id="user-search" placeholder="<?php echo esc_attr__('Search users by name or email...', 'multi-location-product-and-inventory-management'); ?>">
                            <input type="hidden" id="selected-user-id" name="user_id" value="">
                            <div id="user-search-results" class="search-results"></div>
                        </div>
                        <div id="create-new-user" style="display: none;">
                            <hr>
                            <h4><?php echo esc_html__('Or Create New User:', 'multi-location-product-and-inventory-management'); ?></h4>
                            <div class="mulopimfwc-form-row">
                                <label for="new-username"><?php echo esc_html__('Username:', 'multi-location-product-and-inventory-management'); ?></label>
                                <input type="text" id="new-username" name="new_username" value="">
                            </div>
                            <div class="mulopimfwc-form-row">
                                <label for="new-email"><?php echo esc_html__('Email:', 'multi-location-product-and-inventory-management'); ?></label>
                                <input type="email" id="new-email" name="new_email" value="">
                            </div>
                            <div class="mulopimfwc-form-row">
                                <label for="new-first-name"><?php echo esc_html__('First Name:', 'multi-location-product-and-inventory-management'); ?></label>
                                <input type="text" id="new-first-name" name="new_first_name" value="">
                            </div>
                            <div class="mulopimfwc-form-row">
                                <label for="new-last-name"><?php echo esc_html__('Last Name:', 'multi-location-product-and-inventory-management'); ?></label>
                                <input type="text" id="new-last-name" name="new_last_name" value="">
                            </div>
                        </div>
                        <button type="button" id="toggle-create-user" class="button button-secondary">
                            <?php echo esc_html__('Create New User Instead', 'multi-location-product-and-inventory-management'); ?>
                        </button>
                    </div>

                    <div class="mulopimfwc-form-row">
                        <label><?php echo esc_html__('Assign Locations:', 'multi-location-product-and-inventory-management'); ?></label>
                        <div class="location-checkboxes">
                            <?php if (!empty($locations)): ?>
                                <?php foreach ($locations as $location): ?>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="assigned_locations[]" value="<?php echo esc_attr($location->slug); ?>">
                                        <?php echo esc_html($location->name); ?>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p><?php echo esc_html__('No locations available. Please create locations first.', 'multi-location-product-and-inventory-management'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mulopimfwc-form-row">
                        <label><?php echo esc_html__('Individual Permissions:', 'multi-location-product-and-inventory-management'); ?></label>
                        <p class="description"><?php echo esc_html__('Leave unchecked to use global default permissions.', 'multi-location-product-and-inventory-management'); ?></p>
                        <div class="capability-checkboxes">
                            <?php foreach ($capabilities as $cap_key => $cap_label): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="manager_capabilities[]" value="<?php echo esc_attr($cap_key); ?>">
                                    <?php echo esc_html($cap_label); ?>
                                    <?php if (in_array($cap_key, $global_capabilities)): ?>
                                        <span class="global-default">(<?php echo esc_html__('Global Default', 'multi-location-product-and-inventory-management'); ?>)</span>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mulopimfwc-modal-footer">
                        <button type="button" class="button button-secondary mulopimfwc-modal-close">
                            <?php echo esc_html__('Cancel', 'multi-location-product-and-inventory-management'); ?>
                        </button>
                        <button type="submit" class="button button-primary" id="mulopimfwc-save-manager">
                            <?php echo esc_html__('Save Manager', 'multi-location-product-and-inventory-management'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php wp_nonce_field('mulopimfwc_location_managers_nonce', 'mulopimfwc_location_managers_nonce'); ?>

        <style>
            .mulopimfwc-location-managers {
                margin-top: 20px;
            }

            .mulopimfwc-manager-header {
                margin-bottom: 20px;
            }

            .mulopimfwc-managers-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 20px;
            }

            .mulopimfwc-manager-card {
                background: #fff;
                border: 1px solid #ddd;
                padding: 20px;
                border-radius: 8px;
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
                font-size: 16px;
            }

            .manager-email {
                margin: 5px 0 0;
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
                color: #333;
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
                background: #f0f0f1;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                color: #333;
            }

            .capability-tag {
                background: #e7f3ff;
                color: #0073aa;
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

        <script>
            jQuery(document).ready(function($) {
                let searchTimeout;
                let isEditMode = false;

                // Add manager button
                $('#mulopimfwc-add-manager-btn').on('click', function() {
                    isEditMode = false;
                    $('#mulopimfwc-modal-title').text('<?php echo esc_js(__('Add New Location Manager', 'multi-location-product-and-inventory-management')); ?>');
                    $('#action-type').val('create');
                    $('#mulopimfwc-manager-modal').show();
                });

                // Edit manager button
                $(document).on('click', '.mulopimfwc-edit-manager', function() {
                    isEditMode = true;
                    const managerId = $(this).data('manager-id');
                    loadManagerData(managerId);
                });

                // Delete manager button
                $(document).on('click', '.mulopimfwc-delete-manager', function() {
                    if (confirm('<?php echo esc_js(__('Are you sure you want to delete this location manager?', 'multi-location-product-and-inventory-management')); ?>')) {
                    }
                });

                // Close modal
                $(document).on('click', '.mulopimfwc-modal-close', function() {
                    $('#mulopimfwc-manager-modal').hide();
                });

                function loadManagerData(managerId) {
                    // This would typically load data via AJAX
                    // For now, we'll just show the modal
                    $('#mulopimfwc-modal-title').text('<?php echo esc_js(__('Edit Location Manager', 'multi-location-product-and-inventory-management')); ?>');
                    $('#manager-id').val(managerId);
                    $('#action-type').val('edit');
                    $('#mulopimfwc-manager-modal').show();
                }
            });
        </script>
    <?php
    }


    /**
     * Add user profile fields for location manager settings
     */
    public function add_user_profile_fields($user)
    {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        if (!in_array('mulopimfwc_location_manager', $user->roles)) {
            return;
        }

        global $mulopimfwc_locations;
        $assigned_locations = get_user_meta($user->ID, 'mulopimfwc_assigned_locations', true);
        $manager_capabilities = get_user_meta($user->ID, 'mulopimfwc_manager_capabilities', true);
        $capabilities = $this->get_available_capabilities();

        if (!is_array($assigned_locations)) $assigned_locations = [];
        if (!is_array($manager_capabilities)) $manager_capabilities = [];
    ?>

        <h3><?php echo esc_html__('Location Manager Settings', 'multi-location-product-and-inventory-management'); ?></h3>

        <table class="form-table">
            <tr>
                <th><label><?php echo esc_html__('Assigned Locations', 'multi-location-product-and-inventory-management'); ?></label></th>
                <td>
                    <?php if (!empty($mulopimfwc_locations)): ?>
                        <?php foreach ($mulopimfwc_locations as $location): ?>
                            <label style="display: block; margin-bottom: 5px;">
                                <input type="checkbox" name="mulopimfwc_assigned_locations[]" value="<?php echo esc_attr($location->slug); ?>" <?php checked(in_array($location->slug, $assigned_locations)); ?>>
                                <?php echo esc_html($location->name); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><?php echo esc_html__('No locations available', 'multi-location-product-and-inventory-management'); ?></p>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th><label><?php echo esc_html__('Manager Capabilities', 'multi-location-product-and-inventory-management'); ?></label></th>
                <td>
                    <?php foreach ($capabilities as $cap_key => $cap_label): ?>
                        <label style="display: block; margin-bottom: 5px;">
                            <input type="checkbox" name="mulopimfwc_manager_capabilities[]" value="<?php echo esc_attr($cap_key); ?>" <?php checked(in_array($cap_key, $manager_capabilities)); ?>>
                            <?php echo esc_html($cap_label); ?>
                        </label>
                    <?php endforeach; ?>
                    <p class="description"><?php echo esc_html__('Individual permissions for this manager. Leave unchecked to use global defaults.', 'multi-location-product-and-inventory-management'); ?></p>
                </td>
            </tr>
        </table>

<?php
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
            $options = get_option('mulopimfwc_display_options', []);
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
                esc_attr($location->slug),
                selected($selected_location, $location->slug, false),
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
                    esc_html__('You are viewing orders for: %s', 'multi-location-product-and-inventory-management'),
                    '<strong>' . implode(', ', $location_names) . '</strong>'
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
    private function is_current_user_location_manager()
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