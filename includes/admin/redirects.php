<?php
global $wpdb;
global $wp_roles;

$login_master_roles = $wp_roles->roles;
$error_count = 0;

if (isset($_POST['submit'])) {
    foreach ($_POST as $key => $value) {
        if ($key != 'submit') {
            if (filter_var($value, FILTER_VALIDATE_URL) or $value == '') {
                update_option($key, trim(esc_url($value)));
            } else {
                $error_count++;
            }
        }
    }
    if ($error_count > 0) {
        ?>
        <div class="error"><p><?php _e('Some of URLs are not valid'); ?></p></div>
        <?php
    }
}
?>
<div class="wrap">
    <h2 class="login-master-icon redirects-icons">Redirects</h2>
    <form action="" method="post">
        <table class="form-table">
            <tbody>
                <tr>
                    <td colspan="2"><span class="description"><?php _e('URL of the page where users with particular role will be redirected uppon login to wp-admin. If empty, users will not be redirected.', 'login-master'); ?></span></td>
                </tr>
                <?php
                foreach ($login_master_roles as $roles => $role) {
                    if ($roles != 'administrator') {
                        ?>
                        <tr>
                            <th scope="row"><?php echo $role['name']; ?></th>
                            <td><input type="text" name="login_master_redirect_<?php echo $roles; ?>" value="<?php echo get_option('login_master_redirect_' . $roles); ?>" /></td>
                        </tr>
                        <?php
                    }
                }
                ?>

            </tbody>
        </table>

        <?php submit_button(__('Save Changes', 'login-master'), 'primary', 'submit', true); ?>

    </form>

</div><!--wrap-->