<?php

function authenticate($url,$api_key,$api_secret) {
    $token = "";
    $bearer_token_credential = $api_key . ':' . $api_secret;
    $credentials = base64_encode($bearer_token_credential);
    $args = array(
        'method' => 'POST',
        'httpversion' => '1.1',
        'blocking' => true,
        'headers' => array(
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
        ) ,
        'body' => array(
            'grant_type' => 'client_credentials'
        )
    );
    $response = wp_remote_post($url, $args);
    $keys = json_decode($response['body'], true);
    return $keys['access_token'];
}

?>