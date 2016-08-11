<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Autoresponder
 * @subpackage Autoresponder/includes
 * @author     mbj-webdevelopment <mbjwebdevelopment@gmail.com>
 */
class Autoresponder_Activator {

    /**
     * @since    1.0.0
     */
    public static function activate() {
        $upload_dir = wp_upload_dir();
        $files = array(
            array(
                'base' => AUTORESPONDER_LOG_DIR,
                'file' => '.htaccess',
                'content' => 'deny from all'
            ),
            array(
                'base' => AUTORESPONDER_LOG_DIR,
                'file' => 'index.html',
                'content' => ''
            )
        );
        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
                if ($file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w')) {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }
}