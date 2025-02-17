<?php

/**
 * Plugin Name: Page Management
 * Description: A simple plugin to manage pages/posts with title, content & status.
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

    // Handle form submission for adding or editing pages
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pm_submit'])) {
        $title = sanitize_text_field($_POST['pm_title']);
        $content = sanitize_textarea_field($_POST['pm_content']);
        $status = sanitize_text_field($_POST['pm_status']);
        $id = isset($_POST['pm_id']) ? intval($_POST['pm_id']) : 0;

        if ($id > 0) {
            // Update existing page
            $wpdb->update($table_name, ['title' => $title, 'content' => $content, 'status' => $status], ['id' => $id]);
        } else {
            // Insert new page
            $wpdb->insert($table_name, ['title' => $title, 'content' => $content, 'status' => $status]);
        }
    }

    // Handle delete request
    if (isset($_GET['delete_id'])) {
        $id = intval($_GET['delete_id']);
        $wpdb->delete($table_name, ['id' => $id]);
    }

    // Fetch pages
    $pages = $wpdb->get_results("SELECT * FROM $table_name");

    // Handle edit request
    $edit_page = null;
    if (isset($_GET['edit_id'])) {
        $id = intval($_GET['edit_id']);
        $edit_page = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
    }
?>
    <div class="wrap" style="margin: 0 auto; align-items:center">
        <h2 style="color: rebeccapurple; align-items:center; text-decoration:dotted">Page Management :</h2>
        <form method="POST">
            <input type="hidden" name="pm_id" value="<?php echo $edit_page ? esc_attr($edit_page->id) : ''; ?>">
            <div><label style="margin-bottom: 20px;"> <b>Title: </b></label></div>
            <input type="text" name="pm_title" placeholder="Enter Post/Pages Title here" value="<?php echo $edit_page ? esc_attr($edit_page->title) : ''; ?>" required><br>
            <label><b>Content:</b></label>
            <div><textarea name="pm_content" placeholder="Enter Post/Pages Content here" required><?php echo $edit_page ? esc_textarea($edit_page->content) : ''; ?></textarea></div><br>
            <label> <b>Status:</b></label>
            <select name="pm_status">
                <option style="color: purple;" value="published" <?php echo $edit_page && $edit_page->status == 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo $edit_page && $edit_page->status == 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select><br><br>
            <input style="color: purple;" type="submit" name="pm_submit" value="<?php echo $edit_page ? 'Update Page' : 'Add Page'; ?>" required>
        </form>
        <h3 style="text-decoration: underline; color:blue">Pages At A Glance :</h3>
        <table border="1px solid gray">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($pages as $page) { ?>
                <tr>
                    <td><?php echo esc_html($page->id); ?></td>
                    <td><?php echo esc_html($page->title); ?></td>
                    <td><?php echo esc_html($page->status); ?></td>
                    <td>
                        <a href="?page=pm_page_management&edit_id=<?php echo esc_attr($page->id); ?>">Edit</a> |
                        <a href="?page=pm_page_management&delete_id=<?php echo esc_attr($page->id); ?>" onclick="return confirm('Are you sure you want to delete this page?');">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php
}
