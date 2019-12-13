<?php

namespace App\Foundation\AfricasTalking;


class Client
{
    public $username;
    public $key;

    /**
     * Client constructor.
     * @param $username
     * @param $key
     * @param $defaults
     */
    public function __construct($username, $key, $defaults)
    {
        $this->username = $username;
        $this->key = $key;
    }

    public function message()
    {
        return new SmsClient($this);
    }
}