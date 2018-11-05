<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/1/2018
 * Time: 3:01 PM
 */

namespace Mocean\Mailchimp\http;

class Response
{
    public $statusCode;
    public $data;

    public function __construct($statusCode, $data)
    {
        $this->statusCode = $statusCode;
        if ($this->isCurlError()) {
            $this->data = $data;
        } else {
            $this->data = json_decode($data);
        }
    }

    public function isError()
    {
        return $this->statusCode !== 200 && $this->statusCode !== 201 && $this->statusCode !== 202 && $this->statusCode !== 204 && $this->statusCode !== 304;
    }

    public function isOk()
    {
        return $this->statusCode === 200 || $this->statusCode === 201 || $this->statusCode === 202 || $this->statusCode === 204 || $this->statusCode === 304;
    }

    public function isCurlError()
    {
        return $this->statusCode === 0;
    }
}