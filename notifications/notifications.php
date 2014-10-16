<?php

/*
Plugin Name: FH Notifications
Plugin URI: http://www.github.com/iamzozo/notifications/
Description: A simple notification framework plugin. You can insert or delete notification for users and posts.
Author: Zoltan Varkonyi
Version: 1.0
*/

class Notifications
{
    function __construct()
    {
        register_activation_hook(__FILE__, array(&$this, 'install_plugin'));
        register_uninstall_hook(__FILE__, array(&$this, 'uninstall_plugin'));
    }

    function create_notification($user_id, $post_id)
    {
        global $wpdb;
        $insert = $wpdb->insert($wpdb->prefix . 'notifications', array(
            'created_at' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'post_id' => $post_id
        ), array(
            '%s',
            '%d',
            '%d'
        ));
        if ($insert) {
            return $wpdb->insert_id;
        } else {
            return false;
        }
    }

    function get_notifications($id, $target)
    {
        global $wpdb;
        if ($target == 'post') {
            $where = 'post_id';
        } else {
            $where = 'user_id';
        }
        $table = $wpdb->prefix . 'notifications';
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE $where = %d", $id), OBJECT);
        $return = array();
        if ($result) {
            foreach ($result as $row) {
                if($target == 'post')
                    $return[] = $row->user_id;
                else
                    $return[] = $row->post_id;
            }
        }
        return $return;
    }

    function delete_notifications($id, $target)
    {
        global $wpdb;
        if ($target == 'post') {
            $where = 'post_id';
        } else {
            $where = 'user_id';
        }
        $wpdb->delete($wpdb->prefix . 'notifications', array($where => $id), array('%d'));
    }

    function install_plugin()
    {
        $this->create_table();
    }

    function uninstall_plugin()
    {
        $this->drop_table();
    }

    function create_table()
    {
        global $wpdb;
        global $notifications_db_version;

        $table_name = $wpdb->prefix . 'notifications';

        $charset_collate = '';

        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            post_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            tag varchar(255) DEFAULT '',
            UNIQUE KEY id (id),
            INDEX post_id_i (post_id),
            INDEX user_id_i (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('notifications_db_version', $notifications_db_version);
    }

    function drop_table()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}notifications");
        remove_option('notifications_db_version');
    }
}

if (!function_exists('create_notifications')) {
    function create_notification($user_id, $post_id)
    {
        global $notification;
        $notification->create_notification($user_id, $post_id);
    }
}

if (!function_exists('create_notifications')) {
    function get_notifications($id, $target)
    {
        global $notification;
        return $notification->get_notifications($id, $target);
    }
}

if (!function_exists('delete_notifications')) {
    function delete_notifications($id, $target)
    {
        global $notification;
        $notification->delete_notifications($id, $target);
    }
}

$notification = new Notifications();