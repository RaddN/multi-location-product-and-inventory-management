/**
 * Multi-Location Product Location Selector
 * Handles dynamic product updates when location changes
 */
(function ($) {
    'use strict';

    class LocationSelector {
        constructor() {
            this.placed = false;
            this.retryCount = 0;
            this.maxRetries = 20; // ~4s with 200ms interval
            this.retryTimer = null;
            this.isSwitching = false;
            this.settings = window.mulopimfwc_locationWiseProducts || {};

            // Read PHP-provided hints for placement
            this.cfg = window.MULOPIMFWC_LOC_SELECTOR || {
                position: 'after_price',
                targets: {}
            };

            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Handle location change via radio buttons
            $(document).on('change', '.mulopimfwc-location-checkbox', (e) => {
                this.handleLocationChange($(e.target).val());
            });

            // Handle location change via buttons
            $(document).on('click', '.mulopimfwc-location-button', (e) => {
                e.preventDefault();
                const $button = $(e.target);
                const location = $button.data('location');

                // Update button states
                $button.addClass('active').siblings().removeClass('active');

                this.handleLocationChange(location);
            });

            // Handle location change via select dropdown
            $(document).on('change', '.mulopimfwc-location-dropdown', (e) => {
                this.handleLocationChange($(e.target).val());
            });
        }

        handleLocationChange(location) {
            if (!location || this.isSwitching) return;

            const previousLocation = this.getCurrentStoreLocation();

            if (previousLocation && String(previousLocation) === String(location)) {
                return;
            }

            const allowMixed = false;
            const behavior = 'update_cart';
            const shouldUpdateCart = true;

            const proceed = () => this.performLocationSwitch(location, previousLocation);

            if (!shouldUpdateCart) {
                proceed();
                return;
            }

            this.checkCartHasProducts()
                .then((cartHasProducts) => {
                    if (!cartHasProducts) {
                        proceed();
                        return;
                    }

                    const shouldPrompt = behavior === 'prompt_user' || !!this.settings.location_change_notification;
                    if (shouldPrompt) {
                        const message =
                            this.settings.location_notification_text ||
                            'Do you want to change the store location? Your cart will be updated.';
                        const confirmed = window.confirm(message);
                        if (!confirmed) {
                            this.resetSelectorUI(previousLocation);
                            return;
                        }
                    }

                    proceed();
                })
                .catch(() => {
                    proceed();
                });
        }

        hideLoadingState() {
            $('.mulopimfwc-product-location-selector').removeClass('loading');
            $('.mulopimfwc-loading').removeClass('mulopimfwc-loading');
            $('.mulopimfwc-loader').remove();
        }

        setLoadingState(isLoading) {
            const $selector = $('.mulopimfwc-product-location-selector');
            if (isLoading) {
                $selector.addClass('loading');
            } else {
                this.hideLoadingState();
            }
        }

        hideLoadingState() {
            $('.mulopimfwc-product-location-selector').removeClass('loading');
            $('.mulopimfwc-loading').removeClass('mulopimfwc-loading');
            $('.mulopimfwc-loader').remove();
        }

        performLocationSwitch(location, previousLocation) {
            const ajaxUrl = this.settings.ajaxUrl;
            const nonce = this.settings.nonce || '';
            const behavior = 'update_cart';
            const requiresCleanup = true;

            const fallbackReload = () => {
                this.setLocationCookie(location);
                // Perform a hard reload, ensuring any potential form data is not resubmitted
                if (window.location.href.indexOf('//' + window.location.host + window.location.pathname) !== -1) {
                    // Remove query and hash for a clean reload to prevent form resubmission prompt
                    window.location.replace(window.location.origin + window.location.pathname);
                } else {
                    // Fallback: full page reload (hard reload)
                    window.location.reload(true);
                }
            };

            if (!ajaxUrl) {
                fallbackReload();
                return;
            }

            this.isSwitching = true;
            this.setLoadingState(true);

            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mulopimfwc_switch_location',
                    nonce: nonce,
                    location: location
                },
                success: (response) => {
                    this.isSwitching = false;

                    if (response && response.success) {
                        const removedItems = response.data && response.data.removed_items ? response.data.removed_items : [];

                        if (removedItems && removedItems.length) {
                            alert(
                                'The following items were removed because they are not available at the selected location: ' +
                                removedItems.join(', ')
                            );
                        }

                        this.setLocationCookie(location);
                        // Perform a hard reload, ensuring any potential form data is not resubmitted
                        if (window.location.href.indexOf('//' + window.location.host + window.location.pathname) !== -1) {
                            // Remove query and hash for a clean reload to prevent form resubmission prompt
                            window.location.replace(window.location.origin + window.location.pathname);
                        } else {
                            // Fallback: full page reload (hard reload)
                            window.location.reload(true);
                        }
                        return;
                    }

                    this.resetSelectorUI(previousLocation);
                    this.setLoadingState(false);
                    alert((response && response.data && response.data.message) || 'Unable to change location. Please try again.');
                },
                error: () => {
                    this.isSwitching = false;
                    this.resetSelectorUI(previousLocation);
                    this.setLoadingState(false);
                    if (requiresCleanup) {
                        alert('Unable to change location right now. Please try again.');
                        return;
                    }

                    fallbackReload();
                }
            });
        }

        checkCartHasProducts() {
            const ajaxUrl = this.settings.ajaxUrl;

            return new Promise((resolve) => {
                if (!ajaxUrl) {
                    resolve(false);
                    return;
                }

                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: { action: 'check_cart_products' },
                    success: (response) => {
                        const hasProducts = response && response.success && response.data
                            ? !!response.data.cartHasProducts
                            : false;
                        resolve(hasProducts);
                    },
                    error: () => resolve(false)
                });
            });
        }

        getCurrentStoreLocation() {
            const cookieLocation = this.getCookie('mulopimfwc_store_location');
            if (cookieLocation) return cookieLocation;

            return '';
        }

        resetSelectorUI(previousLocation) {
            const targetLocation = previousLocation || this.getCurrentStoreLocation();

            const $buttons = $('.mulopimfwc-location-button');
            if ($buttons.length) {
                $buttons.removeClass('active button-primary btn-primary').addClass('button-secondary btn-secondary plugincy-btn-secondary');
                const $buttonToActivate = $buttons.filter(`[data-location="${targetLocation}"]`);
                if ($buttonToActivate.length) {
                    $buttonToActivate.addClass('active button-primary btn-primary').removeClass('button-secondary btn-secondary plugincy-btn-secondary');
                }
            }

            const $radios = $('.mulopimfwc-location-checkbox');
            if ($radios.length) {
                $radios.prop('checked', false);
                $radios.filter(`[value="${targetLocation}"]`).prop('checked', true);
            }

            const $dropdown = $('.mulopimfwc-location-dropdown');
            if ($dropdown.length) {
                $dropdown.val(targetLocation);
            }
        }

        setLocationCookie(location) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 days
            document.cookie = `mulopimfwc_store_location=${location}; expires=${expires.toUTCString()}; path=/; samesite=lax`;
        }

        getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1, c.length);
                }
                if (c.indexOf(nameEQ) === 0) {
                    return c.substring(nameEQ.length, c.length);
                }
            }
            return null;
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        new LocationSelector();
    });

})(jQuery);