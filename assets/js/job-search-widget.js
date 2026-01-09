(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Auto-submit on radio/checkbox change
        $('.wpjm-radio-option input[type="radio"], .wpjm-checkbox-option input[type="checkbox"]').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Auto-submit on select change
        $('.wpjm-filter-select').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Handle AJAX search forms
        $('.wpjm-ajax-search-form').each(function() {
            var $form = $(this);
            var widgetId = $form.data('widget-id');
            var searchType = $form.data('search-type') || 'ajax';
            
            $form.on('submit', function(e) {
                e.preventDefault();
                
                var $widget = $form.closest('.wpjm-filter-widget');
                
                // Find the Loop Grid container
                var $loopGrid = $('.elementor-widget-loop-grid').first();
                if ($loopGrid.length === 0) {
                    $loopGrid = $('.elementor-loop-container').first();
                }
                
                // Create or get overlay in Loop Grid
                var $overlay = $loopGrid.find('.wpjm-loop-overlay');
                if ($overlay.length === 0) {
                    $overlay = $('<div class="wpjm-loop-overlay" style="display:none;"><div class="wpjm-preloader"><svg class="wpjm-spinner-large" viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg><p>Loading jobs...</p></div></div>');
                    $loopGrid.css('position', 'relative').append($overlay);
                }
                
                // Get form data
                var formData = {
                    search_keywords: $form.find('[name="search_keywords"]').val() || '',
                    search_location: $form.find('input[name="search_location"]:checked').val() || '',
                    job_type: $form.find('input[name="job_type"]:checked').val() || '',
                    date_posted: $form.find('input[name="date_posted"]:checked').val() || ''
                };
                
                // Get checked checkboxes for category
                var categories = [];
                $form.find('input[name="category[]"]:checked').each(function() {
                    categories.push($(this).val());
                });
                formData.category = categories.join(',');
                
                // Get remote position checkbox
                var remotePosition = $form.find('input[name="remote_position"]:checked').val();
                if (remotePosition) {
                    formData.remote_position = remotePosition;
                }
                
                // Get featured checkbox
                var featured = $form.find('input[name="featured"]:checked').val();
                if (featured) {
                    formData.featured = featured;
                }
                
                // Get hide filled checkbox
                var hideFilled = $form.find('input[name="hide_filled"]:checked').val();
                if (hideFilled) {
                    formData.hide_filled = hideFilled;
                }
                
                // Show loading state in Loop Grid
                $overlay.fadeIn(200);
                
                if (searchType === 'ajax') {
                    performAjaxSearch($overlay, formData);
                } else {
                    // Build URL with parameters
                    var currentUrl = new URL(window.location.href);
                    Object.keys(formData).forEach(function(key) {
                        if (formData[key]) {
                            currentUrl.searchParams.set(key, formData[key]);
                        } else {
                            currentUrl.searchParams.delete(key);
                        }
                    });
                    
                    setTimeout(function() {
                        window.location.href = currentUrl.toString();
                    }, 300);
                }
            });
        });
        
        function performAjaxSearch($overlay, formData) {
            if (typeof wpjmSearchData === 'undefined') {
                $overlay.fadeOut(200);
                return;
            }
            
            var ajaxData = {
                action: 'wpjm_search_jobs',
                nonce: wpjmSearchData.nonce,
                search_keywords: formData.search_keywords,
                search_location: formData.search_location,
                job_type: formData.job_type,
                category: formData.category,
                date_posted: formData.date_posted,
                remote_position: formData.remote_position,
                featured: formData.featured,
                hide_filled: formData.hide_filled,
                page_url: window.location.href.split('?')[0]
            };
            
            $.ajax({
                url: wpjmSearchData.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success && response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        $overlay.fadeOut(200);
                    }
                },
                error: function(xhr, status, error) {
                    $overlay.fadeOut(200);
                }
            });
        }
    });
})(jQuery);

// Toggle filters for inline layout
jQuery(document).ready(function($) {
    $('.wpjm-toggle-filters').on('click', function(e) {
        e.preventDefault();
        var $additionalFilters = $(this).closest('.wpjm-inline-form').find('.wpjm-inline-additional-filters');
        $additionalFilters.slideToggle(300);
        $(this).toggleClass('active');
    });
});
