<?php

namespace FastBill\Api;

class MyFastBillClient extends AbstractFastBillClient
{
    public static function create(array $options)
    {
        return new static(
            new \GuzzleHttp\Client([
                'base_uri' => "https://my.fastbill.com/",
                'headers' => ['Content-Type' => 'application/json']
            ]),
            $options
        );
    }
}