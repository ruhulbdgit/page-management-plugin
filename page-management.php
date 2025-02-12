<?php

/**
 * Plugin Name: Page Management
 * Description: A simple plugin to manage pages/posts  with title, content & status.
 * Version: 1.0
 * Author: Ruhul Siddiki
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Activation Hook
function pm_activate_plugin()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'page_management';

    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        content text NOT NULL,
        status varchar(20) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'pm_activate_plugin');

// Admin Menu
function pm_add_admin_menu()
{
    add_menu_page('Page Management', 'Page Management', 'manage_options', 'pm_page_management', 'pm_page_management_callback');
}
add_action('admin_menu', 'pm_add_admin_menu');

// Admin Page Callback
function pm_page_management_callback()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'page_management';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pm_submit'])) {
        $title = sanitize_text_field($_POST['pm_title']);
        $content = sanitize_textarea_field($_POST['pm_content']);
        $status = sanitize_text_field($_POST['pm_status']);

        $wpdb->insert($table_name, [
            'title' => $title,
            'content' => $content,
            'status' => $status
        ]);
    }

    $pages = $wpdb->get_results("SELECT * FROM $table_name");
?>
    <div class="wrap" style="margin: 0 auto; align-items:center">
        <h2 style="color: rebeccapurple; align-items:center; text-decoration:dotted">Page Management :</h2>
        <form method="POST">
            <div><label style="margin-bottom: 20px;"> <b>Title: </b></label></div>
            <input type="text" name="pm_title" placeholder="Enter Post/Pages Title here" required><br>
            <label><b>Content:</b></label>
            <div><textarea name="pm_content" placeholder="Enter Post/Pages Content here" required></textarea></div><br>
            <label> <b> Status:</b></label>
            <select name="pm_status">
                <option style="color: purple;" value="published">Published</option>
                <option value="draft">Draft</option>

            </select><br><br>
            <input style="color: purple;" type="submit" name="pm_submit" value="Add Page" required>
        </form>
        <h3 style="text-decoration: underline; color:blue">Pages At A Glance :</h3>
        <table border="1px solid gray">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Status</th>
            </tr>
            <?php foreach ($pages as $page) { ?>
                <tr>
                    <td><?php echo esc_html($page->id); ?></td>
                    <td><?php echo esc_html($page->title); ?></td>
                    <td><?php echo esc_html($page->status); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php
}
