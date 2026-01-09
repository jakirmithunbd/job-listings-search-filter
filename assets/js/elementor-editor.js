/**
 * Elementor Editor Script for WPJM Integration
 * Automatically sets Query ID for Loop Grid widgets when Jobs source is selected
 */
(function($) {
    'use strict';
    
    // Wait for Elementor to be ready
    $(window).on('elementor:init', function() {
        // Hook into panel changes
        elementor.channels.editor.on('change', function(controlView, elementView) {
            var controlName = controlView.model.get('name');
            var widgetType = elementView.model.get('widgetType');
            
            // Check if this is a Loop Grid or Posts widget
            if (widgetType === 'loop-grid' || widgetType === 'posts' || widgetType === 'archive-posts') {
                
                // Check if the source control changed
                if (controlName === 'post_query_post_type' || controlName === 'posts_post_type' || controlName === 'source') {
                    var selectedSource = controlView.getControlValue();
                    
                    // If Jobs (job_listing) is selected
                    if (selectedSource === 'job_listing') {
                        // Set the Query ID to 'job_listings'
                        setTimeout(function() {
                            var queryIdControl = elementView.container.settings.get('post_query_query_id') || elementView.container.settings.get('query_id');
                            
                            // Only set if it's empty or default
                            if (!queryIdControl || queryIdControl === '') {
                                // Try both possible control names
                                if (elementView.container.settings.get('post_query_query_id') !== undefined) {
                                    elementView.container.settings.set('post_query_query_id', 'job_listings');
                                } else {
                                    elementView.container.settings.set('query_id', 'job_listings');
                                }
                                
                                // Show a notification
                                if (elementor.notifications && elementor.notifications.showToast) {
                                    elementor.notifications.showToast({
                                        message: 'Query ID set to "job_listings" for job filtering',
                                        type: 'info'
                                    });
                                }
                            }
                        }, 100);
                    }
                }
            }
        });
        
        // Also check on widget initialization
        elementor.hooks.addAction('panel/open_editor/widget', function(panel, model, view) {
            var widgetType = model.get('widgetType');
            
            if (widgetType === 'loop-grid' || widgetType === 'posts' || widgetType === 'archive-posts') {
                var settings = model.get('settings');
                var source = settings.get('post_query_post_type') || settings.get('posts_post_type') || settings.get('source');
                var queryId = settings.get('post_query_query_id') || settings.get('query_id');
                
                // If source is job_listing and query_id is empty, set it
                if (source === 'job_listing' && (!queryId || queryId === '')) {
                    setTimeout(function() {
                        // Try both possible control names
                        if (settings.get('post_query_query_id') !== undefined) {
                            settings.set('post_query_query_id', 'job_listings');
                        } else {
                            settings.set('query_id', 'job_listings');
                        }
                    }, 100);
                }
            }
        });
    });
    
})(jQuery);
