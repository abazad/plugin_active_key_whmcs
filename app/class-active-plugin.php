<?php
namespace App;

use App\Utilities\SingletonTrait;
use App\ActiveLicenseKey\Check_License_Key_Whmcs;

class Active_Plugin_License_Key_Whmcs
{
    use SingletonTrait;

    protected function __construct()
    {
        $this->add_hooks();
    }

    protected function add_hooks()
    {
        add_action( 'admin_init', function() {
                $valid_active_license_key_whmcs = $this->valid_active_lisence_key_whmcs();
                if ( $valid_active_license_key_whmcs == false ) {
                    return add_action( 'admin_notices', array( $this, 'notice_active_license_key_whmcs' ) );
                }
                return true;
        } );

        add_action( 'admin_menu', array( $this, 'add_menu' ) );

        add_action( 'admin_post_form_input_license_key', array( $this, 'handle_post_request' ) );

        add_action( 'admin_post_nopriv_form_input_license_key',  array( $this, 'handle_post_request' ) );
    }

    public function add_menu()
    {
        add_menu_page(
            'Input license key',
            'Input license key',
            4,
            $this->get_slug_page_active_whmcs(),
            array( $this, 'menu_active_license_key_whmcs' ),
            'dashicons-admin-network',
            '2'
        );
    }

    protected function get_url_page_active_license_key () {
        $slug_page = $this->get_slug_page_active_whmcs();
        $url = admin_url( 'admin.php?page='.$slug_page );
        return $url;
    }

    protected function get_slug_page_active_whmcs() {
        $slug_page = 'active-license-key-whmcs';
        return $slug_page;
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

    public function notice_active_license_key_whmcs()
    {
        $get_local_key_whmcs    = get_option( 'local_key' );
        $get_license_key_whmcs  = get_option( 'license_key' );
        $url                    = $this->get_url_page_active_license_key();
        if( !$get_local_key_whmcs || !$get_license_key_whmcs ) {
            ob_start(); ?>
            <div class="notice notice-warning is-dismissible">
                <p>Plugin not license key. click <a href=" <?php echo $url ?>">active license key</a> now</p>
            </div>
            <?php
            return;
        }
        $license_transient_expired = get_transient( 'license_key_plugin' );
        if ( !$license_transient_expired ) {
           return $this->active_plugin_with_local_key($get_license_key_whmcs, $get_local_key_whmcs);
        }
    }

    public function menu_active_license_key_whmcs() {
        $get_local_key_whmcs          = get_option( 'local_key' );
        $license_transient_expired    = get_transient( 'license_key_plugin' );
        if ( ! $get_local_key_whmcs  ) {
            $this->page_active_license_key_whmcs();
            return;
        }

        if ( !$license_transient_expired ) {
            $this->page_active_license_key_whmcs();
            $user_info          = wp_get_current_user();
            $user_id            = $user_info->exists() && !empty( $user_info->ID ) ? $user_info->ID : 0;
            $validation_errors  = get_transient( 'validation_errors_' . $user_id );
            if ( $validation_errors !== FALSE ) {
                echo '<div class="error">Please fix the following Validation Errors:<ul><li>' .$validation_errors. '</li></ul></div>';
                delete_transient( 'validation_errors_' . $user_id );
            }
            return;
        }

        return $this->active_license_success_html();
    }

    public function page_active_license_key_whmcs()
    {
        $user_info          = wp_get_current_user();
        $user_id            = $user_info->exists() && !empty( $user_info->ID ) ? $user_info->ID : 0;
        $validation_errors  = get_transient( 'validation_errors_' . $user_id );
        if ( $validation_errors !== FALSE ) {
            echo '<div class="error">Please fix the following Validation Errors:<ul><li><b>' .$validation_errors. '</b></li></ul></div>';
            delete_transient( 'validation_errors_' . $user_id );
        }
        $url = esc_url( admin_url( 'admin-post.php' ) );
        $form_input = $this->form_active_license_key_whmcs( $url ) ;
        return $form_input;
    }

    public function active_license_success_html()
    {
        ?>
        <?php ob_start(); ?>
        <div class="notice notice-success">
                <p>Active success license key</p>
        </div>
        <?php
    }

    function handle_post_request()
    {
        $user_info  = wp_get_current_user();
        $user_id    = $user_info->exists() && !empty( $user_info->ID ) ? $user_info->ID : 0;
        $url        = wp_get_referer();

        if ( ! isset( $_POST['form_active_license_key_whmcs'] )
            || ! wp_verify_nonce( $_POST['form_active_license_key_whmcs'], 'active_license_key_whmcs' ) ) {
            print 'Sorry, your nonce did not verify.';
            exit;
        }

        if ( empty( $_POST['license_key'] ) ){
            $my_errors = 'License key cannot be empty';
            set_transient( 'validation_errors_' . $user_id, $my_errors );
            wp_redirect( $url );
            die();
        }

        $license = $_POST['license_key'];
        $this->active_license_key_whmcs( $license );

    }

    public function active_license_key_whmcs( $license_key )
    {
        $active = new Check_License_Key_Whmcs();
        $get_local_key_whmcs = get_option( 'local_key' );
        if ( !$get_local_key_whmcs ) {
            $response = $active->active_check_license( $license_key );
//            $get_local_key_whmcs = '9tjIxIzNwgDMwIjI6gjOztjIlRXYkt2Ylh2YioTO6M3OicmbpNnblNWasx1cyVmdyV2ccNXZsVHZv1GXzNWbodHXlNmc192czNWbodHXzN2bkRHacBFUNFEWcNHduVWb1N2bExFd0FWTcNnclNXVcpzQioDM4ozc7ISey9GdjVmcpRGZpxWY2JiO0EjOztjIx4CMuAjL3ITMioTO6M3OiAXaklGbhZnI6cjOztjI0N3boxWYj9Gbuc3d3xCdz9GasF2YvxmI6MjM6M3Oi4Wah12bkRWasFmdioTMxozc7ISeshGdu9WTiozN6M3OiUGbjl3Yn5WasxWaiJiOyEjOztjI3ATL4ATL4ADMyIiOwEjOztjIlRXYkVWdkRHel5mI6ETM6M3OicDMtcDMtgDMwIjI6ATM6M3OiUGdhR2ZlJnI6cjOztjIlNXYlxEI5xGa052bNByUD1ESXJiO5EjOztjIl1WYuR3Y1R2byBnI6ETM6M3OicjI6EjOztjIklGdjVHZvJHcioTO6M3Oi02bj5ycj1Ga3BEd0FWbioDNxozc7ICbpFWblJiO1ozc7IyUD1ESXBCd0FWTioDMxozc7ISZtFmbkVmclR3cpdWZyJiO0EjOztjIlZXa0NWQiojN6M3OiMXd0FGdzJiO2ozc7pjMxoTY8baca0885830a33725148e94e693f3f073294c0558d38e31f844c5e399e3c16a';
//               $response['status'] =  "Active";
//               $response['localkey'] = $get_local_key_whmcs;
            $local_key_status = $this->status_response( $response );

            if ( empty( $local_key_status ) ) {
                $this->set_error_input_license_key_whmcs();
            }

            $this->save_local_key( $get_local_key_whmcs );
            $this->save_license_key( $license_key );
            $this->set_transient_active_key( $license_key );
            $url = wp_get_referer();
            wp_redirect( $url );
            die();
        }
        $response = $active->active_check_license( $license_key );
        $get_local_key_whmcs = '9tjIxIzNwgDMwIjI6gjOztjIlRXYkt2Ylh2YioTO6M3OicmbpNnblNWasx1cyVmdyV2ccNXZsVHZv1GXzNWbodHXlNmc192czNWbodHXzN2bkRHacBFUNFEWcNHduVWb1N2bExFd0FWTcNnclNXVcpzQioDM4ozc7ISey9GdjVmcpRGZpxWY2JiO0EjOztjIx4CMuAjL3ITMioTO6M3OiAXaklGbhZnI6cjOztjI0N3boxWYj9Gbuc3d3xCdz9GasF2YvxmI6MjM6M3Oi4Wah12bkRWasFmdioTMxozc7ISeshGdu9WTiozN6M3OiUGbjl3Yn5WasxWaiJiOyEjOztjI3ATL4ATL4ADMyIiOwEjOztjIlRXYkVWdkRHel5mI6ETM6M3OicDMtcDMtgDMwIjI6ATM6M3OiUGdhR2ZlJnI6cjOztjIlNXYlxEI5xGa052bNByUD1ESXJiO5EjOztjIl1WYuR3Y1R2byBnI6ETM6M3OicjI6EjOztjIklGdjVHZvJHcioTO6M3Oi02bj5ycj1Ga3BEd0FWbioDNxozc7ICbpFWblJiO1ozc7IyUD1ESXBCd0FWTioDMxozc7ISZtFmbkVmclR3cpdWZyJiO0EjOztjIlZXa0NWQiojN6M3OiMXd0FGdzJiO2ozc7pjMxoTY8baca0885830a33725148e94e693f3f073294c0558d38e31f844c5e399e3c16a';
        $response['status'] =  "Active";
        $response['localkey'] = $get_local_key_whmcs;
        $local_key_status = $this->status_response( $response );

        if ( empty( $local_key_status ) ) {
            $this->set_error_input_license_key_whmcs();
        }

        $this->save_local_key( $get_local_key_whmcs );
        $this->save_license_key( $license_key );
        $this->set_transient_active_key( $license_key );
        $url = wp_get_referer();
        wp_redirect( $url );
        die();
    }

    public function active_plugin_with_local_key( $license_key, $local_key )
    {
        $active     = new Check_License_Key_Whmcs();
        $response   = $active->active_check_license( $license_key, $local_key );
//        $response['status'] =  "Active";
//        $response['localkey'] = $local_key;
        $local_key_status = $this->status_response( $response );
        if ( empty($local_key_status) ) {
            $url = $this->get_url_page_active_license_key();
            echo '<div class="notice notice-warning is-dismissible">
                    <p>warning : There was an error checking the license key. Click <a href="'.$url.'">active again license key</a> now</p>
                  </div>';
            return;
        }

        $this->save_license_key( $license_key );
        $this->set_transient_active_key( $license_key );
        $this->save_local_key( $local_key );
    }

    function set_transient_active_key( $license ) {
        return set_transient( 'license_key_plugin', $license, 60*60*12 );
    }

    public function save_local_key( $local_key )
    {
        $is_save_data = update_option('local_key', $local_key);
        if( $is_save_data ) {
            return true;
        }
        return false;
    }

    public function save_license_key( $license_key )
    {
        $is_save_data = update_option('license_key', $license_key);
        if( $is_save_data ) {
            return true;
        }
        return false;
    }

    public function status_response( $results )
    {
        $local_key_status = '';
        if ( $results['status'] !== 'Active' ) {
            return $local_key_status;
        }
        return $local_key_status = $results['localkey'];
    }

    public function form_active_license_key_whmcs($url)
    {
        ob_start(); ?>
        <form action=" <?php echo $url ?>" method="post" >
            <table class="form-table">
                <?php wp_nonce_field('active_license_key_whmcs', 'form_active_license_key_whmcs'); ?>
                <tr>
                    <th scope="row"><label for="blogname"> Input license Key: </label> </th>
                    <td><input id="license_key" type="text" name="license_key" class="regular-text"></td>
                </tr>
            </table>
            <input type="hidden" name="action" value="form_input_license_key">
            <p class="submit">
                <input type="submit" name="submit" class="button button-primary" value="Active license key">
            </p>
        </form>
        <?php
        $html = ob_get_clean();
        return print_r( $html );
    }

    public function set_error_input_license_key_whmcs()
    {
        $url        = wp_get_referer();
        $user_info  = wp_get_current_user();
        $user_id    = $user_info->exists() && !empty( $user_info->ID ) ? $user_info->ID : 0;
        $my_errors  = 'License key invalid';
        set_transient( 'validation_errors_' . $user_id, $my_errors );
        wp_redirect( $url );
        die();
    }

    public function valid_active_lisence_key_whmcs()
    {
        $get_local_key_whmcs        = get_option( 'local_key' );
        $license_transient_expired  = get_transient( 'license_key_plugin' );
        if ( !$get_local_key_whmcs ){
            return false;
        }
        if( !$license_transient_expired ) {
            return false;
        }
        return true;
    }

}