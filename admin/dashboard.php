<?php

if (!defined('ABSPATH')) exit;

class MULOPIMFWC_Dashboard
{
    /**
     * Constructor
     */
    public function __construct() {}

    /**
     * Render the dashboard page content
     * 
     * @return void
     */

    public function adjustColorLightness($hex, $adjust)
    {
        // Remove # if present
        if (!is_string($hex) || empty(trim($hex))) {
            return '#000000';
        }
        $hex = ltrim($hex, '#');

        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Adjust lightness
        $r = max(0, min(255, $r + $adjust));
        $g = max(0, min(255, $g + $adjust));
        $b = max(0, min(255, $b + $adjust));

        // Convert back to hex
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
    public function dashboard_page_content()
    {
        global $mulopimfwc_locations;

        // Increase memory limit for dashboard operations
        if (function_exists('ini_set')) {
            ini_set('memory_limit', '512M');
        }

        // Set max execution time
        set_time_limit(300);

        // Enqueue necessary scripts and styles
        wp_enqueue_script('chart-js', plugin_dir_url(__FILE__) . '../assets/js/chart.min.js', array(), '3.9.1', true);
        wp_enqueue_script('lwp-dashboard-js', plugin_dir_url(__FILE__) . '../assets/js/dashboard.js', array('jquery', 'chart-js'), "1.0.8", true);
        wp_enqueue_style('lwp-dashboard-css', plugin_dir_url(__FILE__) . '../assets/css/dashboard.css', array(), "1.0.8");

        // Initialize data arrays
        $location_labels = [];
        $location_colors = [];
        $location_border_colors = [];
        $product_counts = [];
        $stock_levels = [];

        // Check if locations exist and is not an error
        if (empty($mulopimfwc_locations) || is_wp_error($mulopimfwc_locations)) {
            $mulopimfwc_locations = [];
        }

        $base_colors = [
            ['fill' => '#ef4444', 'border' => '#f87171'], // red
            ['fill' => '#f59e0b', 'border' => '#fbbf24'], // orange
            ['fill' => '#10b981', 'border' => '#34d399'], // green
            ['fill' => '#06b6d4', 'border' => '#22d3ee'], // cyan/paste
            ['fill' => '#8b5cf6', 'border' => '#a78bfa'], // violet
            ['fill' => '#ec4899', 'border' => '#f472b6'], // pink
            ['fill' => '#6366f1', 'border' => '#818cf8'], // indigo
        ];

        // Generate colors and get data for each location with pagination
        foreach ($mulopimfwc_locations as $index => $location) {
            $base_index = $index % count($base_colors);
            $cycle = floor($index / count($base_colors));

            if ($cycle == 0) {
                // First 7 locations: use exact colors
                $location_colors[$location->name] = $base_colors[$base_index]['fill'];
                $location_border_colors[$location->name] = $base_colors[$base_index]['border'];
            } else {
                // After 7 locations: create variations by adjusting lightness
                $lightness_adjust = ($cycle * 10) % 30; // Adjust lightness by 10%, 20%, 30%, then repeat

                // Convert hex to HSL, adjust, and convert back
                $fill_color = $base_colors[$base_index]['fill'];
                $border_color = $base_colors[$base_index]['border'];

                // Create lighter/darker variations
                $location_colors[$location->name] = $this->adjustColorLightness($fill_color, $lightness_adjust);
                $location_border_colors[$location->name] = $this->adjustColorLightness($border_color, $lightness_adjust);
            }

            $location_labels[] = $location->name;
        }

        $location_stats = $this->get_location_product_and_stock_totals($mulopimfwc_locations);
        $product_counts = $location_stats['product_counts'];
        $stock_levels = $location_stats['stock_levels'];

        // Get low stock products with limit
        $low_stock_products = $this->get_low_stock_products_efficiently();

        // Get recent products data efficiently
        $recent_products_data = $this->get_recent_products_data();

        // Get monthly investment data with caching
        $monthly_investment_data = $this->get_monthly_investment_data_cached();

        // Calculate totals efficiently
        $total_products = $this->get_total_products_count();
        $total_investment = $this->calculate_total_investment_efficiently();
        $total_revenue = wp_rand(1000, 100000);

        wp_localize_script('lwp-dashboard-js', 'mulopimfwc_DashboardData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'export_nonce' => wp_create_nonce('mulopimfwc_export_nonce'),
            'locationLabels' => $location_labels,
            'productCounts' => $product_counts,
            'stockLevels' => $stock_levels,
            'locationColors' => $location_colors,
            'locationBorderColors' => $location_border_colors,
            'dateLabels' => $recent_products_data['labels'],
            'monthlyInvestmentLabels' => $monthly_investment_data['labels'],
            'currency' => get_woocommerce_currency_symbol(),
            'currency_code' => get_woocommerce_currency(),
            'i18n' => [
                'totalStock' => __('Total Stock', 'multi-location-product-and-inventory-management'),
                'newProducts' => __('New Products', 'multi-location-product-and-inventory-management'),
                'investment' => __('Investment', 'multi-location-product-and-inventory-management'),
                'orders' => __('Orders', 'multi-location-product-and-inventory-management'),
                'revenue' => __('Revenue', 'multi-location-product-and-inventory-management')
            ]
        ]);

?>
        <div class="wrap lwp-dashboard">

            <h1 style="display: none !important;"><?php echo esc_html__('Location Wise Products Dashboard', 'multi-location-product-and-inventory-management'); ?></h1>

            <div class="lwp-dashboard-overview">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <h1><?php echo esc_html__('Location Wise Products Dashboard', 'multi-location-product-and-inventory-management'); ?></h1>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <!-- Filter Toggle Button -->
                        <button class="mulopimfwc-btn-secondary filter_toggle_btn mulopimfwc_pro_only" style="padding: 10px 20px !important;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            <?php echo esc_html__('Filters', 'multi-location-product-and-inventory-management'); ?>
                        </button>

                        <!-- Export Dropdown -->
                        <div class="export_report_dropdown  mulopimfwc_pro_only">
                            <button class="mulopimfwc-btn-primary export_toggle_btn" style="padding: 10px 30px !important;">
                                <svg width="16" height="16" viewBox="0 0 0.48 0.48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M.226.046a.02.02 0 0 1 .028 0l.08.08a.02.02 0 0 1-.028.028L.26.108V.32a.02.02 0 1 1-.04 0V.108L.174.154A.02.02 0 0 1 .146.126zM.1.34a.02.02 0 0 1 .02.02V.4h.24V.36a.02.02 0 1 1 .04 0V.4a.04.04 0 0 1-.04.04H.12A.04.04 0 0 1 .08.4V.36A.02.02 0 0 1 .1.34" />
                                </svg>
                                <?php echo esc_html__('Export Report', 'multi-location-product-and-inventory-management'); ?>
                                <span class="dropdown_icon">▾</span>
                            </button>

                            <div class="dropdown_menu">
                                <button>
                                    <?php echo esc_html__('Export in CSV', 'multi-location-product-and-inventory-management'); ?>
                                </button>

                                <button>
                                    <?php echo esc_html__('Export in Excel (HTML)', 'multi-location-product-and-inventory-management'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <style>
                        .export_report_dropdown {
                            position: relative;
                            display: inline-block;
                        }

                        .dropdown_icon {
                            font-size: 12px;
                            margin-left: 5px;
                            transition: transform 0.2s ease;
                        }

                        .export_report_dropdown.active .dropdown_icon {
                            transform: rotate(180deg);
                        }

                        .dropdown_menu {
                            position: absolute;
                            top: calc(100% + 8px);
                            right: 0;
                            background: #fff;
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
                            display: none;
                            min-width: 220px;
                            overflow: hidden;
                            z-index: 9999;
                            animation: fadeIn 0.2s ease;
                        }

                        .dropdown_menu button {
                            width: 100%;
                            padding: 12px 20px;
                            background: transparent;
                            border: none;
                            text-align: left;
                            font-size: 14px;
                            color: #334155;
                            cursor: pointer;
                            transition: all 0.2s ease;
                        }

                        .dropdown_menu button:hover {
                            background-color: #f3f4f6;
                            color: #1e40af;
                        }

                        @keyframes fadeIn {
                            from {
                                opacity: 0;
                                transform: translateY(-5px);
                            }

                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }
                    </style>

                    <script>
                        jQuery(document).ready(function($) {
                            const dropdown = $('.export_report_dropdown');
                            const toggleBtn = dropdown.find('.export_toggle_btn');
                            const menu = dropdown.find('.dropdown_menu');

                            // Toggle dropdown
                            toggleBtn.on('click', function(e) {
                                e.stopPropagation();
                                dropdown.toggleClass('active');
                                menu.slideToggle(150);
                            });

                            // Close dropdown when clicking outside
                            $(document).on('click', function(e) {
                                if (!dropdown.is(e.target) && dropdown.has(e.target).length === 0) {
                                    dropdown.removeClass('active');
                                    menu.slideUp(150);
                                }
                            });
                        });
                    </script>


                </div>
                <div class="lwp-card-stats">
                    <div class="lwp-stats-grid">
                        <div class="lwp-stat-item">

                            <div class="lwp-stat-item-icon">

                                <svg class="svg-inline--fa fa-box" aria-hidden="true" data-prefix="fas" data-icon="box" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18">
                                    <path fill="#2563eb" d="M50.7 58.5 0 160h208V32H93.7c-18.2 0-34.8 10.3-43 26.5M240 160h208L397.3 58.5c-8.2-16.2-24.8-26.5-43-26.5H240zm208 32H0v224c0 35.3 28.7 64 64 64h320c35.3 0 64-28.7 64-64z" />
                                </svg>
                            </div>
                            <div>
                                <span class="lwp-stat-label"><?php echo esc_html__('Total Products', 'multi-location-product-and-inventory-management'); ?></span>
                                <span class="lwp-stat-value"><?php echo esc_html($total_products); ?></span>
                            </div>
                        </div>
                        <div class="lwp-stat-item">
                            <div class="lwp-stat-item-icon" style="background-color: #dcfce7;">

                                <svg class="svg-inline--fa fa-location-dot" aria-hidden="true" data-prefix="fas" data-icon="location-dot" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="18" height="18">
                                    <path fill="#16a34a" d="M215.7 499.2C267 435 384 279.4 384 192 384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2 12.3 15.3 35.1 15.3 47.4 0M192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128" />
                                </svg>
                            </div>
                            <div>
                                <span class="lwp-stat-label"><?php echo esc_html__('Locations', 'multi-location-product-and-inventory-management'); ?></span>
                                <span class="lwp-stat-value"><?php echo count($mulopimfwc_locations); ?></span>

                            </div>

                        </div>
                        <div class="lwp-stat-item mulopimfwc_pro_only mulopimfwc_pro_only_blur">
                            <div class="lwp-stat-item-icon" style="background-color: #f3e8ff;">

                                <svg class="svg-inline--fa fa-cart-shopping" aria-hidden="true" data-prefix="fas" data-icon="cart-shopping" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="18" height="18">
                                    <path fill="#9333ea" d="M0 24C0 10.7 10.7 0 24 0h45.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-34.6 0-64.3-24.6-70.7-58.5l-51.6-271c-.7-3.8-4-6.5-7.9-6.5H24C10.7 48 0 37.3 0 24m128 440a48 48 0 1 1 96 0 48 48 0 1 1-96 0m336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96" />
                                </svg>
                            </div>
                            <div>
                                <span class="lwp-stat-label"><?php echo esc_html__('Orders (30 days)', 'multi-location-product-and-inventory-management'); ?></span>
                                <span class="lwp-stat-value"><?php echo rand(1, 100); ?></span>

                            </div>

                        </div>
                        <div class="lwp-stat-item">
                            <div class="lwp-stat-item-icon" style="background-color: #cffafe;">

                                <svg class="svg-inline--fa fa-money-bag" aria-hidden="true" data-prefix="fas" data-icon="money-bag" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="18" height="18">
                                    <g class="missing" fill="#0891b2">
                                        <path d="m156.5 447.7-12.6 29.5c-18.7-9.5-35.9-21.2-51.5-34.9l22.7-22.7c12.5 10.9 26.4 20.4 41.4 28.1M40.6 272H8.5c1.4 21.2 5.4 41.7 11.7 61.1L50 321.2c-4.9-15.7-8.2-32.2-9.4-49.2m0-32c1.4-18.8 5.2-37 11.1-54.1l-29.5-12.6c-7.5 21-12.2 43.4-13.7 66.7zm23.7-83.5c7.8-14.9 17.2-28.8 28.1-41.5L69.7 92.3c-13.7 15.6-25.5 32.8-34.9 51.5zM397 419.6c-13.9 12-29.4 22.3-46.1 30.4l11.9 29.8c20.7-9.9 39.8-22.6 56.9-37.6zM115 92.4c13.9-12 29.4-22.3 46.1-30.4l-11.9-29.8c-20.7 9.9-39.8 22.6-56.8 37.6zm332.7 263.1c-7.8 14.9-17.2 28.8-28.1 41.5l22.7 22.7c13.7-15.6 25.5-32.9 34.9-51.5zm23.7-83.5c-1.4 18.8-5.2 37-11.1 54.1l29.5 12.6c7.5-21.1 12.2-43.5 13.6-66.8h-32zM321.2 462c-15.7 5-32.2 8.2-49.2 9.4v32.1c21.2-1.4 41.7-5.4 61.1-11.7zm-81.2 9.4c-18.8-1.4-37-5.2-54.1-11.1l-12.6 29.5c21.1 7.5 43.5 12.2 66.8 13.6v-32zm222-280.6c5 15.7 8.2 32.2 9.4 49.2h32.1c-1.4-21.2-5.4-41.7-11.7-61.1zM92.4 397c-12-13.9-22.3-29.4-30.4-46.1l-29.8 11.9c9.9 20.7 22.6 39.8 37.6 56.9zM272 40.6c18.8 1.4 36.9 5.2 54.1 11.1l12.6-29.5c-21-7.5-43.4-12.2-66.7-13.7zM190.8 50c15.7-5 32.2-8.2 49.2-9.4V8.5c-21.2 1.4-41.7 5.4-61.1 11.7zm251.5 42.3L419.6 115c12 13.9 22.3 29.4 30.5 46.1l29.8-11.9c-9.9-20.7-22.6-39.8-37.6-56.9m-45.3.1 22.7-22.7c-15.6-13.7-32.8-25.5-51.5-34.9l-12.6 29.5c14.8 7.8 28.8 17.2 41.4 28.1" />
                                        <circle cx="256" cy="364" r="28">
                                            <animate attributeType="XML" repeatCount="indefinite" dur="2s" attributeName="r" values="28;14;28;28;14;28;" />
                                            <animate attributeType="XML" repeatCount="indefinite" dur="2s" attributeName="opacity" values="1;0;1;1;0;1;" />
                                        </circle>
                                        <path d="M263.7 312h-16c-6.6 0-12-5.4-12-12 0-71 77.4-63.9 77.4-107.8 0-20-17.8-40.2-57.4-40.2-29.1 0-44.3 9.6-59.2 28.7-3.9 5-11.1 6-16.2 2.4l-13.1-9.2c-5.6-3.9-6.9-11.8-2.6-17.2 21.2-27.2 46.4-44.7 91.2-44.7 52.3 0 97.4 29.8 97.4 80.2 0 67.6-77.4 63.5-77.4 107.8-.1 6.6-5.5 12-12.1 12">
                                            <animate attributeType="XML" repeatCount="indefinite" dur="2s" attributeName="opacity" values="1;0;0;0;0;1;" />
                                        </path>
                                    </g>
                                </svg>
                            </div>
                            <div>
                                <span class="lwp-stat-label"><?php echo esc_html__('Total Investment', 'multi-location-product-and-inventory-management'); ?></span>
                                <span class="lwp-stat-value"><?php echo wp_kses_post(wc_price($total_investment)); ?></span>

                            </div>

                        </div>
                        <div class="lwp-stat-item mulopimfwc_pro_only mulopimfwc_pro_only_blur">
                            <div class="lwp-stat-item-icon" style="background-color: #ffedd5;">

                                <svg class="svg-inline--fa fa-chart-line" aria-hidden="true" data-prefix="fas" data-icon="chart-line" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="18" height="18">
                                    <path fill="#ea580c" d="M64 64c0-17.7-14.3-32-32-32S0 46.3 0 64v336c0 44.2 35.8 80 80 80h400c17.7 0 32-14.3 32-32s-14.3-32-32-32H80c-8.8 0-16-7.2-16-16zm406.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320 210.7l-57.4-57.4c-12.5-12.5-32.8-12.5-45.3 0l-112 112c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l89.4-89.3 57.4 57.4c12.5 12.5 32.8 12.5 45.3 0l128-128z" />
                                </svg>
                            </div>
                            <div>
                                <span class="lwp-stat-label"><?php echo esc_html__('Revenue (30 days)', 'multi-location-product-and-inventory-management'); ?></span>
                                <span class="lwp-stat-value"><?php echo wp_kses_post(wc_price($total_revenue)); ?></span>

                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="lwp-dashboard-charts">
                <div class="lwp-row">
                    <div class="lwp-col">
                        <div class="lwp-card">
                            <h2><?php echo esc_html__('Products by Location', 'multi-location-product-and-inventory-management'); ?></h2>
                            <div class="lwp-chart-container">
                                <canvas id="locationProductsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="lwp-col">
                        <div class="lwp-card">
                            <h2><?php echo esc_html__('Stock Levels by Location', 'multi-location-product-and-inventory-management'); ?></h2>
                            <div class="lwp-chart-container">
                                <canvas id="locationStockChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lwp-row">
                    <div class="lwp-col">
                        <div class="lwp-card">
                            <h2><?php echo esc_html__('Orders by Location (30 days)', 'multi-location-product-and-inventory-management'); ?></h2>
                            <div class="lwp-chart-container mulopimfwc_pro_only mulopimfwc_pro_only_blur">
                                <canvas id="ordersByLocationChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="lwp-col">
                        <div class="lwp-card">
                            <h2><?php echo esc_html__('Revenue by Location (30 days)', 'multi-location-product-and-inventory-management'); ?></h2>
                            <div class="lwp-chart-container mulopimfwc_pro_only mulopimfwc_pro_only_blur">
                                <canvas id="revenueByLocationChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lwp-row">
                    <div class="lwp-col">
                        <div class="lwp-card">
                            <h2><?php echo esc_html__('New Products (Last 30 Days)', 'multi-location-product-and-inventory-management'); ?></h2>
                            <div class="lwp-chart-container mulopimfwc_pro_only mulopimfwc_pro_only_blur">
                                <canvas id="newProductsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="lwp-col">
                        <div class="lwp-card">
                            <h2><?php echo esc_html__('Investment', 'multi-location-product-and-inventory-management'); ?></h2>
                            <div class="lwp-chart-container mulopimfwc_pro_only mulopimfwc_pro_only_blur">
                                <canvas id="investment-30day"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lwp-row">
                    <div class="lwp-col">
                        <div class="lwp-card">
                            <h2><?php esc_html_e('Low Stock Products by Location', 'multi-location-product-and-inventory-management'); ?></h2>
                            <?php if (!empty($low_stock_products)) : ?>
                                <table class="lwp-low-stock-table mulopimfwc_pro_only mulopimfwc_pro_only_blur">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Product', 'multi-location-product-and-inventory-management'); ?></th>
                                            <th><?php esc_html_e('Location', 'multi-location-product-and-inventory-management'); ?></th>
                                            <th><?php esc_html_e('Stock', 'multi-location-product-and-inventory-management'); ?></th>
                                            <th><?php esc_html_e('Status', 'multi-location-product-and-inventory-management'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($low_stock_products as $item) : ?>
                                            <tr>
                                                <td>
                                                    <a>
                                                        <?php echo esc_html($item['product_title']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo esc_html($item['location_name']); ?></td>
                                                <td>
                                                    <span class="stock-quantity <?php echo $item['stock'] == 0 ? 'out-of-stock' : 'low-stock'; ?>">
                                                        <?php echo esc_html($item['stock']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="stock-status <?php echo $item['stock'] == 0 ? 'out-of-stock' : 'low-stock'; ?>">
                                                        <?php
                                                        if ($item['stock'] == 0) {
                                                            esc_html_e('Out of Stock', 'multi-location-product-and-inventory-management');
                                                        } else {
                                                            esc_html_e('Low Stock', 'multi-location-product-and-inventory-management');
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p><?php esc_html_e('No low stock products found for any location.', 'multi-location-product-and-inventory-management'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php $profitability_by_location = [
                    [
                        'location_name' => 'New York',
                        'inventory_value' => 15000,
                        'margin_value' => 4500,
                        'margin_rate' => 30.0,
                        'dead_stock_value' => 2000,
                        'dead_stock_units' => 15,
                        'average_age_days' => 45.5,
                        'shrinkage_rate' => 2.5
                    ],
                    [
                        'location_name' => 'Los Angeles',
                        'inventory_value' => 12000,
                        'margin_value' => 3600,
                        'margin_rate' => 30.0,
                        'dead_stock_value' => 1500,
                        'dead_stock_units' => 10,
                        'average_age_days' => 50.2,
                        'shrinkage_rate' => 3.0
                    ],
                    [
                        'location_name' => 'Chicago',
                        'inventory_value' => 10000,
                        'margin_value' => 2500,
                        'margin_rate' => 25.0,
                        'dead_stock_value' => 1000,
                        'dead_stock_units' => 8,
                        'average_age_days' => 60.1,
                        'shrinkage_rate' => 4.0
                    ],
                    [
                        'location_name' => 'Miami',
                        'inventory_value' => 8000,
                        'margin_value' => 2000,
                        'margin_rate' => 25.0,
                        'dead_stock_value' => 800,
                        'dead_stock_units' => 5,
                        'average_age_days' => 55.3,
                        'shrinkage_rate' => 2.0
                    ],
                    [
                        'location_name' => 'Houston',
                        'inventory_value' => 9000,
                        'margin_value' => 2700,
                        'margin_rate' => 30.0,
                        'dead_stock_value' => 900,
                        'dead_stock_units' => 7,
                        'average_age_days' => 48.7,
                        'shrinkage_rate' => 3.5
                    ]
                ]; ?>
                <div class="lwp-row">
                    <div class="lwp-col">
                        <div class="lwp-card">
                            <h2><?php esc_html_e('Profitability & Aging by Location', 'multi-location-product-and-inventory-management'); ?></h2>
                            <p class="lwp-profitability-description">
                                <?php
                                $dead_stock_days = 90;
                                printf(
                                    esc_html__('Dead stock in this table represents inventory that has not sold in the last %s days. Shrinkage reflects any sold quantity that could not be matched to current stock records.', 'multi-location-product-and-inventory-management'),
                                    esc_html($dead_stock_days)
                                );
                                ?>
                            </p>
                            <?php if (!empty($profitability_by_location)) : ?>
                                <div class="lwp-table-responsive mulopimfwc_pro_only mulopimfwc_pro_only_blur">
                                    <table class="lwp-profitability-table">
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e('Location', 'multi-location-product-and-inventory-management'); ?></th>
                                                <th><?php esc_html_e('Inventory Value', 'multi-location-product-and-inventory-management'); ?></th>
                                                <th><?php esc_html_e('Margin Value', 'multi-location-product-and-inventory-management'); ?></th>
                                                <th><?php esc_html_e('Margin %', 'multi-location-product-and-inventory-management'); ?></th>
                                                <th><?php esc_html_e('Dead Stock Value', 'multi-location-product-and-inventory-management'); ?></th>
                                                <th><?php esc_html_e('Dead Stock Units', 'multi-location-product-and-inventory-management'); ?></th>
                                                <th><?php esc_html_e('Avg. Age (days)', 'multi-location-product-and-inventory-management'); ?></th>
                                                <th><?php esc_html_e('Shrinkage %', 'multi-location-product-and-inventory-management'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($profitability_by_location as $summary) : ?>
                                                <tr>
                                                    <td><?php echo esc_html($summary['location_name']); ?></td>
                                                    <td><?php echo wp_kses_post(wc_price($summary['inventory_value'])); ?></td>
                                                    <td><?php echo wp_kses_post(wc_price($summary['margin_value'])); ?></td>
                                                    <td><?php echo esc_html(number_format((float) $summary['margin_rate'], 1)); ?>%</td>
                                                    <td><?php echo wp_kses_post(wc_price($summary['dead_stock_value'])); ?></td>
                                                    <td><?php echo esc_html(number_format($summary['dead_stock_units'], 0)); ?></td>
                                                    <td><?php echo esc_html(number_format((float) $summary['average_age_days'], 1)); ?></td>
                                                    <td><?php echo esc_html(number_format((float) $summary['shrinkage_rate'], 1)); ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else : ?>
                                <p><?php esc_html_e('No profitability data available yet.', 'multi-location-product-and-inventory-management'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
    }

    /**
     * Get product counts and stock totals for all locations in one query.
     */
    private function get_location_product_and_stock_totals($locations)
    {
        $product_counts = [];
        $stock_levels = [];
        $location_ids = [];
        $location_names = [];

        if (empty($locations) || is_wp_error($locations)) {
            return [
                'product_counts' => $product_counts,
                'stock_levels' => $stock_levels
            ];
        }

        foreach ($locations as $location) {
            $location_id = (int) $location->term_id;
            if ($location_id <= 0) {
                continue;
            }
            $location_ids[] = $location_id;
            $location_names[$location_id] = $location->name;
            $product_counts[$location->name] = 0;
            $stock_levels[$location->name] = 0;
        }

        if (empty($location_ids)) {
            return [
                'product_counts' => $product_counts,
                'stock_levels' => $stock_levels
            ];
        }

        global $wpdb;

        $location_placeholders = implode(',', array_fill(0, count($location_ids), '%d'));
        $meta_keys = array_map(function ($id) {
            return '_location_stock_' . $id;
        }, $location_ids);
        $meta_placeholders = implode(',', array_fill(0, count($meta_keys), '%s'));

        $query = $wpdb->prepare(
            "SELECT 'count' AS metric, tt.term_id AS location_id, NULL AS meta_key,
                    COUNT(DISTINCT p.ID) AS value
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
             INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
             WHERE p.post_type = 'product'
               AND p.post_status = 'publish'
               AND tt.taxonomy = 'mulopimfwc_store_location'
               AND tt.term_id IN ({$location_placeholders})
             GROUP BY tt.term_id
             UNION ALL
             SELECT 'stock' AS metric, NULL AS location_id, pm.meta_key AS meta_key,
                    COALESCE(SUM(CAST(pm.meta_value AS SIGNED)), 0) AS value
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE p.post_type = 'product'
               AND p.post_status = 'publish'
               AND pm.meta_key IN ({$meta_placeholders})
               AND pm.meta_value != ''
               AND pm.meta_value IS NOT NULL
             GROUP BY pm.meta_key",
            array_merge($location_ids, $meta_keys)
        );

        $results = $wpdb->get_results($query);
        if (!empty($results)) {
            foreach ($results as $row) {
                if ($row->metric === 'count') {
                    $location_id = (int) $row->location_id;
                    if (!isset($location_names[$location_id])) {
                        continue;
                    }
                    $location_name = $location_names[$location_id];
                    $product_counts[$location_name] = (int) $row->value;
                    continue;
                }

                if ($row->metric === 'stock' && !empty($row->meta_key)) {
                    $location_id = (int) str_replace('_location_stock_', '', $row->meta_key);
                    if (!isset($location_names[$location_id])) {
                        continue;
                    }
                    $location_name = $location_names[$location_id];
                    $stock_levels[$location_name] = (int) $row->value;
                }
            }
        }

        return [
            'product_counts' => $product_counts,
            'stock_levels' => $stock_levels
        ];
    }

    /**
     * Get orders data (randomized)
     */
    private function get_orders_data_efficiently()
    {
        global $mulopimfwc_locations;

        $orders_by_location = [];
        $location_revenue = [];

        if (empty($mulopimfwc_locations) || is_wp_error($mulopimfwc_locations)) {
            return [
                'orders' => $orders_by_location,
                'revenue' => $location_revenue
            ];
        }

        foreach ($mulopimfwc_locations as $location) {
            $orders_by_location[$location->name] = wp_rand(0, 200);
            $location_revenue[$location->name] = wp_rand(0, 100000);
        }

        return [
            'orders' => $orders_by_location,
            'revenue' => $location_revenue
        ];
    }

    /**
     * Get low stock products with random values
     */
    private function get_low_stock_products_efficiently()
    {
        global $mulopimfwc_locations;

        if (empty($mulopimfwc_locations) || is_wp_error($mulopimfwc_locations)) {
            return [];
        }

        $product_names = [
            'Sample Widget',
            'Demo Gadget',
            'Test Bundle',
            'Preview Kit',
            'Mock Device',
            'Sample Pack',
            'Trial Unit',
            'Placeholder Item',
            'Example Product',
            'Prototype Gear'
        ];

        $items = [];
        $location_count = count($mulopimfwc_locations);
        $max_items = min(10, $location_count * 2);

        for ($i = 0; $i < $max_items; $i++) {
            $location = $mulopimfwc_locations[$i % $location_count];
            $items[] = [
                'product_id' => $i + 1,
                'product_title' => $product_names[$i % count($product_names)],
                'location_name' => $location->name,
                'stock' => wp_rand(0, 3)
            ];
        }

        return $items;
    }

    /**
     * Get recent products data (randomized)
     */
    private function get_recent_products_data()
    {
        $days = 30;
        $labels = [];
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[] = gmdate('M d', strtotime("-$i days"));
            $counts[] = wp_rand(0, 100);
        }

        return [
            'labels' => $labels,
            'counts' => $counts
        ];
    }

    /**
     * Get total products count efficiently
     */
    private function get_total_products_count()
    {
        global $wpdb;

        // Check if current user is a location manager
        $is_location_manager = false;
        $assigned_location_slugs = [];

        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            if (in_array('mulopimfwc_location_manager', $user->roles)) {
                $is_location_manager = true;
                $assigned_location_slugs = get_user_meta($user->ID, 'mulopimfwc_assigned_locations', true);

                if (!is_array($assigned_location_slugs)) {
                    $assigned_location_slugs = [];
                }
            }
        }

        if (
            $is_location_manager &&
            class_exists('MULOPIMFWC_Location_Managers') &&
            MULOPIMFWC_Location_Managers::user_has_capability('all_products')
        ) {
            $is_location_manager = false;
            $assigned_location_slugs = [];
        }

        // For location managers with assigned locations
        if ($is_location_manager && !empty($assigned_location_slugs)) {
            // Get term IDs from slugs
            $term_ids = [];
            foreach ($assigned_location_slugs as $slug) {
                $term = get_term_by('slug', $slug, 'mulopimfwc_store_location');
                if ($term && !is_wp_error($term)) {
                    $term_ids[] = $term->term_id;
                }
            }

            if (empty($term_ids)) {
                return 0;
            }

            // Build query to count products in assigned locations
            $term_ids_placeholder = implode(',', array_fill(0, count($term_ids), '%d'));

            $query = $wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID) 
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE p.post_type = 'product' 
            AND p.post_status = 'publish'
            AND tt.taxonomy = 'mulopimfwc_store_location'
            AND tt.term_id IN ({$term_ids_placeholder})",
                ...$term_ids
            );

            return (int) $wpdb->get_var($query);
        }

        // For admins and other users, return all products
        $query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'";
        return (int) $wpdb->get_var($query);
    }

    /**
     * Calculate total investment efficiently
     */
    private function calculate_total_investment_efficiently()
    {
        // Use caching to prevent recalculation
        $cache_key = 'mulopimfwc_total_investment';
        $cached_value = get_transient($cache_key);

        if ($cached_value !== false) {
            return $cached_value;
        }

        global $wpdb;

        // FIXED: Use prepared statement for security
        // Calculate investment based on _purchase_price and _purchase_quantity
        $total_investment = $wpdb->get_var($wpdb->prepare("
        SELECT COALESCE(SUM(
            CAST(pm1.meta_value AS DECIMAL(10,2)) * 
            COALESCE(CAST(pm2.meta_value AS SIGNED), 0)
        ), 0) as total
        FROM {$wpdb->postmeta} pm1
        INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
        INNER JOIN {$wpdb->posts} p ON pm1.post_id = p.ID
        WHERE pm1.meta_key = %s
        AND pm2.meta_key = %s
        AND p.post_type = %s
        AND p.post_status = %s
        AND pm1.meta_value != ''
        AND pm1.meta_value > 0
        AND pm2.meta_value != ''
        AND pm2.meta_value > 0
        AND pm1.post_id IN (
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_type = %s 
            AND post_status = %s
        )
        ", '_purchase_price', '_purchase_quantity', 'product', 'publish', 'product', 'publish'));

        $total_investment = floatval($total_investment);

        // Cache for 1 hour
        set_transient($cache_key, $total_investment, HOUR_IN_SECONDS);

        return $total_investment;
    }

    /**
     * Get monthly investment data (randomized)
     */
    private function get_monthly_investment_data_cached()
    {
        $months = 12;
        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $labels[] = gmdate('M Y', strtotime("-$i months"));
            $data[] = wp_rand(1000, 10000);
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}
