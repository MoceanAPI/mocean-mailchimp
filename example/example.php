<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/1/2018
 * Time: 12:16 PM
 */

require '../vendor/autoload.php';

header('Content-type:application/json');

$config = array(
    'MOCEAN_API_KEY' => 'xxxx',
    'MCOEAN_API_SECRET' => 'xxxx',
    'MOCEAN_API_TEXT' => 'Testing Text',

    'MAILCHIMP_API_KEY' => 'xxxx-xxx',
);

//create a Mailchimp object
$mailChimp = new Mocean\Mailchimp\Mailchimp($config);


//broadcast through campaigns
//====================================================================================
if (!$campaigns = $mailChimp->campaigns()) {
    echo json_encode(array('error' => Mocean\Mailchimp\api\MailChimpApi::$errMsg));
    exit;
}

//broadcast the message to all the members in selected campaign;
$totalSmsSent = $campaigns->lists()->members()->broadcast();
echo json_encode('Total SMS sent: ' . $totalSmsSent);
//====================================================================================

//broadcast through lists
//====================================================================================
if (!$lists = $mailChimp->lists()) {
    echo json_encode(array('error' => Mocean\Mailchimp\api\MailChimpApi::$errMsg));
    exit;
}

//broadcast the message to all the members in selected campaign;
$totalSmsSent = $lists->members()->broadcast();
echo json_encode('Total SMS sent: ' . $totalSmsSent);
//====================================================================================