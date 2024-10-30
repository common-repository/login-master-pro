<?php
global $wpdb;
$rows_limit = 20;
$page = @$_GET['login_master_page'];

if (isset($_GET['id']) && $_GET['action'] == 'delete') {
    $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "logins WHERE id = %d", $_GET['id']));
}

if (isset($_POST['delete_older_logins_than'])) {
    $number_of_days = $_POST['delete_older_logins_than'];
    if ($wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "logins WHERE time < (NOW() - INTERVAL %d DAY)", $number_of_days))) {
        ?>
        <div class="updated"><p><?php _e('Selected login records deleted successfully.'); ?></p></div>
        <?php
    } else {
        ?>
        <div class="error"><p><?php _e('0 login records in selected period. None deleted.'); ?></p></div>
        <?php
    }
}

if (isset($_GET['user_id'])) {
    $rows_total_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "logins WHERE user_id = %d", $_GET['user_id']));
} else {
    $rows_total_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "logins");
}

if ((!$page) || (is_numeric($page) == false) || ($page < 0) || ($page > $rows_total_count)) {
    $page = 1; //default
}

$total_pages = ceil($rows_total_count / $rows_limit);
$set_limit = $page * $rows_limit - ($rows_limit);

$pageposts = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "logins ORDER BY time DESC LIMIT " . $set_limit . "," . $rows_limit, OBJECT);

$user_pagination_string = '';

if (isset($_GET['user_id'])) {
    $pageposts = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "logins WHERE user_id = %d ORDER BY time DESC LIMIT " . $set_limit . "," . $rows_limit, $_GET['user_id']), OBJECT);
    $user_pagination_string = '&user_id=' . $_GET['user_id'];
}
?>

<div class="wrap">
    <h2 class="login-master-icon logins-icons"><?php _e('Logins', 'login-master'); ?> <?php
if (isset($_GET['user_id'])) {
    echo '<a class="add-new-h2" href="javascript:history.back(-1);">' . __('Back', 'login-master') . '</a>';
}
?></h2>

    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e('User', 'login-master'); ?></th>
                <th><?php _e('Time', 'login-master'); ?></th>
                <th><?php _e('IP Address', 'login-master'); ?></th>
                <th><?php _e('Country', 'login-master'); ?></th>
                <th><?php _e('Delete', 'login-master'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php
            $count = 0;

            foreach ($pageposts as $post) {
                ?>

                <tr>
                    <td><a href="<?php echo site_url() . '/wp-admin/admin.php?page=login_master_logins&user_id=' . $post->user_id; ?>"><?php echo get_userdata($post->user_id)->user_login; ?></a></td>
                    <td><?php echo $post->time; ?></td>
                    <td><?php echo $post->ip; ?></td> 
                    <td><?php echo login_master_ip_to_country($post->ip); ?></td>
                    <td><a href="admin.php?page=<?php echo $_GET['page']; ?>&action=delete&id=<?php echo $post->id; ?>" onclick="return deletechecked();"><?php _e('Delete', 'login-master'); ?></a></td>
                </tr>

                <?php
                $count++;
            }
            if ($count == 0) {
                ?>

                <tr>
                    <td><?php _e('No records yet.', 'login-master'); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>

    <div class="tablenav bottom">

        <div class="alignleft actions">
            <form name="search-form" method="post">
                <label class="open-label">
                    <span class="list-controls displaying-num">Delete logins older than</span>
                    <select name="delete_older_logins_than">
                        <option value="1">1 day</option>
                        <option value="7">7 days</option>
                        <option value="30">1 month</option>
                        <option value="91">3 months</option>
                        <option value="182">6 months</option>
                        <option value="365">1 year</option>

                    </select>
                </label>
                <input type="submit" name="delete" value="<?php _e('Delete', 'login-master'); ?>" class="button button-secondary" onclick="return deletechecked();">
            </form>
        </div>

        <div class="alignleft actions"></div>
        <div class="tablenav-pages"><span class="displaying-num"><?php echo $rows_total_count; ?> <?php _e('items', 'login-master'); ?></span>
            <span class="pagination-links">
                <a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=' . $_GET['page'] . '&login_master_page=1' . $user_pagination_string ?>" title="<?php _e('Go to the first page', 'login-master'); ?>" class="first-page <?php
            if ($page == 1) {
                echo "disabled";
            }
            ?>">«</a>
                <a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=' . $_GET['page'] . '&login_master_page=' . ($page - 1) . $user_pagination_string; ?>" title="<?php _e('Go to the previous page', 'login-master'); ?>" class="prev-page <?php
                   if ($page == 1) {
                       echo "disabled";
                   }
            ?>">‹</a>

                <span class="paging-input"><?php echo $page; ?> of <span class="total-pages"><?php echo $total_pages; ?></span></span>

                <a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=' . $_GET['page'] . '&login_master_page=' . ($page + 1) . $user_pagination_string; ?>" title="<?php _e('Go to the next page', 'login-master'); ?>" class="next-page <?php
                   if ($page == $total_pages) {
                       echo "disabled";
                   }
            ?>">›</a>
                <a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=' . $_GET['page'] . '&login_master_page=' . $total_pages . $user_pagination_string; ?>" title="<?php _e('Go to the last page', 'login-master'); ?>" class="last-page <?php
                   if ($page == $total_pages) {
                       echo "disabled";
                   }
            ?>">»</a></span></div>
        <br class="clear">
    </div>