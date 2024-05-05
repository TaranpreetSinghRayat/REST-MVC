<?php

namespace App\Core;

class OAuth
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $authUrl;
    protected $tokenUrl;
    protected $userInfoUrl;

    public function __construct($clientId, $clientSecret, $redirectUri, $authUrl, $tokenUrl, $userInfoUrl)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->authUrl = $authUrl;
        $this->tokenUrl = $tokenUrl;
        $this->userInfoUrl = $userInfoUrl;
    }

    public function getAuthUrl()
    {
        // Generate the authorization URL
        $url = $this->authUrl . '?response_type=code&client_id=' . $this->clientId . '&redirect_uri=' . urlencode($this->redirectUri);
        return $url;
    }

    public function getAccessToken($code)
    {
        // Exchange the authorization code for an access token
        $params = [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
        ];

        $response = $this->httpPost($this->tokenUrl, $params);
        return json_decode($response, true);
    }

    public function getUserInfo($accessToken)
    {
        // Retrieve user information using the access token
        $headers = ['Authorization: Bearer ' . $accessToken];
        $response = $this->httpGet($this->userInfoUrl, $headers);
        return json_decode($response, true);
    }

    protected function httpPost($url, $params)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    protected function httpGet($url, $headers = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
