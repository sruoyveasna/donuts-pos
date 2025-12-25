<?php

return [

    // App / general
    'app_name'       => 'NovaPOS Donuts',
    'pos_login'      => 'POS Login',
    'app_tagline'    => 'Point of Sale for donut shops',
    'app_role_label' => 'Staff user',
    'logout'         => 'Logout',
    'app_footer'     => 'NovaPOS Donuts',
    'all_rights'     => 'All rights reserved.',
    'theme_dark'     => 'Dark',
    'theme_light'    => 'Light',

    // Page titles
    'login_title' => 'Login',

    // Login hero (left side, desktop)
    'login_headline_prefix'    => 'Fast, simple',
    'login_headline_highlight' => 'cashier login',
    'login_headline_suffix'    => 'for your donut shop.',
    'login_intro'              => 'Switch shifts in seconds. Track sales, print receipts, and keep your team focused on serving customers — not fighting with the system.',

    'login_pill_fast'   => 'Under 5 seconds to log in',
    'login_pill_roles'  => 'Cashier & admin roles',
    'login_pill_donuts' => 'Optimized for donut shops',

    // Login card
    'login_title_card'      => 'Sign in to continue',
    'login_subtitle'        => 'Use your staff account to access the POS.',
    'login_email_label'     => 'Username or Email',
    'login_password_label'  => 'Password',
    'login_remember'        => 'Remember this device',
    'login_forgot'          => 'Forgot password?',
    'login_button'          => 'Sign in',
    'login_busy'            => 'Signing in…',
    'login_demo'            => 'Cashier demo:',
    'login_badge_roles'     => 'Role-based access',

    // Settings
    'settings_title'    => 'Settings',
    'settings_subtitle' => 'Manage your POS app appearance and your staff profile.',

    // Tabs
    'settings_app_tab'     => 'App settings',
    'settings_profile_tab' => 'Profile settings',

    // App settings
    'settings_logo_hint'      => 'PNG/JPG, up to 2 MB',
    'settings_shop_name'      => 'Shop name',
    'settings_default_locale' => 'Default language',

    'settings_tax_enabled' => 'Enable tax',
    'settings_tax_hint'    => 'Applied on each receipt.',
    'settings_tax_rate'    => 'Tax rate (%)',
    'settings_bank_id'     => 'Bank ID / eWallet',

    'settings_currency_code'   => 'Default currency',
    'settings_currency_symbol' => 'Currency symbol',
    'settings_exchange_rate'   => '1 USD = ? KHR',

    'settings_receipt_footer' => 'Receipt footer note',
    'settings_save_app'       => 'Save app settings',

    // Extra help text in app modal
    'settings_edit_app_help' =>
        'Change shop name, logo, tax and currency options used across the POS.',

    // Profile settings
    'settings_avatar_hint'       => 'Square image, up to 2 MB',
    'settings_edit_profile_help' => 'Update your personal details and avatar.',
    'settings_save_profile'      => 'Update profile',

    'profile_name'         => 'Name',
    'profile_phone'        => 'Phone',
    'profile_gender'       => 'Gender',
    'profile_gender_none'  => 'Not specified',
    'profile_gender_male'  => 'Male',
    'profile_gender_female'=> 'Female',
    'profile_gender_other' => 'Other',
    'profile_birthdate'    => 'Birthdate',
    'profile_address'      => 'Address',

    // Generic
    'cancel' => 'Cancel',
    'close'  => 'Close',

    // Categories
    'categories_title'    => 'Categories',
    'categories_subtitle' => 'Organize your menu items into clear groups.',

    'categories_search_placeholder' => 'Search name or slug…',
    'categories_filters_label'      => 'Filters',
    'categories_refresh_label'      => 'Refresh',
    'categories_new_button'         => 'New category',

    'categories_filter_sort_by'      => 'Sort by',
    'categories_filter_sort_name'    => 'Name',
    'categories_filter_sort_slug'    => 'Slug',
    'categories_filter_sort_created' => 'Created',

    'categories_filter_direction' => 'Direction',
    'sort_asc'                    => 'ASC',
    'sort_desc'                   => 'DESC',

    'categories_filter_per_page'      => 'Per page',
    'categories_filter_visibility'    => 'Visibility',
    'categories_filter_visible_only'  => 'Visible only',
    'categories_filter_with_trashed'  => 'Include archived',
    'categories_filter_apply'         => 'Apply',
    'categories_filter_reset'         => 'Reset',

    'categories_col_name'    => 'Name',
    'categories_col_slug'    => 'Slug',
    'categories_col_status'  => 'Status',
    'categories_col_items'   => 'Items',
    'categories_col_actions' => 'Actions',

    'categories_status_active'   => 'Active',
    'categories_status_inactive' => 'Inactive',

    'categories_summary_loading' => 'Loading…',
    'categories_summary_range'   => 'Showing :from–:to of :total categories',

    'categories_load_failed_title'   => 'Couldn’t load categories',
    'categories_load_failed_message' => 'Please try again.',
    'categories_retry'               => 'Retry',

    'categories_empty_title' => 'No categories found',
    'categories_empty_body'  => 'Try adjusting your filters or add a new category.',

    'categories_tooltip_clear'   => 'Clear',
    'categories_tooltip_filters' => 'Filters',
    'categories_tooltip_refresh' => 'Refresh',
    'categories_tooltip_edit'    => 'Edit',
    'categories_tooltip_archive' => 'Archive',
    'categories_tooltip_restore' => 'Restore',

    // Create modal (categories)
    'categories_create_title'                 => 'New category',
    'categories_create_subtitle'             => 'Create a category to group your menu items.',
    'categories_create_name_label'           => 'Category name',
    'categories_create_name_placeholder'     => 'e.g. Drinks, Donuts, Coffee',
    'categories_create_slug_label'           => 'Slug (optional)',
    'categories_create_slug_placeholder'     => 'Auto-generated if empty',
    'categories_create_slug_hint'            => 'Used in URLs and reports. Only letters, numbers and dashes.',
    'categories_create_status_label'         => 'Status',
    'categories_create_status_active_label'  => 'Active (visible in POS)',
    'categories_create_status_hint'          => 'You can archive later if needed.',
    'categories_create_cancel'               => 'Cancel',
    'categories_create_save'                 => 'Create category',

    // Edit modal (categories)
    'categories_edit_title'                        => 'Edit category',
    'categories_edit_subtitle'                    => 'Update category name, slug or visibility.',
    'categories_edit_name_label'                  => 'Category name',
    'categories_edit_name_placeholder'            => 'Category name',
    'categories_edit_slug_label'                  => 'Slug',
    'categories_edit_slug_placeholder'            => 'Leave empty to keep current or auto-generate',
    'categories_edit_slug_hint'                   => 'Changing slug may affect links or reports.',
    'categories_edit_status_label'                => 'Status',
    'categories_edit_status_active_label'         => 'Active (visible in POS)',
    'categories_edit_status_hint'                 => 'Uncheck to hide from POS without archiving.',
    'categories_edit_footer_hint'                 => 'Archiving can be done from the list if you want to hide it completely.',
    'categories_edit_cancel'                      => 'Cancel',
    'categories_edit_save'                        => 'Save changes',
    'categories_field_name_placeholder'   => 'name',
    'categories_field_slug_placeholder' => 'slug',


    'categories_button_save_changes'       => 'save changes',
    'categories_field_name_label'   => 'Name',
    'categories_field_name_hint'    => 'Enter category name',
    'categories_field_slug_label'   => 'Slug',
    'categories_field_slug_hint'    => 'Unique identifier (lowercase, hyphens). Example: donuts',
    'categories_field_active_label' => 'Active',
    'categories_field_active_hint'  => 'Toggle to show/hide this category',
    'categories_field_active_badge' => 'Active',

    'categories_button_cancel'      => 'Cancel',
    'categories_button_create'      => 'Create',
    'categories_button_save'        => 'Save',

    // Shared categories
    'categories_no_permission' => 'You don’t have permission to manage categories.',

    // ----------------------------------------------------------------------
    // Menu – list page
    // ----------------------------------------------------------------------
    'menu_title'    => 'Menu',
    'menu_subtitle' => 'Manage all items, prices, categories and discounts in your POS.',

    'menu_summary_loading' => 'Loading…',
    'menu_summary_range'   => 'Showing :from–:to of :total items',

    // Toggle active / archived
    'menu_toggle_active'  => 'Showing active',
    'menu_toggle_deleted' => 'Showing deleted',

    // Toolbar / search
    'menu_search_placeholder' => 'Search by name, size or category…',
    'menu_tooltip_clear'      => 'Clear search',
    'menu_tooltip_filters'    => 'Filters',
    'menu_tooltip_refresh'    => 'Refresh',
    'menu_new_button'         => 'Add item',

    // Filters panel
    'menu_filters_label'             => 'Filters',
    'menu_filters_category_label'    => 'Category',
    'menu_filters_category_all'      => 'All',
    'menu_filters_min_price'         => 'Min price',
    'menu_filters_max_price'         => 'Max price',
    'menu_filters_sort_by'           => 'Sort by',
    'menu_filters_sort_name'         => 'Name',
    'menu_filters_sort_price'        => 'Price',
    'menu_filters_sort_created'      => 'Created',
    'menu_filters_direction'         => 'Direction',
    'menu_filters_per_page'          => 'Per page',
    'menu_filters_flags'             => 'Flags',
    'menu_filters_visible_only'      => 'Visible only',
    'menu_filters_with_trashed'      => 'Include archived',
    'menu_filters_include_variants'  => 'Include variants',
    'menu_filters_apply'             => 'Apply',
    'menu_filters_reset'             => 'Reset',

    // Table headers
    'menu_col_index'    => '#',
    'menu_col_image'    => 'Image',
    'menu_col_name'     => 'Name',
    'menu_col_sizes'    => 'Sizes',
    'menu_col_price'    => 'Price',
    'menu_col_status'   => 'Status',
    'menu_col_category' => 'Category',
    'menu_col_actions'  => 'Actions',

    // Async states
    'menu_loading_row'          => 'Loading…',
    'menu_load_failed_title'    => 'Couldn’t load menu',
    'menu_load_failed_message'  => 'Please try again.',
    'menu_retry'                => 'Retry',

    'menu_empty_title'      => 'No items found',
    'menu_empty_body'       => 'Try adjusting your filters or add a new item.',
    'menu_empty_add_button' => 'Add item',

    // Status labels
    'menu_status_deleted' => 'Deleted',
    'menu_status_active'  => 'Active',
    'menu_status_hidden'  => 'Hidden',

    // Row actions / tooltips
    'menu_action_edit'           => 'Edit',
    'menu_action_delete'         => 'Delete',
    'menu_action_delete_forever' => 'Delete permanently',
    'menu_action_restore'        => 'Restore',

    // Confirms
    'menu_confirm_archive'         => 'Archive this item?',
    'menu_confirm_delete_forever'  => 'Permanently delete this item? This cannot be undone.',

    // Toast / result messages
    'menu_toast_created'         => 'Menu item added.',
    'menu_toast_updated'         => 'Menu item updated.',
    'menu_toast_archived'        => 'Item archived.',
    'menu_toast_deleted_forever' => 'Item permanently deleted.',
    'menu_toast_restored'        => 'Item restored.',
    'menu_toast_delete_failed'   => 'Delete failed.',
    'menu_toast_restore_failed'  => 'Restore failed.',

    // ----------------------------------------------------------------------
    // Menu – Create modal
    // ----------------------------------------------------------------------
    'menu_create_title'        => 'Add menu item',
    'menu_create_subtitle'     => 'Upload an image, set price and category for this item.',

    'menu_create_image_label'  => 'Image',
    'menu_create_image_hint'   => 'PNG/JPG, up to 2 MB',

    'menu_create_name_label'       => 'Name',
    'menu_create_name_placeholder' => 'e.g. Glazed donut',

    'menu_create_price_label' => 'Price ($)',

    'menu_create_category_label'        => 'Category',
    'menu_create_category_placeholder'  => 'Select category',

    'menu_create_visible_label' => 'Visible (active)',

    // Discount block
    'menu_create_discount_title'               => 'Discount',
    'menu_create_discount_type_none'           => 'None',
    'menu_create_discount_type_percent'        => 'Percent (%)',
    'menu_create_discount_type_fixed'          => 'Fixed ($)',
    'menu_create_discount_value_placeholder'   => '10',
    'menu_create_discount_starts_label'        => 'Starts',
    'menu_create_discount_ends_label'          => 'Ends',
    'menu_create_discount_hint_percent'        => 'Max 100%.',
    'menu_create_discount_hint_fixed'          => 'Cannot exceed price.',

    'menu_create_error_generic' => 'Create failed.',
    'menu_create_cancel'        => 'Cancel',
    'menu_create_save'          => 'Save',

    // ----------------------------------------------------------------------
    // Menu – Edit modal
    // ----------------------------------------------------------------------
    'menu_edit_title'       => 'Edit menu item',
    'menu_edit_subtitle'    => 'Update image, price, category and discount.',

    'menu_edit_image_label'   => 'Image',
    'menu_edit_name_label'    => 'Name',
    'menu_edit_price_label'   => 'Price ($)',
    'menu_edit_category_label'=> 'Category',
    'menu_edit_category_placeholder'=> 'Categories',
    'menu_edit_visible_label' => 'Visible (active)',

    // Discount section
    'menu_edit_discount_title'               => 'Discount',
    'menu_edit_discount_none'           => 'None',
    'menu_edit_discount_percent'        => 'Percent (%)',
    'menu_edit_discount_fixed'          => 'Fixed ($)',
    'menu_edit_discount_value_placeholder'   => '10',
    'menu_edit_discount_starts_label'        => 'Starts',
    'menu_edit_discount_ends_label'          => 'Ends',
    'menu_edit_discount_hint_percent'        => 'Max 100%.',
    'menu_edit_discount_hint_fixed'          => 'Cannot exceed price.',

    // Apply to variants
    'menu_edit_discount_apply_to_variants_label' => 'Apply this discount to selected variants',
    'menu_edit_discount_picker_title'            => 'Select variants',
    'menu_edit_discount_picker_select_all'       => 'Select all',
    'menu_edit_discount_picker_hint'             => 'Selected variants will get the same discount and override their existing discount.',

    // Footer / buttons
    'menu_edit_manage_variants_button' => 'Manage variants',
    'menu_edit_cancel'                 => 'Cancel',
    'menu_edit_save'                   => 'Save changes',
    'menu_edit_error_generic'          => 'Update failed.',

    'menu_edit_toast_updated'       => 'Menu item updated.',
    'menu_edit_toast_update_failed' => 'Update failed.',

    // ----------------------------------------------------------------------
    // Menu – Manage variants modal
    // ----------------------------------------------------------------------
    'menu_variants_title'    => 'Manage variants',
    'menu_variants_subtitle' => 'Create, update and delete size-based variants for this menu item.',
    'menu_variants_for_item' => 'Item:',

    // Variant form
    'menu_variants_size_label'        => 'Size',
    'menu_variants_size_placeholder'  => 'e.g. Small / Medium / Large',
    'menu_variants_price_label'       => 'Price ($)',

    'menu_variants_submit_add'    => 'Add variant',
    'menu_variants_submit_update' => 'Save changes',

    'menu_variants_preview_label' => 'Variant name will be:',
    'menu_variants_preview_empty' => '(name will appear here)',

    // Variant discount fields
    'menu_variants_discount_label'        => 'Discount',
    'menu_variants_discount_none'         => 'None',
    'menu_variants_discount_percent'      => 'Percent (%)',
    'menu_variants_discount_fixed'        => 'Fixed ($)',
    'menu_variants_discount_value_label'  => 'Value',
    'menu_variants_discount_starts_label' => 'Starts',
    'menu_variants_discount_ends_label'   => 'Ends',

    // Table
    'menu_variants_table_title'  => 'Variants',
    'menu_variants_loading'      => 'Loading…',
    'menu_variants_col_name'     => 'Name',
    'menu_variants_col_size'     => 'Size',
    'menu_variants_col_price'    => 'Price',
    'menu_variants_col_final'    => 'Final',
    'menu_variants_col_actions'  => 'Actions',
    'menu_variants_empty'        => 'No variants yet',

    // Actions / badges
    'menu_variants_action_edit'        => 'Edit',
    'menu_variants_action_delete'      => 'Delete',
    'menu_variants_badge_own_discount' => 'own discount',

    // Validation / messages
    'menu_variants_confirm_delete'                 => 'Permanently delete this variant?',
    'menu_variants_delete_failed'                  => 'Delete failed.',
    'menu_variants_error_size_required'            => 'Size is required.',
    'menu_variants_error_price_invalid'            => 'Price must be greater than or equal to 0.',
    'menu_variants_error_discount_value_invalid'   => 'Discount value must be greater than or equal to 0.',
    'menu_variants_error_discount_percent_invalid' => 'Percent cannot exceed 100%.',
    'menu_variants_error_discount_fixed_invalid'   => 'Fixed discount cannot exceed price.',

    'menu_variants_toast_created'  => 'Variant created.',
    'menu_variants_toast_updated'  => 'Variant updated.',
    'menu_variants_save_failed'    => 'Failed to save variant.',
    'menu_variants_count_format'   => '{0} No variants|{1} 1 variant|[2,*] :count variants',
        // ----------------------------------------------------------------------
    // Menu – list page
    // ----------------------------------------------------------------------
    'menu_title'    => 'Menu',
    'menu_subtitle' => 'Manage all your menu items, prices, categories and discounts in one place.',

    'menu_summary_loading' => 'Loading…',
    'menu_summary_range'   => 'Showing :from–:to of :total items',

    // Toggle active / archived
    'menu_toggle_active'  => 'Showing active',
    'menu_toggle_deleted' => 'Showing deleted',

    // Toolbar / search
    'menu_search_placeholder' => 'Search by name, size or category…',
    'menu_tooltip_clear'      => 'Clear search',
    'menu_tooltip_filters'    => 'Filters',
    'menu_tooltip_refresh'    => 'Refresh',
    'menu_new_button'         => 'Add item',

    // Filters panel
    'menu_filters_label'            => 'Filters',
    'menu_filters_category_label'   => 'Category',
    'menu_filters_category_all'     => 'All',
    'menu_filters_min_price'        => 'Min price',
    'menu_filters_max_price'        => 'Max price',
    'menu_filters_sort_by'          => 'Sort by',
    'menu_filters_sort_name'        => 'Name',
    'menu_filters_sort_price'       => 'Price',
    'menu_filters_sort_created'     => 'Created',
    'menu_filters_direction'        => 'Direction',
    'menu_filters_per_page'         => 'Per page',
    'menu_filters_flags'            => 'Flags',
    'menu_filters_visible_only'     => 'Visible only',
    'menu_filters_with_trashed'     => 'Include archived',
    'menu_filters_include_variants' => 'Include variants',
    'menu_filters_apply'            => 'Apply',
    'menu_filters_reset'            => 'Reset',

    // Table headers
    'menu_col_index'    => '#',
    'menu_col_image'    => 'Image',
    'menu_col_name'     => 'Name',
    'menu_col_sizes'    => 'Sizes',
    'menu_col_price'    => 'Price',
    'menu_col_status'   => 'Status',
    'menu_col_category' => 'Category',
    'menu_col_actions'  => 'Actions',

    // Async states
    'menu_loading_row'         => 'Loading…',
    'menu_load_failed_title'   => 'Couldn’t load menu',
    'menu_load_failed_message' => 'Please try again.',
    'menu_retry'               => 'Retry',

    'menu_empty_title'      => 'No items found',
    'menu_empty_body'       => 'Try adjusting your filters or add a new item.',
    'menu_empty_add_button' => 'Add item',

    // Status labels
    'menu_status_deleted' => 'Deleted',
    'menu_status_active'  => 'Active',
    'menu_status_hidden'  => 'Hidden',

    // Row actions / tooltips
    'menu_action_edit'           => 'Edit',
    'menu_action_delete'         => 'Delete',
    'menu_action_delete_forever' => 'Delete permanently',
    'menu_action_restore'        => 'Restore',

    // Confirms
    'menu_confirm_archive'        => 'Archive this item?',
    'menu_confirm_delete_forever' => 'Permanently delete this item? This cannot be undone.',

    // Toast / result messages
    'menu_toast_created'         => 'Menu item added.',
    'menu_toast_updated'         => 'Menu item updated.',
    'menu_toast_archived'        => 'Menu item archived.',
    'menu_toast_deleted_forever' => 'Menu item permanently deleted.',
    'menu_toast_restored'        => 'Menu item restored.',
    'menu_toast_delete_failed'   => 'Delete failed.',
    'menu_toast_restore_failed'  => 'Restore failed.',

    // ----------------------------------------------------------------------
    // Menu – Create modal
    // ----------------------------------------------------------------------
    'menu_create_title'    => 'Add menu item',
    'menu_create_subtitle' => 'Upload an image, set price and group this item under a category.',

    'menu_create_image_label' => 'Image',
    'menu_create_image_hint'  => 'PNG/JPG, up to 2 MB',

    'menu_create_name_label'       => 'Name',
    'menu_create_name_placeholder' => 'e.g. Glazed donut',

    'menu_create_price_label' => 'Price ($)',

    'menu_create_category_label'       => 'Category',
    'menu_create_category_placeholder' => 'Select category',

    'menu_create_visible_label' => 'Visible (active)',

    // Discount block
    'menu_create_discount_title'             => 'Discount',
    'menu_create_discount_type_none'         => 'None',
    'menu_create_discount_type_percent'      => 'Percent (%)',
    'menu_create_discount_type_fixed'        => 'Fixed ($)',
    'menu_create_discount_value_placeholder' => '10',
    'menu_create_discount_starts_label'      => 'Starts',
    'menu_create_discount_ends_label'        => 'Ends',
    'menu_create_discount_hint_percent'      => 'Max 100%.',
    'menu_create_discount_hint_fixed'        => 'Cannot exceed price.',

    'menu_create_error_generic' => 'Create failed.',
    'menu_create_cancel'        => 'Cancel',
    'menu_create_save'          => 'Save',

    // ----------------------------------------------------------------------
    // Menu – Edit modal
    // ----------------------------------------------------------------------
    'menu_edit_title'    => 'Edit menu item',
    'menu_edit_subtitle' => 'Update image, price, category or discount for this item.',

    'menu_edit_image_label'    => 'Image',
    'menu_edit_name_label'     => 'Name',
    'menu_edit_price_label'    => 'Price ($)',
    'menu_edit_category_label' => 'Category',
    'menu_edit_visible_label'  => 'Visible (active)',

    // Discount section
    'menu_edit_discount_title'             => 'Discount',
    'menu_edit_discount_type_none'         => 'None',
    'menu_edit_discount_type_percent'      => 'Percent (%)',
    'menu_edit_discount_type_fixed'        => 'Fixed ($)',
    'menu_edit_discount_value_placeholder' => '10',
    'menu_edit_discount_starts_label'      => 'Starts',
    'menu_edit_discount_ends_label'        => 'Ends',
    'menu_edit_discount_hint_percent'      => 'Max 100%.',
    'menu_edit_discount_hint_fixed'        => 'Cannot exceed price.',

    // Apply to variants
    'menu_edit_discount_apply_to_variants_label' => 'Apply this discount to selected variants',
    'menu_edit_discount_picker_title'            => 'Select variants',
    'menu_edit_discount_picker_select_all'       => 'Select all',
    'menu_edit_discount_picker_hint'             => 'Selected variants will get the same discount and override any existing variant discount.',

    // Footer / buttons
    'menu_edit_manage_variants_button' => 'Manage variants',
    'menu_edit_cancel'                 => 'Cancel',
    'menu_edit_save'                   => 'Save changes',
    'menu_edit_error_generic'          => 'Update failed.',

    'menu_edit_toast_updated'       => 'Menu item updated.',
    'menu_edit_toast_update_failed' => 'Update failed.',

    // ----------------------------------------------------------------------
    // Menu – Manage variants modal
    // ----------------------------------------------------------------------
    'menu_variants_title'    => 'Manage variants',
    'menu_variants_subtitle' => 'Create, edit and delete size variants for this item.',
    'menu_variants_for_item' => 'Item:',

    // Variant form
    'menu_variants_size_label'       => 'Size',
    'menu_variants_size_placeholder' => 'e.g. Small / Medium / Large',
    'menu_variants_price_label'      => 'Price ($)',

    'menu_variants_submit_add'    => 'Add variant',
    'menu_variants_submit_update' => 'Save changes',

    'menu_variants_preview_label' => 'Variant name will be:',
    'menu_variants_preview_empty' => '(name will appear here)',

    // Variant discount fields
    'menu_variants_discount_label'        => 'Discount',
    'menu_variants_discount_none'         => 'None',
    'menu_variants_discount_percent'      => 'Percent (%)',
    'menu_variants_discount_fixed'        => 'Fixed ($)',
    'menu_variants_discount_value_label'  => 'Value',
    'menu_variants_discount_starts_label' => 'Starts',
    'menu_variants_discount_ends_label'   => 'Ends',

    // Table
    'menu_variants_table_title' => 'Variants',
    'menu_variants_loading'     => 'Loading…',
    'menu_variants_col_name'    => 'Name',
    'menu_variants_col_size'    => 'Size',
    'menu_variants_col_price'   => 'Price',
    'menu_variants_col_final'   => 'Final',
    'menu_variants_col_actions' => 'Actions',
    'menu_variants_empty'       => 'No variants yet',

    // Actions / badges
    'menu_variants_action_edit'        => 'Edit',
    'menu_variants_action_delete'      => 'Delete',
    'menu_variants_badge_own_discount' => 'own discount',

    // Validation / messages
    'menu_variants_confirm_delete'                 => 'Permanently delete this variant?',
    'menu_variants_delete_failed'                  => 'Delete failed.',
    'menu_variants_error_size_required'            => 'Size is required.',
    'menu_variants_error_price_invalid'            => 'Price must be ≥ 0.',
    'menu_variants_error_discount_value_invalid'   => 'Discount value must be ≥ 0.',
    'menu_variants_error_discount_percent_invalid' => 'Percent cannot exceed 100%.',
    'menu_variants_error_discount_fixed_invalid'   => 'Fixed discount cannot exceed price.',

    'menu_variants_toast_created' => 'Variant created.',
    'menu_variants_toast_updated' => 'Variant updated.',
    'menu_variants_save_failed'   => 'Failed to save variant.',
        // --- Menu page: extra keys ---

    'menu_manager_badge'              => 'Menu manager',
    'menu_show_archived_label_active' => 'Showing active',
    'menu_show_archived_label_deleted'=> 'Showing deleted',

    'menu_add_button'                 => 'Add item',

    // Filters
    'menu_filter_category'            => 'Category',
    'menu_filter_min_price'           => 'Min price',
    'menu_filter_max_price'           => 'Max price',

    'menu_filter_sort_by'             => 'Sort by',
    'menu_filter_sort_name'           => 'Name',
    'menu_filter_sort_price'          => 'Price',
    'menu_filter_sort_created'        => 'Created',

    'menu_filter_direction'           => 'Direction',
    'menu_filter_per_page'            => 'Per page',
    'menu_filter_flags'               => 'Flags',
    'menu_filter_visible_only'        => 'Visible only',
    'menu_filter_include_variants'    => 'Include variants',
    'menu_filter_apply'               => 'Apply',
    'menu_filter_reset'               => 'Reset',

    // Create modal – discount select
    'menu_create_discount_none'       => 'None',
    'menu_create_discount_percent'    => 'Percent (%)',
    'menu_create_discount_fixed'      => 'Fixed ($)',

    // Edit modal – status + discount block
    'menu_edit_is_active_label'          => 'Visible (active)',
    'menu_edit_discount_section_label'   => 'Discount',
    'menu_edit_discount_starts'          => 'Starts',
    'menu_edit_discount_ends'            => 'Ends',
    'menu_edit_apply_variants_label'     => 'Apply this discount to selected variants',
    'menu_edit_manage_variants'          => 'Manage variants',

    // Actions
    'menu_action_archive'             => 'Archive',
    'menu_create_is_active_label' => 'Active',
    'menu_create_discount_starts' => 'Starts',
    'menu_create_discount_ends'   => 'Ends',
    'menu_create_discount_section_label' => 'Discount',
    // ===== Users page (resources/views/users/index.blade.php) =====
    'users_title'              => 'Users',
    'users_subtitle'           => 'Manage staff accounts, roles, and access.',
    'users_summary_loading'    => 'Loading…',
    'users_summary_range'      => 'Showing :from–:to of :total',

    'users_status_active'      => 'Active',
    'users_status_archived'    => 'Archived',

    'users_badge_title'        => 'User manager',

    'users_search_placeholder' => 'Search by name, email, role…',
    'users_tooltip_clear'      => 'Clear search',

    'users_filters_label'      => 'Filters',
    'users_refresh_label'      => 'Refresh',

    'users_new_button'         => 'New User',
    'users_new_button_short'   => 'New',

    // --- Filter labels ---
    'users_filter_sort_by'     => 'Sort by',
    'users_filter_sort_name'   => 'Name',
    'users_filter_sort_email'  => 'Email',
    'users_filter_sort_role'   => 'Role',
    'users_filter_sort_created'=> 'Created',

    'users_filter_direction'   => 'Direction',
    'users_filter_per_page'    => 'Per page',
    'users_filter_visibility'  => 'Visibility',

    'users_filter_with_trashed'=> 'Include archived',
    'users_filter_only_archived'=> 'Only archived',

    'users_filter_apply'       => 'Apply',
    'users_filter_reset'       => 'Reset',

    // --- Table columns ---
    'users_col_user'           => 'User',
    'users_col_email'          => 'Email',
    'users_col_role'           => 'Role',
    'users_col_status'         => 'Status',
    'users_col_actions'        => 'Actions',

    // --- Loading / empty / error states ---
    'users_load_failed_title'   => "Couldn’t load users",
    'users_load_failed_message' => 'Please try again.',
    'users_retry'               => 'Retry',

    'users_empty_title'         => 'No users found',
    'users_empty_body'          => 'Try adjusting your filters or add a new user.',

    // --- Tooltips / confirmations ---
    'users_tooltip_edit'        => 'Edit',
    'users_tooltip_archive'     => 'Archive',
    'users_tooltip_restore'     => 'Restore',

    'users_confirm_archive'     => 'Archive this user?',

    // --- Toast messages ---
    'users_toast_created'       => 'User created',
    'users_toast_create_failed' => 'Create failed',
    'users_toast_updated'       => 'User updated',
    'users_toast_update_failed' => 'Update failed',
    'users_toast_archived'      => 'Archived',
    'users_toast_archive_failed'=> 'Archive failed',
    'users_toast_restored'      => 'Restored',
    'users_toast_restore_failed'=> 'Restore failed',

    'users_create_title'    => 'New user',
    'users_create_subtitle' => 'Create a new staff account.',
    'users_edit_title'      => 'Edit user',
    'users_edit_subtitle'   => 'Update user information, role, or password.',
    'users_button_close'    => 'Close',

    // Fields
    'users_field_name_label'        => 'Name',
    'users_field_name_placeholder'  => 'Full name',
    'users_field_name_hint'         => 'This name appears in the sidebar and user list.',

    'users_field_email_label'       => 'Email',
    'users_field_email_placeholder' => 'name@example.com',
    'users_field_email_hint'        => 'Used for login and notifications.',

    'users_field_role_label'        => 'Role',
    'users_field_role_placeholder'  => 'Select role…',
    'users_field_role_hint'         => 'Controls what this user can access.',

    'users_field_password_label'       => 'Password',
    'users_field_password_placeholder' => 'Minimum 6 characters',
    'users_field_password_hint'        => 'User can change password later.',
    'users_field_password_edit_placeholder' => 'Minimum 6 characters',
    'users_field_password_edit_hint'        => 'User can change password later.',


    // Edit-only password (optional)
    'users_field_password_optional_label'       => 'Password (optional)',
    'users_field_password_optional_placeholder' => 'Leave blank to keep current password',
    'users_field_password_optional_hint'        => 'Only fill this if you want to change the password.',

    // Buttons
    'users_button_cancel'       => 'Cancel',
    'users_button_create'       => 'Create user',
    'users_button_save_changes' => 'Save changes',
    // ========================
    // Orders - Index page
    // ========================
    'orders_title'                 => 'Orders',
    'orders_subtitle'              => 'Manage orders and payment status.',
    'orders_badge_title'           => 'Order manager',

    'orders_summary_loading'       => 'Loading…',
    'orders_summary_range'         => 'Showing :from–:to of :total',

    'orders_search_placeholder'    => 'Search by code/cashier/status…',
    'orders_tooltip_clear'         => 'Clear search',
    'orders_filters_label'         => 'Filters',
    'orders_refresh_label'         => 'Refresh',

    'orders_filter_sort_by'        => 'Sort by',
    'orders_filter_sort_code'      => 'Code',
    'orders_filter_sort_total'     => 'Total',
    'orders_filter_sort_created'   => 'Created',

    'orders_filter_direction'      => 'Direction',
    /* uses existing shared keys:
    'sort_asc' => 'ASC',
    'sort_desc' => 'DESC',
    */

    'orders_filter_per_page'       => 'Per page',
    'orders_action_view'          => 'View details',
    'orders_filter_status'         => 'Status',
    'orders_filter_status_all'     => 'All',
    'orders_filter_status_unpaid'  => 'Unpaid',
    'orders_filter_status_paid'    => 'Paid',
    'orders_filter_with_trashed'   => 'Include archived',
    'orders_filter_only_archived'  => 'Only archived',

    'orders_filter_apply'          => 'Apply',
    'orders_filter_reset'          => 'Reset',

    'orders_col_code'              => 'Code',
    'orders_col_cashier'           => 'Cashier',
    'orders_col_items'             => 'Items',
    'orders_col_total'             => 'Total',
    'orders_col_status'            => 'Status',
    'orders_col_created'           => 'Created',
    'orders_col_actions'           => 'Actions',

    'orders_status_paid'           => 'Paid',
    'orders_status_unpaid'         => 'Unpaid',
    'orders_status_archived'       => 'Archived',

    'orders_tooltip_view'          => 'View details',
    'orders_tooltip_archive'       => 'Archive',
    'orders_tooltip_restore'       => 'Restore',

    'orders_load_failed_title'     => "Couldn’t load orders",
    'orders_load_failed_message'   => 'Please try again.',
    'orders_retry'                 => 'Retry',

    'orders_empty_title'           => 'No orders found',
    'orders_empty_body'            => 'Try adjusting your filters or search again.',

    'orders_confirm_archive'       => 'Archive this order?',
    'orders_toast_archived'        => 'Archived',
    'orders_toast_archive_failed'  => 'Archive failed',
    'orders_toast_restored'        => 'Restored',
    'orders_toast_restore_failed'  => 'Restore failed',


    // ========================
    // Orders - Show page
    // ========================
    'orders_show_title'            => 'Order details',
    'orders_show_subtitle'         => 'Full details about items and payments.',

    'orders_button_back'           => 'Back',
    'orders_button_print'          => 'Print receipt',

    'orders_show_section_summary'  => 'Summary',
    'orders_show_label_code'       => 'Code',
    'orders_show_label_status'     => 'Status',
    'orders_show_label_cashier'    => 'Cashier',
    'orders_show_label_created_at' => 'Created at',
    'orders_show_label_paid_at'    => 'Paid at',
    'orders_show_label_exchange'   => 'Exchange rate',

    'orders_show_section_items'    => 'Items',
    'orders_show_items_col_item'   => 'Item',
    'orders_show_items_col_variant'=> 'Variant',
    'orders_show_items_col_qty'    => 'Qty',
    'orders_show_items_col_price'  => 'Unit price',
    'orders_show_items_col_sub'    => 'Subtotal',
    'orders_show_items_empty'      => 'No items.',

    'orders_show_section_totals'   => 'Totals',
    'orders_show_label_subtotal'   => 'Subtotal',
    'orders_show_label_discount'   => 'Discount',
    'orders_show_label_tax'        => 'Tax',
    'orders_show_label_total'      => 'Total',
    'orders_show_label_due'        => 'Due',

    'orders_show_section_payments' => 'Payments',
    'orders_show_pay_col_method'   => 'Method',
    'orders_show_pay_col_currency' => 'Currency',
    'orders_show_pay_col_amount'   => 'Amount',
    'orders_show_pay_col_tendered' => 'Tendered',
    'orders_show_pay_col_change'   => 'Change',
    'orders_show_pay_col_time'     => 'Time',
    'orders_show_payments_empty'   => 'No payments.',
    'orders_filter_date_range'    => 'Date range',
    'orders_filter_any'          => 'Any',
        // ===== Orders Show Page (orders/show.blade.php) =====
    'orders_show_title'            => 'Order Details',
    'orders_show_subtitle'         => 'Review items, totals, and payments for this order.',

    // shared/common keys used on this page
    'back'                         => 'Back',
    'loading'                      => 'Loading…',
    'print'                        => 'Print',
    'close'                        => 'Close',
    'cancel'                       => 'Cancel',
    'confirm'                      => 'Confirm',

    // page actions
    'orders_record_payment'        => 'Record payment',
    'orders_record_payment_hint'   => 'Enter tendered amount and confirm.',

    // items table
    'orders_items_title'           => 'Items',
    'orders_col_item'              => 'Item',
    'orders_col_qty'               => 'Qty',
    'orders_col_unit'              => 'Unit (USD)',
    'orders_col_subtotal'          => 'Subtotal (USD)',
    'orders_col_note'              => 'Note',
    'orders_items_empty'           => 'No items.',

    // payments table
    'orders_payments_title'        => 'Payments',
    'orders_col_method'            => 'Method',
    'orders_col_currency'          => 'Cur',
    'orders_col_amount'            => 'Amount (KHR)',
    'orders_col_tendered'          => 'Tendered (KHR)',
    'orders_col_change'            => 'Change (KHR)',
    'orders_col_time'              => 'Time',
    'orders_payments_empty'        => 'No payments.',

    // summary card
    'orders_summary_title'         => 'Summary',
    'orders_cashier'               => 'Cashier',
    'orders_subtotal'              => 'Subtotal',
    'orders_discount'              => 'Discount',
    'orders_tax'                   => 'Tax',
    'orders_total'                 => 'Total',
    'orders_paid'                  => 'Paid',
    'orders_due'                   => 'Due',
    'orders_exchange_rate'         => 'Exchange rate',
    'orders_exchange_rate_hint'    => 'Used when currency is USD.',
    'orders_tax_rate'              => 'Tax rate',

    // payment modal fields
    'orders_payment_method'        => 'Method',
    'orders_payment_currency'      => 'Currency',
    'orders_payment_currency_hint' => 'USD will convert to KHR using exchange rate.',
    'orders_tendered'              => 'Tendered',

    // JS i18n (used in script)
    'orders_load_failed_message'   => 'Couldn’t load this order.',
    'orders_status_paid'           => 'Paid',
    'orders_status_unpaid'         => 'Unpaid',
    'orders_payment_success'       => 'Payment recorded',
    'orders_payment_fail'          => 'Payment failed',
        // Common / shared
    'actions'      => 'Actions',
    'apply'        => 'Apply',
    'back'         => 'Back',
    'cancel'       => 'Cancel',
    'clear'        => 'Clear',
    'close'        => 'Close',
    'confirm'      => 'Confirm',
    'create'       => 'Create',
    'delete'       => 'Delete',
    'direction'    => 'Direction',
    'edit'         => 'Edit',
    'filters'      => 'Filters',
    'loading'      => 'Loading…',
    'new'          => 'New',
    'note'         => 'Note',
    'per_page'     => 'Per page',
    'range'        => 'Showing :from–:to of :total',
    'refresh'      => 'Refresh',
    'reset'        => 'Reset',
    'retry'        => 'Retry',
    'save'         => 'Save',
    'save_failed'  => 'Save failed',
    'sort_asc'     => 'ASC',
    'sort_by'      => 'Sort by',
    'sort_desc'    => 'DESC',
    'view'         => 'View',

    // Ingredients - page titles / subtitles / labels
    'ingredients_title'         => 'Ingredients',
    'ingredients_subtitle'      => 'Manage stock levels and track inventory movements.',
    'ingredients_manager'       => 'Stock manager',
    'ingredients_new'           => 'New Ingredient',
    'ingredients_show_title'    => 'Ingredient Details',
    'ingredients_show_subtitle' => 'View movements and update this ingredient.',
    'ingredients_summary_title' => 'Details',

    // Ingredients - table columns
    'ingredients_col_name'      => 'Name',
    'ingredients_col_unit'      => 'Unit',
    'ingredients_col_current'   => 'Current',
    'ingredients_col_low'       => 'Low alert',
    'ingredients_col_status'    => 'Status',
    'ingredients_col_restocked' => 'Restocked',

    // Ingredients - status
    'ingredients_status_ok'  => 'OK',
    'ingredients_status_low' => 'Low',

    // Ingredients - search / filters / sorts
    'ingredients_search_placeholder' => 'Search ingredient…',
    'ingredients_filter_flags'       => 'Flags',
    'ingredients_filter_low_only'    => 'Low stock only',
    'ingredients_sort_created'       => 'Created',
    'ingredients_sort_name'          => 'Name',
    'ingredients_sort_current'       => 'Current qty',
    'ingredients_sort_low'           => 'Low alert',
    'ingredients_sort_restocked'     => 'Restocked',

    // Ingredients - empty / error / confirms / toasts
    'ingredients_empty_title'        => 'No ingredients found',
    'ingredients_empty_body'         => 'Try adjusting filters or add a new ingredient.',
    'ingredients_load_failed'        => 'Couldn’t load ingredients.',
    'ingredients_confirm_delete'     => 'Delete this ingredient?',
    'ingredients_toast_created'      => 'Ingredient created',
    'ingredients_toast_updated'      => 'Ingredient updated',
    'ingredients_toast_deleted'      => 'Ingredient deleted',

    // Ingredients - misc
    'ingredients_moves' => 'moves',

    // Movements (Show page table)
    'ingredients_movements_title' => 'Movements',
    'ingredients_movements_empty' => 'No movements yet.',
    'ingredients_col_delta'       => 'Delta',
    'ingredients_col_reason'      => 'Reason',
    'ingredients_col_note'        => 'Note',
    'ingredients_col_user'        => 'User',
    'ingredients_col_time'        => 'Time',

    // Adjust stock modal
    'ingredients_adjust'            => 'Adjust stock',
    'ingredients_adjust_action'     => 'Action',
    'ingredients_adjust_qty'        => 'Qty',
    'ingredients_adjust_delta'      => 'Delta (can be negative)',
    'ingredients_action_restock'    => 'Restock',
    'ingredients_action_consume'    => 'Consume',
    'ingredients_action_adjust'     => 'Adjust (delta)',
    'ingredients_note_placeholder'  => 'Optional note…',
    'ingredients_toast_stock_updated' => 'Stock updated',

    // Edit modal hint
    'ingredients_edit_current_hint' => 'Tip: prefer using Adjust for audit trail.',
    'discounts_manager'              => 'Discounts manager',
    'discounts_title'                => 'Discounts',
    'discounts_subtitle'             => 'Manage all discounts applied to menu items and variants.',
    'discounts_search_placeholder' => 'Search by name, type…',
    'new_discount'            => 'New Discount',
    'active'                   => 'Active',
    'archived'                 => 'Archived',
    'visibility'               => 'Visibility',
    'include_archived'         => 'Include archived',
    'only_archived'            => 'Only archived',
    'active_only'            => 'Only active',
    'inactive'           => 'Inactive',
    'discounts_edit_subtitle'      => 'Update discount details for this item.',
    'discounts_create_subtitle'    => 'Create a new discount to apply to menu items or variants.',
    'update'                      => 'Update',
    'confirm_archive'           => 'Archive this',
    'archive'       => 'Archive',
    'categories_toast_updated' => 'Category updated.',
        // Dashboard
    'dashboard_title' => 'Dashboard',
    'dashboard_subtitle' => 'Today summary + quick insights from Orders and Stock.',
    'today_sales' => "Today's Sales",
    'orders_today' => 'Orders (Today)',
    'avg_order' => 'Avg. Order',
    'avg_order_sub' => 'per order',
    'cashiers_today' => 'Cashiers (Today)',
    'cashiers_today_sub' => 'unique in today orders',

    'recent_orders' => 'Recent Orders',
    'low_stock' => 'Low Stock',

    'view_all' => 'View all',
    'view_orders' => 'Orders',
    'view_stock' => 'Stock',

    'time' => 'Time',

    // Generic (used in page fallback text)
    'loading' => 'Loading…',
    'refresh' => 'Refresh',

    // Orders (used in table)
    'orders_empty_title' => 'No orders found',
    'orders_status_paid' => 'Paid',
    'orders_status_unpaid' => 'Unpaid',
    'orders_col_code' => 'Order',
    'orders_col_cashier' => 'Cashier',
    'orders_col_total' => 'Total',
    'orders_col_status' => 'Status',

    // Ingredients (used in low stock panel)
    'ingredients_status_ok' => 'OK',
    'ingredients_status_low' => 'Low',
        'recipes_title' => 'Recipe Management',
    'recipes_subtitle' => 'Create recipes for stock deduction (base + variants).',
    'recipes_manager' => 'Recipe manager',

    // Common UI
    'loading' => 'Loading…',
    'range' => 'Showing :from–:to of :total',
    'retry' => 'Retry',
    'view' => 'View',
    'edit' => 'Edit',
    'saved' => 'Saved',
    'save_failed' => 'Save failed',
    'refresh' => 'Refresh',
    'filters' => 'Filters',
    'clear' => 'Clear',
    'apply' => 'Apply',
    'reset' => 'Reset',
    'per_page' => 'Per page',
    'actions' => 'Actions',

    // Table columns
    'image' => 'Image',
    'menu_col_name' => 'Menu item',
    'variants' => 'Variants',
    'category' => 'Category',
    'status' => 'Status',

    // Empty / Errors
    'recipes_empty_title' => 'No menu items found',
    'recipes_empty_body' => 'Try searching a different keyword.',
    'recipes_load_failed' => 'Couldn’t load menu items.',

    // Search
    'menu_search_placeholder' => 'Search menu item…',

    // Stock status labels
    'ingredients_status_ok' => 'OK',
    'ingredients_status_low' => 'Low',

    // Recipe status pill
    'status_has' => 'Has',
    'status_missing' => 'Missing',

    // Sidebar footer role label (from your sidebar file)
    'app_role_label' => 'Staff user',
    // Recipe editor modal
    'recipes_edit' => 'Edit Recipe',
    'close' => 'Close',

    'scope' => 'Scope',
    'base_recipe' => 'Base recipe',
    'variant_recipe' => 'Variant recipe',

    'variant' => 'Variant',
    'select_variant' => 'Select variant',

    'ingredients' => 'Ingredients',
    'add' => 'Add',

    'qty' => 'Qty',
    'unit' => 'Unit',
    'stock' => 'Stock',
    'status' => 'Status',

    'cancel' => 'Cancel',
    'save' => 'Save',
    'menu_sizes_suffix' => 'Size',
    'menu_filter_with_trashed' => 'Include archived',
    'menu_filter_category_all' => 'All categories',

    // Nested ingredient line modal
    'add_ingredient' => 'Add ingredient',
    'search_ingredient' => 'Search ingredient…',
    'selected' => 'Selected',
      'customer_title' => 'Customer',
  'customer_title_home' => 'Product listing',
  'customer_title_cart' => 'Cart',
  'customer_title_history' => 'History',
  'customer_title_profile' => 'My profile',

  'customer_search' => 'Search',
  'customer_product_listing' => 'Product listing',
  'customer_see_all' => 'See all',

  'customer_your_cart' => 'Your cart',
  'customer_clear' => 'Clear',
  'customer_subtotal' => 'Subtotal',
  'customer_shipping' => 'Shipping',
  'customer_total' => 'Total',
  'customer_checkout' => 'Checkout',
  'customer_cart_empty' => 'Your cart is empty.',
  'customer_each' => 'each',

  'customer_order_history' => 'Order history',
  'customer_no_orders' => 'No orders yet.',
  'customer_items' => 'Items',
  'customer_pending' => 'Pending',

  'customer_logout' => 'Log out',
  'customer_name' => 'Name',
  'customer_phone' => 'Phone',
  'customer_email' => 'Email',
  'customer_password' => 'Password',
  'customer_birthday' => 'Birthday',
  'customer_set_birthday' => 'Set birthday',
  'customer_save' => 'Save',
  'customer_saved_demo' => 'Saved ✅ (demo)',
  'customer_logout_demo' => 'Logged out ✅ (demo)',

  'customer_nav_home' => 'Home',
  'customer_nav_cart' => 'Cart',
  'customer_nav_history' => 'History',
  'customer_nav_account' => 'Account',

  'theme_dark' => 'Dark',
  'theme_light' => 'Light',






];
