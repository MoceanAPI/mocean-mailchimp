Mocean Mailchimp
============================
Mailchimp integration with [Mocean Api](moceanapi.com)

## Installation

To install the library, run this command in terminal.

```bash
composer require mocean/mailchimp
```

### Configuration

default config list
```php
'MOCEAN_API_VERSION' => '1',
'MOCEAN_API' => 'rest.moceanapi.com/rest',
'MOCEAN_API_KEY' => '',
'MCOEAN_API_SECRET' => '',
'MOCEAN_API_SENDER_ID' => '', //for mocean-from field (leave empty to use mailchimp list name as sender id)
'MOCEAN_API_TEXT' => 'Testing Text',

'MAILCHIMP_API_VERSION' => '3.0',
'MAILCHIMP_API' => 'api.mailchimp.com',
'MAILCHIMP_API_KEY' => '',
'MAILCHIMP_LISTS_SEARCH_NAME' => '', //search for mailchimp list to be used (leave this empty to use all lists)
'MAILCHIMP_CAMPAIGNS_SEARCH_TITLE' => '', //search for campaign title to be used (leave this empty to use all campaigns)

'ALLOW_DUPLICATE_MEMBER' => false, //set to true if the you wish to send to the same member in different list multiple time
```

## Usage

If you're using composer, make sure the autoloader is included in your project's bootstrap file:
```php
require_once "vendor/autoload.php";
```

Setup your configuration
```php
$config = array(
    'MOCEAN_API_KEY' => 'xxxx',
    'MCOEAN_API_SECRET' => 'xxxx',
    'MOCEAN_API_TEXT' => 'Testing Text',
    
    'MAILCHIMP_API_KEY' => 'xxxx-xxx',
);
```

Create a Mailchimp object
```php
$mailchimp = new Mocean\Mailchimp\Mailchimp($config);
```

Broadcast message through campaign
```php
$totalSmsSent = $mailchimp->campaigns()->lists()->members()->broadcast();
echo "Total SMS Sent: $totalSmsSent";
```

Broadcast message through lists
```php
$totalSmsSent = $mailchimp->lists()->members()->broadcast();
echo "Total SMS Sent: $totalSmsSent";
```

Broadcast by passing in custom text parameter (default using text in config)
```php
broadcast('custom text');
```

Sometime, you would not using all campaign or list, this library provide a convenient method which you can set it in config
```php
//seperate multiple name by (,)
$config => array(
    'MAILCHIMP_LISTS_SEARCH_NAME' => 'First List,Second List',
    'MAILCHIMP_CAMPAIGNS_SEARCH_TITLE' => 'First Campaign,Second Campaign',
);
```

Use `get()` if you wish to get the response data from mailchimp
```php
$campaignResponse = $mailchimp->campaigns()->get();
$listResponse = $mailchimp->lists()->get();
$memberResponse = $mailchimp->lists()->members()->get();
```

Dynamically set configuration (always override current config)
```php
$mailchimp->setConfig($config);
```

### API Errors

In the event of any api errors occured, the object you request will return false and you can get the specific error msg from the api class
```php
$campaigns = $mailChimp->campaigns();
if($campaigns === false){
    echo Mocean\Mailchimp\api\MailChimpApi::$errMsg;
}
```

### Exceptions

A `Mocean\Mailchimp\exceptions\CampaignNotFoundException` is thrown if the account dont have any campaign or unable to search the campaign in config   

A `Mocean\Mailchimp\exceptions\ListNotFoundException` is thrown if the account dont have any list or unable to search the list in config  
 
A `Mocean\Mailchimp\exceptions\MemberNotFoundException` is thrown if the list dont have any member  

A `Mocean\Mailchimp\exceptions\InvalidKeyException` is thrown if there's key credential error

A `Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException` is thrown if there's error in config key name
    
## Example

there's an example on usage in folder `example/example.php`

## License

This library is released under the [MIT License](LICENSE)