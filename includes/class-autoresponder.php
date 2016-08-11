<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Autoresponder
 * @subpackage Autoresponder/includes
 * @author     mbj-webdevelopment <mbjwebdevelopment@gmail.com>
 */
class Autoresponder {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Autoresponder_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->plugin_name = 'autoresponder';
        $this->version = '2.0.4';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_constants();
        add_action('parse_request', array($this, 'handle_api_requests'), 0);
        add_action('autoresponder_api_ipn_handler', array($this, 'autoresponder_api_ipn_handler'));
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Autoresponder_Loader. Orchestrates the hooks of the plugin.
     * - Autoresponder_i18n. Defines internationalization functionality.
     * - Autoresponder_Admin. Defines all hooks for the admin area.
     * - Autoresponder_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-autoresponder-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-autoresponder-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-autoresponder-admin.php';
        
        /**
         * The class responsible for defining all function related to log file
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-autoresponderl-logger.php';
        
        $this->loader = new Autoresponder_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Autoresponder_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Autoresponder_i18n();
        $plugin_i18n->set_domain($this->get_plugin_name());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Autoresponder_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action( 'autoresponder_paypal_ipn_handler', $plugin_admin, 'autoresponder_paypal_ipn_handler_own', 10, 1);
        
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $order_status = array("pending" => "pending", "failed" => "failed", "on-hold" => "on-hold", "processing" => "processing", "completed" => "completed", "refunded" => "refunded", "cancelled" => "cancelled");
            foreach ($order_status as $status) {
                $this->loader->add_action( 'woocommerce_order_status_'.$status, $plugin_admin, 'add_contact_subscriber_customer_email_for_woo_order_status', 10, 1 );
            }
        }
        if (in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $this->loader->add_action( 'wpcf7_before_send_mail', $plugin_admin, 'custom_mail_sent_function_for_contact_form7', 10, 1 );
        }
        if (in_array('si-contact-form/si-contact-form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $this->loader->add_action( 'fsctf_mail_sent', $plugin_admin, 'custom_mail_sent_function_for_fast_secure_contact_form', 10, 1 );
        }
        if (in_array('jigoshop/jigoshop.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $jigoshop_order_status = array("pending" => "pending", "on-hold" => "on-hold", "waiting-for-payment" => "waiting-for-payment", "processing" => "processing", "completed" => "completed", "refunded" => "refunded", "cancelled" => "cancelled");
            foreach ($jigoshop_order_status as $status) {
                $this->loader->add_action( 'order_status_'.$status, $plugin_admin, 'add_contact_subscriber_customer_email_for_jigoshop_order_status', 10, 1 );
            }
        }
        if (in_array('contact-form-plugin/contact_form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $this->loader->add_action('cntctfrm_get_mail_data', $plugin_admin, 'custom_mail_sent_function_for_contact_form_by_bestwebsoft', 10, 10);
        }
        if (in_array('wr-contactform/main.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $this->loader->add_action('wr_contactform_after_save_form', $plugin_admin, 'custom_mail_sent_function_for_WR_contact_form', 10, 8);
        }
        if (in_array('ninja-forms/ninja-forms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $this->loader->add_action('ninja_forms_process', $plugin_admin, 'custom_mail_sent_function_for_ninja_contact_form', 10);
        }
        if (in_array('caldera-forms/caldera-core.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $this->loader->add_action('caldera_forms_submit_complete', $plugin_admin, 'custom_mail_sent_function_for_caldera_contact_form', 10, 3);
        }
        if (in_array('jetpack/jetpack.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $this->loader->add_action('grunion_pre_message_sent', $plugin_admin, 'custom_mail_sent_function_for_jetpack_contact_form', 15, 3);
        }
        if (in_array('iphorm-form-builder/iphorm-form-builder.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $this->loader->add_action('iphorm_post_process', $plugin_admin, 'custom_mail_sent_function_for_quform_contact_form', 10, 1);
        }
        if (in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $this->loader->add_action('gform_post_submission', $plugin_admin, 'custom_mail_sent_function_for_gravity_contact_form', 10, 2);
        }
        $theme = wp_get_theme(); 
        if ('Enfold' == $theme->name || 'Enfold' == $theme->parent_theme) {
            $this->loader->add_filter('avf_form_send', $plugin_admin, 'custom_mail_sent_function_for_enfold_theme_contact_form', 10, 3);
        }
        
        $this->loader->add_filter('autoresponder_paypal_args', $plugin_admin, 'autoresponder_paypal_args', 10, 1);
        $this->loader->add_filter('autoresponder_paypal_digital_goods_nvp_args', $plugin_admin, 'autoresponder_paypal_digital_goods_nvp_args', 10, 1);
        $this->loader->add_filter('autoresponder_gateway_paypal_pro_payflow_request', $plugin_admin, 'autoresponder_gateway_paypal_pro_payflow_request', 10, 1);
        $this->loader->add_filter('autoresponder_gateway_paypal_pro_request', $plugin_admin, 'autoresponder_gateway_paypal_pro_request', 10, 1);
    }
    
    private function define_constants() {
        if (!defined('AUTORESPONDER_LOG_DIR')) {
            define('AUTORESPONDER_LOG_DIR', ABSPATH . 'autoresponder-logs/');
        }
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Autoresponder_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
    
    public function handle_api_requests() {
        global $wp;		
        if (isset($_GET['action']) && $_GET['action'] == 'ipn_handler') {			
            $wp->query_vars['Autoresponder'] = $_GET['action'];
        }
        if (!empty($wp->query_vars['Autoresponder'])) {			
            ob_start();
            $api = strtolower(esc_attr($wp->query_vars['Autoresponder']));			
            do_action('autoresponder_api_' . $api);
            ob_end_clean();
            die('1');
			
        }
    }
    
    public function autoresponder_api_ipn_handler() {			
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-autoresponder-paypal-listner.php';
        $Autoresponder_PayPal_listner = new Autoresponder_PayPal_listner();		
        if ($Autoresponder_PayPal_listner->check_ipn_request()) {			
            $Autoresponder_PayPal_listner->successful_request($IPN_status = true);
        } else {			
            $Autoresponder_PayPal_listner->successful_request($IPN_status = false);
        }
    }
}