<?php

/**
 * @since      1.0.0
 * @package    Autoresponder
 * @subpackage Autoresponder/includes
 * @author     mbj-webdevelopment <mbjwebdevelopment@gmail.com>
 */
class Autoresponder_Admin_WordPress_List {

    public static function init() {
        add_action('admin_print_scripts', array(__CLASS__, 'disable_autosave'));
        if (is_admin()) {
            add_action('init', array(__CLASS__, 'autoresponder_register_post_types'), 5);
        }
        add_action('add_meta_boxes', array(__CLASS__, 'autoresponder_remove_meta_boxes'), 10);
        add_action('manage_edit-paypal_payment_list_columns', array(__CLASS__, 'autoresponder_add_paypal_payment_list_columns'), 10, 2);
        add_action('manage_paypal_payment_list_posts_custom_column', array(__CLASS__, 'autoresponder_render_paypal_payment_list_columns'), 2);
        add_filter('manage_edit-paypal_payment_list_sortable_columns', array(__CLASS__, 'autoresponder_paypal_payment_list_sortable_columns'));
        add_action('pre_get_posts', array(__CLASS__, 'autoresponder_ipn_column_orderby'));
        add_action('add_meta_boxes', array(__CLASS__, 'autoresponder_add_meta_boxes_ipn_data_custome_fields'), 31);
    }

    public static function autoresponder_register_post_types() {
        global $wpdb;
        if (post_type_exists('paypal_payment_list')) {
            return;
        }

        do_action('autoresponder_register_post_type');

        register_post_type('paypal_payment_list', apply_filters('autoresponder_register_post_type_ipn', array(
            'labels' => array(
                'name' => __('PayPal Payment', 'autoresponder'),
                'singular_name' => __('PayPal Payment', 'autoresponder'),
                'menu_name' => _x('PayPal Payment', 'Admin menu name', 'autoresponder'),
                'add_new' => __('Add PayPal Payment', 'autoresponder'),
                'add_new_item' => __('Add New PayPal Payment', 'autoresponder'),
                'edit' => __('Edit', 'autoresponder'),
                'edit_item' => __('View PayPal Payment', 'autoresponder'),
                'new_item' => __('New PayPal Payment', 'autoresponder'),
                'view' => __('View PayPal Payment', 'autoresponder'),
                'view_item' => __('View PayPal Payment', 'autoresponder'),
                'search_items' => __('Search PayPal Payment', 'autoresponder'),
                'not_found' => __('No PayPal Payment found', 'autoresponder'),
                'not_found_in_trash' => __('No PayPal Payment found in trash', 'autoresponder'),
                'parent' => __('Parent PayPal Payment', 'autoresponder')
            ),
            'description' => __('This is where you can add new IPN to your store.', 'autoresponder'),
            'public' => false,
            'show_ui' => true,
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => false,
            ),
            'map_meta_cap' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'hierarchical' => false,
            'rewrite' => array('slug' => 'paypal_payment_list'),
            'query_var' => true,
            'menu_icon' => PDW_PLUGIN_URL . 'admin/images/autoresponder.png',
            'supports' => array('', ''),
            'has_archive' => true,
            'show_in_nav_menus' => true
                        )
                )
        );
    }

    public static function autoresponder_remove_meta_boxes() {

        remove_meta_box('submitdiv', 'paypal_payment_list', 'side');
        remove_meta_box('slugdiv', 'paypal_payment_list', 'normal');
    }

    public static function autoresponder_add_paypal_payment_list_columns($existing_columns) {
        $columns = array();
        $columns['cb'] = '<input type="checkbox" />';
        $columns['title'] = _x('Transaction ID', 'column name');
        $columns['first_name'] = _x('Name / Company', 'column name');
        $columns['mc_gross'] = __('Amount', 'column name');
        $columns['txn_type'] = __('Transaction Type', 'column name');
        $columns['payment_status'] = __('Payment status');
        $columns['payment_date'] = _x('Date', 'column name');
        return $columns;
    }

    public static function autoresponder_render_paypal_payment_list_columns($column) {
        global $post;

        switch ($column) {
            case 'payment_date' :
                echo esc_attr(get_post_meta($post->ID, 'payment_date', true));
                break;
            case 'first_name' :
                echo esc_attr(get_post_meta($post->ID, 'first_name', true) . ' ' . get_post_meta($post->ID, 'last_name', true));
                echo (get_post_meta($post->ID, 'payer_business_name', true)) ? ' / ' . get_post_meta($post->ID, 'payer_business_name', true) : '';
                break;
            case 'mc_gross' :
                echo esc_attr(get_post_meta($post->ID, 'mc_gross', true)) . ' ' . esc_attr(get_post_meta($post->ID, 'mc_currency', true));
                break;
            case 'txn_type' :
                echo esc_attr(get_post_meta($post->ID, 'txn_type', true));
                break;

            case 'payment_status' :
                echo esc_attr(get_post_meta($post->ID, 'payment_status', true));
                break;
        }
    }

    public static function disable_autosave() {
        global $post;

        if ($post && get_post_type($post->ID) === 'paypal_payment_list') {
            wp_dequeue_script('autosave');
        }
    }

    public static function autoresponder_paypal_payment_list_sortable_columns($columns) {

        $custom = array(
            'title' => 'txn_id',
            'invoice' => 'invoice',
            'payment_date' => 'payment_date',
            'first_name' => 'first_name',
            'mc_gross' => 'mc_gross',
            'txn_type' => 'txn_type',
            'payment_status' => 'payment_status',
            'payment_date' => 'payment_date'
        );

        return wp_parse_args($custom, $columns);
    }

    public static function autoresponder_ipn_column_orderby($query) {
        global $wpdb;
        if (is_admin() && isset($_GET['post_type']) && $_GET['post_type'] == 'paypal_payment_list' && isset($_GET['orderby']) && $_GET['orderby'] != 'None') {
            $query->query_vars['orderby'] = 'meta_value';
            $query->query_vars['meta_key'] = $_GET['orderby'];
        }
    }

    public static function autoresponder_add_meta_boxes_ipn_data_custome_fields() {

        add_meta_box('paypal-payment-list-ipn-data-custome-field', __('PayPal Payment List Fields', 'autoresponder'), array(__CLASS__, 'autoresponder_display_ipn_custome_fields'), 'paypal_payment_list', 'normal', 'high');
    }

    public static function autoresponder_display_ipn_custome_fields() {
        if ($keys = get_post_custom_keys()) {
            echo "<div class='wrap'>";
            echo "<table class='widefat'><thead>
                        <tr>
                            <th>" . __('IPN Field Name', 'autoresponder') . "</th>
                            <th>" . __('IPN Field Value', 'autoresponder') . "</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>" . __('IPN Field Name', 'autoresponder') . "</th>
                            <th>" . __('IPN Field Value', 'autoresponder') . "</th>

                        </tr>
                    </tfoot>";
            foreach ((array) $keys as $key) {
                $keyt = trim($key);
                if (is_protected_meta($keyt, 'post'))
                    continue;
                $values = array_map('trim', get_post_custom_values($key));
                $value = implode($values, ', ');
                echo "<tr><th class='post-meta-key'>$key:</th> <td>$value</td></tr>";
            }
            echo "</table>";
            echo "</div";
        }
    }

}

Autoresponder_Admin_WordPress_List::init();