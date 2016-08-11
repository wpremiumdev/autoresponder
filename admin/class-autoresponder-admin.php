<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Autoresponder
 * @subpackage Autoresponder/admin
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

class Autoresponder_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->load_dependencies();        
    }

    private function load_dependencies() {

        /**
         * The class responsible for defining all actions that occur in the Dashboard
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-autoresponder-admin-display.php';

        /**
         * The class responsible for defining function for display Html element
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-autoresponder-html-output.php';

        /**
         * The class responsible for defining function for display general setting tab
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-autoresponder-general-setting.php';
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name . 'bn', plugin_dir_url(__FILE__) . 'js/autoresponder-bn.js', array('jquery'), $this->version, false);        
    }
    
    public function autoresponder_paypal_args($paypal_args) {
        $paypal_args['bn'] = 'mbjtechnolabs_SP';
        return $paypal_args;
    }

    public function autoresponder_paypal_digital_goods_nvp_args($paypal_args) {
        $paypal_args['BUTTONSOURCE'] = 'mbjtechnolabs_SP';
        return $paypal_args;
    }

    public function autoresponder_gateway_paypal_pro_payflow_request($paypal_args) {
        $paypal_args['BUTTONSOURCE'] = 'mbjtechnolabs_SP';
        return $paypal_args;
    }

    public function autoresponder_gateway_paypal_pro_request($paypal_args) {
        $paypal_args['BUTTONSOURCE'] = 'mbjtechnolabs_SP';
        return $paypal_args;
    }

    public static function add_contact_subscriber_customer_email_for_woo_order_status($order_id) {
        $order_details = self::get_order_details_from_woocommerce($order_id);
        self::contact_subscriber_for_woocommerce($order_details['email'], $order_details['fname'], $order_details['lname'], $order_details['status']);
    }

    public static function get_order_details_from_woocommerce($order_id) {
        $order_details = new WC_Order($order_id);
        if ($order_details->order_custom_fields) {
            $order_details_info['fname'] = $order_details->order_custom_fields['_billing_first_name'][0];
            $order_details_info['lname'] = $order_details->order_custom_fields['_billing_last_name'][0];
            $order_details_info['email'] = $order_details->order_custom_fields['_billing_email'][0];
        } else {
            $order_details_info['fname'] = $order_details->billing_first_name;
            $order_details_info['lname'] = $order_details->billing_last_name;
            $order_details_info['email'] = $order_details->billing_email;
        }
        $order_details_info['status'] = $order_details->get_status();
        return $order_details_info;
    }

    public static function contact_subscriber_for_woocommerce($email, $fname, $lname, $status) {
        if ($email == '')
            return;
        if ("yes" == get_option("enable_woocommerce_mailchimp")) {
            if ($status == get_option("mailchimp_woo_email_order_status_list")) {
                self::add_contact_into_mailchimp($email, $fname, $lname, 'Woocommerce');
            }
        }
        if ("yes" == get_option("enable_woocommerce_getresponse")) {
            if ($status == get_option("getresponse_woo_email_order_status_list")) {
                self::add_contact_into_getresponse($email, $fname, $lname, 'Woocommerce');
            }
        }
        if ("yes" == get_option("enable_woocommerce_icontact")) {
            if ($status == get_option("icontact_woo_email_order_status_list")) {
                self::add_contact_into_icontact($email, $fname, $lname, 'Woocommerce');
            }
        }
        if ("yes" == get_option("enable_woocommerce_infusionsoft")) {
            if ($status == get_option("infusionsoft_woo_email_order_status_list")) {
                self::add_contact_into_infusionsoft($email, $fname, $lname, 'Woocommerce');
            }
        }
        if ("yes" == get_option("enable_woocommerce_constant_contact")) {
            if ($status == get_option("constantcontact_woo_email_order_status_list")) {
                self::add_contact_into_constantcontact($email, $fname, $lname, 'Woocommerce');
            }
        }
        if ("yes" == get_option("enable_woocommerce_campaignmonitor")) {
            if ($status == get_option("campaignmonitor_woo_email_order_status_list")) {
                self::add_contact_into_campaignmonitor($email, $fname, $lname, 'Woocommerce');
            }
        }
    }

    public static function add_contact_into_mailchimp($email, $fname, $lname, $logplugin) {
        $api_key = get_option("mailchimp_api_key");
        $list_id = get_option("mailchimp_lists");
        $debug = (get_option('log_enable_mailChimp') == 'yes') ? 'yes' : 'no';
        if ('yes' == $debug) {
            $log = new Autoresponder_Logger();
        }
        if ((isset($api_key) && !empty($api_key)) && (isset($list_id) && !empty($list_id))) {
            if ($list_id == 'false') {
                return;
            }
            include_once AUTO_PLUGIN_DIR_PATH . '/admin/partials/lib/mailchimp/mailchimp.php';
            $Mailchimp = new Autoresponder_MailChimp_API($api_key);
            try {
                $subscriber = $Mailchimp->listSubscribe($list_id, $email, array('FNAME' => $fname, 'LNAME' => $lname), $email_type = 'html');
                if ('yes' == $debug) {
                    if ("true" == $subscriber) {
                        $log->add('MailChimp', $logplugin . ' ' . $email . ' Successfully Add Contact in Mailchimp');
                    } else {
                        $log->add('MailChimp', $logplugin . ' ' . $email . ' ' . $subscriber . ' Contact already added to target campaign in Mailchimp');
                    }
                }
            } catch (Mailchimp_Error $e) {
                if ('yes' == $debug) {
                    $log->add('MailChimp', $logplugin . ' ' . print_r($e, true));
                }
            }
        }
    }

    public static function add_contact_into_getresponse($email, $fname, $lname, $logplugin) {
        $gt_api_key = get_option("getresponse_api_key");
        $campaign_id = get_option("getresponse_lists");
        $debug = (get_option('log_enable_getresponse') == 'yes') ? 'yes' : 'no';
        if ('yes' == $debug) {
            $log = new Autoresponder_Logger();
        }
        if ((isset($gt_api_key) && !empty($gt_api_key)) && (isset($campaign_id) && !empty($campaign_id))) {
            if ($campaign_id == 'false') {
                return;
            }
            include_once AUTO_PLUGIN_DIR_PATH . 'admin/partials/lib/getresponse/getresponse.php';
            try {
                $getresponse_apiInstance = new Autoresponder_Getesponse_API($gt_api_key);
                $name = $fname . ' ' . $lname;
                $result = $getresponse_apiInstance->addContact($campaign_id, $name, $email);
                if ('yes' == $debug) {
                    if ($result) {
                        $log->add('Getresponse', $logplugin . ' ' . $email . ' Successfully Add Contact in Getresponse');
                    } else {
                        $log->add('Getresponse', $logplugin . ' ' . $email . ' Contact already added to target campaign in Getresponse');
                    }
                }
            } catch (Exception $e) {
                if ('yes' == $debug) {
                    $log->add('Getresponse', $logplugin . ' ' . print_r($e, true));
                }
            }
        }
    }

    public static function add_contact_into_icontact($email, $fname, $lname, $logplugin) {
        $ic_api_key = get_option("icontact_api_id");
        $app_password = get_option("icontact_api_password");
        $app_username = get_option("icontact_api_username");
        $icontact_list = get_option("icontact_lists");
        $debug = (get_option('log_enable_icontact') == 'yes') ? 'yes' : 'no';
        if ('yes' == $debug) {
            $log = new Autoresponder_Logger();
        }
        if ((isset($ic_api_key) && !empty($ic_api_key)) && (isset($app_username) && !empty($app_username)) && (isset($app_password) && !empty($app_password)) && (isset($icontact_list) && !empty($icontact_list))) {
            if ($icontact_list == 'false') {
                return;
            }
            include_once AUTO_PLUGIN_DIR_PATH . '/admin/partials/lib/icontact/icontact.php';
            iContactApi::getInstance()->setConfig(array(
                'appId' => $ic_api_key,
                'apiUsername' => $app_username,
                'apiPassword' => $app_password,
            ));
            $iContact = iContactApi::getInstance();
            try {
                $contactid = $iContact->addContact($email, 'normal', null, $fname, $lname, null, null, null, null, null, null, null, null, null);
                $subscribed = $iContact->subscribeContactToList($contactid->contactId, $icontact_list, 'normal');
                if ('yes' == $debug) {
                    if ($subscribed) {
                        $log->add('Icontact', $logplugin . ' ' . $email . ' Successfully Add Contact in iContact');
                    } else {
                        $log->add('Icontact', $logplugin . ' ' . $email . ' Contact already added to target campaign in iContact');
                    }
                }
            } catch (Exception $e) {
                if ('yes' == $debug) {
                    $log->add('Icontact', $logplugin . ' ' . print_r($e, true));
                }
            }
        }
    }

    public static function add_contact_into_infusionsoft($email, $fname, $lname, $logplugin) {
        $is_api_key = get_option("infusionsoft_api_key");
        $app_name = get_option("infusionsoft_api_app_name");
        $is_list_id = get_option("infusionsoft_lists");
        $debug = (get_option('log_enable_infusionsoft') == 'yes') ? 'yes' : 'no';
        if ('yes' == $debug) {
            $log = new Autoresponder_Logger();
        }
        if ((isset($is_api_key) && !empty($is_api_key)) && (isset($app_name) && !empty($app_name)) && (isset($is_list_id) && !empty($is_list_id))) {
            if ($is_list_id == 'false') {
                return;
            }
            include_once AUTO_PLUGIN_DIR_PATH . '/admin/partials/lib/infusionsoft/isdk.php';
            $app = new iSDK;
            try {
                if ($app->cfgCon($app_name, $is_api_key)) {
                    $contactid = $app->addCon(array('FirstName' => $fname, 'LastName' => $lname, 'Email' => $email));
                    $infusionsoft_result = $app->campAssign($contactid, $is_list_id);
                    if ('yes' == $debug) {
                        $log->add('Infusionsoft', $logplugin . ' ' . print_r($infusionsoft_result, true));
                    }
                }
            } catch (Exception $e) {
                if ('yes' == $debug) {
                    $log->add('Infusionsoft', $logplugin . ' ' . print_r($e, true));
                }
            }
        }
    }

    public static function add_contact_into_constantcontact($email, $fname, $lname, $logplugin) {
        $cc_api_key = get_option("constantcontact_api_key");
        $access_token = get_option("constantcontact_access_token");
        $cc_list_id = get_option("constantcontact_lists");
        $debug = (get_option('log_enable_constant_contact') == 'yes') ? 'yes' : 'no';
        if ('yes' == $debug) {
            $log = new Autoresponder_Logger();
        }
        if ((isset($cc_api_key) && !empty($cc_api_key)) && (isset($access_token) && !empty($access_token)) && (isset($cc_list_id) && !empty($cc_list_id))) {
            if ($cc_list_id == 'false') {
                return;
            }
            try {
                $ConstantContact = new ConstantContact($cc_api_key);
                $response = $ConstantContact->getContactByEmail($access_token, $email);
                if (empty($response->results)) {
                    $Contact = new Contact();
                    $Contact->addEmail($email);
                    $Contact->addList($cc_list_id);
                    $Contact->first_name = $fname;
                    $Contact->last_name = $lname;
                    $NewContact = $ConstantContact->addContact($access_token, $Contact, false);
                    if (isset($NewContact) && 'yes' == $debug) {
                        $log->add('ConstantContact', $logplugin . ' ConstantContact new contact ' . $email . ' added to selected contact list');
                    }
                } else {
                    $Contact = $response->results[0];
                    $Contact->first_name = $fname;
                    $Contact->last_name = $lname;
                    $Contact->addList($cc_list_id);
                    $new_contact = $ConstantContact->updateContact($access_token, $Contact, false);
                    if (isset($new_contact) && 'yes' == $debug) {
                        $log->add('ConstantContact', $logplugin . ' ConstantContact update contact ' . $email . ' to selected contact list');
                    }
                }
            } catch (CtctException $ex) {
                $error = $ex->getErrors();
                $log->add('ConstantContact', $logplugin . ' ' . print_r($error, true));
            }
        } else {
            if ('yes' == $debug) {
                $log->add('ConstantContact', 'Constant Contact API Key OR Constant Contact Access Token does not set');
            }
        }
    }

    public static function add_contact_into_campaignmonitor($email, $fname, $lname, $logplugin) {
        $cm_api_key = get_option("campaignmonitor_api_key");
        $client_id = get_option("campaignmonitor_client_id");
        $cm_list_id = get_option("campaignmonitor_lists");
        $debug = (get_option('log_enable_campaignmonitor') == 'yes') ? 'yes' : 'no';
        if ('yes' == $debug) {
            $log = new Autoresponder_Logger();
        }
        if ((isset($cm_api_key) && !empty($cm_api_key)) && (isset($client_id) && !empty($client_id)) && (isset($cm_list_id) && !empty($cm_list_id))) {
            if ($cm_list_id == 'false') {
                return;
            }
            include_once AUTO_PLUGIN_DIR_PATH . '/admin/partials/lib/campaign_monitor/csrest_subscribers.php';
            $wrap = new CS_REST_Subscribers($cm_list_id, $cm_api_key);
            try {
                $response = $wrap->get($email);
                if ($response->http_status_code == "200") {
                    $result = $wrap->update($email, array(
                        'EmailAddress' => $email,
                        'Name' => $fname . ' ' . $lname,
                        'CustomFields' => array(),
                        'Resubscribe' => true
                    ));
                    if ("yes" == $debug) {
                        if ($response->response->State == "Unsubscribed") {
                            $log->add('CampaignMonitor', $logplugin . ' CampaignMonitor new contact ' . $email . ' added to selected contact list');
                        } else {
                            $log->add('CampaignMonitor', $logplugin . ' CampaignMonitor update contact ' . $email . ' to selected contact list');
                        }
                    }
                } else {
                    $result = $wrap->add(array(
                        'EmailAddress' => $email,
                        'Name' => $fname . ' ' . $lname,
                        'CustomFields' => array(),
                        'Resubscribe' => true
                    ));
                    if (isset($result) && 'yes' == $debug) {
                        $log->add('CampaignMonitor', $logplugin . ' CampaignMonitor new contact ' . $email . ' added to selected contact list');
                    }
                }
            } catch (Exception $e) {
                if ('yes' == $debug) {
                    $log->add('CampaignMonitor', $logplugin . ' ' . print_r($e, true));
                }
            }
        } else {
            if ('yes' == $debug) {
                $log->add('CampaignMonitor', 'Campaign Monitor API Key OR Campaign Monitor Client ID does not set');
            }
        }
    }

    public static function custom_mail_sent_function_for_contact_form7($contact_form) {
        if (property_exists($contact_form, 'posted_data')) {
            $posted_data = $contact_form->posted_data;
        } else {
            $submission = WPCF7_Submission::get_instance();
            if ($submission) {
                $posted_data = $submission->get_posted_data();
            }
        }
        $email = self::get_email_form_posted_data($posted_data);
        $fname = self::get_first_name_form_posted_data($posted_data);
        $lname = self::get_last_name_form_posted_data($posted_data);
        self::add_contact_into_all_autoresponder('contact_form_7', $email, $fname, $lname, 'Contact Form-7');
    }

    public static function custom_mail_sent_function_for_fast_secure_contact_form($contact_form) {
        if (property_exists($contact_form, 'posted_data')) {
            $posted_data = $contact_form->posted_data;
        }
        $email = self::get_email_form_posted_data($posted_data);
        $fname = self::get_first_name_form_posted_data($posted_data);
        $lname = self::get_last_name_form_posted_data($posted_data);
        self::add_contact_into_all_autoresponder('fast_secure_contact_form', $email, $fname, $lname, 'Fast Secure Contact Form');
    }

    public static function custom_mail_sent_function_for_contact_form_by_bestwebsoft($to, $name, $email, $address, $phone, $subject, $message, $form_action_url, $user_agent, $userdomain) {
        $fname = $name;
        $lname = "";
        self::add_contact_into_all_autoresponder('contact_form_by_bestwebsoft', $email, $fname, $lname, 'Contact Form by BestWebSoft');
    }

    public static function custom_mail_sent_function_for_WR_contact_form($dataForms, $postID, $post, $submissionsData, $dataContentEmail, $nameFileByIndentifier, $requiredField, $fileAttach) {
        foreach ($submissionsData as $submissionData) {
            if ("name" == $submissionData['field_type']) {
                $data = json_decode(html_entity_decode($submissionData['submission_data_value']));
                $fname = $data->first;
                $lname = $data->last;
            }
            if ("email" == $submissionData['field_type']) {
                $email = $submissionData['submission_data_value'];
            }
        }
        self::add_contact_into_all_autoresponder('WR_contact_form', $email, $fname, $lname, 'WR Contact Form');
    }

    public static function custom_mail_sent_function_for_ninja_contact_form() {
        global $ninja_forms_processing;
        $all_fields = $ninja_forms_processing->get_all_fields();
        if (is_array($all_fields)) {
            foreach ($all_fields as $field_id => $user_value) {
                if ($field_id == "1") {
                    $fname = $user_value;
                } else {
                    if ($field_id == "6") {
                        $fname = $user_value;
                    }
                }
                if ($field_id == "2") {
                    $email = $user_value;
                }
                if ($field_id == "7") {
                    $lname = $user_value;
                }
            }
        }
        self::add_contact_into_all_autoresponder('ninja_contact_form', $email, $fname, $lname, 'Ninja Contact Form');
    }

    public static function custom_mail_sent_function_for_caldera_contact_form($form, $referrer, $process_id) {
        $data = array();
        foreach ($form['fields'] as $field_id => $field) {
            $data[$field['slug']] = Caldera_Forms::get_field_data($field_id, $form);
        }
        $email = self::get_email_form_posted_data($data);
        $fname = self::get_name_form_posted_data($data);
        if (empty($fname)) {
            $fname = self::get_first_name_form_posted_data($data);
        }
        $lname = self::get_last_name_form_posted_data($data);
        self::add_contact_into_all_autoresponder('caldera_contact_form', $email, $fname, $lname, 'Caldera Contact Form');
    }

    public static function custom_mail_sent_function_for_jetpack_contact_form($post_id, $all_values, $extra_values) {
        $fields = array();
        foreach ($all_values as $key => $value) {
            $underscore = strpos($key, '_');
            $start = $underscore + 1;
            $field = substr($key, $start);
            $fields[$field] = $value;
        }
        $email = self::get_email_form_posted_data($fields);
        $fname = self::get_name_form_posted_data($fields);
        if (empty($fname)) {
            $fname = self::get_first_name_form_posted_data($fields);
        }
        $lname = self::get_last_name_form_posted_data($fields);
        self::add_contact_into_all_autoresponder('jetpack_contact_form', $email, $fname, $lname, 'Jetpack Contact Form');
    }

    public static function custom_mail_sent_function_for_quform_contact_form($form) {
        $elements = $form->getElements();
        $data = array();
        foreach ($elements as $element) {
            $data = self::get_label_name($element, $data);
        }
        $email = self::get_email_form_posted_data($data);
        $fname = self::get_name_form_posted_data($data);
        if (empty($fname)) {
            $fname = self::get_first_name_form_posted_data($data);
        }
        $lname = self::get_last_name_form_posted_data($data);
        self::add_contact_into_all_autoresponder('qu_contact_form', $email, $fname, $lname, 'Quform');
    }

    public static function custom_mail_sent_function_for_gravity_contact_form($entry, $form) {
        $data = array();
        foreach ($form['fields'] as $field) {
            $value = GFFormsModel::get_lead_field_value($entry, $field);
            $data[$field['label']] = GFCommon::get_lead_field_display($field, $value);
        }
        $email = self::get_email_form_posted_data($data);
        $fname = self::get_name_form_posted_data($data);
        if (empty($fname)) {
            $fname = self::get_first_name_form_posted_data($data);
        }
        $lname = self::get_last_name_form_posted_data($data);
        self::add_contact_into_all_autoresponder('gravity_contact_form', $email, $fname, $lname, 'Gravity Contact Form');
    }

    public static function custom_mail_sent_function_for_enfold_theme_contact_form($send, $new_post, $form_params) {
        $fields = array();
        foreach ($new_post as $current_key => $current_post) {
            $underscore = strrpos($current_key, '_', -1);
            $field = substr($current_key, 0, $underscore);
            $fields[$field] = nl2br(urldecode($current_post));
        }
        $email = self::get_email_form_posted_data($fields);
        $fname = self::get_name_form_posted_data($fields);
        if (empty($fname)) {
            $fname = self::get_first_name_form_posted_data($fields);
        }
        $lname = self::get_last_name_form_posted_data($fields);
        self::add_contact_into_all_autoresponder('enfold_theme_contact_form', $email, $fname, $lname, 'Enfold Theme Contact Form');
        return true;
    }

    public static function add_contact_into_all_autoresponder($pluginid, $email, $fname, $lname, $logplugin) {

        if ("yes" == get_option("enable_" . $pluginid . "_mailchimp")) {
            self::add_contact_into_mailchimp($email, $fname, $lname, $logplugin);
        }
        if ("yes" == get_option("enable_" . $pluginid . "_getresponse")) {
            self::add_contact_into_getresponse($email, $fname, $lname, $logplugin);
        }
        if ("yes" == get_option("enable_" . $pluginid . "_icontact")) {
            self::add_contact_into_icontact($email, $fname, $lname, $logplugin);
        }
        if ("yes" == get_option("enable_" . $pluginid . "_infusionsoft")) {
            self::add_contact_into_infusionsoft($email, $fname, $lname, $logplugin);
        }
        if ("yes" == get_option("enable_" . $pluginid . "_constant_contact")) {
            self::add_contact_into_constantcontact($email, $fname, $lname, $logplugin);
        }
        if ("yes" == get_option("enable_" . $pluginid . "_campaignmonitor")) {
            self::add_contact_into_campaignmonitor($email, $fname, $lname, $logplugin);
        }
    }

    public static function get_email_form_posted_data($posted_data) {
        if (array_key_exists('your-email', $posted_data)) {
            $email = $posted_data['your-email'];
        } else if (array_key_exists('your_email', $posted_data)) {
            $email = $posted_data['your_email'];
        } else if (array_key_exists('your_e-mail', $posted_data)) {
            $email = $posted_data['your_e-mail'];
        } else if (array_key_exists('Your Email', $posted_data)) {
            $email = $posted_data['Your Email'];
        } else if (array_key_exists('email', $posted_data)) {
            $email = $posted_data['email'];
        } else if (array_key_exists('Email', $posted_data)) {
            $email = $posted_data['Email'];
        } else if (array_key_exists('E-Mail', $posted_data)) {
            $email = $posted_data['E-Mail'];
        } else if (array_key_exists('Email address', $posted_data)) {
            $email = $posted_data['Email address'];
        } else if (array_key_exists('from_email', $posted_data)) {
            $email = $posted_data['from_email'];
        } else {
            return;
        }
        return $email;
    }

    public static function get_name_form_posted_data($posted_data) {
        $name = '';
        if (array_key_exists('your-name', $posted_data)) {
            $name = $posted_data['your-name'];
        } else if (array_key_exists('your_name', $posted_data)) {
            $name = $posted_data['your_name'];
        } else if (array_key_exists('name', $posted_data)) {
            $name = $posted_data['name'];
        } else if (array_key_exists('Your Name', $posted_data)) {
            $name = $posted_data['Your Name'];
        } else if (array_key_exists('Your name', $posted_data)) {
            $name = $posted_data['Your name'];
        } else if (array_key_exists('Name', $posted_data)) {
            $name = $posted_data['Name'];
        } else if (array_key_exists('full_name', $posted_data)) {
            $name = $posted_data['full_name'];
        } else if (array_key_exists('Full Name', $posted_data)) {
            $name = $posted_data['Full Name'];
        } else if (array_key_exists('my-name', $posted_data)) {
            $name = $posted_data['my-name'];
        }
        return $name;
    }

    public static function get_first_name_form_posted_data($posted_data) {
        $fname = '';
        if (array_key_exists('your-name', $posted_data)) {
            $fname = $posted_data['your-name'];
        } else if (array_key_exists('your-first-name', $posted_data)) {
            $fname = $posted_data['your-first-name'];
        } else if (array_key_exists('your_first_name', $posted_data)) {
            $fname = $posted_data['your_first_name'];
        } else if (array_key_exists('Your First Name', $posted_data)) {
            $fname = $posted_data["Your First Name"];
        } else if (array_key_exists('first-name', $posted_data)) {
            $fname = $posted_data['first-name'];
        } else if (array_key_exists('first_name', $posted_data)) {
            $fname = $posted_data['first_name'];
        } else if (array_key_exists('First_Name', $posted_data)) {
            $fname = $posted_data['First_Name'];
        } else if (array_key_exists('First Name', $posted_data)) {
            $fname = $posted_data['First Name'];
        } else if (array_key_exists('from_name', $posted_data)) {
            $fname = $posted_data['from_name'];
        }
        return $fname;
    }

    public static function get_last_name_form_posted_data($posted_data) {
        if (array_key_exists('your-last-name', $posted_data)) {
            $lname = $posted_data["your-last-name"];
        } else if (array_key_exists('your_last_name', $posted_data)) {
            $lname = $posted_data["your_last_name"];
        } else if (array_key_exists('Your Last Name', $posted_data)) {
            $lname = $posted_data["Your Last Name"];
        } else if (array_key_exists('last-name', $posted_data)) {
            $lname = $posted_data['last-name'];
        } else if (array_key_exists('last_name', $posted_data)) {
            $lname = $posted_data['last_name'];
        } else if (array_key_exists('Last_Name', $posted_data)) {
            $lname = $posted_data['Last_Name'];
        } else if (array_key_exists('Last Name', $posted_data)) {
            $lname = $posted_data['Last Name'];
        } else {
            $lname = '';
        }
        return $lname;
    }

    public static function get_label_name($element, $data) {
        if ($element->getLabel() == "Name" || $element->getLabel() == "name" || $element->getLabel() == "Full Name") {
            $data['name'] = $element->getValue();
        }
        if ($element->getLabel() == "Your Name" || $element->getLabel() == "Your name" || $element->getLabel() == "Your-Name") {
            $data['name'] = $element->getValue();
        }
        if ($element->getLabel() == "First Name" || $element->getLabel() == "First-Name" || $element->getLabel() == "First name" || $element->getLabel() == "first name" || $element->getLabel() == "Your First Name" || $element->getLabel() == "Your first name") {
            $data['first-name'] = $element->getValue();
        }
        if ($element->getLabel() == "Last Name" || $element->getLabel() == "Last-Name" || $element->getLabel() == "Last name" || $element->getLabel() == "lasr name" || $element->getLabel() == "Your Last Name" || $element->getLabel() == "Your last name") {
            $data['last-name'] = $element->getValue();
        }
        if ($element->getLabel() == "Email address" || $element->getLabel() == "email address" || $element->getLabel() == "Email" || $element->getLabel() == "E-Mail" || $element->getLabel() == "email" || $element->getLabel() == "Your Email" || $element->getLabel() == "Your E-Mail" || $element->getLabel() == "Your Email Address" || $element->getLabel() == "Your email address") {
            $data['email'] = $element->getValue();
        }
        return $data;
    }

    public static function add_contact_subscriber_customer_email_for_jigoshop_order_status($order_id) {
        $order_details = self::get_order_details_from_jigoshop($order_id);
        self::contact_subscriber_for_jigoshop($order_details['fname'], $order_details['lname'], $order_details['email'], $order_details['status']);
    }

    public static function get_order_details_from_jigoshop($order_id) {
        $order_details = new jigoshop_order($order_id);
        $order_details_info['fname'] = $order_details->billing_first_name;
        $order_details_info['lname'] = $order_details->billing_last_name;
        $order_details_info['email'] = $order_details->billing_email;
        $order_details_info['status'] = $order_details->status;
        return $order_details_info;
    }

    public static function contact_subscriber_for_jigoshop($fname, $lname, $email, $status) {
        if ($email == '')
            return;
        if ("yes" == get_option("enable_jigoshop_mailchimp")) {
            if ($status == get_option("mailchimp_jigoshop_email_order_status_list")) {
                self::add_contact_into_mailchimp($email, $fname, $lname, 'Jigoshop');
            }
        }
        if ("yes" == get_option("enable_jigoshop_getresponse")) {
            if ($status == get_option("getresponse_jigoshop_email_order_status_list")) {
                self::add_contact_into_getresponse($email, $fname, $lname, 'Jigoshop');
            }
        }
        if ("yes" == get_option("enable_jigoshop_icontact")) {
            if ($status == get_option("icontact_jigoshop_email_order_status_list")) {
                self::add_contact_into_icontact($email, $fname, $lname, 'Jigoshop');
            }
        }
        if ("yes" == get_option("enable_jigoshop_infusionsoft")) {
            if ($status == get_option("infusionsoft_jigoshop_email_order_status_list")) {
                self::add_contact_into_infusionsoft($email, $fname, $lname, 'Jigoshop');
            }
        }
        if ("yes" == get_option("enable_jigoshop_constant_contact")) {
            if ($status == get_option("constantcontact_jigoshop_email_order_status_list")) {
                self::add_contact_into_constantcontact($email, $fname, $lname, 'Jigoshop');
            }
        }
        if ("yes" == get_option("enable_jigoshop_campaignmonitor")) {
            if ($status == get_option("campaignmonitor_jigoshop_email_order_status_list")) {
                self::add_contact_into_campaignmonitor($email, $fname, $lname, 'Jigoshop');
            }
        }
    }
    
    public function autoresponder_paypal_ipn_handler_own($post) {
        $fname = isset($post['first_name']) ? $post['first_name'] : '';
        $lname = isset($post['last_name']) ? $post['last_name'] : '';
        $email = isset($post['payer_email']) ? $post['payer_email'] : $post['receiver_email'];
        if(empty($email)) {
            return;
        }
        self::add_contact_into_mailchimp($email, $fname, $lname, 'PayPal IPN');
        self::add_contact_into_getresponse($email, $fname, $lname, 'PayPal IPN');
        self::add_contact_into_icontact($email, $fname, $lname, 'PayPal IPN');
        self::add_contact_into_infusionsoft($email, $fname, $lname, 'PayPal IPN');
        self::add_contact_into_constantcontact($email, $fname, $lname, 'PayPal IPN');
        self::add_contact_into_campaignmonitor($email, $fname, $lname, 'PayPal IPN');
        
    }
}