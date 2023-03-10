<?php
/**
 * Plugin Name: Custom WooCommerce Access
 * Plugin URI: https://github.com/kamephis/woocommerce-group-access
 * Description: Restricts purchasing to selected user roles.
 * Version: 1.0.0
 * Author: Marlon Boehland
 * Author URI: https://www.boehland.one
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-registration-agreement
 * Domain Path: /languages
 */

/**
 * Restrict purchasing for non-customer users
 *
 * @return void
 */
function restrict_purchase_access()
{
    $user = wp_get_current_user();
    $selected_roles = get_option('custom_woocommerce_access_role', array('customer'));
    $has_access = false;

    foreach ($selected_roles as $role) {
        if (in_array($role, $user->roles)) {
            $has_access = true;
            break;
        }
    }

    if (!$has_access) {
        add_filter('woocommerce_is_purchasable', '__return_false', 10, 2);
    }
}

add_action('template_redirect', 'restrict_purchase_access');
add_action('admin_init', 'custom_woocommerce_access_settings');

/**
 * @return void
 */
function custom_woocommerce_access_settings()
{
    register_setting('custom_woocommerce_access_settings', 'custom_woocommerce_access_role');
    add_settings_section('custom_woocommerce_access_section', 'Access Settings', 'custom_woocommerce_access_section_text', 'custom-woocommerce-access');
}

/**
 * @return void
 */
function custom_woocommerce_access_section_text()
{
    echo '<p>Select the user role to grant access to the shop:</p>';
}

add_action('admin_menu', 'custom_woocommerce_access_menu');

/**
 * @return void
 */
function custom_woocommerce_access_menu()
{
    add_options_page('Custom WooCommerce Access Settings', 'Custom WooCommerce Access', 'manage_options', 'custom-woocommerce-access', 'custom_woocommerce_access_settings_page');
}

/**
 * @return void
 * @todo: add an option for not logged in users
 */
/**
 * @return void
 */
function custom_woocommerce_access_settings_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $selected_roles = get_option('custom_woocommerce_access_role', array('customer'));

    if (!is_array($selected_roles)) {
        $selected_roles = array($selected_roles);
    }

    echo '<div class="wrap">';
    echo '<h2>Custom WooCommerce Access Settings</h2>';
    echo '<form method="post" action="options.php">';
    settings_fields('custom_woocommerce_access_settings');
    do_settings_sections('custom-woocommerce-access');
    echo '<label>Select User Role(s) to grant access:</label><br />';

    $all_roles = wp_roles()->roles;

    foreach ($all_roles as $role_name => $role_info) {
        $checked = in_array($role_name, $selected_roles) ? 'checked="checked"' : '';
        echo '<label>';
        echo '<input type="checkbox" name="custom_woocommerce_access_role[]" value="' . esc_attr($role_name) . '" ' . $checked . '>';
        echo esc_html($role_info['name']);
        echo '</label><br />';
    }

    submit_button();
    echo '</form></div>';
}

