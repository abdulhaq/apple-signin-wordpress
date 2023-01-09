<?php

/**
 * Plugin Name: Apple Signin
 * Plugin URI: https://github.com/keygen-sh/example-wordpress-plugin
 * Description: Signin with Apple.
 * Version: 1.0.0
 * Author: Abdul Haq
 * Author URI: https://it.haq.life/?ref=plugins
 */

include_once(plugin_dir_path(__FILE__) . 'variables.php');

//signup with apple
add_action('wp_ajax_nopriv_apple_signin', 'apple_signin');
add_action('wp_ajax_apple_signin', 'apple_signin');
function apple_signin()
{

    session_start();
    $client_id = 'com.lockersuites.app';

    $client_secret = generateJWT();
    $redirect_uri = get_site_url() . '/wp-admin/admin-ajax.php?action=apple_signin';


    $post_code = $_REQUEST['code'];
    if (isset($post_code)) {

        // if ($_SESSION['state'] != $_POST['state']) {
        //     die('Authorization server returned an invalid state parameter');
        // }

        if (isset($_REQUEST['error'])) {
            die('Authorization server returned an error: ' . htmlspecialchars($_REQUEST['error']));
        }

        $body = [
            'grant_type' => 'authorization_code',
            'code' => $post_code,
            'redirect_uri' => $redirect_uri,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
        ];

        $body = wp_json_encode($body);

        $options = [
            'body'        => $body,
        ];

        $ch = curl_init('https://appleid.apple.com/auth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $post_code,
            'redirect_uri' => $redirect_uri,
            'client_id' => $client_id,
            'client_secret' => $client_secret
        ]));

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            //"cache-control: no-cache",
            //"content-type: application/json"
        ));
        $response = curl_exec($ch);
        //echo $response;
        $response = json_decode($response, true);


        // echo 'here: ' . $client_secret;
        // exit();

        if (!isset($response['access_token'])) {
            echo '<p>Error getting an access token:</p>';
            echo $response['error'];
            echo $response['access_token'];
            echo '<pre>';
            print_r($response);
            echo '</pre>';
            echo '<p><a href="/">Start Over</a></p>';
            die();
        }

        echo '<h3>Access Token Response</h3>';
        echo '<pre>';
        print_r($response);
        echo '</pre>';


        $claims = explode('.', $response['id_token'])[1];
        $claims = json_decode(base64_decode($claims));

        echo '<pre>';
        print_r($claims);
        print_r($claims->email);
        echo '</pre>';

        //login or signup to wordpress
        $user = get_user_by('email', $claims->email);
        $user_id = $user->ID;
        // echo $user_id;
        // exit();
        if ($user_id) {
            //login
            $user = get_user_by('id', $user_id);
            if ($user) {
                wp_set_current_user($user_id, $user->user_login);
                wp_set_auth_cookie($user_id);
                //do_action('wp_login', $user->user_login);
                wp_redirect($post_login_url);
                exit();
            }
        } else {
            //signup
            $new_user_id = wp_create_user($claims->email, '', $claims->email);
            if (is_wp_error($new_user_id)) {
                $error = $new_user_id->get_error_message();
                //handle error here
            }
            $new_user = get_user_by('id', $new_user_id);
            wp_set_current_user($new_user_id, $new_user->user_login);
            wp_set_auth_cookie($new_user_id);
            wp_redirect($post_login_url);
            exit();
        }

        die();
    }
}

function generateJWT()
{
    $header = [
        'alg' => 'ES256',
        'kid' => $kid
    ];
    $body = [
        'iss' => $iss,
        'iat' => time(),
        'exp' => time() + 3600,
        'aud' => 'https://appleid.apple.com',
        'sub' => $sub
    ];

    //$privKey = openssl_pkey_get_private(file_get_contents('AuthKey_8UJ8MDL8W9.p8'));
    $privKey = openssl_pkey_get_private($private_key);

    if (!$privKey) {
        echo 'here';
        return false;
    }

    $payload = encode(json_encode($header)) . '.' . encode(json_encode($body));

    $signature = '';
    $success = openssl_sign($payload, $signature, $privKey, OPENSSL_ALGO_SHA256);
    if (!$success) return false;

    $raw_signature = fromDER($signature, 64);

    return $payload . '.' . encode($raw_signature);
}

function encode($data)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

function fromDER(string $der, int $partLength)
{
    $hex = unpack('H*', $der)[1];
    if ('30' !== mb_substr($hex, 0, 2, '8bit')) { // SEQUENCE
        throw new \RuntimeException();
    }
    if ('81' === mb_substr($hex, 2, 2, '8bit')) { // LENGTH > 128
        $hex = mb_substr($hex, 6, null, '8bit');
    } else {
        $hex = mb_substr($hex, 4, null, '8bit');
    }
    if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
        throw new \RuntimeException();
    }
    $Rl = hexdec(mb_substr($hex, 2, 2, '8bit'));
    $R = retrievePositiveInteger(mb_substr($hex, 4, $Rl * 2, '8bit'));
    $R = str_pad($R, $partLength, '0', STR_PAD_LEFT);
    $hex = mb_substr($hex, 4 + $Rl * 2, null, '8bit');
    if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
        throw new \RuntimeException();
    }
    $Sl = hexdec(mb_substr($hex, 2, 2, '8bit'));
    $S = retrievePositiveInteger(mb_substr($hex, 4, $Sl * 2, '8bit'));
    $S = str_pad($S, $partLength, '0', STR_PAD_LEFT);
    return pack('H*', $R . $S);
}
/**
 * @param string $data
 *
 * @return string
 */
function preparePositiveInteger(string $data)
{
    if (mb_substr($data, 0, 2, '8bit') > '7f') {
        return '00' . $data;
    }
    while ('00' === mb_substr($data, 0, 2, '8bit') && mb_substr($data, 2, 2, '8bit') <= '7f') {
        $data = mb_substr($data, 2, null, '8bit');
    }
    return $data;
}
/**
 * @param string $data
 *
 * @return string
 */
function retrievePositiveInteger(string $data)
{
    while ('00' === mb_substr($data, 0, 2, '8bit') && mb_substr($data, 2, 2, '8bit') > '7f') {
        $data = mb_substr($data, 2, null, '8bit');
    }
    return $data;
}
