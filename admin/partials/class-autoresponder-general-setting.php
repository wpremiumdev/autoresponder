<?php
/**
 * @since      1.0.0
 * @package    Autoresponder
 * @subpackage Autoresponder/includes
 * @author     mbj-webdevelopment <mbjwebdevelopment@gmail.com>
 */
include_once AUTO_PLUGIN_DIR_PATH . '/admin/partials/lib/constant_contact/Ctct/autoload.php';

use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\Contacts\EmailAddress;
use Ctct\Exceptions\CtctException;
use Ctct\Auth\SessionDataStore;
use Ctct\Auth\CtctDataStore;
use Ctct\Services;

include_once AUTO_PLUGIN_DIR_PATH . '/admin/partials/lib/constant_contact/Ctct/ConstantContact.php';

class Autoresponder_Admin_General_Setting {

    public static function init() {

        add_action('autoresponder_mailchimp_setting_save_field', array(__CLASS__, 'autoresponder_mailchimp_setting_save_field'));
        add_action('autoresponder_mailchimp_setting', array(__CLASS__, 'autoresponder_mailchimp_setting'));

        add_action('autoresponder_getresponse_setting_save_field', array(__CLASS__, 'autoresponder_getresponse_setting_save_field'));
        add_action('autoresponder_getresponse_setting', array(__CLASS__, 'autoresponder_getresponse_setting'));

        add_action('autoresponder_icontact_setting_save_field', array(__CLASS__, 'autoresponder_icontact_setting_save_field'));
        add_action('autoresponder_icontact_setting', array(__CLASS__, 'autoresponder_icontact_setting'));

        add_action('autoresponder_infusionsoft_setting_save_field', array(__CLASS__, 'autoresponder_infusionsoft_setting_save_field'));
        add_action('autoresponder_infusionsoft_setting', array(__CLASS__, 'autoresponder_infusionsoft_setting'));

        add_action('autoresponder_constantcontact_setting_save_field', array(__CLASS__, 'autoresponder_constantcontact_setting_save_field'));
        add_action('autoresponder_constantcontact_setting', array(__CLASS__, 'autoresponder_constantcontact_setting'));

        add_action('autoresponder_campaignmonitor_setting_save_field', array(__CLASS__, 'autoresponder_campaignmonitor_setting_save_field'));
        add_action('autoresponder_campaignmonitor_setting', array(__CLASS__, 'autoresponder_campaignmonitor_setting'));
    }

    public static function autoresponder_mailchimp_setting_fields() {        
        $fields[] = array('title' => __('MailChimp Integration', 'autoresponder'), 'type' => 'title', 'desc' => '', 'id' => 'general_options');
        if (in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Contact Form-7', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_7_mailchimp');        
        }
        if (in_array('si-contact-form/si-contact-form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Fast Secure Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_fast_secure_contact_form_mailchimp'); 
        }
        if (in_array('contact-form-plugin/contact_form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Contact Form by BestWebSoft', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_by_bestwebsoft_mailchimp'); 
        }
        if (in_array('wr-contactform/main.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For WR Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_WR_contact_form_mailchimp'); 
        }
        if (in_array('ninja-forms/ninja-forms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Ninja Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_ninja_contact_form_mailchimp'); 
        }
        if (in_array('caldera-forms/caldera-core.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Caldera Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_caldera_contact_form_mailchimp'); 
        }
        if (in_array('jetpack/jetpack.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Jetpack Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jetpack_contact_form_mailchimp'); 
        }
        if (in_array('iphorm-form-builder/iphorm-form-builder.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Quform', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_qu_contact_form_mailchimp'); 
        }
        if (in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Gravity Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_gravity_contact_form_mailchimp'); 
        }
        $theme = wp_get_theme(); 
        if ('Enfold' == $theme->name || 'Enfold' == $theme->parent_theme) {
            $fields[] = array('title' => __('Enable For Enfold Theme Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_enfold_theme_contact_form_mailchimp'); 
        }
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Woocommerce', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_woocommerce_mailchimp');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'mailchimp_woo_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_woocommerce_email_order_status_list()
            );
        }
        if (in_array('jigoshop/jigoshop.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Jigoshop', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jigoshop_mailchimp');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'mailchimp_jigoshop_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_jigoshop_email_order_status_list()
            );
        }
        $fields[] = array(
            'title' => __('MailChimp API Key', 'autoresponder'),
            'desc' => __('Enter your API Key. <a target="_blank" href="http://admin.mailchimp.com/account/api-key-popup">Get your API key</a>', 'autoresponder'),
            'id' => 'mailchimp_api_key',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );
        $fields[] = array(
            'title' => __('MailChimp lists', 'autoresponder'),
            'desc' => __('After you add your MailChimp API Key above and save it this list will be populated.', 'Option'),
            'id' => 'mailchimp_lists',
            'css' => 'min-width:300px;',
            'type' => 'select',
            'options' => self::autoresponder_get_mailchimp_lists(get_option('mailchimp_api_key'))
        );
        $fields[] = array(
            'title' => __('Force MailChimp lists refresh', 'autoresponder'),
            'desc' => __("Check and 'Save changes' this if you've added a new MailChimp list and it's not showing in the list above.", 'autoresponder'),
            'id' => 'autoresponder_mailchimp_force_refresh',
            'type' => 'checkbox',
        );
        $fields[] = array(
            'title' => __('Debug Log', 'autoresponder'),
            'id' => 'log_enable_mailChimp',
            'type' => 'checkbox',
            'label' => __('Enable logging', 'autoresponder'),
            'default' => 'no',
            'desc' => sprintf(__('Log MailChimp events, inside <code>%s</code>', 'autoresponder'), AUTORESPONDER_LOG_DIR)
        );
        $fields[] = array('type' => 'sectionend', 'id' => 'general_options');
        return $fields;
    }

    public static function autoresponder_mailchimp_setting() {
        $mailchimp_setting_fields = self::autoresponder_mailchimp_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        ?>
        <form id="mailChimp_integration_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($mailchimp_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="mailChimp_integration" class="button-primary" value="<?php esc_attr_e('Save changes', 'Option'); ?>" />
            </p>
        </form>
        <?php
    }

    public static function autoresponder_get_mailchimp_lists($apikey) {
        $mailchimp_lists = array();        
        $mailchimp_debug = (get_option('log_enable_mailChimp') == 'yes') ? 'yes' : 'no';
        
        if ('yes' == $mailchimp_debug) {
            $log = new Autoresponder_Logger();
        }        
        
        if (isset($apikey) && !empty($apikey)) {
            $mailchimp_lists = unserialize(get_transient('autoresponder_mailchimp_list'));            
            $mailchimp_debug_log = (get_option('log_enable_mailchimp') == 'yes') ? 'yes' : 'no';
            
            if (empty($mailchimp_lists) || get_option('autoresponder_mailchimp_force_refresh') == 'yes') {
                include_once AUTO_PLUGIN_DIR_PATH . '/admin/partials/lib/mailchimp/mailchimp.php';
                
                $api = new Autoresponder_MailChimp_API($apikey);
                $retval = $api->lists();
                if ($api->errorCode) {
                    unset($mailchimp_lists);
                    $mailchimp_lists['false'] = __("Unable to load MailChimp lists, check your API Key.", 'doation-button');
                    if ('yes' == $mailchimp_debug_log) {
                        $log->add('MailChimp', 'Unable to load MailChimp lists, check your API Key.');
                    }
                } else {
                    unset($mailchimp_lists);
                    if ($retval['total'] == 0) {
                        if ('yes' == $mailchimp_debug_log) {
                            $log->add('MailChimp', 'You have not created any lists at MailChimp.');
                        }
                        $mailchimp_lists['false'] = __("You have not created any lists at MailChimp", 'doation-button');
                        return $mailchimp_lists;
                    }
                    foreach ($retval['data'] as $list) {
                        $mailchimp_lists[$list['id']] = $list['name'];
                    }
                    if ('yes' == $mailchimp_debug_log) {
                        $log->add('MailChimp', 'MailChimp Get List Success..');
                    }
                    set_transient('autoresponder_mailchimp_list', serialize($mailchimp_lists), 86400);
                    update_option('autoresponder_mailchimp_force_refresh', 'no');
                }
            }
        }
        return $mailchimp_lists;
    }


    public static function autoresponder_mailchimp_setting_save_field() {
        $mailchimp_setting_fields = self::autoresponder_mailchimp_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        $Html_output->save_fields($mailchimp_setting_fields);
    }

    public static function autoresponder_getresponse_setting_fields() {
        $fields[] = array('title' => __('Getesponse Integration', 'autoresponder'), 'type' => 'title', 'desc' => '', 'id' => 'general_options');
        if (in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Contact Form-7', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_7_getresponse');        
        }
        if (in_array('si-contact-form/si-contact-form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Fast Secure Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_fast_secure_contact_form_getresponse');
        }
        if (in_array('contact-form-plugin/contact_form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Contact Form by BestWebSoft', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_by_bestwebsoft_getresponse'); 
        }
        if (in_array('wr-contactform/main.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For WR Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_WR_contact_form_getresponse'); 
        }
        if (in_array('ninja-forms/ninja-forms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Ninja Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_ninja_contact_form_getresponse'); 
        }
        if (in_array('caldera-forms/caldera-core.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Caldera Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_caldera_contact_form_getresponse'); 
        }
        if (in_array('jetpack/jetpack.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Jetpack Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jetpack_contact_form_getresponse'); 
        }
        if (in_array('iphorm-form-builder/iphorm-form-builder.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Quform', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_qu_contact_form_getresponse'); 
        }
        if (in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Gravity Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_gravity_contact_form_getresponse'); 
        }
        $theme = wp_get_theme(); 
        if ('Enfold' == $theme->name || 'Enfold' == $theme->parent_theme) {
            $fields[] = array('title' => __('Enable For Enfold Theme Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_enfold_theme_contact_form_getresponse'); 
        }
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Woocommerce', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_woocommerce_getresponse');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'getresponse_woo_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_woocommerce_email_order_status_list()
            );
        } 
        if (in_array('jigoshop/jigoshop.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Jigoshop', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jigoshop_getresponse');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'getresponse_jigoshop_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_jigoshop_email_order_status_list()
            );
        }
        $fields[] = array(
            'title' => __('Getesponse API Key', 'autoresponder'),
            'desc' => __('Enter your API Key. <a target="_blank" href="https://app.getresponse.com/account.html#api">Get your API key</a>', 'autoresponder'),
            'id' => 'getresponse_api_key',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );
        $fields[] = array(
            'title' => __('Getesponse lists', 'autoresponder'),
            'desc' => __('After you add your Getesponse API Key above and save it this list will be populated.', 'Option'),
            'id' => 'getresponse_lists',
            'css' => 'min-width:300px;',
            'type' => 'select',
            'options' => self::autoresponder_get_getresponse_lists(get_option('getresponse_api_key'))
        );
        $fields[] = array(
            'title' => __('Force Getesponse lists refresh', 'autoresponder'),
            'desc' => __("Check and 'Save changes' this if you've added a new Getesponse list and it's not showing in the list above.", 'autoresponder'),
            'id' => 'autoresponder_getresponse_force_refresh',
            'type' => 'checkbox',
        );
        $fields[] = array(
            'title' => __('Debug Log', 'autoresponder'),
            'id' => 'log_enable_getresponse',
            'type' => 'checkbox',
            'label' => __('Enable logging', 'autoresponder'),
            'default' => 'no',
            'desc' => sprintf(__('Log Getesponse events, inside <code>%s</code>', 'autoresponder'), AUTORESPONDER_LOG_DIR)
        );
        $fields[] = array('type' => 'sectionend', 'id' => 'general_options');
        return $fields;
    }

    public static function autoresponder_getresponse_setting() {
        $getresponse_setting_fields = self::autoresponder_getresponse_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        ?>
        <form id="Getresponse_integration_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($getresponse_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="Getresponse_integration" class="button-primary" value="<?php esc_attr_e('Save changes', 'Option'); ?>" />
            </p>
        </form>
        <?php
    }

	
    public static function autoresponder_get_getresponse_lists($apikey) {
        $getresponse_lists = array();
        $getresponse_debug = (get_option('log_enable_getresponse') == 'yes') ? 'yes' : 'no';
        if ('yes' == $getresponse_debug) {
            $log = new Autoresponder_Logger();
        }
        if (isset($apikey) && !empty($apikey)) {
            $getresponse_lists = unserialize(get_transient('autoresponder_getresponse_list'));
            if (empty($getresponse_lists) || get_option('autoresponder_getresponse_force_refresh') == 'yes') {
                include_once AUTO_PLUGIN_DIR_PATH . 'admin/partials/lib/getresponse/getresponse.php';
                $api = new Autoresponder_Getesponse_API($apikey);
                $campaigns = $api->getCampaigns();
                $campaigns = (array) $campaigns;
                if (count($campaigns) > 0 and is_array($campaigns)) {
                    unset($getresponse_lists);
                    foreach ($campaigns as $list_id => $list) {
                        $list = (array) $list;
                        $getresponse_lists[$list_id] = $list['name'];
                    }
                    delete_transient('autoresponder_getresponse_list');
                    set_transient('autoresponder_getresponse_list', serialize($getresponse_lists), 86400);
                    if ('yes' == $getresponse_debug) {
                        $log->add('Getresponse', 'Getresponse Get List Success..');
                    }
                    update_option('autoresponder_getresponse_force_refresh', 'no');
                } else {
                    unset($getresponse_lists);
                    $getresponse_lists = array();
                    $getresponse_lists['false'] = __("Unable to load Getesponse lists, check your API Key.", 'autoresponder');
                    if ('yes' == $getresponse_debug) {
                        $log->add('Getresponse', 'Unable to load Getesponse lists, check your API Key.');
                    }
                }
            }
        } else {
            $getresponse_lists['false'] = __("API Key is empty.", 'autoresponder');
            if ('yes' == $getresponse_debug) {
                $log->add('Getresponse', 'API Key is empty.');
            }
        }
        return $getresponse_lists;
    }

    public static function autoresponder_getresponse_setting_save_field() {
        $getresponse_setting_fields = self::autoresponder_getresponse_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        $Html_output->save_fields($getresponse_setting_fields);
    }

    public static function autoresponder_icontact_setting_fields() {
        $fields[] = array('title' => __('Icontact Integration', 'autoresponder'), 'type' => 'title', 'desc' => '', 'id' => 'general_options');
        if (in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Contact Form-7', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_7_icontact');        
        }
        if (in_array('si-contact-form/si-contact-form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Fast Secure Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_fast_secure_contact_form_icontact');
        }
        if (in_array('contact-form-plugin/contact_form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Contact Form by BestWebSoft', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_by_bestwebsoft_icontact'); 
        }
        if (in_array('wr-contactform/main.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For WR Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_WR_contact_form_icontact'); 
        }
        if (in_array('ninja-forms/ninja-forms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Ninja Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_ninja_contact_form_icontact'); 
        }
        if (in_array('caldera-forms/caldera-core.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Caldera Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_caldera_contact_form_icontact'); 
        }
        if (in_array('jetpack/jetpack.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Jetpack Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jetpack_contact_form_icontact'); 
        }
        if (in_array('iphorm-form-builder/iphorm-form-builder.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Quform', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_qu_contact_form_icontact'); 
        }
        if (in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Gravity Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_gravity_contact_form_icontact'); 
        }
        $theme = wp_get_theme(); 
        if ('Enfold' == $theme->name || 'Enfold' == $theme->parent_theme) {
            $fields[] = array('title' => __('Enable For Enfold Theme Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_enfold_theme_contact_form_icontact'); 
        }
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Woocommerce', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_woocommerce_icontact');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'icontact_woo_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_woocommerce_email_order_status_list()
            );
        }
        if (in_array('jigoshop/jigoshop.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Jigoshop', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jigoshop_icontact');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'icontact_jigoshop_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_jigoshop_email_order_status_list()
            );
        }
        $fields[] = array(
            'title' => __('Icontact Application ID', 'autoresponder'),
            'desc' => __('Obtained when you Register the API application. <a target="_blank" href="https://app.icontact.com/icp/core/registerapp/">Get your API key</a> This identifier is used to uniquely identify your application.', 'autoresponder'),
            'id' => 'icontact_api_id',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );
        $fields[] = array(
            'title' => __('Icontact Username/Email ID', 'autoresponder'),
            'desc' => __('The iContact username for logging into your iContact account. If you are using the sandbox for testing, this is your sandbox environment username.', 'autoresponder'),
            'id' => 'icontact_api_username',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );
        $fields[] = array(
            'title' => __('Icontact Application Password', 'autoresponder'),
            'desc' => __('The API application password set when the application was registered. This API password is used as input when your application authenticates to the API. This password is not the same as the password you use to log in to iContact.', 'autoresponder'),
            'id' => 'icontact_api_password',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );
        $fields[] = array(
            'title' => __('Icontact lists', 'autoresponder'),
            'desc' => __('After you add your Icontact API Key above and save it this list will be populated.', 'Option'),
            'id' => 'icontact_lists',
            'css' => 'min-width:300px;',
            'type' => 'select',
            'options' => self::autoresponder_get_icontact_lists()
        );
        $fields[] = array(
            'title' => __('Force Icontact lists refresh', 'autoresponder'),
            'desc' => __("Check and 'Save changes' this if you've added a new Icontact list and it's not showing in the list above.", 'autoresponder'),
            'id' => 'autoresponder_icontact_force_refresh',
            'type' => 'checkbox',
        );
        $fields[] = array(
            'title' => __('Debug Log', 'autoresponder'),
            'id' => 'log_enable_icontact',
            'type' => 'checkbox',
            'label' => __('Enable logging', 'autoresponder'),
            'default' => 'no',
            'desc' => sprintf(__('Log Icontact events, inside <code>%s</code>', 'autoresponder'), AUTORESPONDER_LOG_DIR)
        );
        $fields[] = array('type' => 'sectionend', 'id' => 'general_options');
        return $fields;
    }

    public static function autoresponder_icontact_setting() {
        $icontact_setting_fields = self::autoresponder_icontact_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        ?>
        <form id="Icontact_integration_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($icontact_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="Icontact_integration" class="button-primary" value="<?php esc_attr_e('Save changes', 'Option'); ?>" />
            </p>
        </form>
        <?php
    }

    public static function autoresponder_get_icontact_lists() {
        $icontact_lists = array();
        $icontact_lists = unserialize(get_transient('autoresponder_icontact_list'));
        $icontact_debug = (get_option('log_enable_icontact') == 'yes') ? 'yes' : 'no';
        if ('yes' == $icontact_debug) {
            $log = new Autoresponder_Logger();
        }
        if (empty($icontact_lists) || get_option('autoresponder_icontact_force_refresh') == 'yes') {
            include_once AUTO_PLUGIN_DIR_PATH . '/admin/partials/lib/icontact/icontact.php';
            $icontact_api_id = get_option('icontact_api_id');
            $icontact_api_username = get_option('icontact_api_username');
            $icontact_api_password = get_option('icontact_api_password');
            if ((isset($icontact_api_id) && !empty($icontact_api_id)) && (isset($icontact_api_username) && !empty($icontact_api_username)) && (isset($icontact_api_password) && !empty($icontact_api_password))) {
                iContactApi::getInstance()->setConfig(array(
                    'appId' => get_option('icontact_api_id'),
                    'apiUsername' => get_option('icontact_api_username'),
                    'apiPassword' => get_option('icontact_api_password'),
                ));
                $oiContact = iContactApi::getInstance();
                try {
                    $lists = $oiContact->getLists();
                } catch (Exception $oException) {
                    unset($icontact_lists);
                    $icontact_lists['false'] = 'API details is invalid';
                    if ('yes' == $icontact_debug) {
                        $log->add('Icontact', 'Icontact API Details is Invalid.');
                    }
                }
                if (count($lists) > 0 and is_array($lists)) {
                    unset($icontact_lists);
                    foreach ($lists as $list) {
                        $icontact_lists[$list->listId] = $list->name;
                    }
                    delete_transient('autoresponder_icontact_list');
                    set_transient('autoresponder_icontact_list', serialize($icontact_lists), 86400);
                    if ('yes' == $icontact_debug) {
                        $log->add('Icontact', 'Icontact Get List Success..');
                    }
                    update_option('autoresponder_icontact_force_refresh', 'no');
                }
            } else {
                $icontact_lists['false'] = __("Required information is empty.", 'autoresponder');
                if ('yes' == $icontact_debug) {
                    $log->add('Icontact', 'Required information is empty.');
                }
            }
        }
        return $icontact_lists;
    }

    public static function autoresponder_icontact_setting_save_field() {
        $icontact_setting_fields = self::autoresponder_icontact_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        $Html_output->save_fields($icontact_setting_fields);
    }

    public static function autoresponder_infusionsoft_setting_fields() {
        $fields[] = array('title' => __('Infusionsoft Integration', 'autoresponder'), 'type' => 'title', 'desc' => '', 'id' => 'general_options');
        if (in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Contact Form-7', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_7_infusionsoft');        
        }
        if (in_array('si-contact-form/si-contact-form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Fast Secure Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_fast_secure_contact_form_infusionsoft');
        }
        if (in_array('contact-form-plugin/contact_form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Contact Form by BestWebSoft', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_by_bestwebsoft_infusionsoft'); 
        }
        if (in_array('wr-contactform/main.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For WR Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_WR_contact_form_infusionsoft'); 
        }
        if (in_array('ninja-forms/ninja-forms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Ninja Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_ninja_contact_form_infusionsoft'); 
        }
        if (in_array('caldera-forms/caldera-core.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Caldera Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_caldera_contact_form_infusionsoft'); 
        }
        if (in_array('jetpack/jetpack.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Jetpack Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jetpack_contact_form_infusionsoft'); 
        }
        if (in_array('iphorm-form-builder/iphorm-form-builder.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Quform', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_qu_contact_form_infusionsoft'); 
        }
        if (in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Gravity Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_gravity_contact_form_infusionsoft'); 
        }
        $theme = wp_get_theme(); 
        if ('Enfold' == $theme->name || 'Enfold' == $theme->parent_theme) {
            $fields[] = array('title' => __('Enable For Enfold Theme Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_enfold_theme_contact_form_infusionsoft'); 
        }
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Woocommerce', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_woocommerce_infusionsoft');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'infusionsoft_woo_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_woocommerce_email_order_status_list()
            );
        }
        if (in_array('jigoshop/jigoshop.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Jigoshop', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jigoshop_infusionsoft');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'infusionsoft_jigoshop_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_jigoshop_email_order_status_list()
            );
        }
        $fields[] = array(
            'title' => __('Infusionsoft API Key', 'autoresponder'),
            'desc' => __('Enter Infusionsoft API Key', 'autoresponder'),
            'id' => 'infusionsoft_api_key',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );
        $fields[] = array(
            'title' => __('Infusionsoft Application Name', 'autoresponder'),
            'desc' => __('Enter Infusionsoft Application Name.', 'autoresponder'),
            'id' => 'infusionsoft_api_app_name',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );

        $fields[] = array(
            'title' => __('Infusionsoft lists', 'autoresponder'),
            'desc' => __('After you add your Infusionsoft API Key above and save it this list will be populated.', 'Option'),
            'id' => 'infusionsoft_lists',
            'css' => 'min-width:300px;',
            'type' => 'select',
            'options' => self::autoresponder_get_infusionsoft_lists()
        );
        $fields[] = array(
            'title' => __('Force Infusionsoft lists refresh', 'autoresponder'),
            'desc' => __("Check and 'Save changes' this if you've added a new Infusionsoft list and it's not showing in the list above.", 'autoresponder'),
            'id' => 'autoresponder_infusionsoft_force_refresh',
            'type' => 'checkbox',
        );
        $fields[] = array(
            'title' => __('Debug Log', 'autoresponder'),
            'id' => 'log_enable_infusionsoft',
            'type' => 'checkbox',
            'label' => __('Enable logging', 'autoresponder'),
            'default' => 'no',
            'desc' => sprintf(__('Log Infusionsoft events, inside <code>%s</code>', 'autoresponder'), AUTORESPONDER_LOG_DIR)
        );
        $fields[] = array('type' => 'sectionend', 'id' => 'general_options');
        return $fields;
    }

    public static function autoresponder_infusionsoft_setting() {
        $infusionsoft_setting_fields = self::autoresponder_infusionsoft_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        ?>
        <form id="Infusionsoft_integration_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($infusionsoft_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="Infusionsoft_integration" class="button-primary" value="<?php esc_attr_e('Save changes', 'Option'); ?>" />
            </p>
        </form>
        <?php
    }

    public static function autoresponder_get_infusionsoft_lists() {
        $infusionsoft_lists = array();
        $infusionsoft_debug = (get_option('log_enable_infusionsoft') == 'yes') ? 'yes' : 'no';
        if ('yes' == $infusionsoft_debug) {
            $log = new Autoresponder_Logger();
        }
        $infusionsoft_lists = unserialize(get_transient('autoresponder_infusionsoft_list'));
        if (empty($infusionsoft_lists) || get_option('autoresponder_infusionsoft_force_refresh') == 'yes') {
            include_once AUTO_PLUGIN_DIR_PATH . '/admin/partials/lib/infusionsoft/isdk.php';
            $infusionsoft_api_key = get_option('infusionsoft_api_key');
            $infusionsoft_api_app_name = get_option('infusionsoft_api_app_name');
            if ((isset($infusionsoft_api_key) && !empty($infusionsoft_api_key)) && (isset($infusionsoft_api_app_name) && !empty($infusionsoft_api_app_name))) {
                $app = new iSDK;
                try {
                    if ($app->cfgCon($infusionsoft_api_app_name, $infusionsoft_api_key)) {
                        $returnFields = array('Id', 'Name');
                        $query = array('Id' => '%');
                        $lists = $app->dsQuery("Campaign", 1000, 0, $query, $returnFields);
                    }
                } catch (Exception $e) {
                    unset($infusionsoft_lists);
                    $infusionsoft_lists['false'] = $e->getMessage();
                }

                if (count($lists) > 0 and is_array($lists)) {
                    unset($infusionsoft_lists);
                    foreach ($lists as $list) {
                        $infusionsoft_lists[$list['Id']] = $list['Name'];
                    }
                    delete_transient('autoresponder_infusionsoft_list');
                    set_transient('autoresponder_infusionsoft_list', serialize($infusionsoft_lists), 86400);
                    if ('yes' == $infusionsoft_debug) {
                        $log->add('Infusionsoft', 'Infusionsoft Get List Success..');
                    }
                    update_option('autoresponder_infusionsoft_force_refresh', 'no');
                } else {
                    unset($infusionsoft_lists);
                    if ('yes' == $infusionsoft_debug) {
                        $log->add('Infusionsoft', 'Infusionsoft API Key And Application Name Please Check.');
                    }
                    $infusionsoft_lists['false'] = __("Infusionsoft API Key And Application Name Please Check.", 'autoresponder');
                }
            } else {
                unset($infusionsoft_lists);
                if ('yes' == $infusionsoft_debug) {
                    $log->add('Infusionsoft', 'Required information is empty.');
                }
                $infusionsoft_lists['false'] = __("Required information is empty.", 'autoresponder');
            }
        }
        return $infusionsoft_lists;
    }

    public static function autoresponder_infusionsoft_setting_save_field() {
        $infusionsoft_setting_fields = self::autoresponder_infusionsoft_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        $Html_output->save_fields($infusionsoft_setting_fields);
    }

    public static function autoresponder_constantcontact_setting_fields() {
        $fields[] = array('title' => __('Constant Contact Integration', 'autoresponder'), 'type' => 'title', 'desc' => '', 'id' => 'general_options');
        if (in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Contact Form-7', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_7_constant_contact');        
        }
        if (in_array('si-contact-form/si-contact-form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Fast Secure Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_fast_secure_contact_form_constant_contact');
        }
        if (in_array('contact-form-plugin/contact_form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Contact Form by BestWebSoft', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_by_bestwebsoft_constant_contact'); 
        }
        if (in_array('wr-contactform/main.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For WR Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_WR_contact_form_constant_contact'); 
        }
        if (in_array('ninja-forms/ninja-forms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Ninja Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_ninja_contact_form_constant_contact'); 
        }
        if (in_array('caldera-forms/caldera-core.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Caldera Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_caldera_contact_form_constant_contact'); 
        }
        if (in_array('jetpack/jetpack.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Jetpack Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jetpack_contact_form_constant_contact'); 
        }
        if (in_array('iphorm-form-builder/iphorm-form-builder.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Quform', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_qu_contact_form_constant_contact'); 
        }
        if (in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Gravity Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_gravity_contact_form_constant_contact'); 
        }
        $theme = wp_get_theme(); 
        if ('Enfold' == $theme->name || 'Enfold' == $theme->parent_theme) {
            $fields[] = array('title' => __('Enable For Enfold Theme Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_enfold_theme_contact_form_constant_contact'); 
        }
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Woocommerce', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_woocommerce_constant_contact');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'constantcontact_woo_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_woocommerce_email_order_status_list()
            );
        } 
        if (in_array('jigoshop/jigoshop.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Jigoshop', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jigoshop_constant_contact');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'constantcontact_jigoshop_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_jigoshop_email_order_status_list()
            );
        }
        $fields[] = array(
            'title' => __('Constant Contact API Key', 'autoresponder'),
            'desc' => __('Enter your API Key. <a target="_blank" href="https://constantcontact.mashery.com/apps/mykeys">Get your API key</a>', 'autoresponder'),
            'id' => 'constantcontact_api_key',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );
        $fields[] = array(
            'title' => __('Constant Contact Access Token', 'autoresponder'),
            'desc' => __('Enter Your Access Token', 'autoresponder'),
            'id' => 'constantcontact_access_token',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );
        $fields[] = array(
            'title' => __('Constant Contact lists', 'autoresponder'),
            'desc' => __('After you add your Constant Contact API Key above and save it this list will be populated.', 'Option'),
            'id' => 'constantcontact_lists',
            'css' => 'min-width:300px;',
            'type' => 'select',
            'options' => self::autoresponder_get_constantcontact_lists()
        );
        $fields[] = array(
            'title' => __('Force Constant Contact lists refresh', 'autoresponder'),
            'desc' => __("Check and 'Save changes' this if you've added a new Constant Contact list and it's not showing in the list above.", 'autoresponder'),
            'id' => 'constantcontact_force_refresh',
            'type' => 'checkbox',
        );
        $fields[] = array(
            'title' => __('Debug Log', 'autoresponder'),
            'id' => 'log_enable_constant_contact',
            'type' => 'checkbox',
            'label' => __('Enable logging', 'autoresponder'),
            'default' => 'no',
            'desc' => sprintf(__('Log Constant Contact events, inside <code>%s</code>', 'autoresponder'), AUTORESPONDER_LOG_DIR)
        );
        $fields[] = array('type' => 'sectionend', 'id' => 'general_options');

        return $fields;
    }

    public static function autoresponder_constantcontact_setting() {
        $constantcontact_setting_fields = self::autoresponder_constantcontact_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        ?>
        <form id="Constant_Contact_integration_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($constantcontact_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="Constant_Contact_integration" class="button-primary" value="<?php esc_attr_e('Save changes', 'Option'); ?>" />
            </p>
        </form>
        <?php
    }

    public static function autoresponder_get_constantcontact_lists() {
        $constantcontact_lists = array();
        $concontact_api_key = get_option('constantcontact_api_key');
        $constantcontact_access_token = get_option('constantcontact_access_token');
        $constant_contact_debug = (get_option('log_enable_constant_contact') == 'yes') ? 'yes' : 'no';
        if ('yes' == $constant_contact_debug) {
            $log = new Autoresponder_Logger();
        }
        if ((isset($concontact_api_key) && !empty($concontact_api_key)) && ( isset($constantcontact_access_token) && !empty($constantcontact_access_token))) {
            $constantcontact_lists = unserialize(get_transient('constantcontact_lists'));
            if (empty($constantcontact_lists) || get_option('constantcontact_force_refresh') == 'yes') {
                try {
                    $cc = new ConstantContact($concontact_api_key);
                    $list_name = $cc->getLists($constantcontact_access_token);
                    if (isset($list_name) && !empty($list_name)) {
                        unset($constantcontact_lists);
                        foreach ($list_name as $list_namekey => $list_namevalue) {
                            $constantcontact_lists[$list_namevalue->id] = $list_namevalue->name;
                        }
                        set_transient('constantcontact_lists', serialize($constantcontact_lists), 86400);
                        if ('yes' == $constant_contact_debug) {
                            $log->add('ConstantContact', 'ConstantContact Get List Success..');
                        }
                        update_option('constantcontact_force_refresh', 'no');
                    } else {
                        if ('yes' == $constant_contact_debug) {
                            $log->add('ConstantContact', 'No ConstantContact List Available, check your API Key.');
                        }
                        $constantcontact_lists['false'] = __("Unable to load Constant Contact lists, check your API Key.", 'autoresponder');
                    }
                } catch (CtctException $ex) {
                    unset($constantcontact_lists);
                    $constantcontact_lists = array();
                    $constantcontact_lists['false'] = __("Unable to load Constant Contact lists, check your API Key.", 'autoresponder');
                    set_transient('constantcontact_lists', serialize($constantcontact_lists), 86400);
                    if ('yes' == $constant_contact_debug) {
                        $log->add('ConstantContact', 'Unable to load Constant Contact lists, check your API Key.');
                    }
                }
            }
        } else {
            $constantcontact_lists['false'] = __("Required information is empty.", 'autoresponder');
        }
        return $constantcontact_lists;
    }

    public static function autoresponder_constantcontact_setting_save_field() {
        $constantcontact_setting_fields = self::autoresponder_constantcontact_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        $Html_output->save_fields($constantcontact_setting_fields);
    }

    public static function autoresponder_campaignmonitor_setting_fields() {
        $fields[] = array('title' => __('Campaign Monitor Integration', 'autoresponder'), 'type' => 'title', 'desc' => '', 'id' => 'general_options');
        if (in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Contact Form-7', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_7_campaignmonitor');        
        }
        if (in_array('si-contact-form/si-contact-form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Fast Secure Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_fast_secure_contact_form_campaignmonitor');
        }
        if (in_array('contact-form-plugin/contact_form.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Contact Form by BestWebSoft', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_contact_form_by_bestwebsoft_campaignmonitor'); 
        }
        if (in_array('wr-contactform/main.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For WR Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_WR_contact_form_campaignmonitor'); 
        }
        if (in_array('ninja-forms/ninja-forms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Ninja Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_ninja_contact_form_campaignmonitor'); 
        }
        if (in_array('caldera-forms/caldera-core.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Caldera Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_caldera_contact_form_campaignmonitor'); 
        }
        if (in_array('jetpack/jetpack.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Jetpack Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jetpack_contact_form_campaignmonitor'); 
        }
        if (in_array('iphorm-form-builder/iphorm-form-builder.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Quform', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_qu_contact_form_campaignmonitor'); 
        }
        if (in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
           $fields[] = array('title' => __('Enable For Gravity Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_gravity_contact_form_campaignmonitor'); 
        }
        $theme = wp_get_theme(); 
        if ('Enfold' == $theme->name || 'Enfold' == $theme->parent_theme) {
            $fields[] = array('title' => __('Enable For Enfold Theme Contact Form', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_enfold_theme_contact_form_campaignmonitor'); 
        }
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Woocommerce', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_woocommerce_campaignmonitor');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'campaignmonitor_woo_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_woocommerce_email_order_status_list()
            );
        } 
        if (in_array('jigoshop/jigoshop.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $fields[] = array('title' => __('Enable For Jigoshop', 'autoresponder'), 'type' => 'checkbox', 'desc' => '', 'id' => 'enable_jigoshop_campaignmonitor');
            $fields[] = array(
                'title' => __('Send Email Notifications for these statuses', 'autoresponder'),
                'id' => 'campaignmonitor_jigoshop_email_order_status_list',
                'css' => 'min-width:300px;',
                'type' => 'select',
                'options' => self::get_jigoshop_email_order_status_list()
            );
        }
        $fields[] = array(
            'title' => __('Campaign Monitor API Key', 'autoresponder'),
            'desc' => __('Enter your API Key. <a target="_blank" href="https://login.createsend.com/l">Get your API key And Client Id</a>', 'autoresponder'),
            'id' => 'campaignmonitor_api_key',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );
        $fields[] = array(
            'title' => __('Campaign Monitor Client ID', 'autoresponder'),
            'desc' => __('Enter Campaign Monitor Client ID.', 'autoresponder'),
            'id' => 'campaignmonitor_client_id',
            'type' => 'text',
            'css' => 'min-width:300px;',
        );
        $fields[] = array(
            'title' => __('Campaign Monitor lists', 'autoresponder'),
            'desc' => __('After you add your Campaign Monitor API Key above and save it this list will be populated.', 'Option'),
            'id' => 'campaignmonitor_lists',
            'css' => 'min-width:300px;',
            'type' => 'select',
            'options' => self::autoresponder_get_campaignmonitor_lists(get_option('campaignmonitor_api_key'))
        );
        $fields[] = array(
            'title' => __('Force Campaign Monitor lists refresh', 'autoresponder'),
            'desc' => __("Check and 'Save changes' this if you've added a new Campaign Monitor list and it's not showing in the list above.", 'autoresponder'),
            'id' => 'autoresponder_campaignmonitor_force_refresh',
            'type' => 'checkbox',
        );
        $fields[] = array(
            'title' => __('Debug Log', 'autoresponder'),
            'id' => 'log_enable_campaignmonitor',
            'type' => 'checkbox',
            'label' => __('Enable logging', 'autoresponder'),
            'default' => 'no',
            'desc' => sprintf(__('Log Campaign Monitor events, inside <code>%s</code>', 'autoresponder'), AUTORESPONDER_LOG_DIR)
        );
        $fields[] = array('type' => 'sectionend', 'id' => 'general_options');
        return $fields;
    }

    public static function autoresponder_campaignmonitor_setting() {
        $campaignmonitor_setting_fields = self::autoresponder_campaignmonitor_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        ?>
        <form id="campaignmonitor_integration_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($campaignmonitor_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="campaignmonitor_integration" class="button-primary" value="<?php esc_attr_e('Save changes', 'Option'); ?>" />
            </p>
        </form>
        <?php
    }

    public static function autoresponder_get_campaignmonitor_lists($apikey) {
        $campaignmonitor_lists = array();
        $campaignmonitor_debug = (get_option('log_enable_campaignmonitor') == 'yes') ? 'yes' : 'no';
        if ('yes' == $campaignmonitor_debug) {
            $log = new Autoresponder_Logger();
        }
        if (isset($apikey) && !empty($apikey)) {
            $campaignmonitor_lists = unserialize(get_transient('autoresponder_campaignmonitor_list'));
            if (empty($campaignmonitor_lists) || get_option('autoresponder_campaignmonitor_force_refresh') == 'yes') {
                include_once AUTO_PLUGIN_DIR_PATH . '/admin/partials/lib/campaign_monitor/cmapi.php';
                $api = new Autoresponder_Campaign_Monitor_API($apikey);
                $lists = $api->get_lists();
                if (count($lists) > 0 and is_array($lists)) {
                    unset($campaignmonitor_lists);
                    $campaignmonitor_lists = array();
                    foreach ($lists as $key => $value) {
                        $campaignmonitor_lists[$value->ListID] = $value->Name;
                    }
                    delete_transient('autoresponder_campaignmonitor_list');
                    set_transient('autoresponder_campaignmonitor_list', serialize($campaignmonitor_lists), 86400);
                    if ('yes' == $campaignmonitor_debug) {
                        $log->add('CampaignMonitor', 'Campaign Monitor Get List Success..');
                    }
                    update_option('autoresponder_campaignmonitor_force_refresh', 'no');
                } else {
                    unset($campaignmonitor_lists);
                    $campaignmonitor_lists = array();
                    $campaignmonitor_lists['false'] = __("Unable to load Campaign Monitor lists, check your API Key.", 'autoresponder');
                    if ('yes' == $campaignmonitor_debug) {
                        $log->add('CampaignMonitor', 'Unable to load Campaign Monitor lists, check your API Key.');
                    }
                }
            }
        } else {
            $campaignmonitor_lists['false'] = __("API Key is empty.", 'autoresponder');
            if ('yes' == $campaignmonitor_debug) {
                $log->add('CampaignMonitor', 'API Key is empty.');
            }
        }
        return $campaignmonitor_lists;
    }

    public static function autoresponder_campaignmonitor_setting_save_field() {
        $campaignmonitor_setting_fields = self::autoresponder_campaignmonitor_setting_fields();
        $Html_output = new Autoresponder_Html_output();
        $Html_output->save_fields($campaignmonitor_setting_fields);
    }

    public static function get_woocommerce_email_order_status_list() {
        return array("" => __("select", 'autoresponder'),
            "pending" => __("Pending", 'autoresponder'),
            "on-hold" => __("On-Hold", 'autoresponder'),
            "processing" => __("Processing", 'autoresponder'),
            "completed" => __("Completed", 'autoresponder'),
            "cancelled" => __("Cancelled", 'autoresponder'),
            "refunded" => __("Refunded", 'autoresponder'),
            "failed" => __("Failed", 'autoresponder')
        );
    }
    
    public static function get_jigoshop_email_order_status_list() {
        return array("" => __("select", 'autoresponder'),
            "pending" => __("Pending", 'autoresponder'),
            "on-hold" => __("On-Hold", 'autoresponder'),
            "waiting-for-payment" => __("Waiting for payment", 'autoresponder'),
            "processing" => __("Processing", 'autoresponder'),
            "completed" => __("Completed", 'autoresponder'),
            "refunded" => __("Refunded", 'autoresponder') ,
            "cancelled" => __("Cancelled", 'autoresponder')                      
        );
    }

}

Autoresponder_Admin_General_Setting::init();