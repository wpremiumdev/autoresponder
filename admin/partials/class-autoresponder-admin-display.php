<?php

/**
 * @since      1.0.0
 * @package    Autoresponder
 * @subpackage Autoresponder/includes
 * @author     mbj-webdevelopment <mbjwebdevelopment@gmail.com>
 */
class Autoresponder_Admin_Display {

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_settings_menu'));
    }

    public static function add_settings_menu() {
        add_options_page('Auto Responder', 'Auto Responder', 'manage_options', 'autoresponder', array(__CLASS__, 'autoresponder_options'));
    }

    public static function autoresponder_options() {

        $setting_tabs = apply_filters('autoresponder_options_setting_tab', array('mailchimp' => 'MailChimp', 'getresponse' => 'Getesponse', 'icontact' => 'Icontact', 'infusionsoft' => 'Infusionsoft', 'constantcontact' => 'Constant Contact', 'campaignmonitor' => 'Campaign Monitor'));
        $current_tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'mailchimp';
        ?>
        <h2 class="nav-tab-wrapper">
            <?php
            foreach ($setting_tabs as $name => $label) {
                echo '<a href="' . admin_url('admin.php?page=autoresponder&tab=' . $name) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
            }
            ?>
        </h2>
        <?php
        foreach ($setting_tabs as $setting_tabkey => $setting_tabvalue) {
            switch ($setting_tabkey) {
                case $current_tab:

                    do_action('autoresponder_' . $setting_tabkey . '_setting_save_field');
                    do_action('autoresponder_' . $setting_tabkey . '_setting');
                    break;
            }
        }
    }
}
Autoresponder_Admin_Display::init();