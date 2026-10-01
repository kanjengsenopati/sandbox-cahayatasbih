<script>
/**
 * DataTables Stale-While-Revalidate (SWR) Engine & Client-Side Session Cache
 * - 0ms instant display from sessionStorage when switching pages/tabs
 * - Silent background auto-sync without blocking UI
 * - Loading indicator ("Mohon Tunggu") only displays when genuinely waiting (first load / cache miss)
 * - Auto-invalidates on mutations (POST, PUT, DELETE, PATCH, forms) & browser refresh
 */
(function($) {
    if (!$ || !$.fn || !$.fn.dataTable) return;

    // Set DataTables global defaults & unified modern loading indicator
    $.fn.dataTable.ext.errMode = 'console';
    var UNIFIED_PROCESSING_HTML = `
        <div class="d-flex flex-column align-items-center justify-content-center">
            <div class="spinner-border text-primary mb-3" style="width: 2.2rem; height: 2.2rem; border-width: 0.22em;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="fs-6 fw-bolder text-gray-800 mb-1">Mohon Tunggu</div>
            <div class="fs-8 text-muted">Sedang memuat data...</div>
        </div>
    `;

    $.extend(true, $.fn.dataTable.defaults, {
        stateSave: true,
        stateDuration: 7200, // 2 hours
        language: {
            processing: UNIFIED_PROCESSING_HTML
        }
    });

    // Toggle active class on wrapper when processing state changes
    $(document).on('processing.dt', function(e, settings, processing) {
        var wrapper = $(e.target).closest('.dataTables_wrapper');
        if (processing) {
            // If table has valid cached data ready, suppress the floating "Mohon Tunggu" popup!
            if (settings && settings._swrServingCache) {
                wrapper.removeClass('dt-processing-active');
                return;
            }
            wrapper.addClass('dt-processing-active');
            var $proc = wrapper.find('div.dataTables_processing');
            if ($proc.length && (!$proc.find('.spinner-border').length || $proc.text().includes('Processing') || $proc.text().includes('Sedang memproses'))) {
                $proc.html(UNIFIED_PROCESSING_HTML);
            }
        } else {
            wrapper.removeClass('dt-processing-active');
        }
    });

    // Helper: Remove all SWR datatables cache entries from sessionStorage
    window.cleanupOldSwrCache = function(prefix) {
        try {
            var p = prefix || 'swr_dt_';
            var keysToRemove = [];
            for (var i = 0; i < sessionStorage.length; i++) {
                var k = sessionStorage.key(i);
                if (k && k.indexOf(p) === 0) {
                    keysToRemove.push(k);
                }
            }
            keysToRemove.forEach(function(k) {
                sessionStorage.removeItem(k);
            });
        } catch(e) {}
    };

    // Auto-invalidate cache on browser page reload (F5 / Ctrl+R)
    try {
        var navEntries = performance.getEntriesByType('navigation');
        if (navEntries.length && navEntries[0].type === 'reload') {
            window.cleanupOldSwrCache();
        }
    } catch(e) {}

    // Auto-invalidate cache when any mutation occurs via jQuery AJAX (POST, PUT, DELETE, PATCH)
    $(document).ajaxComplete(function(e, xhr, settings) {
        if (settings && settings.type && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(settings.type.toUpperCase())) {
            window.cleanupOldSwrCache();
        }
    });

    // Auto-invalidate cache when any mutation occurs via Axios
    if (typeof axios !== 'undefined') {
        axios.interceptors.response.use(function(response) {
            if (response.config && response.config.method && ['post', 'put', 'delete', 'patch'].includes(response.config.method.toLowerCase())) {
                window.cleanupOldSwrCache();
            }
            return response;
        }, function(error) {
            return Promise.reject(error);
        });
    }

    // Auto-invalidate on HTML form submission or logout click
    $(document).on('submit', 'form', function() {
        window.cleanupOldSwrCache();
    });

    $(document).on('click', 'a[href*="logout"], button[formaction*="logout"]', function() {
        window.cleanupOldSwrCache();
    });

    // SWR AJAX Handler Factory
    function createSwrAjax(originalAjax, tableId) {
        return function(data, callback, settings) {
            var $table = $(settings.nTable);
            var actualTableId = tableId || $table.attr('id') || (settings && settings.sTableId) || 'dt';

            // 1. Resolve AJAX settings
            var ajaxOptions = {};
            if (typeof originalAjax === 'string') {
                ajaxOptions.url = originalAjax;
                ajaxOptions.type = 'GET';
            } else if (typeof originalAjax === 'object') {
                ajaxOptions = $.extend(true, {}, originalAjax);
            } else if (typeof originalAjax === 'function') {
                return originalAjax.call(this, data, callback, settings);
            }

            // 2. Resolve request parameters if originalAjax.data was a function or object
            var requestData = $.extend({}, data);
            if (typeof originalAjax === 'object' && typeof originalAjax.data === 'function') {
                originalAjax.data(requestData);
            } else if (typeof originalAjax === 'object' && typeof originalAjax.data === 'object') {
                $.extend(requestData, originalAjax.data);
            }

            // 3. Generate deterministic cache key
            var cacheParams = $.extend(true, {}, requestData);
            delete cacheParams.draw;
            delete cacheParams._;

            var cacheKey = 'swr_dt_' + window.location.pathname + '_' + actualTableId + '_' + JSON.stringify(cacheParams);

            // 4. Check sessionStorage for cached data
            var cachedData = null;
            try {
                var raw = sessionStorage.getItem(cacheKey);
                if (raw) {
                    var parsed = JSON.parse(raw);
                    // 15-minute TTL
                    if (Date.now() - parsed.timestamp < 15 * 60 * 1000 && parsed.payload) {
                        cachedData = parsed.payload;
                    } else {
                        sessionStorage.removeItem(cacheKey);
                    }
                }
            } catch(e) {}

            var renderedFromCache = false;

            // 5. IF CACHE HIT -> RENDER INSTANTLY (0ms!)
            if (cachedData && cachedData.data && Array.isArray(cachedData.data)) {
                renderedFromCache = true;
                if (settings) settings._swrServingCache = true;

                var cachedResponse = $.extend(true, {}, cachedData);
                cachedResponse.draw = data.draw;

                // Immediately suppress processing modal so it never flashes on screen
                var $wrapper = $table.closest('.dataTables_wrapper');
                $wrapper.removeClass('dt-processing-active');

                // Instant draw from cache
                callback(cachedResponse);
            } else {
                if (settings) settings._swrServingCache = false;
            }

            // 6. BACKGROUND AUTO-SYNC (or normal server fetch if no cache)
            var origSuccess = ajaxOptions.success;
            var origError = ajaxOptions.error;

            ajaxOptions.url = ajaxOptions.url || window.location.href;
            ajaxOptions.type = ajaxOptions.type || 'GET';
            ajaxOptions.data = requestData;
            ajaxOptions.dataType = ajaxOptions.dataType || 'json';

            ajaxOptions.success = function(json) {
                if (!json || typeof json !== 'object') {
                    if (settings) settings._swrServingCache = false;
                    if (origSuccess) origSuccess(json);
                    return;
                }

                // Save fresh response into sessionStorage
                try {
                    sessionStorage.setItem(cacheKey, JSON.stringify({
                        timestamp: Date.now(),
                        payload: json
                    }));
                } catch(e) {
                    window.cleanupOldSwrCache();
                }

                if (!renderedFromCache) {
                    // Cache miss / first load -> normal render (dismisses the "Mohon Tunggu" popup)
                    if (settings) settings._swrServingCache = false;
                    callback(json);
                } else {
                    // Cache hit -> check if fresh data differs from cached data
                    var isDifferent = (json.recordsTotal !== cachedData.recordsTotal) ||
                                      (json.recordsFiltered !== cachedData.recordsFiltered) ||
                                      (JSON.stringify(json.data) !== JSON.stringify(cachedData.data));

                    if (isDifferent) {
                        // Silently and smoothly update table without showing "Mohon Tunggu"
                        var $wrapper = $table.closest('.dataTables_wrapper');
                        $wrapper.removeClass('dt-processing-active');
                        json.draw = data.draw;
                        callback(json);
                    }
                    if (settings) settings._swrServingCache = false;
                }

                if (origSuccess) origSuccess(json);
            };

            ajaxOptions.error = function(xhr, status, error) {
                if (settings) settings._swrServingCache = false;
                if (!renderedFromCache) {
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: []
                    });
                    if (origError) origError(xhr, status, error);
                } else {
                    console.warn('[SWR] Background revalidation failed silently:', error);
                }
            };

            // If rendered from cache, keep dt-processing-active off
            if (renderedFromCache) {
                var $wrapper = $table.closest('.dataTables_wrapper');
                $wrapper.removeClass('dt-processing-active');
            }

            // Send background or primary AJAX request
            return $.ajax(ajaxOptions);
        };
    }

    // Wrap $.fn.dataTable and $.fn.DataTable globally
    var origDataTable = $.fn.dataTable;

    function wrapSwrDataTable(options) {
        if (options && options.ajax && options.serverSide !== false && options.swr !== false) {
            options.ajax = createSwrAjax(options.ajax, $(this).attr('id'));
        }
        return origDataTable.apply(this, arguments);
    }

    for (var prop in origDataTable) {
        if (Object.prototype.hasOwnProperty.call(origDataTable, prop)) {
            wrapSwrDataTable[prop] = origDataTable[prop];
        }
    }

    $.fn.dataTable = wrapSwrDataTable;
    $.fn.DataTable = function(opts) {
        return $(this).dataTable(opts).api();
    };

    for (var prop in origDataTable) {
        if (Object.prototype.hasOwnProperty.call(origDataTable, prop)) {
            $.fn.DataTable[prop] = origDataTable[prop];
        }
    }
})(jQuery);
</script>
