<?php
// templates/shortcode-selector.php
if (!defined('ABSPATH')) exit;
// Template for the store location selector shortcode
?>
<?php 
    // If no unit is present, append 'px'
    $max_width = trim($atts['max_width']);
    if ($max_width !== '' && !preg_match('/(px|em|rem|vw|vh|%|pt|cm|mm|in|ex|ch)$/i', $max_width)) {
        $max_width .= 'px';
    }
?>
<div class="lwp-shortcode-store-selector <?php echo esc_attr($atts['class']); ?>" style="max-width: <?php echo esc_attr($max_width);?>;">
    <?php if ($atts['show_title'] === 'on'): ?>
        <h3 class="lwp-shortcode-title"><?php echo esc_html($atts['title']); ?></h3>
    <?php endif; ?>
    <form id="lwp-shortcode-selector-form" class="lwp-selector-form">
        <?php wp_nonce_field('mulopimfwc_shortcode_selector', 'mulopimfwc_shortcode_selector_nonce'); ?>
        <?php if ($atts["herichical"] === "seperately"): ?>
            <?php
            // Organize locations into a hierarchical structure for separate selects
            $location_hierarchy = array();
            $parent_children_map = array();
            $child_counts = array();
            $depth_map = array();
            $max_depth = 0;
            $show_count = isset($atts["show_count"]) && $atts["show_count"] === "on";
            // First pass: identify all parent-child relationships and depths
            foreach ($locations as $location) {
                if ($location->parent == 0) {
                    $depth_map[$location->term_id] = 0;
                    $location_hierarchy[0][] = $location;
                    $child_counts[$location->term_id] = 0; // Initialize child count
                } else {
                    $parent_children_map[$location->parent][] = $location;
                    // Increment child count for parent
                    if (!isset($child_counts[$location->parent])) {
                        $child_counts[$location->parent] = 1;
                    } else {
                        $child_counts[$location->parent]++;
                    }
                }
            }
            // Calculate depth for each location
            $calculate_depth = function ($location_id, $current_depth = 0) use (&$calculate_depth, &$depth_map, &$parent_children_map, &$max_depth) {
                $depth_map[$location_id] = $current_depth;
                if ($current_depth > $max_depth) {
                    $max_depth = $current_depth;
                }
                if (isset($parent_children_map[$location_id])) {
                    foreach ($parent_children_map[$location_id] as $child) {
                        $calculate_depth($child->term_id, $current_depth + 1);
                    }
                }
            };
            // Calculate depths starting from root locations
            if (!empty($location_hierarchy[0])) {
                foreach ($location_hierarchy[0] as $root_location) {
                    $calculate_depth($root_location->term_id);
                }
            }
            // Build full hierarchy
            for ($i = 1; $i <= $max_depth; $i++) {
                $location_hierarchy[$i] = array();
                foreach ($parent_children_map as $parent_id => $children) {
                    foreach ($children as $child) {
                        if ($depth_map[$parent_id] == $i - 1) {
                            $location_hierarchy[$i][] = $child;
                        }
                    }
                }
            }
            // Convert locations to JSON for JavaScript
            $locations_json = wp_json_encode($locations);
            $parent_children_json = wp_json_encode($parent_children_map);
            $child_counts_json = wp_json_encode($child_counts);
            // Generate separate dropdowns for each level
            for ($level = 0; $level <= $max_depth; $level++):
                $select_id = "lwp-shortcode-selector-level-{$level}";
                // translators: %s: The name of the location level (e.g., Area, Sub-area)
                $placeholder = $level == 0 ? ($atts['placeholder'] ?? '-- Select a Store --') : sprintf(__('-- Select %s --', 'multi-location-product-and-inventory-management'), ($level == 1 ? 'Area' : 'Sub-area'));
            ?>
                <div class="lwp-select-container level-<?php echo esc_html($level); ?>" <?php echo $level > 0 ? 'style="display:none;"' : ''; ?>>
                    <select id="<?php echo esc_html($select_id); ?>" class="lwp-shortcode-selector-dropdown" data-level="<?php echo esc_html($level); ?>">
                        <option value=""><?php echo esc_html($placeholder); ?></option>
                        <?php if ($level == 0 && $is_admin_or_manager && $show_all_products_admin === 'on'): ?>
                            <option value="all-products" <?php echo ($selected_location === 'all-products') ? 'selected' : ''; ?>>
                                <?php echo esc_html_e('All Products', 'multi-location-product-and-inventory-management'); ?>
                            </option>
                        <?php endif; ?>
                        <?php if ($level == 0 && !empty($location_hierarchy[0])): ?>
                            <?php foreach ($location_hierarchy[0] as $location): ?>
                                <?php
                                $child_count = isset($child_counts[$location->term_id]) ? $child_counts[$location->term_id] : 0;
                                $display_name = esc_html($location->name);
                                if ($show_count && $child_count > 0) {
                                    $display_name .= ' (' . $child_count . ')';
                                }
                                $selected = ($location->slug === $selected_location) ? 'selected' : '';
                                ?>
                                <option value="<?php echo esc_attr($location->slug); ?>"
                                    data-term-id="<?php echo esc_attr($location->term_id); ?>"
                                    <?php echo esc_html($selected); ?>>
                                    <?php echo esc_html($display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            <?php endfor; ?>
            <input type="hidden" id="lwp-selected-store-shortcode" name="mulopimfwc_selected_store" value="<?php echo esc_attr($selected_location); ?>">
        <?php else: ?>
            <!-- Single dropdown implementation -->
            <select id="lwp-shortcode-selector" class="lwp-location-dropdown">
                <option value=""><?php echo esc_html($atts['placeholder'] ?? '-- Select a Store --'); ?></option>
                <?php if ($is_admin_or_manager && $show_all_products_admin === 'on'): ?>
                    <?php $selected = ($selected_location === 'all-products') ? 'selected' : ''; ?>
                    <option value="all-products" <?php echo esc_attr($selected); ?>><?php echo esc_html_e('All Products', 'multi-location-product-and-inventory-management'); ?></option>
                <?php endif; ?>
                <?php if (!empty($locations) && !is_wp_error($locations)): ?>
                    <?php
                    if ($atts["herichical"] === "on") {
                        // Organize locations into a hierarchical structure
                        $parent_locations = array();
                        $child_locations = array();
                        $child_counts = array();
                        $show_count = isset($atts["show_count"]) && $atts["show_count"] === "on";
                        foreach ($locations as $location) {
                            if ($location->parent == 0) {
                                $parent_locations[] = $location;
                                $child_counts[$location->term_id] = 0; // Initialize child count
                            } else {
                                $child_locations[$location->parent][] = $location;
                                // Increment child count for parent
                                if (!isset($child_counts[$location->parent])) {
                                    $child_counts[$location->parent] = 1;
                                } else {
                                    $child_counts[$location->parent]++;
                                }
                            }
                        }
                        // Display parent locations and their children
                        foreach ($parent_locations as $parent) {
                            $child_count = isset($child_counts[$parent->term_id]) ? $child_counts[$parent->term_id] : 0;
                            $display_name = esc_html($parent->name);
                            if ($show_count && $child_count > 0) {
                                $display_name .= ' (' . $child_count . ')';
                            }
                            $selected = ($parent->slug === $selected_location) ? 'selected' : '';
                            echo '<option value="' . esc_attr($parent->slug) . '" ' . esc_html($selected) . '>' . $display_name . '</option>';
                            // Check if this parent has children
                            if (isset($child_locations[$parent->term_id])) {
                                foreach ($child_locations[$parent->term_id] as $child) {
                                    $selected = ($child->slug === $selected_location) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($child->slug) . '" ' . esc_html($selected) . '>&nbsp;&nbsp;— ' . esc_html($child->name) . '</option>';
                                }
                            }
                        }
                    } else {
                        // Display locations in flat list
                        foreach ($locations as $location) {
                            $selected = ($location->slug === $selected_location) ? 'selected' : '';
                            echo '<option value="' . esc_attr($location->slug) . '" ' . esc_html($selected) . '>' . esc_html($location->name) . '</option>';
                        }
                    }
                    ?>
                <?php endif; ?>
            </select>
            <input type="hidden" id="lwp-selected-store-shortcode" name="mulopimfwc_selected_store" value="<?php echo esc_attr($selected_location); ?>">
        <?php endif; ?>
        <?php if ($atts['show_button'] === 'on'): ?>
            <button type="button" class="button lwp-shortcode-submit">
                <?php echo esc_html($atts['button_text'] ?? 'Change Location'); ?>
            </button>
        <?php endif; ?>
    </form>
</div>

<!-- Location Popup -->
<div id="lwp-location-popup" class="lwp-location-popup" style="display:none;">
    <div class="lwp-popup-content">
        <div class="lwp-popup-header">
            <h3><?php _e('Set Your Location', 'multi-location-product-and-inventory-management'); ?></h3>
            <button type="button" class="lwp-close-popup">&times;</button>
        </div>
        <div id="lwp-location-step1" class="lwp-location-step">
            <div class="lwp-map-container">
                <div id="lwp-location-map" class="lwp-location-map"></div>
                <div class="lwp-map-controls">
                    <button type="button" class="button button-primary lwp-continue-btn"><?php _e('Continue', 'multi-location-product-and-inventory-management'); ?></button>
                </div>
            </div>
        </div>

        <div id="lwp-location-step2" class="lwp-location-step" style="display:none;">
            <div class="lwp-location-details">
                <div class="lwp-details-header">
                    <h4><?php _e('Location Details', 'multi-location-product-and-inventory-management'); ?></h4>
                    <p class="lwp-address-preview"></p>
                    <span class="edit_location_map" style="cursor: pointer;">✏️</span>
                </div>
                <form id="lwp-location-form">
                    <?php wp_nonce_field('mulopimfwc_save_user_location', 'mulopimfwc_save_user_location_nonce'); ?>
                    <input type="hidden" id="lwp-editing-location-id" name="location_id" value="">
                    <div class="lwp-form-group">
                        <label for="lwp-location-label"><?php _e('Label', 'multi-location-product-and-inventory-management'); ?></label>
                        <select id="lwp-location-label" name="label" required>
                            <option value="Home"><?php _e('Home', 'multi-location-product-and-inventory-management'); ?></option>
                            <option value="Work"><?php _e('Work', 'multi-location-product-and-inventory-management'); ?></option>
                            <option value="Partner"><?php _e('Partner', 'multi-location-product-and-inventory-management'); ?></option>
                            <option value="Other"><?php _e('Other', 'multi-location-product-and-inventory-management'); ?></option>
                        </select>
                    </div>
                    <div class="lwp-form-group">
                        <label for="lwp-location-street"><?php _e('Street Address', 'multi-location-product-and-inventory-management'); ?></label>
                        <input type="text" id="lwp-location-street" name="street" required>
                    </div>
                    <div class="lwp-form-group">
                        <label for="lwp-location-apartment"><?php _e('Apartment, suite, etc.', 'multi-location-product-and-inventory-management'); ?></label>
                        <input type="text" id="lwp-location-apartment" name="apartment">
                    </div>
                    <div class="lwp-form-group">
                        <label for="lwp-location-note"><?php _e('Note', 'multi-location-product-and-inventory-management'); ?></label>
                        <textarea id="lwp-location-note" name="note"></textarea>
                    </div>
                    <input type="hidden" id="lwp-location-lat" name="lat">
                    <input type="hidden" id="lwp-location-lng" name="lng">
                    <input type="hidden" id="lwp-location-city" name="city">
                    <input type="hidden" id="lwp-location-state" name="state">
                    <input type="hidden" id="lwp-location-postal" name="postal">
                    <input type="hidden" id="lwp-location-country" name="country">
                    <div class="lwp-form-actions">
                        <button type="button" class="button lwp-back-btn"><?php _e('Back', 'multi-location-product-and-inventory-management'); ?></button>
                        <button type="submit" class="button button-primary"><?php _e('Save Location', 'multi-location-product-and-inventory-management'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($atts["herichical"] === "seperately"):
    // Prepare JS variables for inline script
    $locations_json = wp_json_encode($locations);
    $parent_children_json = wp_json_encode($parent_children_map);
    $child_counts_json = wp_json_encode($child_counts);
    $show_count_js = $show_count ? 'true' : 'false';
    $auto_submit_js = $atts['show_button'] === 'off' ? 'true' : 'false';
    $max_depth_js = (int)$max_depth;
    $use_select2 = $atts["use_select2"] === "on";
    $select2_init = $use_select2 ? "$('.lwp-shortcode-selector-dropdown').select2();" : "";
    $inline_js = <<<JS
jQuery(document).ready(function($) {
    var locationsData = $locations_json;
    var parentChildrenMap = $parent_children_json;
    var childCounts = $child_counts_json;
    var showCount = $show_count_js;
    var autoSubmit = $auto_submit_js;
    $('.lwp-shortcode-selector-dropdown').on('change', function() {
        var selectedLevel = $(this).data('level');
        var selectedTermId = $(this).find(':selected').data('term-id');
        var selectedValue = $(this).val();
        if (selectedValue) {
            $('#lwp-selected-store-shortcode').val(selectedValue);
            if (autoSubmit) {
                $('#lwp-shortcode-selector-form').submit();
            }
        }
        for (var i = selectedLevel + 1; i <= $max_depth_js; i++) {
            $('.lwp-select-container.level-' + i).hide();
            $('#lwp-shortcode-selector-level-' + i).empty().append('<option value="">' + mulopimfwc_selector_i18n.select + '</option>');
        }
        if (selectedValue && selectedTermId && parentChildrenMap[selectedTermId]) {
            var nextLevel = selectedLevel + 1;
            var nextDropdown = $('#lwp-shortcode-selector-level-' + nextLevel);
            nextDropdown.empty().append('<option value="">' + mulopimfwc_selector_i18n.select + '</option>');
            $.each(parentChildrenMap[selectedTermId], function(index, location) {
                var childCount = childCounts[location.term_id] || 0;
                var displayText = location.name;
                if (showCount && childCount > 0) {
                    displayText += ' (' + childCount + ')';
                }
                nextDropdown.append('<option value="' + location.slug + '" data-term-id="' + location.term_id + '">' + displayText + '</option>');
            });
            $('.lwp-select-container.level-' + nextLevel).show();
        } else if (autoSubmit && selectedValue) {
            $('#lwp-shortcode-selector-form').submit();
        }
    });
    $select2_init
    $('.lwp-shortcode-submit').on('click', function() {
        $('#lwp-shortcode-selector-form').submit();
    });
});
JS;
    // Pass translation string for '-- Select --'
    wp_localize_script('mulopimfwc_script', 'mulopimfwc_selector_i18n', array(
        'select' => esc_html__('-- Select --', 'multi-location-product-and-inventory-management'),
    ));
    // Output inline script
    wp_add_inline_script('mulopimfwc_script', $inline_js);
else:
    $use_select2 = $atts["use_select2"] === "on";
    $show_button = $atts['show_button'] === 'on';
    $auto_submit_js = $atts['show_button'] === 'off' ? 'true' : 'false';
    $inline_js = <<<JS
jQuery(document).ready(function($) {
    if ($use_select2) {
        $('#lwp-shortcode-selector').select2();
    }
    $('#lwp-shortcode-selector').on('change', function() {
        var selectedValue = $(this).val();
        if (selectedValue) {
            $('#lwp-selected-store-shortcode').val(selectedValue);
            if ($auto_submit_js) {
                $('#lwp-shortcode-selector-form').submit();
            }
        }
    });
    $('.lwp-shortcode-submit').on('click', function() {
        $('#lwp-shortcode-selector-form').submit();
    });
});
JS;
    wp_add_inline_script('mulopimfwc_script', $inline_js);
endif;