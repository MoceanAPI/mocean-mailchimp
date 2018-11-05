<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/5/2018
 * Time: 9:42 AM
 */

namespace Mocean\Mailchimp\exceptions;


use Exception;

class ListNotFoundException extends Exception
{
    protected $message = 'List not found';
}