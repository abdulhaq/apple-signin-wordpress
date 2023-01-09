<?php
include_once(plugin_dir_path(__FILE__) . 'variables.php');

function signin_button()
{

    $redirect_uri = get_site_url() . '/wp-admin/admin-ajax.php?action=apple_signin';
    $_SESSION['state'] = bin2hex(random_bytes(5));

    $authorize_url = 'https://appleid.apple.com/auth/authorize' . '?' . http_build_query([
        'response_type' => 'code',
        'response_mode' => 'form_post',
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'state' => $_SESSION['state'],
        'scope' => 'name email',
    ]);

    echo '<a href="<?= $authorize_url; ?>">
        <ul class="list-unstyled social-icon mb-0" style="max-width: 200px;margin: 0px auto!important;border: 1px solid black;padding: 5px 5px;border-radius: 5px;cursor: pointer;font-size: 15px;color: black">
            <img src="https://cdn-icons-png.flaticon.com/512/0/747.png" alt="" title="" class="img-small" style="width: 25px;margin-top: -7px;margin-right: 10px;">Signin with Apple

        </ul>
    </a>';
}
// register shortcode
add_shortcode('apple_signin_button', 'signin_button');
