# Job Listings Search & Filter for Elementor

Seamlessly integrate WP Job Manager with Elementor Loop Grids and Dynamic Tags for powerful job listing displays.

## Description

**Job Listings Search & Filter for Elementor** bridges the gap between WP Job Manager and Elementor, allowing you to create stunning job listing pages with Elementor's Loop Grid and access job data through Dynamic Tags.

## Features

### Job Search Filter Widget
- **Multiple Layout Options**: Sidebar (vertical) and Inline (horizontal)
- **Advanced Filtering**:
  - Keyword search
  - Location filter (radio buttons with actual job locations from database)
  - Job Type/Positions filter (radio buttons)
  - Job Categories (multiple checkbox selection)
  - Date Posted filter (Anytime, Today, Last 7/14/30 days)
  - Remote Position filter
  - Featured Jobs filter
  - Hide Filled Positions filter
- **Customizable Labels**: Change filter section labels to match your brand
- **Auto-submit**: Radio buttons and checkboxes trigger search automatically
- **AJAX Support**: Optional instant results without page reload
- **Loop Grid Preloader**: Loading indicator appears only over job listings

### Dynamic Tags
Access job data anywhere in Elementor:
- Job Title
- Job Type
- Job Location
- Company Name
- Company Logo
- Company Tagline
- Job Date
- Job Salary
- Job Description
- Application Button/Email

### Loop Grid Integration
- Automatic query modification for `job_listing` post type
- Query ID system (`job_listings`)
- Filters work seamlessly with Elementor Loop Grid

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Elementor (free version for Dynamic Tags)
- Elementor Pro (for Loop Grid widget)
- WP Job Manager plugin

## Installation

1. Download the plugin
2. Upload to `/wp-content/plugins/` directory
3. Activate through WordPress Admin > Plugins
4. Make sure Elementor and WP Job Manager are installed and activated

## Usage

### Setting Up Job Listing Page

1. **Create a new page** in Elementor
2. **Add Job Search Filter Widget**:
   - Choose layout: Sidebar or Inline
   - Enable desired filters
   - Customize filter labels
   - Set search type: AJAX or Page Reload
3. **Add Loop Grid Widget** (Elementor Pro required):
   - Set Source: `job_listing`
   - Set Query ID: `job_listings`
4. **Design your job template** using Dynamic Tags

### Widget Settings

#### Filter Settings
- **Show Search Field**: Toggle keyword search
- **Show Positions Filter**: Toggle job type radio buttons
- **Show Location Filter**: Toggle location radio buttons
- **Show Contract Types Filter**: Toggle categories and remote position checkboxes
- **Show Date Posted Filter**: Toggle date posted radio buttons
- **Show Job Status Filters**: Toggle featured and filled position checkboxes
- **Target Loop Grid Query ID**: Default `job_listings`
- **Layout Style**: Sidebar or Inline
- **Search Type**: AJAX or Page Reload

#### Customizable Labels
- Positions Label (default: "Function")
- Location Label (default: "Location")
- Contract Types Label (default: "Contract type")
- Date Posted Label (default: "Date Posted")
- Job Status Label (default: "Job Status")

### Filter Options Explained

#### Positions (Job Types)
- Radio button selection
- "Everything" option shows all job types
- Filters by `job_listing_type` taxonomy

#### Location
- Radio button selection
- "All locations" option shows all locations
- Dynamically pulls unique locations from database
- Filters by `_job_location` meta field

#### Contract Types
- Multiple checkbox selection
- Remote Position: Filters by `_remote_position` meta field
- Categories: Filters by `job_listing_category` taxonomy
- Multiple selections combined with comma-separated format

#### Date Posted
- Radio button selection
- Options: Anytime, Today, Last 7 days, Last 14 days, Last 30 days
- Filters by post publish date

#### Job Status
- Featured Jobs Only: Shows jobs with `_featured` = 1
- Hide Filled Positions: Excludes jobs with `_filled` = 1

## Technical Details

### Query Hook
The plugin uses `elementor/query/job_listings` hook to modify Loop Grid queries automatically.

### Meta Fields Used
- `_job_location`: Job location
- `_remote_position`: Remote position flag (1 or empty)
- `_featured`: Featured job flag (1 or empty)
- `_filled`: Position filled flag (1 or empty)

### Taxonomies Used
- `job_listing_type`: Job types (Full Time, Part Time, etc.)
- `job_listing_category`: Job categories

### AJAX Endpoint
- Action: `wpjm_search_jobs`
- Returns redirect URL with filter parameters

## Frequently Asked Questions

**Q: Do I need Elementor Pro?**
A: The Job Search Filter widget works with free Elementor. Loop Grid requires Elementor Pro.

**Q: Can I customize the filter appearance?**
A: Yes, use Elementor's style controls in the widget settings under the Style tab.

**Q: Does it work with custom job fields?**
A: The plugin uses standard WP Job Manager fields. Custom fields require code modifications.

**Q: Can I translate the plugin?**
A: Yes, the plugin is translation-ready with text domain `elementor-wpjm`.

## Changelog

### Version 1.0.0
- Initial release
- Job Search Filter widget with sidebar and inline layouts
- Radio button filters for Location, Job Type, and Date Posted
- Checkbox filters for Categories, Remote Position, Featured Jobs, and Filled Positions
- 10+ Dynamic Tags for job data
- Elementor Loop Grid integration with Query ID system
- AJAX search support
- Auto-submit on filter changes
- Customizable filter labels
- Loop Grid preloader with loading indicator

## License

This plugin is licensed under the GPLv2 or later.

## Support

For support and feature requests, please visit the WordPress.org support forums.

## Credits

Developed with ❤️ for the WordPress community.
