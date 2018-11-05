<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/1/2018
 * Time: 4:02 PM
 */

namespace Mocean\Mailchimp\model;

use Mocean\Mailchimp\api\MailChimpApi;
use Mocean\Mailchimp\api\MoceanApi;
use Mocean\Mailchimp\config\Config;
use Mocean\Mailchimp\exceptions\MemberNotFoundException;
use stdClass;

class Members
{
    private $memberInfos;

    private function __construct(array $memberInfos)
    {
        $this->memberInfos = $memberInfos;
    }

    /**
     * get the members json response
     *
     * @return array
     */
    public function get()
    {
        return $this->memberInfos;
    }

    /**
     * send sms to all of the members
     *
     * @param null $text custom text which will override the text in setting
     * @return int $totalSmsSent total amount of sms sent
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\InvalidKeyException
     */
    public function broadcast($text = null)
    {
        if ($text === null) {
            $text = Config::get('MOCEAN_API_TEXT');
        }

        $datas = $this->removeLeadingPlusFromPhoneNumber($this->memberInfos);

        $tmpList = '';
        $tmpJoinedPhoneNumbers = '';
        $tmpSmsCount = 0;
        $totalSmsSent = 0;
        foreach ($datas as $data) {
            if (!$tmpList) {
                //init list
                $tmpSmsCount++;
                $tmpList = $data->IN_LIST;
                $tmpJoinedPhoneNumbers .= $data->PHONE;
            } else if ($tmpList !== $data->IN_LIST) {
                //send sms and refresh tmpList
                error_log('Sending SMS to ' . $tmpJoinedPhoneNumbers);
                $res = MoceanApi::sendSms(array(
                    'mocean-from' => Config::get('MOCEAN_API_SENDER_ID') ?: $tmpList,
                    'mocean-to' => $tmpJoinedPhoneNumbers,
                    'mocean-text' => $text
                ));
                if (!$res) {
                    error_log('Error while sending (' . json_encode(MoceanApi::$errMsg) . ')');
                } else {
                    $totalSmsSent += $tmpSmsCount;
                    $tmpSmsCount = 0;
                    error_log('Successfully sent, result: ' . json_encode($res));
                }
                $tmpSmsCount++;
                $tmpList = $data->IN_LIST;
                $tmpJoinedPhoneNumbers = $data->PHONE;
            } else {
                $tmpSmsCount++;
                $tmpJoinedPhoneNumbers .= ',' . $data->PHONE;
            }
        }

        //send sms for the last batch
        error_log('Sending SMS to ' . $tmpJoinedPhoneNumbers);
        $res = MoceanApi::sendSms(array(
            'mocean-from' => Config::get('MOCEAN_API_SENDER_ID') ?: $tmpList,
            'mocean-to' => $tmpJoinedPhoneNumbers,
            'mocean-text' => Config::get('MOCEAN_API_TEXT')
        ));
        if (!$res) {
            error_log('Error while sending (' . json_encode(MoceanApi::$errMsg) . ')');
        } else {
            $totalSmsSent += $tmpSmsCount;
            error_log('Successfully sent, result: ' . json_encode($res));
        }

        return $totalSmsSent;
    }

    private function extract($key)
    {
        $data = array();
        foreach ($this->memberInfos as $memberInfo) {
            $tmpObj = new stdClass();
            if (is_array($key)) {
                foreach ($key as $in) {
                    $tmpObj->$in = $memberInfo->$in;
                }
            } else {
                $tmpObj->$key = $memberInfo->$key;
            }
            $data[] = (array)$tmpObj;
        }

        return $data;
    }

    private function removeLeadingPlusFromPhoneNumber($datas)
    {
        foreach ($datas as $data) {
            foreach ($data as $key => $value) {
                if ($key === 'PHONE' && $value[0] === '+') {
                    $data->$key = substr($value, 1, strlen($value));
                }
            }
        }

        return $datas;
    }

    /**
     * load members by lists
     *
     * @param $lists
     * @return Members|bool
     * @throws MemberNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\InvalidKeyException
     */
    public static function load($lists)
    {
        error_log('Loading members by lists from mailchimp api');
        $memberInfos = array();

        foreach ($lists as $list) {
            $members = MailChimpApi::getListMembers($list['id']);
            if (!$members) {
                error_log('Error while loading members (' . MailChimpApi::$errMsg . ')');
                return false;
            }
            foreach ($members as $member) {
                if ($member->status === 'subscribed' && $member->merge_fields->PHONE) {
                    $member->merge_fields->EMAIL = $member->email_address;
                    $member->merge_fields->IN_LIST = $list['name'];
                    $memberInfos[] = $member->merge_fields;
                }
            }
        }

        if (!Config::get('ALLOW_DUPLICATE_MEMBER')) {
            $memberInfos = self::removeDuplicatePhone($memberInfos);
        }

        if (count($memberInfos) <= 0) {
            throw new MemberNotFoundException('these lists dont have any member');
        }
        return new Members($memberInfos);
    }

    private static function removeDuplicatePhone($memberInfos)
    {
        $uniqueMembers = array();
        $tmpNumbers = array();

        foreach ($memberInfos as $memberInfo) {
            if (array_key_exists($memberInfo->PHONE, $tmpNumbers)) continue;
            $tmpNumbers[$memberInfo->PHONE] = $memberInfo;
            $uniqueMembers[] = $memberInfo;
        }

        return $uniqueMembers;
    }
}