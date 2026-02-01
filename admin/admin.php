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

        <div class="form-field mulopimfwc_pro_only">
            <label for="low_stock_threshold"><?php _e('Low Stock Threshold', 'multi-location-product-and-inventory-management'); ?></label>
            <input disabled type="number" name="low_stock_threshold" id="low_stock_threshold" value="" min="0" step="1" />
            <p class="description"><?php _e('Alert threshold for low stock at this location (overrides global default).', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <div class="form-field mulopimfwc_pro_only">
            <label for="out_of_stock_threshold"><?php _e('Out of Stock Threshold', 'multi-location-product-and-inventory-management'); ?></label>
            <input disabled type="number" name="out_of_stock_threshold" id="out_of_stock_threshold" value="" min="0" step="1" />
            <p class="description"><?php _e('Alert threshold for out-of-stock at this location (overrides global default).', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <!-- Latitude / Longitude -->
        <div class="form-field mulopimfwc_pro_only">
            <label for="latitude"><?php _e('Latitude', 'multi-location-product-and-inventory-management'); ?></label>
            <input disabled type="text" name="latitude" id="latitude" value="" />
            <p class="description"><?php _e('Decimal latitude (e.g. 23.7808)', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <div class="form-field mulopimfwc_pro_only">
            <label for="longitude"><?php _e('Longitude', 'multi-location-product-and-inventory-management'); ?></label>
            <input disabled type="text" name="longitude" id="longitude" value="" />
            <p class="description"><?php _e('Decimal longitude (e.g. 90.2792)', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <!-- Logo -->
        <div class="form-field mulopimfwc-media-wrap">
            <label><?php _e('Logo', 'multi-location-product-and-inventory-management'); ?></label>
            <input disabled type="hidden" name="logo_id" class="mulopimfwc-logo-id" value="">
            <div class="mulopimfwc-logo-preview" style="margin:6px 0;"></div>
            <p class="mulopimfwc_pro_only">
                <span class="button mulopimfwc-upload-logo disabled"><?php _e('Upload/Choose Logo', 'multi-location-product-and-inventory-management'); ?></span>
                <span class="button button-link-delete mulopimfwc-remove-logo disabled"><?php _e('Remove', 'multi-location-product-and-inventory-management'); ?></span>
            </p>
        </div>

        <!-- Gallery -->
        <div class="form-field mulopimfwc-media-wrap">
            <label><?php _e('Gallery', 'multi-location-product-and-inventory-management'); ?></label>
            <input disabled type="hidden" name="gallery_ids" class="mulopimfwc-gallery-ids" value="">
            <div class="mulopimfwc-gallery-preview" style="margin:6px 0;display:flex;flex-wrap:wrap;gap:4px;"></div>
            <p class="mulopimfwc_pro_only">
                <span class="button mulopimfwc-upload-gallery disabled"><?php _e('Add Images', 'multi-location-product-and-inventory-management'); ?></span>
                <span class="button button-link-delete mulopimfwc-clear-gallery disabled"><?php _e('Clear', 'multi-location-product-and-inventory-management'); ?></span>
            </p>
        </div>

        <!-- Business Hours -->
        <?php
        $def = [
            'timezone' => get_option('timezone_string') ?: 'UTC',
            'days' => [
                'mon' => ['open' => '09:00', 'close' => '17:00'],
                'tue' => ['open' => '09:00', 'close' => '17:00'],
                'wed' => ['open' => '09:00', 'close' => '17:00'],
                'thu' => ['open' => '09:00', 'close' => '17:00'],
                'fri' => ['open' => '09:00', 'close' => '17:00'],
                'sat' => ['open' => '10:00', 'close' => '14:00'],
                'sun' => ['open' => '', 'close' => ''],
            ],
        ];
        $tzs = timezone_identifiers_list(); // basic list
        $days_labels = [
            'mon' => __('Monday', 'multi-location-product-and-inventory-management'),
            'tue' => __('Tuesday', 'multi-location-product-and-inventory-management'),
            'wed' => __('Wednesday', 'multi-location-product-and-inventory-management'),
            'thu' => __('Thursday', 'multi-location-product-and-inventory-management'),
            'fri' => __('Friday', 'multi-location-product-and-inventory-management'),
            'sat' => __('Saturday', 'multi-location-product-and-inventory-management'),
            'sun' => __('Sunday', 'multi-location-product-and-inventory-management'),
        ];
        ?>
        <div class="form-field">
            <label><?php _e('Business Hours', 'multi-location-product-and-inventory-management'); ?></label>
            <div class="mulopimfwc_pro_only" style="border:1px solid #ddd;border-radius:6px;padding:10px;max-width:660px;">
                <p class="description" style="margin-top:0;"><?php _e('Set opening hours for each day. Use “Closed” for off days or “24 hours” for round-the-clock.', 'multi-location-product-and-inventory-management'); ?></p>

                <p>
                    <strong><?php _e('Timezone', 'multi-location-product-and-inventory-management'); ?>:</strong>
                    <select disabled name="bh[timezone]" style="min-width:280px;">
                        <?php foreach ($tzs as $tz): ?>
                            <option value="<?php echo esc_attr($tz); ?>" <?php selected($tz, $def['timezone']); ?>>
                                <?php echo esc_html($tz); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <table class="form-table" style="width:auto;border-collapse:collapse;">
                    <tbody>
                        <?php foreach ($def['days'] as $key => $vals): ?>
                            <tr>
                                <th style="text-align:left;padding:6px 8px;width:140px;"><?php echo esc_html($days_labels[$key]); ?></th>
                                <td style="padding:6px 8px;">
                                    <label style="margin-right:10px;">
                                        <input disabled type="checkbox" name="bh[days][<?php echo esc_attr($key); ?>][closed]" value="1">
                                        <?php _e('Closed', 'multi-location-product-and-inventory-management'); ?>
                                    </label>
                                    <label style="margin-right:10px;">
                                        <input disabled type="checkbox" name="bh[days][<?php echo esc_attr($key); ?>][all_day]" value="1">
                                        <?php _e('24 hours', 'multi-location-product-and-inventory-management'); ?>
                                    </label>
                                    <span style="margin-left:10px;">
                                        <input disabled type="time" name="bh[days][<?php echo esc_attr($key); ?>][open]" value="<?php echo esc_attr($vals['open']); ?>">
                                        &nbsp;–&nbsp;
                                        <input disabled type="time" name="bh[days][<?php echo esc_attr($key); ?>][close]" value="<?php echo esc_attr($vals['close']); ?>">
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Shipping Zones -->
        <?php $zones = [
            'zone_1' => 'Zone 1',
            'zone_2' => 'Zone 2',
            'zone_3' => 'Zone 3',
        ]; ?>
        <div class="form-field mulopimfwc_pro_only">
            <label for="shipping_zones"><?php _e('Shipping Zones', 'multi-location-product-and-inventory-management'); ?></label>
            <select disabled name="shipping_zones[]" id="shipping_zones" multiple style="min-width: 320px;">
                <?php foreach ($zones as $zid => $zname): ?>
                    <option value="<?php echo esc_attr($zid); ?>"><?php echo esc_html($zname); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e('Choose the shipping zones served by this location.', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <!-- Shipping Methods (instances) -->
        <?php $zone_methods = [
            'zone_1' => [
                'flat_rate:1' => 'Flat Rate (Instance 1)',
                'free_shipping:2' => 'Free Shipping (Instance 2)',
            ],
            'zone_2' => [
                'local_pickup:3' => 'Local Pickup (Instance 3)',
            ],
            'zone_3' => [
                'flat_rate:4' => 'Flat Rate (Instance 4)',
                'free_shipping:5' => 'Free Shipping (Instance 5)',
                'local_pickup:6' => 'Local Pickup (Instance 6)',
            ],
        ]; ?>
        <div class="form-field mulopimfwc_pro_only">
            <label for="shipping_methods"><?php _e('Shipping Methods', 'multi-location-product-and-inventory-management'); ?></label>
            <select disabled name="shipping_methods[]" id="shipping_methods" multiple style="min-width: 420px;">
                <?php foreach ($zone_methods as $zid => $methods): ?>
                    <?php if (!empty($methods)): ?>
                        <optgroup label="<?php echo esc_attr(sprintf(__('Zone: %s', 'multi-location-product-and-inventory-management'), $zones[$zid] ?? $zid)); ?>">
                            <?php foreach ($methods as $instance_id => $label): ?>
                                <option value="<?php echo esc_attr($zid . ':' . $instance_id); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e('Select enabled shipping method instances (grouped by zone).', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <!-- Payment Methods -->
        <?php $payments = [
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'cod' => 'Cash on Delivery',
        ]; ?>
        <div class="form-field mulopimfwc_pro_only">
            <label for="payment_methods"><?php _e('Payment Methods', 'multi-location-product-and-inventory-management'); ?></label>
            <select disabled name="payment_methods[]" id="payment_methods" multiple style="min-width: 320px;">
                <?php foreach ($payments as $pid => $ptitle): ?>
                    <option value="<?php echo esc_attr($pid); ?>"><?php echo esc_html($ptitle); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e('Choose allowed payment gateways for this location.', 'multi-location-product-and-inventory-management'); ?></p>
        </div>

        <!-- Pickup Locations -->
        <?php $pickup_locations = [
            'location_1' => 'Location 1',
            'location_2' => 'Location 2',
            'location_3' => 'Location 3',
        ]; ?>
        <?php if (!empty($pickup_locations)): ?>
            <div class="form-field mulopimfwc_pro_only">
                <label for="pickup_locations"><?php _e('Pickup Locations', 'multi-location-product-and-inventory-management'); ?></label>
                <select disabled name="pickup_locations[]" id="pickup_locations" multiple style="min-width: 320px;">
                    <?php foreach ($pickup_locations as $pid => $ptitle): ?>
                        <option value="<?php echo esc_attr($pid); ?>"><?php echo esc_html($ptitle); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e('Choose allowed pickup locations for this store location.', 'multi-location-product-and-inventory-management'); ?></p>
            </div>
        <?php endif; ?>

        <!-- Tax Class -->
        <?php $tax_classes = [
            '' => __('Standard', 'multi-location-product-and-inventory-management'),
            'reduced-rate' => __('Reduced Rate', 'multi-location-product-and-inventory-management'),
            'zero-rate' => __('Zero Rate', 'multi-location-product-and-inventory-management'),
        ]; ?>
        <div class="form-field mulopimfwc_pro_only">
            <label for="tax_class"><?php _e('Tax Class', 'multi-location-product-and-inventory-management'); ?></label>
            <select disabled name="tax_class" id="tax_class" style="min-width: 220px;">
                <?php foreach ($tax_classes as $key => $label): ?>
                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e('Select default tax class for this location.', 'multi-location-product-and-inventory-management'); ?></p>
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
            <th scope="row"><label for="low_stock_threshold"><?php _e('Low Stock Threshold', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td class="mulopimfwc_pro_only">
                <input disabled type="number" name="low_stock_threshold" id="low_stock_threshold" value="5" min="0" step="1" />
                <p class="description"><?php _e('Alert threshold for low stock at this location (overrides global default).', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="out_of_stock_threshold"><?php _e('Out of Stock Threshold', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td class="mulopimfwc_pro_only">
                <input disabled type="number" name="out_of_stock_threshold" id="out_of_stock_threshold" value="0" min="0" step="1" />
                <p class="description"><?php _e('Alert threshold for out-of-stock at this location (overrides global default).', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="latitude"><?php _e('Latitude', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td class="mulopimfwc_pro_only">
                <input disabled type="text" name="latitude" id="latitude" value="" />
                <p class="description"><?php _e('Decimal latitude (e.g. 23.7808)', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="longitude"><?php _e('Longitude', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td class="mulopimfwc_pro_only">
                <input disabled type="text" name="longitude" id="longitude" value="" />
                <p class="description"><?php _e('Decimal longitude (e.g. 90.2792)', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label><?php _e('Logo', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td class="mulopimfwc-media-wrap">
                <input disabled type="hidden" name="logo_id" class="mulopimfwc-logo-id" value="">
                <p class="mulopimfwc_pro_only">
                    <span class="button mulopimfwc-upload-logo disabled"><?php _e('Upload/Choose Logo', 'multi-location-product-and-inventory-management'); ?></span>
                    <span class="button button-link-delete mulopimfwc-remove-logo disabled"><?php _e('Remove', 'multi-location-product-and-inventory-management'); ?></span>
                </p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label><?php _e('Gallery', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td class="mulopimfwc-media-wrap">
                <input disabled type="hidden" name="gallery_ids" class="mulopimfwc-gallery-ids" value="">
                <p class="mulopimfwc_pro_only">
                    <span class="button mulopimfwc-upload-gallery disabled"><?php _e('Add Images', 'multi-location-product-and-inventory-management'); ?></span>
                    <span class="button button-link-delete mulopimfwc-clear-gallery disabled"><?php _e('Clear', 'multi-location-product-and-inventory-management'); ?></span>
                </p>
            </td>
        </tr>

        <?php
        $bh = [
            'timezone' => get_option('timezone_string') ?: 'UTC',
            'days' => [
                'mon' => ['open' => '09:00', 'close' => '17:00'],
                'tue' => ['open' => '09:00', 'close' => '17:00'],
                'wed' => ['open' => '09:00', 'close' => '17:00'],
                'thu' => ['open' => '09:00', 'close' => '17:00'],
                'fri' => ['open' => '09:00', 'close' => '17:00'],
                'sat' => ['open' => '10:00', 'close' => '14:00'],
                'sun' => ['open' => '', 'close' => ''],
            ],
        ];
        $tzs = timezone_identifiers_list();
        $days_labels = [
            'mon' => __('Monday', 'multi-location-product-and-inventory-management'),
            'tue' => __('Tuesday', 'multi-location-product-and-inventory-management'),
            'wed' => __('Wednesday', 'multi-location-product-and-inventory-management'),
            'thu' => __('Thursday', 'multi-location-product-and-inventory-management'),
            'fri' => __('Friday', 'multi-location-product-and-inventory-management'),
            'sat' => __('Saturday', 'multi-location-product-and-inventory-management'),
            'sun' => __('Sunday', 'multi-location-product-and-inventory-management'),
        ];
        ?>
        <tr class="form-field">
            <th scope="row"><label><?php _e('Business Hours', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td>
                <div  style="border:1px solid #ddd;border-radius:6px;padding:10px;max-width:660px;">
                    <p class="description" style="margin-top:0;"><?php _e('Set opening hours for each day. Use “Closed” for off days or “24 hours” for round-the-clock.', 'multi-location-product-and-inventory-management'); ?></p>

                    <p>
                        <strong><?php _e('Timezone', 'multi-location-product-and-inventory-management'); ?>:</strong>
                        <select disabled name="bh[timezone]" style="min-width:280px;">
                            <?php foreach ($tzs as $tz): ?>
                                <option value="<?php echo esc_attr($tz); ?>" <?php selected($tz, $bh['timezone']); ?>>
                                    <?php echo esc_html($tz); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <table class="form-table mulopimfwc_pro_only" style="width:auto;border-collapse:collapse;">
                        <tbody>
                            <?php foreach ($bh['days'] as $key => $vals): ?>
                                <tr>
                                    <th style="text-align:left;padding:6px 8px;width:140px;"><?php echo esc_html($days_labels[$key]); ?></th>
                                    <td style="padding:6px 8px;">
                                        <label style="margin-right:10px;">
                                            <input disabled type="checkbox" name="bh[days][<?php echo esc_attr($key); ?>][closed]" value="1" <?php checked(!empty($vals['closed'])); ?>>
                                            <?php _e('Closed', 'multi-location-product-and-inventory-management'); ?>
                                        </label>
                                        <label style="margin-right:10px;">
                                            <input disabled type="checkbox" name="bh[days][<?php echo esc_attr($key); ?>][all_day]" value="1" <?php checked(!empty($vals['all_day'])); ?>>
                                            <?php _e('24 hours', 'multi-location-product-and-inventory-management'); ?>
                                        </label>
                                        <span style="margin-left:10px;">
                                            <input disabled type="time" name="bh[days][<?php echo esc_attr($key); ?>][open]" value="<?php echo esc_attr($vals['open']); ?>">
                                            &nbsp;–&nbsp;
                                            <input disabled type="time" name="bh[days][<?php echo esc_attr($key); ?>][close]" value="<?php echo esc_attr($vals['close']); ?>">
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="shipping_zones"><?php _e('Shipping Zones', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td class="mulopimfwc_pro_only">
                <select disabled name="shipping_zones[]" id="shipping_zones" multiple style="min-width: 320px;">
                    <?php
                    $zones = [
                        'zone_1' => 'Zone 1',
                        'zone_2' => 'Zone 2',
                        'zone_3' => 'Zone 3',
                    ];
                    foreach ($zones as $zid => $zname): ?>
                        <option value="<?php echo esc_attr($zid); ?>">
                            <?php echo esc_html($zname); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e('Choose the shipping zones served by this location.', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="shipping_methods"><?php _e('Shipping Methods', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td class="mulopimfwc_pro_only">
                <select disabled name="shipping_methods[]" id="shipping_methods" multiple style="min-width: 420px;">
                    <?php
                    $zone_methods = [
                        'zone_1' => [
                            'flat_rate:1' => 'Flat Rate (Instance 1)',
                            'free_shipping:2' => 'Free Shipping (Instance 2)',
                        ],
                        'zone_2' => [
                            'local_pickup:3' => 'Local Pickup (Instance 3)',
                        ],
                        'zone_3' => [
                            'flat_rate:4' => 'Flat Rate (Instance 4)',
                            'free_shipping:5' => 'Free Shipping (Instance 5)',
                            'local_pickup:6' => 'Local Pickup (Instance 6)',
                        ],
                    ];
                    
                    foreach ($zone_methods as $zid => $methods): if (empty($methods)) continue; ?>
                        <optgroup label="<?php echo esc_attr(sprintf(__('Zone: %s', 'multi-location-product-and-inventory-management'), $zones[$zid] ?? $zid)); ?>">
                            <?php foreach ($methods as $instance_id => $label):
                                $val = $zid . ':' . $instance_id;
                            ?>
                                <option value="<?php echo esc_attr($val); ?>">
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e('Select enabled shipping method instances (grouped by zone).', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="payment_methods"><?php _e('Payment Methods', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td class="mulopimfwc_pro_only">
                <select disabled name="payment_methods[]" id="payment_methods" multiple style="min-width: 320px;">
                    <?php
                    $payments = [
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'cod' => 'Cash on Delivery',
                    ];
                    foreach ($payments as $pid => $ptitle): ?>
                        <option value="<?php echo esc_attr($pid); ?>">
                            <?php echo esc_html($ptitle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e('Choose allowed payment gateways for this location.', 'multi-location-product-and-inventory-management'); ?></p>
            </td>
        </tr>

        <!-- Pickup Locations -->
        <?php
        $pickup_locations = [
            'location_1' => 'Location 1',
            'location_2' => 'Location 2',
            'location_3' => 'Location 3',
        ];
        $sel_pickup = (array) get_term_meta($term->term_id, 'pickup_locations', true);
        ?>
        <?php if (!empty($pickup_locations)): ?>
            <tr class="form-field">
                <th scope="row"><label for="pickup_locations"><?php _e('Pickup Locations', 'multi-location-product-and-inventory-management'); ?></label></th>
                <td class="mulopimfwc_pro_only">
                    <select disabled name="pickup_locations[]" id="pickup_locations" multiple style="min-width: 320px;">
                        <?php foreach ($pickup_locations as $pid => $ptitle): ?>
                            <option value="<?php echo esc_attr($pid); ?>" <?php selected(in_array($pid, (array)$sel_pickup, true)); ?>>
                                <?php echo esc_html($ptitle); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('Choose allowed pickup locations for this store location.', 'multi-location-product-and-inventory-management'); ?></p>
                </td>
            </tr>
        <?php endif; ?>

        <tr class="form-field">
            <th scope="row"><label for="tax_class"><?php _e('Tax Class', 'multi-location-product-and-inventory-management'); ?></label></th>
            <td class="mulopimfwc_pro_only">
                <select disabled name="tax_class" id="tax_class" style="min-width: 220px;">
                    <?php
                    $tax_classes = [
                        '' => __('Standard', 'multi-location-product-and-inventory-management'),
                        'reduced-rate' => __('Reduced Rate', 'multi-location-product-and-inventory-management'),
                        'zero-rate' => __('Zero Rate', 'multi-location-product-and-inventory-management'),
                    ];
                    foreach ($tax_classes as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>">
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e('Select default tax class for this location.', 'multi-location-product-and-inventory-management'); ?></p>
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

        add_submenu_page(
            'multi-location-product-and-inventory-management',
            __('Our Plugins', 'multi-location-product-and-inventory-management'),
            __('Our Plugins', 'multi-location-product-and-inventory-management'),
            'install_plugins',
            'plugincy-plugins',
            array($this, 'render_plugincy_plugins_page')
        );


        // Add "Get Pro" submenu (external link)
        add_submenu_page(
            'multi-location-product-and-inventory-management',
            __('Get Pro', 'multi-location-product-and-inventory-management'),
            __('⭐ Get Pro', 'multi-location-product-and-inventory-management'),
            'manage_options',
            'https://plugincy.com/multi-location-product-and-inventory-management/'
        );
    }


    public function render_plugincy_plugins_page()
    {
        if (!current_user_can('install_plugins')) {
            wp_die(esc_html__('You do not have sufficient permissions to install plugins on this site.', 'multi-location-product-and-inventory-management'));
        }

        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        wp_enqueue_style('plugin-install');
        wp_enqueue_script('plugin-install');
        wp_enqueue_script('updates');
        add_thickbox();

        $api = plugins_api('query_plugins', array(
            'author' => 'plugincy',
            'page' => 1,
            'per_page' => 30,
            'fields' => array(
                'short_description' => true,
                'icons' => true,
                'active_installs' => true,
                'sections' => false,
            ),
        ));

        echo '<div class="wrap plugin-install-php">';
        echo '<h1>' . esc_html__('Plugincy Plugins', 'multi-location-product-and-inventory-management') . '</h1>';

        if (is_wp_error($api)) {
            echo '<div class="notice notice-error"><p>' . esc_html($api->get_error_message()) . '</p></div></div>';
            return;
        }

        $plugins = !empty($api->plugins) ? $api->plugins : array();

        if (empty($plugins)) {
            echo '<p>' . esc_html__('No plugins found for this author.', 'multi-location-product-and-inventory-management') . '</p></div>';
            return;
        }

        echo '<div id="the-list" class="wp-list-table widefat plugin-install-grid">';

        foreach ($plugins as $plugin) {
            $plugin_obj = is_array($plugin) ? (object) $plugin : $plugin;

            $status = install_plugin_install_status($plugin_obj);
            $action_class = 'button';
            $action_url = '';
            $action_text = '';
            $action_disabled = false;

            switch ($status['status']) {
                case 'install':
                    $action_class = 'install-now button button-primary';
                    $action_text = esc_html__('Install Now', 'multi-location-product-and-inventory-management');
                    $action_url = $status['url'];
                    break;
                case 'update_available':
                    $action_class = 'update-now button';
                    $action_text = esc_html__('Update Now', 'multi-location-product-and-inventory-management');
                    $action_url = $status['url'];
                    break;
                default:
                    if (!empty($status['file']) && is_plugin_active($status['file'])) {
                        $action_class = 'button disabled';
                        $action_text = esc_html__('Active', 'multi-location-product-and-inventory-management');
                        $action_disabled = true;
                    } elseif (!empty($status['file']) && current_user_can('activate_plugin', $status['file'])) {
                        $action_class = 'activate-now button button-primary';
                        $action_text = esc_html__('Activate', 'multi-location-product-and-inventory-management');
                        $action_url = wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin=' . $status['file']), 'activate-plugin_' . $status['file']);
                    } else {
                        $action_class = 'button disabled';
                        $action_text = esc_html__('Installed', 'multi-location-product-and-inventory-management');
                        $action_disabled = true;
                    }
            }

            $icon = '';
            $icons = (!empty($plugin_obj->icons) && is_array($plugin_obj->icons)) ? $plugin_obj->icons : array();
            if (!empty($icons)) {
                $preferred = array('svg', '2x', '1x', 'default');
                foreach ($preferred as $size) {
                    if (!empty($icons[$size])) {
                        $icon = esc_url($icons[$size]);
                        break;
                    }
                }
            }

            $name = isset($plugin_obj->name) ? $plugin_obj->name : '';
            $short_description = isset($plugin_obj->short_description) ? $plugin_obj->short_description : '';
            $version = isset($plugin_obj->version) ? $plugin_obj->version : '';
            $active_installs = isset($plugin_obj->active_installs) ? $plugin_obj->active_installs : null;
            $author = isset($plugin_obj->author) ? $plugin_obj->author : '';
            $slug = !empty($plugin_obj->slug) ? sanitize_title($plugin_obj->slug) : sanitize_title($name);
            $details_url = $slug ? self_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $slug . '&TB_iframe=true&width=600&height=550') : '';

            if ($action_url && !$action_disabled) {
                $action_html = '<a class="' . esc_attr($action_class) . '" href="' . esc_url($action_url) . '" data-slug="' . esc_attr($slug) . '" data-name="' . esc_attr($name) . '">' . esc_html($action_text) . '</a>';
            } else {
                $action_html = '<span class="' . esc_attr($action_class) . '" aria-disabled="true">' . esc_html($action_text) . '</span>';
            }

            echo '<div class="plugin-card plugin-card-' . esc_attr($slug) . '">';
            echo '<div class="plugin-card-top">';
            echo '<div class="name column-name">';
            if ($icon) {
                echo '<img class="plugin-icon" src="' . $icon . '" alt="" />';
            }
            if ($details_url) {
                echo '<h3><a class="thickbox open-plugin-details-modal" href="' . esc_url($details_url) . '" aria-label="' . esc_attr(sprintf(__('More details about %s', 'multi-location-product-and-inventory-management'), $name)) . '">' . esc_html($name) . '</a></h3>';
            } else {
                echo '<h3>' . esc_html($name) . '</h3>';
            }
            if (!empty($author)) {
                echo '<p class="author">' . sprintf(esc_html__('By %s', 'multi-location-product-and-inventory-management'), wp_kses_post($author)) . '</p>';
            }
            echo '</div>';
            echo '<div class="action-links"><ul class="plugin-action-buttons"><li>' . $action_html . '</li></ul></div>';
            echo '<div class="desc column-description"><p>' . wp_kses_post($short_description) . '</p></div>';
            echo '</div>';

            echo '<div class="plugin-card-bottom">';
            echo '<div class="vers column-rating">';
            echo '<span>' . sprintf(esc_html__('Version %s', 'multi-location-product-and-inventory-management'), esc_html($version)) . '</span>';
            if ($active_installs !== null) {
                $installs = number_format_i18n((int) $active_installs);
                echo '<span style="margin-left:10px;">' . sprintf(esc_html__('%s+ active installs', 'multi-location-product-and-inventory-management'), esc_html($installs)) . '</span>';
            }
            echo '</div>';
            echo '<div class="column-compatibility"><span class="compatibility-compatible">' . esc_html__('Compatible with your version of WordPress', 'multi-location-product-and-inventory-management') . '</span></div>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
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


if (!function_exists('mlpimforwc_is_new_year_deal_active')) {
    /**
     * Check if New Year deal is active.
     */
    function mlpimforwc_is_new_year_deal_active()
    {
        $start_timestamp  = strtotime('2025-12-05 00:00:00'); //2025-12-08 00:00:00
        $expiry_timestamp = strtotime('2026-01-09 23:59:00');

        $now = current_time('timestamp');

        return $now >= $start_timestamp && $now <= $expiry_timestamp;
    }
}

// AJAX handler for dismissing notice
add_action('wp_ajax_mlpimforwc_dismiss_ny_notice', 'mlpimforwc_dismiss_ny_notice_handler');

function mlpimforwc_dismiss_ny_notice_handler()
{
    check_ajax_referer('mlpimforwc_dismiss_ny_notice', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $hours = isset($_POST['hours']) ? intval($_POST['hours']) : 3;
    $dismiss_until = time() + ($hours * 3600);

    update_user_meta(get_current_user_id(), 'mlpimforwc_ny_notice_dismissed_until', $dismiss_until);

    wp_send_json_success('Notice dismissed for ' . $hours . ' hours');
}

add_action('admin_notices', 'mlpimforwc_show_new_year_notice');

function mlpimforwc_show_new_year_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!function_exists('mlpimforwc_is_new_year_deal_active') || !mlpimforwc_is_new_year_deal_active()) {
        return;
    }

    // Check if notice is dismissed and if dismissal period has expired
    $dismissed_until = get_user_meta(get_current_user_id(), 'mlpimforwc_ny_notice_dismissed_until', true);
    if ($dismissed_until && time() < $dismissed_until) {
        return;
    }

    echo '<style>
.mlpimforwc-ny-wrap {
    padding:24px;
    border-radius:16px;
    background:linear-gradient(135deg,#0f172a 0%,#1e293b 25%,#312e81 50%,#1e1b4b 75%,#0f172a 100%);
    background-size:300% 300%;
    color:#f8fafc;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    flex-wrap:wrap;
    box-shadow:0 20px 60px rgba(0,0,0,0.4),0 0 0 1px rgba(255,255,255,0.1) inset,0 0 80px rgba(147,51,234,0.15);
    border:1px solid rgba(168,85,247,0.2);
    position:relative;
    overflow:hidden;
    animation:mlpimforwc_ny_gradient 8s ease infinite;
}

.mlpimforwc-ny-wrap::before {
    content:"";
    position:absolute;
    top:0;
    left:-100%;
    width:100%;
    height:100%;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,0.1),transparent);
    animation:mlpimforwc_ny_shine 3s ease-in-out infinite;
}

.mlpimforwc-ny-icon {
    font-size:28px;
    height:28px;
    width:28px;
    color:#1e1b4b;
    background:linear-gradient(135deg,#fbbf24 0%,#f59e0b 50%,#fbbf24 100%);
    background-size:200% 200%;
    border-radius:50%;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:14px;
    box-shadow:0 10px 30px rgba(245,158,11,0.4),0 0 30px rgba(251,191,36,0.3);
    z-index:1;
    animation:mlpimforwc_ny_icon_pulse 2s ease-in-out infinite,mlpimforwc_ny_icon_glow 3s ease infinite;
}

.mlpimforwc-ny-chip {
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-bottom:10px;
    font-weight:900;
    text-transform:uppercase;
    font-size:11px;
    letter-spacing:1.2px;
    color:#1e1b4b;
    background:linear-gradient(135deg,#fef3c7 0%,#fde68a 50%,#fef3c7 100%);
    background-size:200% 200%;
    padding:7px 16px;
    border-radius:999px;
    box-shadow:0 8px 20px rgba(245,158,11,0.3),0 0 20px rgba(251,191,36,0.2);
    animation:mlpimforwc_ny_chip_shine 4s ease infinite;
}

.mlpimforwc-ny-chip::before {
    content:"🎉";
    animation:mlpimforwc_ny_emoji_spin 3s linear infinite;
}

.mlpimforwc-ny-heading {
    font-size:18px;
    line-height:1.5;
    color:#ffffff;
    font-weight:900;
    margin:0 0 6px;
    display:flex;
    align-items:center;
    gap:10px;
    text-shadow:0 2px 10px rgba(0,0,0,0.3);
    background:linear-gradient(90deg,#fff 0%,#fbbf24 50%,#fff 100%);
    background-size:200% auto;
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
    animation:mlpimforwc_ny_text_shine 3s linear infinite;
}

.mlpimforwc-ny-sub {
    font-size:14px;
    line-height:1.6;
    color:#e2e8f0;
    font-weight:600;
    margin:0;
}

.mlpimforwc-ny-sub code {
    background:linear-gradient(135deg,#fef08a 0%,#fde047 100%);
    color:#1e1b4b;
    padding:4px 10px;
    border-radius:8px;
    font-weight:900;
    font-size:13px;
    box-shadow:0 4px 12px rgba(254,240,138,0.4);
    border:1px solid rgba(253,224,71,0.5);
    animation:mlpimforwc_ny_code_pulse 2s ease-in-out infinite;
}

.mlpimforwc-ny-cta.button.button-primary {
    background:linear-gradient(135deg,#b45309 0%,#92400e 25%,#78350f 50%,#92400e 75%,#b45309 100%) !important;
    background-size:400% 100% !important;
    border:2px solid #d97706 !important;
    color:#ffffff !important;
    box-shadow:0 0 0 3px rgba(217,119,6,0.3),0 20px 50px rgba(146,64,14,0.6),0 0 60px rgba(180,83,9,0.5),inset 0 1px 0 rgba(251,191,36,0.2) !important;
    padding:18px 40px !important;
    font-weight:900 !important;
    border-radius:16px !important;
    text-transform:uppercase !important;
    letter-spacing:1px !important;
    font-size:16px !important;
    cursor:pointer !important;
    transition:all 0.4s cubic-bezier(0.68,-0.55,0.265,1.55) !important;
    position:relative !important;
    overflow:hidden !important;
    animation:mlpimforwc_ny_button_glow 2s ease-in-out infinite,mlpimforwc_ny_button_gradient 4s linear infinite !important;
    text-decoration:none !important;
    display:inline-flex !important;
    align-items:center !important;
    gap:12px !important;
    white-space:nowrap !important;
    text-shadow:0 2px 8px rgba(0,0,0,0.4) !important;
    height:auto !important;
    line-height:1 !important;
}

.mlpimforwc-ny-cta.button.button-primary::before {
    content:"" !important;
    position:absolute !important;
    top:-50% !important;
    left:-50% !important;
    width:200% !important;
    height:200% !important;
    background:linear-gradient(45deg,transparent 30%,rgba(251,191,36,0.4) 50%,transparent 70%) !important;
    transform:rotate(45deg) !important;
    animation:mlpimforwc_ny_button_shine 3s ease-in-out infinite !important;
}

.mlpimforwc-ny-cta.button.button-primary::after {
    content:"→" !important;
    font-size:24px !important;
    font-weight:900 !important;
    transition:transform 0.4s cubic-bezier(0.68,-0.55,0.265,1.55) !important;
}

.mlpimforwc-ny-cta.button.button-primary:hover {
    transform:translateY(-5px) scale(1.1) !important;
    box-shadow:0 0 0 4px rgba(217,119,6,0.5),0 25px 60px rgba(146,64,14,0.8),0 0 80px rgba(180,83,9,0.7),inset 0 1px 0 rgba(251,191,36,0.3) !important;
    animation-play-state:paused !important;
    color:#ffffff !important;
    border-color:#f59e0b !important;
}

.mlpimforwc-ny-cta.button.button-primary:hover::after {
    transform:translateX(8px) scale(1.2) !important;
}

.mlpimforwc-ny-cta.button.button-primary:active {
    transform:translateY(-2px) scale(1.08) !important;
}

.mlpimforwc-ny-cta.button.button-primary:focus {
    box-shadow:0 0 0 4px rgba(217,119,6,0.5),0 25px 60px rgba(146,64,14,0.8),0 0 80px rgba(180,83,9,0.7),inset 0 1px 0 rgba(251,191,36,0.3) !important;
    color:#ffffff !important;
}

.mlpimforwc-ny-blast {
    position:absolute;
    width:250px;
    height:250px;
    border:3px solid rgba(168,85,247,0.5);
    border-radius:50%;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%) scale(0.3);
    opacity:0;
    animation:mlpimforwc_ny_blast 3s ease-out infinite;
    pointer-events:none;
}

.mlpimforwc-ny-confetti {
    position:absolute;
    width:10px;
    height:20px;
    border-radius:5px;
    background:linear-gradient(180deg,#fde68a 0%,#f59e0b 50%,#fcd34d 100%);
    top:-40px;
    left:var(--x,50%);
    opacity:0;
    transform:rotate(var(--r,0deg));
    animation:mlpimforwc_ny_confetti var(--duration,5s) ease-in infinite;
    animation-delay:var(--delay,0s);
}

.mlpimforwc-ny-sparkle {
    position:absolute;
    width:6px;
    height:6px;
    background:#fbbf24;
    border-radius:50%;
    top:var(--y,50%);
    left:var(--x,50%);
    box-shadow:0 0 10px #fbbf24;
    opacity:0;
    animation:mlpimforwc_ny_sparkle var(--duration,2s) ease-in-out infinite;
    animation-delay:var(--delay,0s);
}

.mlpimforwc-ny-sparkle::before,
.mlpimforwc-ny-sparkle::after {
    content:"";
    position:absolute;
    width:2px;
    height:12px;
    background:#fbbf24;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    box-shadow:0 0 8px #fbbf24;
}

.mlpimforwc-ny-sparkle::after {
    transform:translate(-50%,-50%) rotate(90deg);
}

.mlpimforwc-ny-firework {
    position:absolute;
    width:4px;
    height:4px;
    border-radius:50%;
    top:var(--y,20%);
    left:var(--x,20%);
    animation:mlpimforwc_ny_firework var(--duration,3s) ease-out infinite;
    animation-delay:var(--delay,0s);
}

.mlpimforwc-ny-firework::before {
    content:"";
    position:absolute;
    width:100%;
    height:100%;
    border-radius:50%;
    background:radial-gradient(circle,rgba(251,191,36,1) 0%,rgba(168,85,247,0.8) 40%,transparent 70%);
    box-shadow:0 0 20px rgba(251,191,36,0.8);
}

.mlpimforwc-ny-content {
    flex:1;
    min-width:280px;
    position:relative;
    z-index:1;
    animation:mlpimforwc_ny_slide 0.8s ease both;
}

.mlpimforwc-ny-cta {
    z-index:1;
    animation:mlpimforwc_ny_slide 1s ease both;
}

@keyframes mlpimforwc_ny_gradient {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

@keyframes mlpimforwc_ny_shine {
    0%{left:-100%;}
    50%,100%{left:100%;}
}

@keyframes mlpimforwc_ny_icon_pulse {
    0%,100%{transform:scale(1);}
    50%{transform:scale(1.1);}
}

@keyframes mlpimforwc_ny_icon_glow {
    0%,100%{box-shadow:0 10px 30px rgba(245,158,11,0.4),0 0 30px rgba(251,191,36,0.3);}
    50%{box-shadow:0 10px 40px rgba(245,158,11,0.6),0 0 50px rgba(251,191,36,0.5);}
}

@keyframes mlpimforwc_ny_chip_shine {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

@keyframes mlpimforwc_ny_emoji_spin {
    0%,100%{transform:rotate(0deg) scale(1);}
    25%{transform:rotate(-15deg) scale(1.1);}
    75%{transform:rotate(15deg) scale(1.1);}
}

@keyframes mlpimforwc_ny_text_shine {
    0%{background-position:0% center;}
    100%{background-position:200% center;}
}

@keyframes mlpimforwc_ny_code_pulse {
    0%,100%{transform:scale(1);}
    50%{transform:scale(1.05);}
}

@keyframes mlpimforwc_ny_blast {
    0%{transform:translate(-50%,-50%) scale(0.3);opacity:0.9;}
    50%{opacity:0.4;}
    100%{transform:translate(-50%,-50%) scale(1.3);opacity:0;}
}

@keyframes mlpimforwc_ny_confetti {
    0%{transform:translateY(0) rotate(var(--r,0deg)) scale(1);opacity:0;}
    10%{opacity:1;}
    70%{opacity:1;}
    100%{transform:translateY(200px) rotate(calc(var(--r,0deg) + 180deg)) scale(0.5);opacity:0;}
}

@keyframes mlpimforwc_ny_sparkle {
    0%,100%{opacity:0;transform:scale(0) rotate(0deg);}
    50%{opacity:1;transform:scale(1) rotate(180deg);}
}

@keyframes mlpimforwc_ny_firework {
    0%{transform:scale(0);opacity:1;}
    50%{opacity:0.8;}
    100%{transform:scale(3);opacity:0;}
}

@keyframes mlpimforwc_ny_slide {
    0%{opacity:0;transform:translateY(20px);}
    100%{opacity:1;transform:translateY(0);}
}

@keyframes mlpimforwc_ny_button_glow {
    0%,100%{box-shadow:0 0 0 3px rgba(217,119,6,0.3),0 20px 50px rgba(146,64,14,0.6),0 0 60px rgba(180,83,9,0.5),inset 0 1px 0 rgba(251,191,36,0.2);}
    50%{box-shadow:0 0 0 4px rgba(217,119,6,0.5),0 25px 60px rgba(146,64,14,0.8),0 0 80px rgba(180,83,9,0.7),inset 0 1px 0 rgba(251,191,36,0.3);}
}

@keyframes mlpimforwc_ny_button_gradient {
    0%{background-position:0% 50%;}
    100%{background-position:400% 50%;}
}

@keyframes mlpimforwc_ny_button_shine {
    0%{transform:translateX(-100%) translateY(-100%) rotate(45deg);}
    100%{transform:translateX(100%) translateY(100%) rotate(45deg);}
}

.mlpimforwc-ny-dismiss-menu {
    display:none;
    position:absolute;
    top:100%;
    right:0;
    background:#ffffff;
    border:1px solid #ddd;
    border-radius:8px;
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
    min-width:180px;
    z-index:10000;
    margin-top:5px;
}

.mlpimforwc-ny-dismiss-menu.active {
    display:block;
}

.mlpimforwc-ny-dismiss-menu a {
    display:block;
    padding:10px 16px;
    color:#2c3338;
    text-decoration:none;
    font-size:13px;
    transition:background 0.2s ease;
    border-bottom:1px solid #f0f0f0;
}

.mlpimforwc-ny-dismiss-menu a:last-child {
    border-bottom:none;
}

.mlpimforwc-ny-dismiss-menu a:hover {
    background:#f6f7f7;
    color:#0073aa;
}

.notice-dismiss-wrapper {
    position:relative;
}

.notice-dismiss {
    z-index:99999999;
}

p.mlpimforwc-ny-sub {
    display: flex;
    align-items: center;
    gap: 6px;
}
</style>';

    echo '<div class="notice notice-info is-dismissible" style="padding:0;border:none;background:transparent;box-shadow:none;">';
    echo '  <div class="notice-dismiss-wrapper">';
    echo '      <button type="button" class="notice-dismiss mlpimforwc-ny-dismiss-trigger"><span class="screen-reader-text">Dismiss this notice.</span></button>';
    echo '      <div class="mlpimforwc-ny-dismiss-menu">';
    echo '          <a href="#" data-hours="3">Show again in 3 hours</a>';
    echo '          <a href="#" data-hours="12">Show again in 12 hours</a>';
    echo '          <a href="#" data-hours="24">Show again in 1 day</a>';
    echo '      </div>';
    echo '  </div>';
    echo '  <div class="mlpimforwc-ny-wrap">';
    echo '      <span class="dashicons dashicons-megaphone mlpimforwc-ny-icon"></span>';
    echo '      <div class="mlpimforwc-ny-content">';
    echo '          <div class="mlpimforwc-ny-chip">Happy New Year 2026</div>';
    echo '          <p class="mlpimforwc-ny-heading">Celebrate with 30% off Multi Location Product & Inventory Management for WooCommerce Pro</p>';
    echo '          <p class="mlpimforwc-ny-sub">Use code <code>NYP30</code> at checkout.</p>';
    echo '      </div>';
    echo '      <a class="button button-primary mlpimforwc-ny-cta" target="_blank" href="https://plugincy.com/multi-location-product-inventory-management-new-year-deal/">Claim 30% Off</a>';

    // Blast effects
    echo '      <span class="mlpimforwc-ny-blast"></span>';

    // Confetti particles
    echo '      <span class="mlpimforwc-ny-confetti" style="--x:8%;--delay:0s;--duration:5.2s;--r:18deg;"></span>';
    echo '      <span class="mlpimforwc-ny-confetti" style="--x:22%;--delay:0.7s;--duration:5.6s;--r:-15deg;"></span>';
    echo '      <span class="mlpimforwc-ny-confetti" style="--x:38%;--delay:1.2s;--duration:5.3s;--r:25deg;"></span>';
    echo '      <span class="mlpimforwc-ny-confetti" style="--x:54%;--delay:0.4s;--duration:5.8s;--r:-20deg;"></span>';
    echo '      <span class="mlpimforwc-ny-confetti" style="--x:68%;--delay:1.8s;--duration:5.1s;--r:12deg;"></span>';
    echo '      <span class="mlpimforwc-ny-confetti" style="--x:82%;--delay:0.9s;--duration:5.5s;--r:-18deg;"></span>';
    echo '      <span class="mlpimforwc-ny-confetti" style="--x:94%;--delay:1.5s;--duration:5.4s;--r:22deg;"></span>';

    // Sparkle effects
    echo '      <span class="mlpimforwc-ny-sparkle" style="--x:15%;--y:25%;--delay:0s;--duration:2.5s;"></span>';
    echo '      <span class="mlpimforwc-ny-sparkle" style="--x:85%;--y:30%;--delay:0.8s;--duration:2.2s;"></span>';
    echo '      <span class="mlpimforwc-ny-sparkle" style="--x:45%;--y:15%;--delay:1.5s;--duration:2.8s;"></span>';
    echo '      <span class="mlpimforwc-ny-sparkle" style="--x:70%;--y:70%;--delay:1s;--duration:2.4s;"></span>';
    echo '      <span class="mlpimforwc-ny-sparkle" style="--x:25%;--y:65%;--delay:1.8s;--duration:2.6s;"></span>';

    // Firework effects
    echo '      <span class="mlpimforwc-ny-firework" style="--x:20%;--y:20%;--delay:0s;--duration:3s;"></span>';
    echo '      <span class="mlpimforwc-ny-firework" style="--x:80%;--y:25%;--delay:1s;--duration:3.2s;"></span>';
    echo '      <span class="mlpimforwc-ny-firework" style="--x:50%;--y:15%;--delay:2s;--duration:2.8s;"></span>';

    echo '  </div>';
    echo '</div>';

    // Add JavaScript for dismiss functionality
    echo '<script>
    jQuery(document).ready(function($) {
        var dismissMenu = $(".mlpimforwc-ny-dismiss-menu");
        var dismissTrigger = $(".mlpimforwc-ny-dismiss-trigger");
        var noticeWrapper = $(".notice-info");
        
        // Toggle menu on X button click
        dismissTrigger.on("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            dismissMenu.toggleClass("active");
        });
        
        // Handle dismiss option clicks
        dismissMenu.find("a").on("click", function(e) {
            e.preventDefault();
            var hours = $(this).data("hours");
            
            // Immediately close the menu and fade out the notice
            dismissMenu.removeClass("active");
            noticeWrapper.fadeOut(300);
            
            // Send AJAX request to save dismiss time
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "mlpimforwc_dismiss_ny_notice",
                    hours: hours,
                    nonce: "' . wp_create_nonce('mlpimforwc_dismiss_ny_notice') . '"
                }
            });
        });
        
        // Close menu when clicking outside
        $(document).on("click", function(e) {
            if (!$(e.target).closest(".notice-dismiss-wrapper").length) {
                dismissMenu.removeClass("active");
            }
        });
    });
    </script>';
}
