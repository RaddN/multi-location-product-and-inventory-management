<?php
if (!defined('ABSPATH')) {
    exit;
}

class mulopimfwc_License_Manager
{
    private $api_url = 'https://plugincy.com/';
    private $item_id = 697;
    private $pro_plugin_file = 'multi-location-product-and-inventory-management-pro/multi-location-product-and-inventory-management-pro.php';
    private $pro_plugin_dir = 'multi-location-product-and-inventory-management-pro';

    public function __construct()
    {
        add_action('admin_init', array($this, 'handle_license_actions'));
        // Move admin notices to proper hook
        add_action('admin_notices', array($this, 'show_admin_notices'));
    }

    public function handle_license_actions()
    {
        if (!isset($_POST['mulopimfwc_license_action']) || !isset($_POST['mulopimfwc_license_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mulopimfwc_license_nonce'])), 'mulopimfwc_license_nonce')) {
            return;
        }

        $action = sanitize_text_field(wp_unslash($_POST['mulopimfwc_license_action']));

        // Start output buffering to prevent header issues
        ob_start();

        try {
            if ($action === 'activate') {
                $license_key = isset($_POST['mulopimfwc_license_key']) ? sanitize_text_field(wp_unslash($_POST['mulopimfwc_license_key'])) : '';

                if (empty($license_key)) {
                    $this->set_transient_notice('Please enter a valid license key.', 'error');
                    return;
                }

                $this->activate_license($license_key);
            } elseif ($action === 'deactivate') {
                $this->deactivate_license();
            } elseif ($action === 'reinstall') {
                $this->reinstall_pro_plugin();
            }
        } catch (Exception $e) {
            $this->set_transient_notice('An error occurred: ' . $e->getMessage(), 'error');
        }

        // Clean output buffer
        ob_end_clean();

        // Redirect to prevent form resubmission
        wp_redirect(add_query_arg(array('updated' => 1), wp_get_referer()));
        exit;
    }

    private function set_transient_notice($message, $type = 'success')
    {
        set_transient('mulopimfwc_admin_notice', array(
            'message' => $message,
            'type' => $type
        ), 30);
    }

    public function show_admin_notices()
    {
        $notice = get_transient('mulopimfwc_admin_notice');
        if ($notice) {
            $class = $notice['type'] === 'error' ? 'notice-error' : 'notice-success';
            echo '<div class="notice ' . esc_attr($class) . ' is-dismissible">';
            echo '<p>' . esc_html($notice['message']) . '</p>';
            echo '</div>';
            delete_transient('mulopimfwc_admin_notice');
        }
    }

    private function activate_license($license_key)
    {
        // Step 1: Activate the license
        $api_params = array(
            'edd_action' => 'activate_license',
            'license' => $license_key,
            'item_id' => $this->item_id,
            'url' => home_url()
        );

        $response = wp_remote_get(add_query_arg($api_params, $this->api_url), array(
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'DAPF/' . (defined('DAPFFORWC_VERSION') ? DAPFFORWC_VERSION : '1.0.5.14')
        ));

        if (is_wp_error($response)) {
            $this->set_transient_notice('Unable to connect to licensing server. Please try again later.', 'error');
            return;
        }

        $license_data = json_decode(wp_remote_retrieve_body($response));

        if ($license_data && $license_data->license == 'valid') {
            // Step 2: Get version info to get download URL
            $version_info = $this->get_version_info($license_key);

            if ($version_info && isset($version_info->download_link)) {
                // Step 3: Download and install the pro plugin
                $install_result = $this->download_and_install_pro_plugin($version_info->download_link);

                if ($install_result) {
                    update_option('mulopimfwc_license_key', $license_key);
                    update_option('mulopimfwc_license_status', 'valid');
                    $this->set_transient_notice('License activated and Pro plugin installed successfully!', 'success');
                } else {
                    $this->set_transient_notice('License activated but failed to install Pro plugin. Please try again.', 'error');
                }
            } else {
                $this->set_transient_notice('License activated but unable to get download link. Please contact support.', 'error');
            }
        } else {
            $error_message = $this->get_license_error_message($license_data);
            $this->set_transient_notice($error_message, 'error');
        }
    }

    private function reinstall_pro_plugin()
    {
        $license_key = get_option('mulopimfwc_license_key', '');

        if (empty($license_key)) {
            $this->set_transient_notice('No valid license found. Please activate your license first.', 'error');
            return;
        }

        // Get version info to get download URL using existing license
        $version_info = $this->get_version_info($license_key);

        if ($version_info && isset($version_info->download_link)) {
            // Download and install the pro plugin
            $install_result = $this->download_and_install_pro_plugin($version_info->download_link);

            if ($install_result) {
                $this->set_transient_notice('Pro plugin reinstalled and activated successfully!', 'success');
            } else {
                $this->set_transient_notice('Failed to reinstall Pro plugin. Please try again or contact support.', 'error');
            }
        } else {
            $this->set_transient_notice('Unable to get download link. Please contact support.', 'error');
        }
    }

    private function deactivate_license()
    {
        $license_key = get_option('mulopimfwc_license_key', '');

        if (empty($license_key)) {
            $this->set_transient_notice('No license key found to deactivate.', 'error');
            return;
        }

        // Step 1: Deactivate license on server
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license' => $license_key,
            'item_id' => $this->item_id,
            'url' => home_url()
        );

        $response = wp_remote_get(add_query_arg($api_params, $this->api_url), array(
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'DAPF/' . (defined('DAPFFORWC_VERSION') ? DAPFFORWC_VERSION : '1.0.5.14')
        ));

        // Step 2: Deactivate Pro plugin if it's active (before removing license data)
        if (is_plugin_active($this->pro_plugin_file)) {
            deactivate_plugins($this->pro_plugin_file, true); // Silent deactivation
        }

        // Step 3: Remove local license data
        delete_option('mulopimfwc_license_key');
        delete_option('mulopimfwc_license_status');

        if (is_wp_error($response)) {
            $this->set_transient_notice('License removed locally but unable to connect to server. License may still be active on server.', 'error');
        } else {
            $license_data = json_decode(wp_remote_retrieve_body($response));
            if ($license_data && $license_data->license == 'deactivated') {
                $this->set_transient_notice('License deactivated successfully!', 'success');
            } else {
                $this->set_transient_notice('License removed locally. Server response: ' . ($license_data->license ?? 'unknown'), 'error');
            }
        }
    }

    private function get_version_info($license_key)
    {
        $api_params = array(
            'edd_action' => 'get_version',
            'license' => $license_key,
            'item_id' => $this->item_id,
            'url' => home_url()
        );

        $response = wp_remote_get(add_query_arg($api_params, $this->api_url), array(
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'DAPF/' . (defined('DAPFFORWC_VERSION') ? DAPFFORWC_VERSION : '1.0.5.14')
        ));

        if (is_wp_error($response)) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response));
    }

    private function remove_existing_plugin_directory()
    {
        $plugin_dir = WP_PLUGIN_DIR . '/' . $this->pro_plugin_dir;

        if (is_dir($plugin_dir)) {
            // Use WordPress filesystem API if available
            if (function_exists('WP_Filesystem')) {
                global $wp_filesystem;
                if (WP_Filesystem()) {
                    return $wp_filesystem->rmdir($plugin_dir, true);
                }
            }
        }

        return true;
    }

    private function download_and_install_pro_plugin($download_url)
    {
        if (!current_user_can('install_plugins')) {
            return false;
        }

        // Include necessary WordPress files
        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!class_exists('Plugin_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }

        // Step 1: Deactivate the pro plugin if it's currently active
        if (is_plugin_active($this->pro_plugin_file)) {
            deactivate_plugins($this->pro_plugin_file, true); // Silent deactivation
        }

        // Step 2: Remove existing plugin directory if it exists
        if (!$this->remove_existing_plugin_directory()) {
        }

        // Step 3: Download the plugin zip file
        $temp_file = download_url($download_url);

        if (is_wp_error($temp_file)) {
            return false;
        }

        // Step 4: Install the plugin using WordPress upgrader with silent skin
        $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
        $install_result = $upgrader->install($temp_file);

        // Clean up temp file
        if (file_exists($temp_file)) {
            wp_delete_file($temp_file);
        }

        if (is_wp_error($install_result) || !$install_result) {
            return false;
        }

        // Step 5: Get the plugin file path and activate
        $plugin_file = $upgrader->plugin_info();

        if ($plugin_file) {
            // Activate the pro plugin silently
            $activate_result = activate_plugin($plugin_file, '', false, true);

            if (is_wp_error($activate_result)) {
                return false;
            }

            // Deactivate the free version if it exists and is different from pro
            $free_plugin = plugin_basename(__FILE__);
            if (is_plugin_active($free_plugin) && $free_plugin !== $plugin_file) {
                deactivate_plugins($free_plugin, true);
            }
        }

        return true;
    }

    private function get_license_error_message($license_data)
    {
        if (!$license_data || !isset($license_data->license)) {
            return 'Invalid response from license server. Please try again.';
        }

        switch ($license_data->license) {
            case 'expired':
                return 'Your license key has expired. Please renew your license.';
            case 'disabled':
            case 'revoked':
                return 'Your license key has been disabled.';
            case 'missing':
                return 'Invalid license key. Please check your license key and try again.';
            case 'invalid':
            case 'site_inactive':
                return 'Your license is not active for this URL.';
            case 'item_name_mismatch':
                return 'This license key does not belong to this product.';
            case 'no_activations_left':
                return 'Your license key has reached its activation limit.';
            default:
                return 'License validation failed: ' . ($license_data->license ?? 'unknown error');
        }
    }

    private function is_pro_version_active()
    {
        return is_plugin_active($this->pro_plugin_file);
    }

    public function render_license_form()
    {
        $license_key = get_option('mulopimfwc_license_key', '');
        $license_status = get_option('mulopimfwc_license_status', '');
        $is_pro_active = $this->is_pro_version_active();
?>
        <div class="wrap" style="background: #fff; padding: 1rem;">
            <h2 style="font-size: 17.5px; font-weight: 500;">
                <svg width="16" height="16" style="margin-right:6px;vertical-align:middle;background-color:#dbeafe;padding:10px;border-radius:6px" viewBox="0 0 0.48 0.48" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#2563eb" d="M.276.293.195.374H.142v.053H.089V.48H0V.391L.187.204A.2.2 0 0 1 .178.151a.151.151 0 1 1 .097.141zM.427.107A.053.053 0 1 0 .374.16.053.053 0 0 0 .427.107"></path>
                </svg>
                License Settings
            </h2>

            <?php if ($license_status === 'valid' && $is_pro_active): ?>
                <div class="notice notice-success">
                    <p><strong>✓ <?php echo esc_html__('Pro Version Activated!', 'multi-location-product-and-inventory-management'); ?></strong> <?php echo esc_html__('Your license is active and the Pro plugin is running.', 'multi-location-product-and-inventory-management'); ?></p>
                </div>
                <form method="post" action="">
                    <?php wp_nonce_field('mulopimfwc_license_nonce', 'mulopimfwc_license_nonce'); ?>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label><?php echo esc_html__('Current License Key', 'multi-location-product-and-inventory-management'); ?></label>
                                </th>
                                <td>
                                    <code><?php echo esc_html(substr($license_key, 0, 8) . '...' . substr($license_key, -8)); ?></code>
                                    <p class="description"><?php echo esc_html__('Your license is currently active.', 'multi-location-product-and-inventory-management'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <input type="hidden" name="mulopimfwc_license_action" value="deactivate" />
                    <input type="submit" class="button button-secondary" value="Deactivate License" onclick="return confirm('Are you sure you want to deactivate your license? This will deactivate the Pro plugin.');" />
                </form>
            <?php elseif ($license_status === 'valid' && !$is_pro_active): ?>
                <div class="notice notice-warning">
                    <p><strong>⚠ <?php echo esc_html__('License Active but Pro Plugin Not Running!', 'multi-location-product-and-inventory-management'); ?></strong><?php echo esc_html__('You have a valid license but the Pro plugin is not active.', 'multi-location-product-and-inventory-management'); ?> </p>
                </div>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label><?php echo esc_html__('Current License Key', 'multi-location-product-and-inventory-management'); ?></label>
                            </th>
                            <td>
                                <code><?php echo esc_html(substr($license_key, 0, 8) . '...' . substr($license_key, -8)); ?></code>
                                <p class="description"><?php echo esc_html__('License is valid but Pro plugin is not active. Click below to reinstall and activate the Pro plugin.', 'multi-location-product-and-inventory-management'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Reinstall Form -->
                <form method="post" action="" style="display: inline-block; padding: 10px 0; border: none;">
                    <?php wp_nonce_field('mulopimfwc_license_nonce', 'mulopimfwc_license_nonce'); ?>
                    <input type="hidden" name="mulopimfwc_license_action" value="reinstall" />
                    <button type="submit" class="button button-primary" style="background: #2560e8; display: flex; align-items: center; gap: 8px; margin-top: 15px; padding: 6px 20px;">
                        <svg width="18" height="18" viewBox="0 0 0.54 0.54" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M.27.045a.02.02 0 0 1 .022.022v.238L.344.254a.022.022 0 0 1 .032.032l-.09.09a.022.022 0 0 1-.032 0l-.09-.09A.022.022 0 1 1 .196.254l.051.052V.068A.022.022 0 0 1 .27.045M.113.383a.02.02 0 0 1 .022.022V.45h.27V.405a.022.022 0 1 1 .045 0V.45a.045.045 0 0 1-.045.045h-.27A.045.045 0 0 1 .09.45V.405A.022.022 0 0 1 .113.383" fill="#fff" />
                        </svg>
                        <?php echo esc_html__('Reinstall & Activate Pro Plugin', 'multi-location-product-and-inventory-management'); ?>
                    </button>
                </form>

                <!-- Deactivate Form -->
                <form method="post" action="" style="display: inline-block; margin-left: 10px; padding: 0; border: none;">
                    <?php wp_nonce_field('mulopimfwc_license_nonce', 'mulopimfwc_license_nonce'); ?>
                    <input type="hidden" name="mulopimfwc_license_action" value="deactivate" />
                    <button type="submit" class="button button-primary" style="background: #eef1f6;color:#24262a;display: flex;align-items: center;gap: 8px;margin-top: 15px;padding: 6px 20px;border: 1px solid #e3e3e3;">
                        <svg width="20" height="20" viewBox="0 0 0.8 0.8" xmlns="http://www.w3.org/2000/svg">
                            <path d="M.564.135a.031.031 0 0 0-.03.055.281.281 0 1 1-.27.001L.265.19A.031.031 0 0 0 .25.131a.02.02 0 0 0-.015.004.344.344 0 1 0 .331.001zM.399.382A.03.03 0 0 0 .43.351V.05a.031.031 0 0 0-.063 0v.301c0 .017.014.031.031.031" />
                        </svg>
                        <?php echo esc_html__('Deactivate License', 'multi-location-product-and-inventory-management'); ?>
                    </button>
                </form>
            <?php else: ?>
                <form method="post" action="" style="padding-top: 10px;">
                    <?php wp_nonce_field('mulopimfwc_license_nonce', 'mulopimfwc_license_nonce'); ?>
                    <label for="mulopimfwc_license_key" style=" font-size: 16px; "><?php echo esc_html__('License Key', 'multi-location-product-and-inventory-management'); ?></label>
                    <div style="position: relative;">
                        <input type="password" style="width: 100%;border: 1px solid #eee;padding: 6px;" id="mulopimfwc_license_key" name="mulopimfwc_license_key" value="<?php echo esc_attr($license_key); ?>" class="regular-text" placeholder="Enter your license key" />
                        <span id="mulopimfwc_license_eye" style="vertical-align: middle;margin-left: 8px;position: absolute;right: 0;top: 0;background: #eee;height: 100%;display: flex;align-items: center;justify-content: center;padding: 0 20px;border-radius: 0 2px 2px 0;cursor:pointer;" class="dashicons dashicons-visibility" title="<?php echo esc_attr__('Show license key', 'multi-location-product-and-inventory-management'); ?>"></span>
                        <script>
                            (function() {
                                var input = document.getElementById('mulopimfwc_license_key');
                                var eye = document.getElementById('mulopimfwc_license_eye');
                                var visible = false;
                                eye.addEventListener('click', function() {
                                    visible = !visible;
                                    input.type = visible ? 'text' : 'password';
                                    eye.classList.toggle('dashicons-visibility');
                                    eye.classList.toggle('dashicons-visibility-alt');
                                    eye.title = visible ? '<?php echo esc_js(__('Hide license key', 'multi-location-product-and-inventory-management')); ?>' : '<?php echo esc_js(__('Show license key', 'multi-location-product-and-inventory-management')); ?>';
                                });
                            })();
                        </script>
                    </div>
                    <p class="description"><?php echo esc_html__('Enter your license key to download and activate the Pro version.', 'multi-location-product-and-inventory-management'); ?></p>
                    <input type="hidden" name="mulopimfwc_license_action" value="activate" />
                    <button type="submit" class="button button-primary" style="background: #2560e8; display: flex; align-items: center; gap: 8px; margin-top: 15px; padding: 6px 20px;">
                        <svg width="18" height="18" viewBox="0 0 0.54 0.54" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M.27.045a.02.02 0 0 1 .022.022v.238L.344.254a.022.022 0 0 1 .032.032l-.09.09a.022.022 0 0 1-.032 0l-.09-.09A.022.022 0 1 1 .196.254l.051.052V.068A.022.022 0 0 1 .27.045M.113.383a.02.02 0 0 1 .022.022V.45h.27V.405a.022.022 0 1 1 .045 0V.45a.045.045 0 0 1-.045.045h-.27A.045.045 0 0 1 .09.45V.405A.022.022 0 0 1 .113.383" fill="#fff" />
                        </svg>
                        Activate License & Install Pro
                    </button>
                </form>
            <?php endif; ?>

            <p class="support-links">
                <span style="margin-right: 16px;">
                    <a href="https://plugincy.com/support" target="_blank" style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-sos" style="font-size: 16px; vertical-align: middle; margin-bottom: -6px;"></span>
                        <?php echo esc_html__('Contact Support', 'multi-location-product-and-inventory-management'); ?>
                    </a>
                </span>
                <span>
                    <a href="https://plugincy.com/my-account" target="_blank" style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-admin-network" style="font-size: 16px; vertical-align: middle; margin-bottom: -6px;"></span>
                        <?php echo esc_html__('Manage Your Licenses', 'multi-location-product-and-inventory-management'); ?>
                    </a>
                </span>
            </p>
        </div>
<?php
    }
}

new mulopimfwc_License_Manager();


global $mulopimfwc_License_Manager;

$mulopimfwc_License_Manager = new mulopimfwc_License_Manager();
