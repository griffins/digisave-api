<?php

namespace App\Foundation\AfricasTalking;


use GuzzleHttp\Client as Http;
use GuzzleHttp\RequestOptions;

class SmsClient
{

    private $client;

    /**
     * SmsClient constructor.
     * @param $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    public function send($options)
    {
        $params = array(
            'username' => $this->client->username,
            'to' => $options['to'],
            'message' => $options['text'],
            'from' => $options['from'],
            "Apikey" => $this->client->key,
        );
        $http = new Http();
        $http->get("https://api.africastalking.com/restless/send", [RequestOptions::QUERY => $params]);
        return true;
    }
}