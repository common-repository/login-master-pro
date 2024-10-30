<?php
global $wpdb;

$rows_limit = 20;
$page = @$_GET['login_master_page'];

$error_message = '';
$ok_message = '';

if (isset($_POST['submit'])) {

    $username = $_POST['username'];
    $times = $_POST['times'];
    $per_value = $_POST['per_value'];
    $per = $_POST['per'];

    if (!username_exists($username)) {
        $error_message = __('Username does not exists.', 'login-master');
    }

    if (!is_numeric($times)) {
        $error_message = __('Login times must be a number', 'login-master');
    }
    
    if (is_numeric($times) && $times == 0) {
        $error_message = __('0 is not allowed value', 'login-master');
    }

    if (!is_numeric($per_value)) {
        $error_message = __('"Times per" value must be a number', 'login-master');
    }
    
    if (is_numeric($per_value) && $per_value == 0) {
        $error_message = __('0 is not allowed value', 'login-master');
    }

    if ($error_message == '') {
        $wpdb->show_errors = false; //we will show custom error messages
        $wpdb->suppress_errors = true;

        if (isset($_POST['edit'])) {//Edit block rule
            if ($wpdb->update($wpdb->prefix . 'login_master_limit_users', array(
                        'username' => $username,
                        'times' => $times,
                        'per_value' => $per_value,
                        'per' => $per,
                            ), array('limit_id' => $_POST['edit']), array(
                        '%s',
                        '%d',
                        '%d',
                        '%d'
                            ), array('%d'))
            ) {
                $ok_message = __('Limit rule updated successfully', 'login-master');
            }else{
                $error_message = $wpdb->last_error;
                if (preg_match("/Duplicate/i", $error_message)) {
                    $error_message = __('Duplicate entry for', 'login-master') . ' "' . $username . '"';
                }
            }
        } else {//Insert limit rule
            
            if ($wpdb->insert(
                            $wpdb->prefix . 'login_master_limit_users', array(
                        'username' => $username,
                        'times' => $times,
                        'per_value' => $per_value,
                        'per' => $per,
                            ), array(
                        '%s',
                        '%d',
                        '%d',
                        '%d'
                    ))) {
                $ok_message = __('Limit rule added successfully', 'login-master');
            }else{
                $error_message = $wpdb->last_error;
                if (preg_match("/Duplicate/i", $error_message)) {
                    $error_message = __('Duplicate entry for', 'login-master') . ' "' . $username . '"';
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
    $single_block = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "login_master_limit_users WHERE limit_id = %d", $_GET['limit_id']), OBJECT);
}
?>
<div class="wrap">
    <h2 class="login-master-icon limits-icons"><?php _e('Limit Users', 'login-master'); ?> <?php if (isset($_GET['action']) and $_GET['action'] == 'edit') { ?>
            <a href="admin.php?page=<?php echo $_GET['page']; ?>" class="add-new-h2"><?php _e('Add New', 'login-master'); ?></a>  
            <?php
        }
        ?></h2>
    
    <form action="" method="post">
        <table class="form-table">
            <tbody>
                <tr>
                    <td colspan="2"><span class="description"><?php _e('Limit login times per hour or day.', 'login-master'); ?></span></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Username: <input type="text" name="username" value="<?php if(isset($single_block->username)){ echo $single_block->username; }?>" /> can login <input type="text" name="times" value="<?php if(isset($single_block->times)){ echo $single_block->times; }?>" /> times per <input type="text" name="per_value" value="<?php if(isset($single_block->per_value)){ echo $single_block->per_value; }?>" /> <select name="per"><option value="1" <?php if(isset($single_block->per)){ if($single_block->per == 1){ echo 'selected';}}?>>Hour(s)</option><option value="2" <?php if(isset($single_block->per)){ if($single_block->per == 2){ echo 'selected';}}?>>Day(s)</option></select></th> 
                </tr>

            </tbody>
        </table>

        <?php
        if (isset($_GET['action']) && $_GET['action'] == 'edit') {
            ?>
            <input type="hidden" name="edit" value="<?php echo $_GET['limit_id']; ?>" />
            <?php
        }
        ?>

        <?php submit_button(__('Save', 'login-master')); ?>

    </form>

    <?php
    if (isset($_GET['limit_id']) && $_GET['action'] == 'delete') {
        $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "login_master_limit_users WHERE limit_id = %d", $_GET['limit_id']));
    }

    if (isset($_GET['keyword']) && @$_GET['keyword'] != '') {
        $rows_total_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "login_master_limit_users WHERE username LIKE %s", '%' . $_GET['keyword'] . '%'), OBJECT);
    } else {
        $rows_total_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "login_master_limit_users", OBJECT);
    }

    if ((!$page) || (is_numeric($page) == false) || ($page < 0) || ($page > $rows_total_count)) {
        $page = 1; //default
    }

    $total_pages = ceil($rows_total_count / $rows_limit);
    $set_limit = $page * $rows_limit - ($rows_limit);

    if (isset($_GET['keyword']) && @$_GET['keyword'] != '') {
        $keyword = '&keyword=' . @$_GET['keyword'] . '&';
        $search_results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "login_master_limit_users WHERE username LIKE %s ORDER BY limit_id DESC LIMIT " . $set_limit . "," . $rows_limit, '%' . $_GET['keyword'] . '%'), OBJECT);
    } else {
        $keyword = '';
        $search_results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "login_master_limit_users ORDER BY limit_id DESC LIMIT " . $set_limit . "," . $rows_limit, OBJECT);
    }
    ?>

    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e('Username', 'login-master'); ?></th>
                <th><?php _e('Login Times', 'login-master'); ?></th>
                <th><?php _e('Limit', 'login-master'); ?></th>
                <th><?php _e('Edit', 'login-master'); ?></th>
                <th><?php _e('Delete', 'login-master'); ?></th>
            </tr>
        </thead>

        <tbody>

            <?php
            $count = 0;
            foreach ($search_results as $post) {
                if($post->per == 1 && $post->per_value == 1){
                    $unit = 'hour';
                }
                if($post->per == 1 && $post->per_value > 1){
                    $unit = 'hours';
                }
                if($post->per == 2 && $post->per_value == 1){
                    $unit = 'day';
                }
                if($post->per == 2 && $post->per_value > 1){
                    $unit = 'days';
                }
                ?>
                <tr>
                    <td><?php echo $post->username; ?></td> 
                    <td><?php echo $post->times; ?></td> 
                    <td><?php echo 'per '.$post->per_value.' '.$unit; ?></td>  
                    <td><a href="admin.php?page=<?php echo $_GET['page']; ?>&action=edit&limit_id=<?php echo $post->limit_id; ?>"><?php _e('Edit', 'login-master'); ?></a></td>
                    <td><a href="admin.php?page=<?php echo $_GET['page']; ?>&action=delete&limit_id=<?php echo $post->limit_id; ?>" onclick="return deletechecked();"><?php _e('Delete', 'login-master'); ?></a></td>
                </tr>

                <?php
                $count++;
            }
            if ($count == 0) {
                ?>
                <tr>
                    <td colspan="6"><?php _e('0 limits yet'); ?></td>
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