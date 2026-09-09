<script>
    window.addEventListener('load', function() {
        var trackedEvents = window.gtmTrackedEvents = window.gtmTrackedEvents || {};

        // Debounce to prevent duplicate event firing within 2s for the same (event, productId) pair.
        // Guards against rapid double-clicks and multiple dispatchers of ecommerce.cart.added
        // (front-ecommerce.js, up-sale-bundle.js, front-upsale-crosssale.js all dispatch this event).
        function isEventTracked(eventName, productId) {
            var key = eventName + '_' + (productId || 'global');
            var now = Date.now();
            if (trackedEvents[key] && (now - trackedEvents[key]) < 2000) {
                return true;
            }
            trackedEvents[key] = now;
            return false;
        }

        // Match the server-side push mechanism (see GoogleTagManager::shouldUseGtag).
        // For GTM-container / custom setups we push a single flat object so client-side
        // events (add_to_cart, etc.) stay consistent with the server-rendered ones and
        // never get the GA4 eventModel wrapper - which previously produced a duplicate,
        // double-shaped add_to_cart push alongside the flat one.
        var gtmUseGtag = @json(app(\Botble\Ecommerce\AdsTracking\GoogleTagManager::class)->shouldUseGtag());

        function pushEvent(eventName, eventData) {
            if (gtmUseGtag && typeof gtag === 'function') {
                gtag('event', eventName, eventData);
                return;
            }

            window.dataLayer = window.dataLayer || [];

            var dataLayerEvent = {
                event: eventName
            };
            for (var key in eventData) {
                if (eventData.hasOwnProperty(key)) {
                    dataLayerEvent[key] = eventData[key];
                }
            }
            window.dataLayer.push(dataLayerEvent);
        }

        function formatItemCategories(categories) {
            if (!categories) {
                return {};
            }

            var formattedCategories = {};

            categories.split(',').forEach(function (key, index) {
                var keyName = index === 0 ? 'item_category': `item_category${index}`;
                formattedCategories[keyName] = key;
            });

            return formattedCategories;
        }

        function getProductDataFromElement($element) {
            var $productElement = $element.data('product-id') ? $element : $element.closest('[data-product-id]');

            if (!$productElement.length || !$productElement.data('product-id')) {
                return null;
            }

            var price = parseFloat($productElement.data('product-price')) || 0;
            var categories = formatItemCategories($productElement.data('product-categories'));

            return {
                item_id: $productElement.data('product-id'),
                item_name: $productElement.data('product-name') || '',
                price: price,
                item_brand: $productElement.data('product-brand') || '',
                ...categories,
            };
        }

        document.addEventListener('ecommerce.cart.added', function(e) {
            var detail = e.detail;
            var productData = null;

            if (detail.extraData) {
                productData = detail.extraData;
            } else {
                var element = $(detail.element);
                productData = getProductDataFromElement(element);

                if (!productData) {
                    return;
                }

                var quantity = 1;
                var $form = element.closest('form');
                if ($form.length) {
                    var qtyInput = $form.find('input[name="qty"]');
                    if (qtyInput.length) {
                        quantity = parseInt(qtyInput.val()) || 1;
                    }
                }

                productData.quantity = quantity;
            }

            if (isEventTracked('add_to_cart', productData.item_id)) {
                return;
            }

            pushEvent('add_to_cart', {
                currency: '{{ get_application_currency()->title }}',
                value: productData.price * productData.quantity,
                items: [productData],
            });
        });

        document.addEventListener('ecommerce.cart.removed', function(e) {
            var detail = e.detail;
            var productData = null;

            if (detail.extraData) {
                productData = detail.extraData;
            } else {
                var element = $(detail.element);
                productData = getProductDataFromElement(element);

                if (!productData) {
                    return;
                }

                var quantity = parseInt(element.data('product-quantity')) || 1;
                productData.quantity = quantity;
            }

            if (isEventTracked('remove_from_cart', productData.item_id)) {
                return;
            }

            pushEvent('remove_from_cart', {
                currency: '{{ get_application_currency()->title }}',
                value: productData.price * productData.quantity,
                items: [productData],
            });
        });

        document.addEventListener('ecommerce.wishlist.added', function(e) {
            var detail = e.detail;
            var productData = null;

            if (detail.extraData) {
                productData = detail.extraData;
            } else {
                var element = $(detail.element);
                productData = getProductDataFromElement(element);

                if (!productData) {
                    return;
                }

                productData.quantity = 1;
            }

            if (isEventTracked('add_to_wishlist', productData.item_id)) {
                return;
            }

            pushEvent('add_to_wishlist', {
                currency: '{{ get_application_currency()->title }}',
                value: productData.price,
                items: [productData],
            });
        });

        document.addEventListener('ecommerce.wishlist.removed', function(e) {
            var detail = e.detail;
            var productData = null;

            if (detail.extraData) {
                productData = detail.extraData;
            } else {
                var element = $(detail.element);
                productData = getProductDataFromElement(element);

                if (!productData) {
                    return;
                }

                productData.quantity = 1;
            }

            if (isEventTracked('remove_from_wishlist', productData.item_id)) {
                return;
            }

            pushEvent('remove_from_wishlist', {
                currency: '{{ get_application_currency()->title }}',
                value: productData.price,
                items: [productData],
            });
        });

        document.addEventListener('ecommerce.compare.added', function(e) {
            var detail = e.detail;
            var productData = null;

            if (detail.extraData) {
                productData = detail.extraData;
            } else {
                var element = $(detail.element);
                productData = getProductDataFromElement(element);

                if (!productData) {
                    return;
                }

                productData.quantity = 1;
            }

            if (isEventTracked('add_to_compare', productData.item_id)) {
                return;
            }

            pushEvent('add_to_compare', {
                currency: '{{ get_application_currency()->title }}',
                value: productData.price,
                items: [productData],
            });
        });

        document.addEventListener('ecommerce.compare.removed', function(e) {
            var detail = e.detail;
            var productData = null;

            if (detail.extraData) {
                productData = detail.extraData;
            } else {
                var element = $(detail.element);
                productData = getProductDataFromElement(element);

                if (!productData) {
                    return;
                }

                productData.quantity = 1;
            }

            if (isEventTracked('remove_from_compare', productData.item_id)) {
                return;
            }

            pushEvent('remove_from_compare', {
                currency: '{{ get_application_currency()->title }}',
                value: productData.price,
                items: [productData],
            });
        });

        $(document).on('click', '[data-bb-toggle="product-link"], .product-item a, .product-card a', function (e) {
            var currentTarget = $(e.currentTarget);
            var productElement = currentTarget.closest('[data-product-id]').length
                ? currentTarget.closest('[data-product-id]')
                : currentTarget;

            if (!productElement.data('product-id')) {
                return;
            }

            var price = productElement.data('product-price');
            var categories = formatItemCategories(productElement.data('product-categories'));
            var listName = productElement.data('product-list-name') || 'Product List';
            var listIndex = productElement.data('product-list-index') || 0;

            pushEvent('select_item', {
                item_list_id: listName.toLowerCase().replace(/\s+/g, '_'),
                item_list_name: listName,
                items: [
                    {
                        item_id: productElement.data('product-id'),
                        item_name: productElement.data('product-name'),
                        price: price,
                        index: listIndex,
                        item_brand: productElement.data('product-brand'),
                        ...categories,
                    },
                ],
            });
        });

        $(document).on('bb-promotion-view', function (e, promotionData) {
            if (!promotionData || !promotionData.items) {
                return;
            }

            pushEvent('view_promotion', {
                promotion_id: promotionData.id || '',
                promotion_name: promotionData.name || '',
                creative_name: promotionData.creative || '',
                creative_slot: promotionData.slot || '',
                location_id: promotionData.location || '',
                items: promotionData.items
            });
        });

        $(document).on('click', '[data-bb-toggle="promotion-link"]', function (e) {
            var currentTarget = $(e.currentTarget);

            pushEvent('select_promotion', {
                promotion_id: currentTarget.data('promotion-id') || '',
                promotion_name: currentTarget.data('promotion-name') || '',
                creative_name: currentTarget.data('promotion-creative') || '',
                creative_slot: currentTarget.data('promotion-slot') || '',
                location_id: currentTarget.data('promotion-location') || ''
            });
        });

        var scrollDepths = [25, 50, 75, 90];
        var scrolledDepths = [];

        $(window).on('scroll', function() {
            var scrollTop = $(window).scrollTop();
            var docHeight = $(document).height();
            var winHeight = $(window).height();
            var scrollPercent = Math.round((scrollTop / (docHeight - winHeight)) * 100);

            scrollDepths.forEach(function(depth) {
                if (scrollPercent >= depth && scrolledDepths.indexOf(depth) === -1) {
                    scrolledDepths.push(depth);

                    pushEvent('scroll', {
                        percent_scrolled: depth
                    });
                }
            });
        });

        var startTime = Date.now();
        var timeEngagementThresholds = [10, 30, 60, 120, 180];
        var firedThresholds = [];

        setInterval(function() {
            var timeOnPage = Math.round((Date.now() - startTime) / 1000);

            timeEngagementThresholds.forEach(function(threshold) {
                if (timeOnPage >= threshold && firedThresholds.indexOf(threshold) === -1) {
                    firedThresholds.push(threshold);

                    pushEvent('user_engagement', {
                        engagement_time_msec: threshold * 1000
                    });
                }
            });
        }, 5000);

        @if(session('ecommerce_customer_registered'))
        pushEvent('sign_up', {
            method: 'email'
        });
        @endif

        $(document).on('ajaxComplete', function(event, xhr, settings) {
            if (settings.url && settings.url.includes('ajax/search')) {
                var searchInput = $('.bb-form-quick-search input[name="q"]');
                var searchTerm = searchInput.val();

                if (searchTerm) {
                    setTimeout(function() {
                        var resultCount = $('.bb-quick-search-results .bb-quick-search-item').length;

                        pushEvent('search', {
                            search_term: searchTerm,
                            result_count: resultCount
                        });
                    }, 100);
                }
            }
        });

        @if(request()->routeIs('public.products') && request('q'))
        (function() {
            var productItems = document.querySelectorAll('.product-item, .product-card, [data-product-id]');
            pushEvent('search', {
                search_term: @json(BaseHelper::stringify(request('q'))),
                result_count: productItems.length
            });
        })();
        @endif

    });
</script>
