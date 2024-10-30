<?php
/*
  Plugin Name: Login Master PRO
  Plugin URI: http://wpsalad.com/login-master-pro/
  Description: Track who, wherefrom and when logged into wp-admin area. Block users by username or IP address. Limit hourly or daily number of logins per user. Redirect certain user type to a choosen page. Go to <a href="admin.php?page=login_master_settings">Login Master Settings</a> to start.
  Version: 1.0.2
  Author: WP Salad
  Author URI: http://www.wpsalad.com/
 */

//Plugin installation
register_activation_hook(__FILE__, 'login_master_install');

function login_master_install() {
    include("includes/install.php");
}

function login_master_admin_scripts() {
    wp_register_script('admin-js', plugins_url('includes/js/admin-js.js', __FILE__));
    wp_enqueue_script('admin-js');
}

add_action('admin_print_scripts', 'login_master_admin_scripts');

function login_master_admin_styles() {
    wp_register_style('login_master_admin_style', plugins_url('includes/css/admin-style.css', __FILE__));
    wp_enqueue_style('login_master_admin_style');
}

add_action('admin_print_scripts', 'login_master_admin_styles');

function login_master_additional_users_columns_titles($column) {
    $column['country'] = __('Country', 'login-master');
    $column['login_times'] = __('Logins', 'login-master');
    return $column;
}

add_filter('manage_users_columns', 'login_master_additional_users_columns_titles');

function login_master_additional_users_columns($val, $column_name, $user_id) {
    $user = get_userdata($user_id);

    switch ($column_name) {
        case 'country' :
            return login_master_ip_to_country_user($user_id);
            break;
        case 'login_times' :
            return login_master_get_login_times($user_id);
            break;

        default:
    }

    return $return;
}

add_filter('manage_users_custom_column', 'login_master_additional_users_columns', 10, 3);

function login_master_create_menu() {//Add menu and submenu items
    add_menu_page(__('Login Master', 'login-master'), __('Login Master', 'login-master'), 'manage_options', 'login_master_logins', 'login_master_logins', plugins_url('/includes/img/login.png', __FILE__));
    add_submenu_page('login_master_logins', __('Logins', 'login-master'), __('Logins', 'login-master'), 'manage_options', 'login_master_logins', 'login_master_logins');
    add_submenu_page('login_master_logins', __('Admin Redirects', 'login-master'), __('Admin Redirects', 'login-master'), 'manage_options', 'login_master_redirects', 'login_master_redirects');
    add_submenu_page('login_master_logins', __('Block Users', 'login-master'), __('Block Users', 'login-master'), 'manage_options', 'login_master_block_users', 'login_master_block_users');
    add_submenu_page('login_master_logins', __('Limit Users', 'login-master'), __('Limit Users', 'login-master'), 'manage_options', 'login_master_limit_users', 'login_master_limit_users');
    add_submenu_page('login_master_logins', __('Settings', 'login-master'), __('Settings', 'login-master'), 'manage_options', 'login_master_settings', 'login_master_settings');
}

add_action('admin_menu', 'login_master_create_menu');

function login_master_settings() {
    include("includes/admin/settings.php");
}

function login_master_logins() {
    include("includes/admin/logins.php");
}

function login_master_block_users() {
    include("includes/admin/block-users.php");
}

function login_master_limit_users() {
    include("includes/admin/limit-users.php");
}

function login_master_redirects() {
    include("includes/admin/redirects.php");
}

// Create the function to output the contents of our Dashboard Widget
function login_master_latest_logins() {
    global $wpdb;

    $limit = get_option('login_master_show_dashboard_widget_record_count');

    if (!is_numeric($limit)) {
        $limit = 3;
    }

    $latest_logins_query = "SELECT user_id, time, ip FROM " . $wpdb->prefix . "logins ORDER BY time DESC LIMIT " . $limit;
    $latest_logins = $wpdb->get_results($latest_logins_query, OBJECT);
    ?>
    <div class="inside">
        <?php
        foreach ($latest_logins as $latest_login) {
            $user = get_userdata($latest_login->user_id);
            $user_country = login_master_ip_to_country($latest_login->ip);
            ?>
            <div data-wp-lists="list:comment" id="the-comment-list">
                <div class="comment even thread-even depth-1 comment-item approved">
                    <?php echo get_avatar($latest_login->user_id, '50'); ?> 
                    <div class="dashboard-comment-wrap">
                        <h4 class="comment-meta">
                            <cite class="comment-author"><a class="url" rel="external nofollow" href="<?php echo site_url(); ?>/wp-admin/admin.php?page=login_master_logins&user_id=<?php echo $user->ID; ?>"><?php echo $user->user_login; ?></a></cite> <?php _e('on', 'login-master') ?> <?php echo date_i18n(get_option('date_format'), strtotime($latest_login->time)); ?> <?php echo date_i18n(get_option('time_format'), strtotime($latest_login->time)); ?></h4>
                        <blockquote><p><?php _e('IP Address', 'login-master'); ?>: <?php echo $latest_login->ip; ?><?php
                    if ($user_country != '') {
                        echo '<br />' . __('From', 'master-login') . ' ' . $user_country;
                    }
                    ?></p></blockquote>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

function login_master_get_login_times($user_id) {
    global $wpdb;
    $login_times = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "logins WHERE user_id = " . $user_id);
    return $login_times;
}

//Function which gets country from IP Address
function login_master_ip_to_country($ip) {
    global $wpdb;

    $country_query = "SELECT 
	            c.country 
	        FROM 
	            " . $wpdb->prefix . "login_master_countries c,
	            " . $wpdb->prefix . "login_master_countries_ip i 
	        WHERE 
	            i.ip < INET_ATON('" . $ip . "') 
	            AND 
	            c.country_code = i.country_code 
	        ORDER BY 
	            i.ip DESC 
	        LIMIT 0,1";

    if ($ip == '127.0.0.1' or $ip == '::1') {// Catch both IPv4 and IPv6 local IP addresses
        $countryName = __('a private location');
    } else {
        $countryName = $wpdb->get_var($country_query);
    }

    // Output full country name
    return $countryName;
}

//Get user IP Address
function login_master_get_ip() {
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        elseif (isset($_SERVER['HTTP_CLIENT_IP']))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        else
            $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR'))
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        elseif (getenv('HTTP_CLIENT_IP'))
            $ip = getenv('HTTP_CLIENT_IP');
        else
            $ip = getenv('REMOTE_ADDR');
    }
    return $ip;
}

//Get user country by IP Address
function login_master_ip_to_country_user($user_id) {
    global $wpdb;
    $last_user_ip_query = "SELECT ip FROM " . $wpdb->prefix . "logins WHERE user_id = " . $user_id . " ORDER BY time DESC LIMIT 1";
    $last_user_ip = $wpdb->get_var(($last_user_ip_query));

    return login_master_ip_to_country($last_user_ip);
}

// Create the function use in the action hook
function login_master_add_dashboard_widgets() {
    if (current_user_can('administrator')) {
        wp_add_dashboard_widget('login_master_latest_logins', __('Latest Logins'), 'login_master_latest_logins');
    }
}

if (get_option('login_master_show_dashboard_widget') == 'yes') {
    add_action('wp_dashboard_setup', 'login_master_add_dashboard_widgets');
}

function login_master_user_limited_action($username) {
    global $wpdb;
    global $error;
    global $pagenow;

    if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {//Execute only on login and registration pages
        $limited = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "login_master_limit_users WHERE username = '%s'", $username));
        if (isset($limited->limit_id)) {

            if ($limited->per == 1) {
                $time_unit = 'HOUR';
            } else {
                $time_unit = 'DAY';
            }
            $limited_user = get_user_by('login', $username);

            $logins_in_limited_period = $wpdb->get_row("SELECT COUNT(*) as cnt FROM " . $wpdb->prefix . "logins WHERE user_id = " . $limited_user->ID . " AND time > (NOW() - INTERVAL " . $limited->per_value . " " . $time_unit . ")", OBJECT);

            if ($limited->times <= $logins_in_limited_period->cnt) {
                $error = __('Login limit exceeded. Please try again later.', 'login-master');
                add_filter('login_errors', 'login_master_limit_error_message');
                return false;
            } else {
                return true;
            }
        }
        return true;
    }
}

function login_master_is_blocked_action($username, $ip) {
    global $wpdb;
    global $error;

    $blocked_user_data = $wpdb->get_row("SELECT block_action, block_action_value FROM " . $wpdb->prefix . "login_master_block_users WHERE block_value = '" . $username . "' OR block_value = '" . $ip . "'", OBJECT);
    if (!empty($blocked_user_data)) {
        if ($blocked_user_data->block_action == 1) {//403 Forbidden (on every page, not just wp-login / registration)
            header("HTTP/1.1 403 Forbidden");
            exit;
        }
        if ($blocked_user_data->block_action == 2) {//URL Redirect (on every page, not just wp-login / registratiion)
            ob_end_clean();
            wp_redirect($blocked_user_data->block_action_value);
            exit;
        }
        if ($blocked_user_data->block_action == 3) {//Only on login / registration pages
            global $pagenow;
            if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {//Execute only on login and registration pages
                $error = $blocked_user_data->block_action_value;
                add_filter('login_errors', 'login_master_blocked_error_message');
                return false;
            }
        }
    } else {
        return true; //Not blocked
    }
}

function login_master_limit_error_message($error) {
    global $error;
    return $error;
}

function login_master_blocked_error_message($error) {
    global $error;
    return $error;
}

if (!function_exists('wp_check_password')) {

    function wp_check_password($password, $hash, $user_id = '') {
        global $wpdb;
        global $wp_hasher;

        $ip = login_master_get_ip();
        $user = get_userdata($user_id);

        if (empty($wp_hasher)) {
            require_once( ABSPATH . 'wp-includes/class-phpass.php');
            $wp_hasher = new PasswordHash(8, TRUE);
        }

        $check = false;

        if ($password == '') {
            $check = false;
        }

        if ($user->user_pass == md5($password)) {
            $check = true;
            wp_setcookie($user_id, md5($password));
        }

        if ($user->user_pass == sha1($password)) {
            $check = true;
            wp_setcookie($user_id, sha1($password));
        }

        if ($wp_hasher->CheckPassword($password, $user->user_pass)) {
            $check = true;
        }

        $hash = md5($user->user_pass);

        $check = login_master_is_blocked_action($user->user_login, $ip);
        $check = login_master_user_limited_action($user->user_login);

        if ($check == true) {
            $insert = "INSERT INTO " . $wpdb->prefix . "logins (user_id,time,ip) VALUES (" . $user_id . ", '" . current_time('mysql') . "', '" . $ip . "')";

            if (get_option('login_master_track_logins') == 'yes') {
                $wpdb->query($insert);
            }

            return apply_filters('check_password', $check, $password, $hash, $user_id);
        } else {
            return false;
        }
    }

}

if (!function_exists('wp_hash_password')) {
    function wp_hash_password($password) {
        return md5($password);
    }

}

function login_master_get_current_user_role($id) {
    if (is_user_logged_in()) {
        $user = new WP_User($id);
        if (!empty($user->roles) && is_array($user->roles)) {
            foreach ($user->roles as $role)
                return $role;
        }
    }
}

function login_master_output_buffer() {
    ob_start();
}

function login_master_admin_redirect() {
    global $wpdb;
    global $wp_roles;

    if (is_admin()) {

        $login_master_roles = $wp_roles->roles;
        $current_user = wp_get_current_user();

        foreach ($login_master_roles as $roles => $role) {
            if (get_option('login_master_redirect_' . $roles) != '') {
                if (current_user_can($roles)) {
                    ob_end_clean();
                    wp_redirect(get_option('login_master_redirect_' . $roles));
                    exit;
                }
            }
        }
    }
}

add_action('init', 'login_master_output_buffer', 1); // Solution for Cannot modify header information - headers already sent
add_action('init', 'login_master_admin_redirect', 2);
?>
