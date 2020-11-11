<?php
namespace TypeRocketProPlugin;

use TypeRocket\Controllers\Controller;

final class SettingsController extends Controller
{
    public function typerocket()
    {
        return tr_view(__DIR__ . '/../../admin.php');
    }

    public function activate(\TypeRocket\Http\Request $request, \TypeRocket\Http\Response $response)
    {
        $lic = trim($request->getFields('typerocket_pro_license_key'));
        update_option('typerocket_pro_license_key', $lic, 'yes');

        $response->flashNext('TypeRocket Pro activated successfully!'); // unless...

        $api_params = [
            'edd_action' => 'activate_license',
            'license'    => $lic,
            'item_id'    => TYPEROCKET_PLUGIN_DL_PRO_ID,
            'url'        => home_url()
        ];

        // Call the custom API.
        $remote = wp_remote_post('https://typerocket.com', ['timeout' => 15, 'sslverify' => false, 'body' => $api_params]);

        // make sure the response came back okay
        if ( is_wp_error( $remote ) || 200 !== wp_remote_retrieve_response_code( $remote ) ) {
            $message =  ( is_wp_error( $remote ) && ! empty( $remote->get_error_message() ) ) ? $remote->get_error_message() : __( 'An error occurred, please try again.' );
        } else {
            $data = json_decode( wp_remote_retrieve_body( $remote ) );
            if ( false === $data->success ) {
                switch( $data->error ) {
                    case 'expired' :
                        $message = sprintf(
                            __( 'Your license key expired on %s.' ),
                            date_i18n( get_option( 'date_format' ), strtotime( $data->expires, current_time( 'timestamp' ) ) )
                        );
                        break;
                    case 'revoked' :
                        $message = __( 'Your license key has been disabled.' );
                        break;
                    case 'missing' :
                        $message = __( 'Invalid license.' );
                        break;
                    case 'invalid' :
                    case 'site_inactive' :
                        $message = __( 'Your license is not active for this URL.' );
                        break;
                    case 'item_name_mismatch' :
                        $message = __( 'This appears to be an invalid license key for TypeRocket Pro.' );
                        break;
                    case 'no_activations_left':
                        $message = __( 'Your license key has reached its site activation limit. Login to your account on typerocket.com to manage your sites.' );
                        break;
                    default :
                        $message = __( 'An error occurred, please try again.' );
                        break;
                }
            }

            // $license_data->license will be either "valid" or "invalid"
            update_option('typerocket_pro_license_status', $data->license, 'yes');
        }

        // Check if anything passed on a message constituting a failure
        if ( ! empty( $message ) ) {
            $response->flashNext($message, 'error');
        }

        return tr_redirect()->back();
    }
}