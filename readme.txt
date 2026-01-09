=== Job Listings Search & Filter for Elementor ===
Contributors: jakirmithunbd, codeconfig
Tags: elementor, wp job manager, jobs, loop grid, dynamic tags, job search, job filter
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Seamlessly integrate WP Job Manager with Elementor Loop Grids and Dynamic Tags for powerful job listing displays.

== Description ==

**Job Listings Search & Filter for Elementor** bridges the gap between WP Job Manager and Elementor, allowing you to create stunning job listing pages with Elementor's Loop Grid and access job data through Dynamic Tags.

= Key Features =

* **Job Search Filter Widget** - Advanced filtering with multiple layout options
* **Dynamic Tags** - Display job data anywhere in Elementor
* **Loop Grid Integration** - Automatic query modification for job listings
* **Filter Options Include:**
  * Keyword search
  * Location (radio buttons with actual job locations)
  * Job Type/Positions (radio buttons)
  * Job Categories (multiple checkbox selection)
  * Date Posted (today, 7 days, 14 days, 30 days)
  * Remote Position filter
  * Featured Jobs filter
  * Hide Filled Positions filter
* **Two Layout Styles:**
  * Sidebar (vertical) layout with radio/checkbox filters
  * Inline (horizontal) layout for header placements
* **AJAX Search** - Optional instant results without page reload
* **Customizable Labels** - Change filter labels to match your brand
* **Auto-submit Filters** - Radio buttons and checkboxes trigger search automatically
* **Loop Grid Preloader** - Loading indicator appears only over job listings

= Dynamic Tags Available =

* Job Title
* Job Type
* Job Location
* Company Name
* Company Logo
* Company Tagline
* Job Date
* Job Salary
* Job Description
* Application Button/Email

= Requirements =

* WordPress 5.8 or higher
* PHP 7.4 or higher
* Elementor (free version)
* WP Job Manager plugin

= Pro Features (Elementor Pro Required) =

* Loop Grid with job listing queries
* Advanced dynamic content placement

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/job-listings-search-filter` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Make sure you have both Elementor and WP Job Manager plugins installed and activated
4. Create a new page with Elementor
5. Add the "Job Search Filter" widget from the widget panel
6. Add a "Loop Grid" widget (Elementor Pro) and set the source to "job_listing"
7. Set the Loop Grid Query ID to "job_listings"
8. Design your job listing template

== Frequently Asked Questions ==

= Do I need Elementor Pro? =

The Job Search Filter widget works with free Elementor. However, to use Loop Grid for displaying job listings, you need Elementor Pro.

= How do I display job listings? =

1. Add the Loop Grid widget to your page
2. Set Source to "job_listing" post type
3. Set Query ID to "job_listings"
4. Design your template using Dynamic Tags

= Can I customize the filter labels? =

Yes! Each filter section has a customizable label in the widget settings under the Content tab.

= Does it work with AJAX? =

Yes, you can choose between AJAX (instant results) and Page Reload in the widget settings.

= How do I add the filter to my sidebar? =

Use the "Sidebar (Vertical)" layout option in the widget settings. It's designed specifically for sidebar placements.

= Can users select multiple job categories? =

Yes, the Contract Types filter uses checkboxes, allowing multiple selections. They are combined with a comma-separated format.

= What if I want to filter by remote positions only? =

Enable the "Show Contract Types Filter" and check the "Remote Position" checkbox. It filters jobs with `_remote_position` meta field set to 1.

== Sourcecode ==
Source Code: [github](https://github.com/jakirmithunbd/job-listings-search-filter)

== Screenshots ==

1. Job Search Filter widget in sidebar layout with all filter options
2. Inline search layout for header placement
3. Widget settings panel showing customization options
4. Loop Grid displaying filtered job listings
5. Dynamic Tags available for job data

== Changelog ==

= 1.0.0 =
* Initial release
* Job Search Filter widget with sidebar and inline layouts
* Radio button filters for Location, Job Type, and Date Posted
* Checkbox filters for Categories, Remote Position, Featured Jobs, and Filled Positions
* 10+ Dynamic Tags for job data
* Elementor Loop Grid integration with Query ID system
* AJAX search support
* Auto-submit on filter changes
* Customizable filter labels
* Loop Grid preloader with loading indicator

== Upgrade Notice ==

= 1.0.0 =
Initial release of Elementor WP Job Manager Integration.

== Additional Info ==

= Support =

For support, please use the WordPress.org support forums.

= Documentation =

Detailed documentation is available in the plugin's README.md file.

= Contributing =

Contributions are welcome! Visit the plugin repository for contribution guidelines.
