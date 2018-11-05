<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/5/2018
 * Time: 9:42 AM
 */

namespace Mocean\Mailchimp\exceptions;


use Exception;

class CampaignNotFoundException extends Exception
{
    protected $message = 'Campaign not found';
}