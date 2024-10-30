<?php
if (isset($_POST['submit'])) {
    update_option('login_master_normal_auth_timeout', $_POST['login_master_normal_auth_timeout']);
    update_option('login_master_normal_auth_timeout_duration', $_POST['login_master_normal_auth_timeout_duration']);
    update_option('login_master_remember_me_auth_timeout', $_POST['login_master_remember_me_auth_timeout']);
    update_option('login_master_remember_me_auth_duration', $_POST['login_master_remember_me_auth_duration']);
    update_option('login_master_show_dashboard_widget', $_POST['login_master_show_dashboard_widget']);
    update_option('login_master_show_dashboard_widget_record_count', $_POST['login_master_show_dashboard_widget_record_count']);
    update_option('login_master_track_logins', $_POST['login_master_track_logins']);
}
?>
<div class="wrap">
    <form name="login-master-settings" action="" method="post">
        <h2 class="login-master-icon settings-icons"><?php _e('Settings', 'login-master'); ?></h2>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Dashboard widget record count', 'login-master'); ?></th>
                <td>
                    <select name="login_master_show_dashboard_widget_record_count">
                        <?php
                        $dashboard_record_count = get_option('login_master_show_dashboard_widget_record_count');
                        for ($i = 1; $i <= 20; $i++) {
                            ?>
                            <option value="<?php echo $i; ?>" <?php
                        if ($dashboard_record_count == $i) {
                            echo 'selected';
                        }
                            ?>><?php echo $i; ?></option>
                                    <?php
                                }
                                ?>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Show dashboard widgets', 'login-master'); ?></th>
                <td>
                        <?php $show_dashboard_widget = get_option('login_master_show_dashboard_widget'); ?>
                    <select name="login_master_show_dashboard_widget">
                        <option value="yes" <?php
                        if ($show_dashboard_widget == 'yes') {
                            echo 'selected';
                        }
                        ?>><?php _e('Yes', 'login-master'); ?></option>
                        <option value="no" <?php
                                if ($show_dashboard_widget == 'no') {
                                    echo 'selected';
                                }
                        ?>><?php _e('No', 'login-master'); ?></option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Track Logins', 'login-master'); ?></th>
                <td>
                        <?php $track_logins = get_option('login_master_track_logins'); ?>
                    <select name="login_master_track_logins">
                        <option value="yes" <?php
                        if ($track_logins == 'yes') {
                            echo 'selected';
                        }
                        ?>><?php _e('Yes', 'login-master'); ?></option>
                        <option value="no" <?php
                        if ($track_logins == 'no') {
                            echo 'selected';
                        }
                        ?>><?php _e('No', 'login-master'); ?></option>
                    </select>
                    <span class="description"><?php _e('If you turn off login tracking option some of the core plugin functions will not work the way it is supposed to. (Limit Users, Dashboard Widget, etc.) ', 'login-master'); ?></span>
                </td>
            </tr>

            </tbody>

        </table>

<?php submit_button('Save', 'primary', 'submit', true); ?>
    </form>
</div>
