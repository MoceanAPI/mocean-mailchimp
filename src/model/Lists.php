<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/1/2018
 * Time: 3:54 PM
 */

namespace Mocean\Mailchimp\model;

use Mocean\Mailchimp\api\MailChimpApi;
use Mocean\Mailchimp\config\Config;
use Mocean\Mailchimp\exceptions\ListNotFoundException;

class Lists
{
    private $lists;
    /** @var Members $members */
    private $members;

    /**
     * Lists constructor.
     * @param array $lists
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\InvalidKeyException
     * @throws \Mocean\Mailchimp\exceptions\MemberNotFoundException
     */
    private function __construct(array $lists)
    {
        $this->lists = $lists;
        $this->members = Members::load($this->lists);
    }

    /**
     * get the lists json response
     *
     * @return array
     */
    public function get()
    {
        return $this->lists;
    }

    /**
     * get Members object from this list
     *
     * @return Members
     */
    public function members()
    {
        return $this->members;
    }

    /**
     * load list
     *
     * @return bool|Lists
     * @throws ListNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\InvalidKeyException
     * @throws \Mocean\Mailchimp\exceptions\MemberNotFoundException
     */
    public static function load()
    {
        error_log('Loading lists from mailchimp api');
        $lists = MailChimpApi::getLists();
        if (!$lists) {
            error_log('Error while loading lists (' . MailChimpApi::$errMsg . ')');
            return false;
        }

        error_log('Total list from mailchimp: ' . count($lists));
        if (count($lists) <= 0) {
            throw new ListNotFoundException('this mailchimp account dont have any list');
        }
        $selectedList = array();

        if ($configListName = Config::get('MAILCHIMP_LISTS_SEARCH_NAME')) {
            $searchNames = explode(',', $configListName);

            foreach ($searchNames as $searchName) {
                foreach ($lists as $list) {
                    if (strtoupper($list->name) === strtoupper($searchName)) {
                        $selectedList[] = array('name' => $list->name, 'id' => $list->id);
                        break;
                    }
                }
            }
            error_log('List is define in config, currently using list: (' . $configListName . ')');
        } else {
            foreach ($lists as $list) {
                $selectedList[] = array('name' => $list->name, 'id' => $list->id);
            }
            error_log('List is not define in config, currently using all lists');
        }

        if (count($selectedList) <= 0) {
            throw new ListNotFoundException();
        }
        return new Lists($selectedList);
    }

    /**
     * load list by campaigns
     *
     * @param array $campaigns
     * @return Lists
     * @throws ListNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\InvalidKeyException
     * @throws \Mocean\Mailchimp\exceptions\MemberNotFoundException
     */
    public static function loadByCampaigns(array $campaigns)
    {
        error_log('Loading lists by campaigns');
        $selectedList = array();
        if ($configListName = Config::get('MAILCHIMP_LISTS_SEARCH_NAME')) {
            $searchNames = explode(',', $configListName);

            foreach ($searchNames as $searchName) {
                foreach ($campaigns as $campaign) {
                    if ($campaign->recipients->list_name && strtoupper($campaign->recipients->list_name) === strtoupper($searchName)) {
                        $selectedList[] = array('name' => $campaign->recipients->list_name, 'id' => $campaign->recipients->list_id);
                    }
                }
            }

            error_log('List is define in config, currently using list: (' . $configListName . ')');
        } else {
            foreach ($campaigns as $campaign) {
                if ($campaign->recipients->list_name) {
                    $selectedList[] = array('name' => $campaign->recipients->list_name, 'id' => $campaign->recipients->list_id);
                }
            }
            error_log('List is not define in config, currently using all lists');
        }

        $selectedList = array_unique($selectedList, SORT_REGULAR);

        if (count($selectedList) <= 0) {
            throw new ListNotFoundException();
        }
        return new Lists($selectedList);
    }
}