(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Collapsible filter sections
        $('.wpjm-collapsible-header').on('click', function() {
            var $section = $(this).closest('.wpjm-collapsible-section');
            var $content = $section.find('.wpjm-collapsible-content');
            var $icon = $(this).find('.wpjm-collapse-icon');
            
            if ($content.is(':visible')) {
                $content.slideUp(250);
                $section.removeClass('is-open').addClass('is-closed');
                $icon.addClass('collapsed');
            } else {
                $content.slideDown(250);
                $section.removeClass('is-closed').addClass('is-open');
                $icon.removeClass('collapsed');
            }
        });
        
        // Auto-submit on radio/checkbox change
        $('.wpjm-radio-option input[type="radio"], .wpjm-checkbox-option input[type="checkbox"]').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Auto-submit on select change
        $('.wpjm-filter-select').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Handle filter removal
        $('.wpjm-remove-filter').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var param = $(this).data('param');
            var value = $(this).data('value');
            var currentUrl = new URL(window.location.href);
            
            // Handle multiple parameters (for hour range)
            if (param && param.includes(',')) {
                var params = param.split(',');
                params.forEach(function(p) {
                    currentUrl.searchParams.delete(p.trim());
                });
            } else if (value) {
                // Remove specific value from array parameter
                var currentValues = currentUrl.searchParams.get(param);
                if (currentValues) {
                    var valuesArray = currentValues.split(',').filter(function(v) {
                        return v !== value;
                    });
                    
                    if (valuesArray.length > 0) {
                        currentUrl.searchParams.set(param, valuesArray.join(','));
                    } else {
                        currentUrl.searchParams.delete(param);
                    }
                }
            } else {
                // Remove entire parameter
                currentUrl.searchParams.delete(param);
            }
            
            window.location.href = currentUrl.toString();
        });
        
        // Handle clear all filters
        $('.wpjm-clear-all').on('click', function(e) {
            e.preventDefault();
            var baseUrl = window.location.href.split('?')[0];
            window.location.href = baseUrl;
        });
        
        // Handle AJAX search forms
        $('.wpjm-ajax-search-form').each(function() {
            var $form = $(this);
            var widgetId = $form.data('widget-id');
            var searchType = $form.data('search-type') || 'ajax';
            
            $form.on('submit', function(e) {
                e.preventDefault();
                
                var $widget = $form.closest('.wpjm-filter-widget');
                
                // Find the Loop Grid container or create a results container
                var $loopGrid = $('.elementor-widget-loop-grid').first();
                if ($loopGrid.length === 0) {
                    $loopGrid = $('.elementor-loop-container').first();
                }
                
                // If no Loop Grid found, create a results container after the widget
                if ($loopGrid.length === 0) {
                    $loopGrid = $widget.next('.wpjm-job-results-container');
                    if ($loopGrid.length === 0) {
                        $loopGrid = $('<div class="wpjm-job-results-container"></div>');
                        $widget.after($loopGrid);
                    }
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
                    date_posted: $form.find('input[name="date_posted"]:checked').val() || ''
                };
                
                // Get checked checkboxes for job_type
                var jobTypes = [];
                $form.find('input[name="job_type[]"]:checked').each(function() {
                    jobTypes.push($(this).val());
                });
                if (jobTypes.length > 0) {
                    formData.job_type = jobTypes.join(',');
                }
                
                // Get checked checkboxes for location
                var locations = [];
                $form.find('input[name="search_location[]"]:checked').each(function() {
                    locations.push($(this).val());
                });
                if (locations.length > 0) {
                    formData.search_location = locations.join(',');
                }
                
                // Get checked checkboxes for category
                var categories = [];
                $form.find('input[name="category[]"]:checked').each(function() {
                    categories.push($(this).val());
                });
                if (categories.length > 0) {
                    formData.category = categories.join(',');
                }
                
                // Get checked checkboxes for working hours
                var workingHours = [];
                $form.find('input[name="working_hours[]"]:checked').each(function() {
                    workingHours.push($(this).val());
                });
                if (workingHours.length > 0) {
                    formData.working_hours = workingHours.join(',');
                }
                
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
                
                // Get hour range values
                var minHours = $form.find('input[name="min_hours"]').val();
                var maxHours = $form.find('input[name="max_hours"]').val();
                if (minHours) {
                    formData.min_hours = minHours;
                }
                if (maxHours) {
                    formData.max_hours = maxHours;
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
                console.error('wpjmSearchData not defined');
                $overlay.fadeOut(200);
                return;
            }
            
            var $widget = $('.wpjm-filter-widget').first();
            
            var ajaxData = {
                action: 'wpjm_search_jobs',
                nonce: wpjmSearchData.nonce,
                query_id: wpjmSearchData.targetQueryId || 'job_listings',
                search_keywords: formData.search_keywords || '',
                search_location: formData.search_location || '',
                job_type: formData.job_type || '',
                category: formData.category || '',
                working_hours: formData.working_hours || '',
                date_posted: formData.date_posted || '',
                remote_position: formData.remote_position || '',
                featured: formData.featured || '',
                hide_filled: formData.hide_filled || '',
                min_hours: formData.min_hours || '',
                max_hours: formData.max_hours || ''
            };
            
            console.log('Sending AJAX request:', ajaxData);
            
            $.ajax({
                url: wpjmSearchData.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    console.log('AJAX Response:', response);
                    
                    if (response.success) {
                        var data = response.data;
                        console.log('Found posts:', data.found_posts);
                        
                        // Update URL with filter parameters without reloading
                        var currentUrl = new URL(window.location.href);
                        Object.keys(formData).forEach(function(key) {
                            if (formData[key]) {
                                currentUrl.searchParams.set(key, formData[key]);
                            } else {
                                currentUrl.searchParams.delete(key);
                            }
                        });
                        window.history.pushState({}, '', currentUrl.toString());
                        
                        // Reload page to refresh Elementor Loop Grid with new query parameters
                        setTimeout(function() {
                            location.reload();
                        }, 100);
                        
                    } else {
                        $overlay.fadeOut(200);
                        console.error('AJAX search failed:', response);
                        alert('Search failed. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error, xhr);
                    $overlay.fadeOut(200);
                    alert('An error occurred. Please try again.');
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
    
    // Real-time selected options display
    function updateSelectedOptionsDisplay() {
        var $widget = $('.wpjm-filter-widget');
        var $form = $widget.find('.wpjm-filter-form');
        var $selectedBox = $widget.find('.wpjm-selected-filters-box');
        
        if ($selectedBox.length === 0) {
            return; // Exit if selected box doesn't exist
        }
        
        var $tagsContainer = $selectedBox.find('.wpjm-selected-filters-tags');
        $tagsContainer.empty();
        
        var hasSelections = false;
        
        // Get search keywords
        var keywords = $form.find('input[name="search_keywords"]').val();
        if (keywords && keywords.trim() !== '') {
            hasSelections = true;
            $tagsContainer.append(
                '<span class="wpjm-filter-tag" style="background: white; padding: 8px 12px; border-radius: 20px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; color: #333; font-weight: 500;">' +
                escapeHtml(keywords) +
                '<button type="button" class="wpjm-clear-tag" data-type="keywords" style="background: none; border: none; cursor: pointer; font-size: 18px; line-height: 1; color: #666; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center;">×</button>' +
                '</span>'
            );
        }
        
        // Get checked job types
        $form.find('input[name="job_type[]"]:checked').each(function() {
            hasSelections = true;
            var label = $(this).closest('label').find('span').clone();
            label.find('.wpjm-filter-count').remove();
            var labelText = label.text().trim();
            
            $tagsContainer.append(
                '<span class="wpjm-filter-tag" data-param="job_type" data-value="' + escapeHtml($(this).val()) + '" style="background: white; padding: 8px 12px; border-radius: 20px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; color: #333; font-weight: 500;">' +
                escapeHtml(labelText) +
                '<button type="button" class="wpjm-clear-tag" data-type="checkbox" data-name="job_type[]" data-value="' + escapeHtml($(this).val()) + '" style="background: none; border: none; cursor: pointer; font-size: 18px; line-height: 1; color: #666; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center;">×</button>' +
                '</span>'
            );
        });
        
        // Get checked locations
        $form.find('input[name="search_location[]"]:checked').each(function() {
            hasSelections = true;
            var label = $(this).closest('label').find('span').clone();
            label.find('.wpjm-filter-count').remove();
            var labelText = label.text().trim();
            
            $tagsContainer.append(
                '<span class="wpjm-filter-tag" data-param="search_location" data-value="' + escapeHtml($(this).val()) + '" style="background: white; padding: 8px 12px; border-radius: 20px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; color: #333; font-weight: 500;">' +
                escapeHtml(labelText) +
                '<button type="button" class="wpjm-clear-tag" data-type="checkbox" data-name="search_location[]" data-value="' + escapeHtml($(this).val()) + '" style="background: none; border: none; cursor: pointer; font-size: 18px; line-height: 1; color: #666; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center;">×</button>' +
                '</span>'
            );
        });
        
        // Get checked categories
        $form.find('input[name="category[]"]:checked').each(function() {
            hasSelections = true;
            var label = $(this).closest('label').find('span').clone();
            label.find('.wpjm-filter-count').remove();
            var labelText = label.text().trim();
            
            $tagsContainer.append(
                '<span class="wpjm-filter-tag" data-param="category" data-value="' + escapeHtml($(this).val()) + '" style="background: white; padding: 8px 12px; border-radius: 20px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; color: #333; font-weight: 500;">' +
                escapeHtml(labelText) +
                '<button type="button" class="wpjm-clear-tag" data-type="checkbox" data-name="category[]" data-value="' + escapeHtml($(this).val()) + '" style="background: none; border: none; cursor: pointer; font-size: 18px; line-height: 1; color: #666; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center;">×</button>' +
                '</span>'
            );
        });
        
        // Get checked working hours
        $form.find('input[name="working_hours[]"]:checked').each(function() {
            hasSelections = true;
            var label = $(this).closest('label').find('span').clone();
            label.find('.wpjm-filter-count').remove();
            var labelText = label.text().trim();
            
            $tagsContainer.append(
                '<span class="wpjm-filter-tag" data-param="working_hours" data-value="' + escapeHtml($(this).val()) + '" style="background: white; padding: 8px 12px; border-radius: 20px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; color: #333; font-weight: 500;">' +
                escapeHtml(labelText) +
                '<button type="button" class="wpjm-clear-tag" data-type="checkbox" data-name="working_hours[]" data-value="' + escapeHtml($(this).val()) + '" style="background: none; border: none; cursor: pointer; font-size: 18px; line-height: 1; color: #666; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center;">×</button>' +
                '</span>'
            );
        });
        
        // Show or hide the box
        if (hasSelections) {
            $selectedBox.slideDown(200);
        } else {
            $selectedBox.slideUp(200);
        }
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Update on checkbox/input changes
    $(document).on('change', '.wpjm-filter-form input[type="checkbox"], .wpjm-filter-form input[type="radio"]', function() {
        updateSelectedOptionsDisplay();
    });
    
    $(document).on('input', '.wpjm-filter-form input[name="search_keywords"]', function() {
        updateSelectedOptionsDisplay();
    });
    
    // Handle tag removal
    $(document).on('click', '.wpjm-clear-tag', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var type = $(this).data('type');
        
        if (type === 'keywords') {
            $('.wpjm-filter-form input[name="search_keywords"]').val('');
        } else if (type === 'checkbox') {
            var name = $(this).data('name');
            var value = $(this).data('value');
            $('.wpjm-filter-form input[name="' + name + '"][value="' + value + '"]').prop('checked', false);
        }
        
        updateSelectedOptionsDisplay();
    });
    
    // Hour Range Slider functionality (Custom Slider)
    function initHourRangeSlider() {
        var $wrapper = $('.wpjm-hour-slider-wrapper');
        var $minInput = $('.wpjm-hour-min');
        var $maxInput = $('.wpjm-hour-max');
        
        if ($wrapper.length === 0) {
            return; // Exit if slider doesn't exist
        }
        
        var minValue = parseInt($wrapper.attr('data-min')) || 0;
        var maxValue = parseInt($wrapper.attr('data-max')) || 40;
        var currentMin = parseInt($wrapper.attr('data-value-min')) || minValue;
        var currentMax = parseInt($wrapper.attr('data-value-max')) || maxValue;
        
        var $minHandle = $wrapper.find('.wpjm-hour-slider-handle-min');
        var $maxHandle = $wrapper.find('.wpjm-hour-slider-handle-max');
        var $range = $wrapper.find('.wpjm-hour-slider-range');
        
        var isDragging = false;
        var activeHandle = null;
        var isInitializing = true;
        
        function updateUI() {
            var minPercent = ((currentMin - minValue) / (maxValue - minValue)) * 100;
            var maxPercent = ((currentMax - minValue) / (maxValue - minValue)) * 100;
            
            $minHandle.css('left', minPercent + '%');
            $maxHandle.css('left', maxPercent + '%');
            
            $range.css({
                'left': minPercent + '%',
                'width': (maxPercent - minPercent) + '%'
            });
            
            $minInput.val(currentMin);
            $maxInput.val(currentMax);
            
            $wrapper.attr('data-value-min', currentMin);
            $wrapper.attr('data-value-max', currentMax);
        }
        
        function getValueFromPosition(clientX) {
            var rect = $wrapper[0].getBoundingClientRect();
            var percent = (clientX - rect.left) / rect.width;
            percent = Math.max(0, Math.min(1, percent));
            var value = Math.round(minValue + (percent * (maxValue - minValue)));
            return value;
        }
        
        function handleMove(clientX) {
            if (!isDragging || !activeHandle) return;
            
            var value = getValueFromPosition(clientX);
            
            if (activeHandle === 'min') {
                currentMin = Math.min(value, currentMax);
            } else {
                currentMax = Math.max(value, currentMin);
            }
            
            updateUI();
        }
        
        function handleEnd() {
            if (isDragging && !isInitializing) {
                isDragging = false;
                activeHandle = null;
                $minHandle.removeClass('dragging');
                $maxHandle.removeClass('dragging');
                
                // Submit form after dragging ends
                setTimeout(function() {
                    $wrapper.closest('form').submit();
                }, 300);
            }
        }
        
        // Mouse events
        $minHandle.on('mousedown', function(e) {
            e.preventDefault();
            isDragging = true;
            activeHandle = 'min';
            $(this).addClass('dragging');
        });
        
        $maxHandle.on('mousedown', function(e) {
            e.preventDefault();
            isDragging = true;
            activeHandle = 'max';
            $(this).addClass('dragging');
        });
        
        $(document).on('mousemove.hourslider', function(e) {
            if (isDragging) {
                handleMove(e.clientX);
            }
        });
        
        $(document).on('mouseup.hourslider', function() {
            if (isDragging) {
                handleEnd();
            }
        });
        
        // Touch events
        $minHandle.on('touchstart', function(e) {
            e.preventDefault();
            isDragging = true;
            activeHandle = 'min';
            $(this).addClass('dragging');
        });
        
        $maxHandle.on('touchstart', function(e) {
            e.preventDefault();
            isDragging = true;
            activeHandle = 'max';
            $(this).addClass('dragging');
        });
        
        $(document).on('touchmove.hourslider', function(e) {
            if (isDragging && e.originalEvent.touches.length > 0) {
                handleMove(e.originalEvent.touches[0].clientX);
            }
        });
        
        $(document).on('touchend.hourslider', function() {
            if (isDragging) {
                handleEnd();
            }
        });
        
        // Click on track to move nearest handle
        $wrapper.on('click', function(e) {
            if ($(e.target).hasClass('wpjm-hour-slider-handle')) {
                return;
            }
            
            var value = getValueFromPosition(e.clientX);
            var distToMin = Math.abs(value - currentMin);
            var distToMax = Math.abs(value - currentMax);
            
            if (distToMin < distToMax) {
                currentMin = Math.min(value, currentMax);
            } else {
                currentMax = Math.max(value, currentMin);
            }
            
            updateUI();
            
            if (!isInitializing) {
                setTimeout(function() {
                    $wrapper.closest('form').submit();
                }, 300);
            }
        });
        
        // Initialize UI
        updateUI();
        
        // Allow submissions after initialization
        setTimeout(function() {
            isInitializing = false;
        }, 500);
    }
    
    // Initialize hour range slider
    initHourRangeSlider();
    
    // Re-initialize after AJAX updates
    $(document).on('wpjm-filters-updated', function() {
        initHourRangeSlider();
    });
    
    // Initial update on page load
    updateSelectedOptionsDisplay();
});
