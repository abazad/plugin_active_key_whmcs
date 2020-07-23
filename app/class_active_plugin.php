<?php
namespace App;

use App\Utilities\SingletonTrait;
use App\ActiveLicenseKey\class_check_sample_code;

class Active_Plucgin
{
    use SingletonTrait;

    protected function __construct()
    {
        $this->add_hooks();
    }

    protected function add_hooks()
    {
        add_action('admin_notices', array( $this, 'admin_notice' ) );

        add_action( 'admin_menu', array( $this, 'add_menu' ) );

        add_action( 'admin_post_contact_form', array( $this, 'handle_post_request' ) );

        add_action( 'admin_post_nopriv_contact_form',  array( $this, 'handle_post_request' ) );
    }

    public function add_menu()
    {
        add_menu_page(
            'menu-dat',
            'Nhập license key',
            4 //
            , 'activeLicenseKey',
            array( $this, 'example_menu' ),
            'dashicons-admin-network',
            '2'
        );
    }

    /**
     * On activate plugin
     */
    public static function activate()
    {

    }

    /**
     * On deactivate plugin
     */
    public static function deactivate()
    {

    }

    public function admin_notice()
    {
        $getLocalKey    = get_option('local_key');
        $getLicenseKey  = get_option('license_key');
        $url            = admin_url('admin.php?page=activeLicenseKey');
        if( !$getLocalKey || !$getLicenseKey ) {
            echo '<div class="notice notice-warning is-dismissible">
                    <p>Plugin not license key. click <a href="'.$url.'">active license key</a> now</p>
                  </div>';
            return;
        }
        $licenseTransientExpired = get_transient( 'license_key_plugin' );
        if ( !$licenseTransientExpired ) {
            $this->active_plugin_with_local_key($getLicenseKey, $getLocalKey);
        }
    }

    public function example_menu() {
        $getLocalKey                = get_option('local_key');
        $licenseTransientExpired    = get_transient( 'license_key_plugin' );
        if ( ! $getLocalKey  ) {
            $this->active_page();
            return;
        }

        if ( !$licenseTransientExpired ) {
            $this->active_page();
            $user_info          = wp_get_current_user();
            $user_id            = $user_info->exists() && !empty($user_info->ID) ? $user_info->ID : 0;
            $validation_errors  = get_transient('validation_errors_' . $user_id);
            if ($validation_errors !== FALSE) {
                echo '<div class="error">Please fix the following Validation Errors:<ul><li>' .$validation_errors. '</li></ul></div>';
                delete_transient('validation_errors_' . $user_id);
            }
            return;
        }

        $this->active_license_success_html();
    }

    public function active_page()
    {
        $user_info          = wp_get_current_user();
        $user_id            = $user_info->exists() && !empty($user_info->ID) ? $user_info->ID : 0;
        $validation_errors  = get_transient('validation_errors_' . $user_id);
        if ($validation_errors !== FALSE) {
            echo '<div class="error">Please fix the following Validation Errors:<ul><li><b>' .$validation_errors. '</b></li></ul></div>';
            delete_transient('validation_errors_' . $user_id);
        }
        $licenseTransientExpired    = get_transient( 'license_key_plugin' );
        $getLocalKey                = get_option('local_key');
        if ( !$licenseTransientExpired && $getLocalKey ) {
            echo '<div class="notice notice-warning is-dismissible">
                    <p>There was an error</p>
                    <p><b>Retype license key, please!</b></p>
                  </div>';
        }
        $url = esc_url( admin_url('admin-post.php') );
        echo '
             <form action="'.$url.'" method="post" >
             <table class="form-table">
                <tr>
                    <th scope="row"><label for="blogname"> Nhập license Key: </label> </th>
                    <td><input id="license_key" type="text" name="license_key" class="regular-text"></td>
                </tr>               
             </table>
             <input type="hidden" name="action" value="contact_form">
             <p class="submit">
                <input type="submit" name="submit" class="button button-primary" value="Active license key">
             </p>
            </form>
            ';
    }

    public function active_license_success_html()
    {
        echo '<div class="notice notice-success">
                <p>Active success license key</p>
              </div>';
    }

    function handle_post_request()
    {
        $user_info  = wp_get_current_user();
        $user_id    = $user_info->exists() && !empty($user_info->ID) ? $user_info->ID : 0;
        $url        = wp_get_referer();

        if ( empty($_POST['license_key'] ) ){
            $my_errors = 'License key cannot be empty';
            set_transient('validation_errors_' . $user_id, $my_errors);
            wp_redirect($url);
            die();
        }

        $license = $_POST['license_key'];
        $this->active_plugin($license);

    }

    public function active_plugin($licenseKey)
    {
        $active = new class_check_sample_code();
        $getLocalKey = get_option('local_key');
        if ( !$getLocalKey ) {
            $response = $active->active_check_license($licenseKey);
//            $getLocalKey = '9tjIxIzNwgDMwIjI6gjOztjIlRXYkt2Ylh2YioTO6M3OicmbpNnblNWasx1cyVmdyV2ccNXZsVHZv1GXzNWbodHXlNmc192czNWbodHXzN2bkRHacBFUNFEWcNHduVWb1N2bExFd0FWTcNnclNXVcpzQioDM4ozc7ISey9GdjVmcpRGZpxWY2JiO0EjOztjIx4CMuAjL3ITMioTO6M3OiAXaklGbhZnI6cjOztjI0N3boxWYj9Gbuc3d3xCdz9GasF2YvxmI6MjM6M3Oi4Wah12bkRWasFmdioTMxozc7ISeshGdu9WTiozN6M3OiUGbjl3Yn5WasxWaiJiOyEjOztjI3ATL4ATL4ADMyIiOwEjOztjIlRXYkVWdkRHel5mI6ETM6M3OicDMtcDMtgDMwIjI6ATM6M3OiUGdhR2ZlJnI6cjOztjIlNXYlxEI5xGa052bNByUD1ESXJiO5EjOztjIl1WYuR3Y1R2byBnI6ETM6M3OicjI6EjOztjIklGdjVHZvJHcioTO6M3Oi02bj5ycj1Ga3BEd0FWbioDNxozc7ICbpFWblJiO1ozc7IyUD1ESXBCd0FWTioDMxozc7ISZtFmbkVmclR3cpdWZyJiO0EjOztjIlZXa0NWQiojN6M3OiMXd0FGdzJiO2ozc7pjMxoTY8baca0885830a33725148e94e693f3f073294c0558d38e31f844c5e399e3c16a';
//               $response['status'] =  "Active";
//               $response['localkey'] = $getLocalKey;
            $local_key = $this->status_response($response);

            if ( empty($local_key) ) {
                $url        = wp_get_referer();
                $user_info  = wp_get_current_user();
                $user_id    = $user_info->exists() && !empty($user_info->ID) ? $user_info->ID : 0;
                $my_errors  = 'License key invalid';
                set_transient('validation_errors_' . $user_id, $my_errors);
                wp_redirect($url);
                die();
            }

            $this->save_local_key($getLocalKey);
            $this->save_license_key($licenseKey);
            $this->set_transient_active_key($licenseKey);
            $url = wp_get_referer();
            wp_redirect($url);
            die();
        }
        delete_site_option( 'license_key');
        delete_site_option( 'local_key');
        $response = $active->active_check_license($licenseKey);
//        $getLocalKey = '9tjIxIzNwgDMwIjI6gjOztjIlRXYkt2Ylh2YioTO6M3OicmbpNnblNWasx1cyVmdyV2ccNXZsVHZv1GXzNWbodHXlNmc192czNWbodHXzN2bkRHacBFUNFEWcNHduVWb1N2bExFd0FWTcNnclNXVcpzQioDM4ozc7ISey9GdjVmcpRGZpxWY2JiO0EjOztjIx4CMuAjL3ITMioTO6M3OiAXaklGbhZnI6cjOztjI0N3boxWYj9Gbuc3d3xCdz9GasF2YvxmI6MjM6M3Oi4Wah12bkRWasFmdioTMxozc7ISeshGdu9WTiozN6M3OiUGbjl3Yn5WasxWaiJiOyEjOztjI3ATL4ATL4ADMyIiOwEjOztjIlRXYkVWdkRHel5mI6ETM6M3OicDMtcDMtgDMwIjI6ATM6M3OiUGdhR2ZlJnI6cjOztjIlNXYlxEI5xGa052bNByUD1ESXJiO5EjOztjIl1WYuR3Y1R2byBnI6ETM6M3OicjI6EjOztjIklGdjVHZvJHcioTO6M3Oi02bj5ycj1Ga3BEd0FWbioDNxozc7ICbpFWblJiO1ozc7IyUD1ESXBCd0FWTioDMxozc7ISZtFmbkVmclR3cpdWZyJiO0EjOztjIlZXa0NWQiojN6M3OiMXd0FGdzJiO2ozc7pjMxoTY8baca0885830a33725148e94e693f3f073294c0558d38e31f844c5e399e3c16a';
//        $response['status'] =  "Active";
//        $response['localkey'] = $getLocalKey;
        $local_key = $this->status_response($response);

        if ( empty($local_key) ) {
            $url = wp_get_referer();
            $user_info = wp_get_current_user();
            $user_id = $user_info->exists() && !empty($user_info->ID) ? $user_info->ID : 0;
            $my_errors = 'License key invalid';
            set_transient('validation_errors_' . $user_id, $my_errors);
            wp_redirect($url);
            die();
        }

        $this->save_local_key($getLocalKey);
        $this->save_license_key($licenseKey);
        $this->set_transient_active_key($licenseKey);
        $url = wp_get_referer();
        wp_redirect($url);
        die();
    }

    public function active_plugin_with_local_key($licenseKey, $LocalKey)
    {
        $active     = new class_check_sample_code();
        $response   = $active->active_check_license($licenseKey, $LocalKey);
//        $response['status'] =  "Active";
//        $response['localkey'] = $LocalKey;
        $local_key = $this->status_response($response);
        if ( empty($local_key) ) {
            $url = admin_url('admin.php?page=activeLicenseKey');
            echo '<div class="notice notice-warning is-dismissible">
                    <p>Plugin not license key. click <a href="'.$url.'">active license key</a> now</p>
                  </div>';
            return;
        }

        $this->save_license_key($licenseKey);
        $this->set_transient_active_key($licenseKey);
        $this->save_local_key($LocalKey);
    }

    function set_transient_active_key($license) {
        set_transient( 'license_key_plugin', $license, 60*60*12 );
        $my_transient = get_transient ( 'timeout_facebook-like' );
        return $my_transient;
    }

    public function save_local_key($localKey)
    {
        global $wpdb;
        $wpdb->insert(
            'wp_options',
            array(
                'option_name'   => 'local_key',
                'option_value'  => $localKey
            ),
            array( '%s' )
        );
    }

    public function save_license_key($licenseKey)
    {
        global $wpdb;
        $wpdb->insert(
            'wp_options',
            array(
                'option_name'   => 'license_key',
                'option_value'  => $licenseKey
            ),
            array( '%s' )
        );
    }

    public function status_response($results)
    {
        $local_key = '';
        switch ($results['status']) {
            case "Active":
                $local_key = $results['localkey'];
                break;
            case "Invalid":
                break;
            case "Expired":
                break;
            case "Suspended":
                break;
            default:
                break;
        }
        return $local_key;
    }

}