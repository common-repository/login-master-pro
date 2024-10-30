<?php
global $wpdb;
$rows_limit = 20;
$page = @$_GET['login_master_page'];

$error_message = '';
$ok_message = '';

if (isset($_POST['submit'])) {

    if ($_POST['block_by'] == 'ip_address') {
        $block_by = 1;
        $block_value = $_POST['ip_address'];

        if (!filter_var($block_value, FILTER_VALIDATE_IP) and !filter_var($block_value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $error_message = __('Valid IP Address is required.', 'login-master');
        }
    } else {
        $block_by = 2;
        $block_value = $_POST['username'];

        if (!username_exists($block_value)) {
            $error_message = __('Username does not exists.', 'login-master');
        }
    }

    if ($_POST['block_type'] == '403') {
        $block_action = 1;
        $block_action_value = '';
    }

    if ($_POST['block_type'] == 'url') {
        $block_action = 2;
        $block_action_value = $_POST['url'];

        if (!filter_var($block_action_value, FILTER_VALIDATE_URL)) {
            $error_message = __('Valid URL required.', 'login-master');
        }
    }

    if ($_POST['block_type'] == 'error') {
        $block_action = 3;
        $block_action_value = $_POST['error'];
    }

    if ($error_message == '') {
        $wpdb->show_errors = false; //we will show custom error messages
        $wpdb->suppress_errors = true;

        if (isset($_POST['edit'])) {//Edit block rule
            if ($wpdb->update($wpdb->prefix . 'login_master_block_users', array('block_by' => $block_by,
                        'block_value' => $block_value,
                        'block_action' => $block_action,
                        'block_action_value' => $block_action_value,
                            ), array('block_id' => $_POST['edit']), array(
                        '%d',
                        '%s',
                        '%d',
                        '%s'
                            ), array('%d'))
            ) {
                $ok_message = __('Block rule updated successfully', 'login-master');
            } else {
                $error_message = $wpdb->last_error;
                if (preg_match("/Duplicate/i", $error_message)) {
                    $error_message = __('Duplicate entry for', 'login-master') . ' "' . $block_value . '"';
                }
            }
        } else {//Insert block rule
            if ($wpdb->insert(
                            $wpdb->prefix . 'login_master_block_users', array(
                        'block_by' => $block_by,
                        'block_value' => $block_value,
                        'block_action' => $block_action,
                        'block_action_value' => $block_action_value,
                            ), array(
                        '%d',
                        '%s',
                        '%d',
                        '%s',
                    ))) {
                $ok_message = __('Block rule added successfully', 'login-master');
            } else {

                $error_message = $wpdb->last_error;
                if (preg_match("/Duplicate/i", $error_message)) {
                    $error_message = __('Duplicate entry for', 'login-master') . ' "' . $block_value . '"';
                }
            }
        }
    }

    if ($ok_message != '') {
        ?>
        <div class="updated"><p><?php echo $ok_message; ?></p></div>
        <?php
    }
    if ($error_message != '') {
        ?>
        <div class="error"><p><?php echo $error_message; ?></p></div>
        <?php
    }
}//end submit

if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $single_block = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "login_master_block_users WHERE block_id = %d", $_GET['block_id']), OBJECT);
}
?>
<div class="wrap">
    <h2 class="login-master-icon blocks-icons"><?php _e('Block Users', 'login-master'); ?> <?php if (isset($_GET['action']) and $_GET['action'] == 'edit') { ?>
            <a href="admin.php?page=<?php echo $_GET['page']; ?>" class="add-new-h2"><?php _e('Add New', 'login-master'); ?></a>  
            <?php
        }
        ?></h2>
    <form action="" method="post">
        <table class="form-table">
            <tbody>
                <tr>
                    <td colspan="2"><h3><?php _e('Block By', 'login-master'); ?></h3></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="ip_address"><input type="radio" name="block_by" value="ip_address" <?php
        if (isset($single_block) && ($single_block->block_by == '1') or !isset($single_block->block_by)) {
            echo 'checked';
        }
        ?> /> <?php _e('IP Address', 'login-master'); ?></label></th>
                    <td><input type="text" name="ip_address" value="<?php
        if (isset($single_block) && $single_block->block_by == '1') {
            echo esc_attr(stripslashes($single_block->block_value));
        }
        ?>" />
                        <span class="description"><?php _e('IP Address of a person you want to block. Example: 192.0.2.24', 'login-master'); ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="username"><input type="radio" name="block_by" value="username" <?php
        if (isset($single_block) && $single_block->block_by == '2') {
            echo 'checked';
        }
        ?> /> <?php _e('Username', 'login-master'); ?></label></th>
                    <td><input type="text" name="username" value="<?php
        if (isset($single_block) && $single_block->block_by == '2') {
            echo esc_attr(stripslashes($single_block->block_value));
        }
        ?>" />
                        <span class="description"><?php _e('Username of a WordPress user you want to block. Example: test_user', 'login-master'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td colspan="2"><h3><?php _e('Block Type', 'login-master'); ?></h3></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="http403"><input type="radio" name="block_type" value="403" <?php
        if (isset($single_block) && ($single_block->block_action == '1') or !isset($single_block->block_action)) {
            echo 'checked';
        }
        ?> /> <?php _e('403 HTTP response', 'login-master'); ?></label></th>
                    <td>
                        <span class="description"><?php _e('Send a 403 HTTP response (access forbidden) to a user browser', 'login-master'); ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="url"><input type="radio" name="block_type" value="url" <?php
        if (isset($single_block) && $single_block->block_action == '2') {
            echo 'checked';
        }
        ?> /> <?php _e('Redirect to a URL', 'login-master'); ?></label></th>
                    <td><input type="text" name="url" value="<?php
        if (isset($single_block) && $single_block->block_action == '2') {
            echo esc_attr(stripslashes($single_block->block_action_value));
        }
        ?>" />
                        <span class="description"><?php _e('Redirect user to a "block" notification page. ', 'login-master'); ?> </span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="error"><input type="radio" name="block_type" value="error" <?php
        if (isset($single_block) && $single_block->block_action == '3') {
            echo 'checked';
        }
        ?> /> <?php _e('Login form error message', 'login-master'); ?></label></th>
                    <td><input type="text" name="error" value="<?php
        if (isset($single_block) && $single_block->block_action == '3') {
            echo esc_attr(stripslashes($single_block->block_action_value));
        }
        ?>" />
                        <span class="description"><?php _e('Show an error message on the login form', 'login-master'); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php
        if (isset($_GET['action']) && $_GET['action'] == 'edit') {
            ?>
            <input type="hidden" name="edit" value="<?php echo $_GET['block_id']; ?>" />
            <?php
        }
        ?>

<?php submit_button(__('Save', 'login-master')); ?>

    </form>

    <?php
    if (isset($_GET['block_id']) && $_GET['action'] == 'delete') {
        $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "login_master_block_users WHERE block_id = %d", $_GET['block_id']));
    }

    if (isset($_GET['keyword']) && @$_GET['keyword'] != '') {
        $rows_total_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "login_master_block_users WHERE block_value LIKE %s OR block_action_value LIKE %s", '%' . $_GET['keyword'] . '%', '%' . $_GET['keyword'] . '%'), OBJECT);
    } else {
        $rows_total_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "login_master_block_users", OBJECT);
    }

    if ((!$page) || (is_numeric($page) == false) || ($page < 0) || ($page > $rows_total_count)) {
        $page = 1; //default
    }

    $total_pages = ceil($rows_total_count / $rows_limit);
    $set_limit = $page * $rows_limit - ($rows_limit);

    if (isset($_GET['keyword']) && @$_GET['keyword'] != '') {
        $keyword = '&keyword=' . @$_GET['keyword'] . '&';
        $search_results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "login_master_block_users WHERE block_value LIKE %s OR block_action_value LIKE %s ORDER BY block_id DESC LIMIT " . $set_limit . "," . $rows_limit, '%' . $_GET['keyword'] . '%', '%' . $_GET['keyword'] . '%'), OBJECT);
    } else {
        $keyword = '';
        $search_results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "login_master_block_users ORDER BY block_id DESC LIMIT " . $set_limit . "," . $rows_limit, OBJECT);
    }
    ?>

    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e('Block By', 'login-master'); ?></th>
                <th></th>
                <th><?php _e('Block Action', 'login-master'); ?></th>
                <th></th>
                <th><?php _e('Edit', 'login-master'); ?></th>
                <th><?php _e('Delete', 'login-master'); ?></th>
            </tr>
        </thead>

        <tbody>

            <?php
            $count = 0;
            foreach ($search_results as $post) {

                if ($post->block_by == 1) {
                    $block_by = __('IP Address', 'login-master');
                }
                if ($post->block_by == 2) {
                    $block_by = __('Username', 'login-master');
                }

                if ($post->block_action == 1) {
                    $block_action = __('403 HTTP response', 'login-master');
                }
                if ($post->block_action == 2) {
                    $block_action = __('Redirect to a URL', 'login-master');
                }
                if ($post->block_action == 3) {
                    $block_action = __('Login form error message', 'login-master');
                }
                ?>
                <tr>
                    <td><?php echo $block_by; ?></td> 
                    <td><?php echo $post->block_value; ?></td> 
                    <td><?php echo $block_action; ?></td>  
                    <td><?php echo stripslashes($post->block_action_value); ?></td> 
                    <td><a href="admin.php?page=<?php echo $_GET['page']; ?>&action=edit&block_id=<?php echo $post->block_id; ?>"><?php _e('Edit', 'login-master'); ?></a></td>
                    <td><a href="admin.php?page=<?php echo $_GET['page']; ?>&action=delete&block_id=<?php echo $post->block_id; ?>" onclick="return deletechecked();"><?php _e('Delete', 'login-master'); ?></a></td>
                </tr>

                <?php
                $count++;
            }
            if ($count == 0) {
                ?>
                <tr>
                    <td colspan="6"><?php _e('0 blocks yet'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="tablenav bottom">

        <div class="alignleft actions">
            <form name="search-form" method="get">                   
                <input type="text" name="keyword" value="">
                <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
                <input type="hidden" name="login_master_page" value="">
                <?php submit_button(__('Search', 'login-master'), 'secondary', 'search', false); ?>
            </form>
        </div>

        <div class="alignleft actions"></div>

        <div class="tablenav-pages"><span class="displaying-num"><?php echo $rows_total_count; ?> <?php _e('items', 'login-master'); ?></span>
            <span class="pagination-links">
                <a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=' . $_GET['page'] . '&login_master_page=1' . $keyword ?>" title="<?php _e('Go to the first page', 'login-master'); ?>" class="first-page <?php
                if ($page == 1) {
                    echo "disabled";
                }
                ?>">«</a>
                <a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=' . $_GET['page'] . '&login_master_page=' . ($page - 1) . '' . $keyword; ?>" title="<?php _e('Go to the previous page', 'login-master'); ?>" class="prev-page <?php
                   if ($page == 1) {
                       echo "disabled";
                   }
                ?>">‹</a>

                <span class="paging-input"><?php echo $page; ?> of <span class="total-pages"><?php echo $total_pages; ?></span></span>

                <a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=' . $_GET['page'] . '&login_master_page=' . ($page + 1) . '' . $keyword; ?>" title="<?php _e('Go to the next page', 'login-master'); ?>" class="next-page <?php
                   if ($page == $total_pages) {
                       echo "disabled";
                   }
                ?>">›</a>
                <a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=' . $_GET['page'] . '&login_master_page=' . $total_pages . '' . $keyword; ?>" title="<?php _e('Go to the last page', 'login-master'); ?>" class="last-page <?php
                   if ($page == $total_pages) {
                       echo "disabled";
                   }
                ?>">»</a></span></div>
        <br class="clear">
    </div>

</div><!--wrap-->