<?php

/**
 * Smaily PH SDK
 *
 * Helper class for Smaily e-mail marketing software
 *
 * PHP version 7.1
 *
 * @author      Janar Nagel
 * @copyright   2017 Janar Nagel
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License v.3
 *
 */

use Smaily\SmailyRequestHandler as RequestHandler;

class Smaily {
    protected $username;
    protected $password;
    protected $domain;
    protected $protocol = 'https';
    protected $tld = 'net';
    protected $url;

    private $filterData = array();

    protected $contactHandler;
    protected $requestHandler;


    /**
     * Smaily constructor.
     */
    public function __construct($username, $password, $domain) {
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setDomain($domain);

        $this->connect();

        $endpoint = $this->getSmailyURL();

        $this->requestHandler = new RequestHandler($endpoint, $username, $password);

    }

    /**
     * Function to get subscriber data
     *
     * @param $email    Email address. Must be exact (wildcard does not work). Required.
     * @return mixed
     */
    public function getSubscriberData($email) {
        $requestBody = new stdClass();
        $requestBody->url = "contact.php";
        $requestBody->query = array("email" => $email);

        $response = $this->requestHandler->makeGetRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to get subscribers list
     *
     * @param $list         ID of the user-made filter. Listing IDs can be done with “List user-made filters”. Required.
     * @param int $offset   So-called “page” number, which begins at 0. 0 gives contacts from 0 to 25000. 1 gives 25001 to 50000 etc. If not specified, 0 will be set.
     * @param int $limit    Number of subscribers on one “page”. If not specified, 25000 will be set, which is also the maximum value. If set as more than 25000, no error will be given, but the value will be defaulted back to 25000.
     * @return mixed
     */
    public function getSubscribersList($list, $offset = 0, $limit = 25000) {
        $requestBody = new stdClass();
        $requestBody->url = "contact.php";
        $requestBody->query = array("list" => $list, "offset" => $offset, "limit" => $limit);

        $response = $this->requestHandler->makeGetRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to get subscribers history
     *
     * @param $start_at     History range start date. Required. Value has to be in UNIX timestamp.
     * @param $end_at       History range end date. Required. Value has to be in UNIX timestamp, must not be less than start_at value.
     * @param int $offset   So-called “page” number, which begins at 0. 0 gives contacts from 0 to 10000. 1 gives 10001 to 20000 etc. If not specified, 0 will be set.
     * @param int $limit    Number of subscribers on one “page”. If not specified, no limit will be applied. Value cap is set at 10000. If a larger value is specified, no error will be given.
     * @return mixed
     */
    public function getSubscribersHistory($email, $start_at, $end_at, $offset = 0, $limit = 25000) {
        $requestBody = new stdClass();
        $requestBody->url = "contact.php";
        $requestBody->query = array("email" => $email, "start_at" => $start_at, "end_at" => $end_at, "offset" => $offset, "limit" => $limit);

        $response = $this->requestHandler->makeGetRequest($requestBody->url, $requestBody->query);

        return $response;
    }



    /**
     * Function to add or update subscriber data
     *
     * @param string    $email              E-mail address. Must be exact (wildcard does not work). Required.
     * @param int       $is_unsubscribed    Has the email address been set as unsubscribed? 0 - no, emails will be delivered; 1 - yes, email address will be excluded from all campaigns.
     * @param array     $extra_fields       Custom fields. New fields will be automatically created and attached with subscriber data. Array key must me field name and array value as string
     * @return mixed
     */
    public function saveSubscriber($email, $is_unsubscribed = 0, $extra_fields = array()) {
        $query = array(
            'email' => $email,
            'is_unsubscribed' => $is_unsubscribed
        );

        if(!empty($extra_fields)) {
            $query = array_merge($query, $extra_fields);
        }

        $requestBody = new stdClass();
        $requestBody->url = "contact.php";
        $requestBody->query = $query;

        $response = $this->requestHandler->makePostRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to add or update multiple subscribers data at once
     *
     * @param $array    Array of subscribers data. Look more info from saveSubscriber function
     * @return mixed
     */
    public function saveSubscribers($array) {
        $requestBody = new stdClass();
        $requestBody->url = "contact.php";
        $requestBody->query = $array;

        $response = $this->requestHandler->makePostRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to get user-made filters
     *
     * @return mixed
     */
    public function getFilters() {
        $requestBody = new stdClass();
        $requestBody->url = "list.php";
        $requestBody->query = array();

        $response = $this->requestHandler->makeGetRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Helper function to build filter segmentation rules data
     *
     * @param $field        Field name, must exists in subscriber data
     * @param $condition    Condition. Available options: Equal, NotEqual, BeginsWith, Contains, DoesNotContain, EndsWith, LessThan, LessThanEqual, GreaterThanEqual, GreaterThan
     * @param $value        Condition value
     * @return void
     * @throws \Exception
     */
    public function buildFilterSegmentsData($field, $condition, $value) {
        $conditions = array('Equal', 'NotEqual', 'BeginsWith', 'Contains', 'DoesNotContain', 'EndsWith', 'LessThan', 'LessThanEqual', 'GreaterThanEqual', 'GreaterThan');
        if(!in_array($condition, $conditions)) {
            throw new Exception("Wrong condition type");
        }

        $this->filterData[] = array($field, array($condition, $value));
    }

    /**
     * Function to get filter segmentation rules built by segmentation rules helper function
     *
     * @return array
     */
    public function getFilterSegementsData() {
        return $this->filterData;
    }

    /**
     * Function to add or update user-made filter
     *
     * @param $name             Name of filter. Required.
     * @param $filter_type      Parameter to specify the inclusion of conditions. Possible values: ALL - subscriber data must conform to all the segmentation rules, specified with filter_data and ANY - subscriber data must conform with at least one of the specified segmentation rules. Required.
     * @param $filter_data      Segmentation rules. Required. For simpler use please use buildFilterSegmentsData function build filter data segmentation and getFilterSegementsData function to get segmented filter data
     * @param null|int $id      ID of the filter, if editing.
     * @return mixed
     * @throws \Exception
     */
    public function saveFilter($name, $filter_type, $filter_data, $id = null) {
        $filter_types = array("ALL", "ANY");
        if(!in_array($filter_type, $filter_types)) {
            throw new Exception("Wrong filter type");
        }

        $filter = array(
            'name' => $name,
            'filter_type' => $filter_type,
            'filter_data' => $filter_data,
        );

        if(!empty($id)) {
            $filter["id"] = $id;
        }

        $requestBody = new stdClass();
        $requestBody->url = "list.php";
        $requestBody->query = $filter;

        $response = $this->requestHandler->makePostRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to get campaigns list
     *
     * @param int $limit            Limit number of records to return per request. If this parameter has not been provided or has value of 0, then limit is not applied and all records are returned.
     * @param int $page             Defines record set offset. Applies only if parameter limit has “truthy” value. Default value is 1.
     * @param string|array $status  Filter records by status. Possible values are - DRAFT, PENDING, COMPLETED. To apply multiple status filters include status parameter in URI as status[]=DRAFT&status[]=PENDING.
     * @param string|array $tags    Filter records by tags. To apply multiple tag filters include tags parameter in URI as tags[]=Tag1&tags[]=Tag2.
     * @param string $sort_by       Field to sort loaded entries by. At the moment only created_at value is allowed, that will also be applied by default.
     * @param string $sort_order    Entries sorting direction. Possible values are: ASC -ascending order; DESC - descending order. By default entries will be sorted in ascending order.
     * @return mixed
     */
    public function getCampaigns($limit = 0, $page = 1, $status = null, $tags = null, $sort_by = "created_at", $sort_order = "ASC") {
        $query = array();
        $query["limit"] = $limit;
        $query["page"] = $page;
        $query["status"] = $status;
        $query["tags"] = $tags;
        $query["sort_by"] = $sort_by;
        $query["sort_order"] = ($sort_order == "ASC") ? "ASC" : "DESC";

        $requestBody = new stdClass();
        $requestBody->url = "campaign.php";
        $requestBody->query = $query;

        $response = $this->requestHandler->makeGetRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to get campaign statistics
     *
     * @param int $campaign_id  Unique ID of campaign, which is returned on launch. Can be found in Campaing list. Required.
     * @param int $detailed     Include detailed opens/clicks statistics per contact with response. Possible values: 0 - not included; 1 - detailed statistics is included.
     * @param int $offset       So-called “page” number, which begins at 0. 0 gives contacts from 0 to 10000. 1 gives 10001 to 20000 etc. If not specified, 0 will be set.
     * @param int $limit        Number of subscribers on one “page”. If not specified, 10000 will be set, which is also the maximum value. If set as more than 10000, no error will be given, but the value will be defaulted back to 10000.
     *
     * @return mixed
     */
    public function getCampaignStatistics($campaign_id, $detailed = 1, $offset = 0, $limit = 10000) {
        $query = array();
        $query["id"] = $campaign_id;
        $query["detailed"] = $detailed;
        $query["offset"] = $offset;
        $query["limit"] = $limit;

        $requestBody = new stdClass();
        $requestBody->url = "campaign.php";
        $requestBody->query = $query;

        $response = $this->requestHandler->makeGetRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to Launch a campaign
     *
     * @param string $subject       Email subject. Required. Text field.
     * @param string $from          Sender From address. Required. Must be added first in Smaily > Account profile.
     * @param string $from_name     Sender Name. Not required. If not set, default Name will be used from Smaily > Account profile.
     * @param string $html          Link to HTML content, which needs to be in a publicly accessible URL. Required.
     * @param string $text          Link to plain-text content, which needs to be in a publicly accessible URL. Required.
     * @param array $list           List or lists of subscribers the campaign is being sent to. IDs of filters in Smaily > Subscribers. Required. See also “List user-made filters”.
     * @param datetime $due         Due date of the campaign. Value must be formatted as date-time (“YYYY-MM-DD HH:MM:SS”). If no value is set, “now” will be used with a 5-minute “grace” period, if the campaign still needs to be cancelled, which can be done in Smaily > Campaigns.
     *
     * @return mixed
     * @throws \Exception
     */
    public function saveCampaign($subject, $from, $from_name, $html, $text, $list, $due = null) {
        if(!is_array($list)) {
            throw new Exception("List must be as array");
        }

        $filter = array(
            'subject' => $subject,
            'from' => $from,
            'from_name' => $from_name,
            'html' => $html,
            'text' => $text,
            'list' => $list,
            'due' => $due,
        );

        $requestBody = new stdClass();
        $requestBody->url = "campaign.php";
        $requestBody->query = $filter;

        $response = $this->requestHandler->makePostRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to unsubscribing subscriber from a specific campaign
     *
     * @param int $campaign_id      Unique identifier of the campaign, that is returned on campaign launch request or can be found from the results of campaigns list request. Required.
     * @param string $email         Subscriber’s email address. Required.
     *
     * @return mixed
     */
    public function removeFromCampaign($campaign_id, $email) {
        $filter = array(
            'campaign_id' => $campaign_id,
            'email' => $email,
        );

        $requestBody = new stdClass();
        $requestBody->url = "unsubscribe.php";
        $requestBody->query = $filter;

        $response = $this->requestHandler->makePostRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to get list of autoresponders
     *
     * @param int $limit            Limit number of records to return per request. If this parameter has not been provided or has value of 0, then limit is not applied and all records are returned.
     * @param int $page             Defines record set offset. Applies only if parameter limit has “truthy” value. Default value is 1.
     * @param null $status          Filter records by status. Possible values are - INCATIVE, ACTIVE. To apply multiple status filters include status parameter in URI as status[]=INCATIVE&status[]=ACTIVE.
     * @param string $sort_by       Field to sort loaded entries by. At the moment only created_at value is allowed, that will also be applied by default.
     * @param string $sort_order    Entries sorting direction. Possible values are: ASC - ascending order; DESC - descending order. By default entries will be sorted in ascending order.
     *
     * @return mixed
     */
    public function getAutoResponders($limit = 0, $page = 1, $status = null, $sort_by = "created_at", $sort_order = "ASC") {
        $query = array();
        $query["limit"] = $limit;
        $query["page"] = $page;
        $query["status"] = $status;
        $query["sort_by"] = $sort_by;
        $query["sort_order"] = ($sort_order == "ASC") ? "ASC" : "DESC";

        $requestBody = new stdClass();
        $requestBody->url = "autoresponder.php";
        $requestBody->query = $query;

        $response = $this->requestHandler->makeGetRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to save AutoResponder
     *
     * @param int $autoresponder_id Autoresponder ID. Required.
     * @param array $addresses      Array of email addresses the autoresponder is being sent to. Required. See the structure of array below under “Parameter structure of addresses”.
     * @param $from                 Override autoresponder's default From address for contacts in addresses array.
     * @param $from_name            Override autoresponder's default From name for contacts in addresses array.
     *
     *   Parameter structure of addresses
     *      array('email' => 'email@address.com')
     *
     * @return bool
     * @throws \Exception
     */
    public function saveAutoResponder($autoresponder_id, $addresses, $from, $from_name) {
        if(!is_array($addresses)) {
            throw new Exception("Addresses must be as array");
        }

        $isAddressesValid = $this->validateAutoResponderAddresses($addresses);
        if(!$isAddressesValid) {
            throw new Exception("Addresses array is not valid");
        }

        $filter = array(
            'autoresponder' => $autoresponder_id,
            'addresses' => $addresses,
            'from' => $from,
            'from_name' => $from_name,
        );

        $requestBody = new stdClass();
        $requestBody->url = "autoresponder.php";
        $requestBody->query = $filter;

        $response = $this->requestHandler->makePostRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to validate addresses array
     *
     * @param array $addresses  Array of email addresses the autoresponder is being sent to.
     *   Addresses array must contain array with these fields:
     *   - email        Email address. Required.
     *   - ...          Custom fields. New fields are added automatically to the subscriber. Text.
     *
     * @return bool
     */
    private function validateAutoResponderAddresses($addresses) {
        $isValid = true;
        if(!empty($addresses)) {
            foreach($addresses as $address) {
                if(!array_key_exists('email', $address)) {
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }

    /**
     * Function to get Statistics of an A/B split test campaign
     *
     * @param int $campaign_id  Unique ID of the A/B split test campaign, which will be given with the response when starting the campaign. Required.
     * @param int $detailed     Include detailed opens/clicks statistics per contact with response. Possible values: 0 - not included; 1 - detailed statistics is included.
     *
     * @return mixed
     */
    public function getSplitCampaignStatistics($campaign_id, $detailed = 1) {
        $query = array();
        $query["id"] = $campaign_id;
        $query["detailed"] = $detailed;

        $requestBody = new stdClass();
        $requestBody->url = "split.php";
        $requestBody->query = $query;

        $response = $this->requestHandler->makeGetRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to Launch an A/B split test campaign
     *
     * @param array $splits         An array of campaigns, which will be part of the A/B split test. Required. See also “validateSplits” function.
     * @param array $list           List or lists of subscribers the campaign is being sent to. IDs of filters in Smaily > Subscribers. Required. See also List user-made filters.
     * @param int $size             A percentage taken from unique subscribers, contained in lists obtained from the parameter list, which is divided equally between all the splits specified in the array of parameter splits. See an example calculation at “Finding the number of subscribers”. Required.
     * @param datetime $win_at      Due date to send out a campaign to the rest of the list with the best-performing split options. Value must be formatted as date-time (“YYYY-MM-DD HH:MM:SS”). If no value is set, wrong formatting is used of the specified date-time is in the past, query will be stopped. Required.
     * @param string $condition     Winning condition, which will be used to select the best-performing split. Possible values are openrate, clicks, views, unsubscribed. Default is clicks.
     *
     * @return mixed
     * @throws \Exception
     */
    public function saveSplitCampaign($splits, $list, $size, $win_at, $condition = "clicks") {
        if(!is_array($splits)) {
            throw new Exception("Splits must be as array");
        }

        if(!is_array($list)) {
            throw new Exception("List must be as array");
        }

        $isSplitsValid = $this->validateSplits($splits);
        if(!$isSplitsValid) {
            throw new Exception("Splits are not valid");
        }

        $validConditions = array("openrate", "clicks", "views", "unsubscribed");
        if(!in_array($condition, $validConditions)) {
            throw new Exception("Not valid condition");
        }

        $filter = array(
            'splits' => $splits,
            'list' => $list,
            'size' => $size,
            'condition' => $condition,
            'win_at' => $win_at,
        );

        $requestBody = new stdClass();
        $requestBody->url = "split.php";
        $requestBody->query = $filter;

        $response = $this->requestHandler->makePostRequest($requestBody->url, $requestBody->query);

        return $response;
    }

    /**
     * Function to validate splits
     *
     * @param array $splits     An array of campaigns, which will be part of the A/B split test. Required.
     *   Every split must be as array and contain these elements:
     *   - subject      Email subject. Required. Text field.
     *   - html         Link to HTML content, which needs to be in a publicly accessible URL. Required.
     *   - text         Link to plain-text content, which needs to be in a publicly accessible URL. Required.
     *   - from         Sender From address. Required. Must be added first in Smaily > Account profile.
     *   - from_name    Sender Name. Not required. If not set, default Name will be used from Smaily > Account profile.
     *   - due          Due date of the campaign split. Value must be formatted as date-time (“YYYY-MM-DD HH:MM:SS”). If no value is set, “now” will be used with a 5-minute “grace” period, if the campaign still needs to be cancelled, which can be done in Smaily > Campaigns. Due date must not be later than parameter win_at!
     *
     * @return bool
     */
    private function validateSplits($splits) {
        $requiredFields = array("subject", "html", "text", "from");
        $isValid = true;
        if(!empty($splits)) {
            foreach($splits as $split) {
                foreach($requiredFields as $requiredField) {
                    if(!array_key_exists($requiredField, $split)) {
                        $isValid = false;
                    }
                }
            }
        }

        return $isValid;
    }

    /**
     * Function to check all data necessary to connect Smaily API
     *
     * @throws \Exception
     * @return void
     */
    private function connect() {
        $username = $this->getUsername();
        if(empty($username)) {
            throw new \Exception("Missing username", 201);
        }

        $password = $this->getPassword();
        if(empty($password)) {
            throw new \Exception("Missing password", 201);
        }

        $domain = $this->getDomain();
        if(empty($domain)) {
            throw new \Exception("Missing domain", 201);
        }

        $protocol = $this->getProtocol();
        if(empty($protocol)) {
            throw new \Exception("Missing protocol", 201);
        }

        $tld = $this->getTLD();
        if(empty($tld)) {
            throw new \Exception("Missing TLD", 201);
        }

        $this->url = $protocol . '://' . $domain . '.sendsmaily.' . $tld . '/api/';
    }


    /**
     * Function to set username
     *
     * @param $string
     */
    private function setUsername($string) {
        $this->username = $string;
    }

    /**
     * Function to get username
     *
     * @return mixed
     */
    private function getUsername() {
        return $this->username;
    }

    /**
     * Function to set password
     *
     * @param $password
     */
    private function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Function to get password
     *
     * @return mixed
     */
    private function getPassword() {
        return $this->password;
    }

    /**
     * Function to set domain
     *
     * @param $domain
     */
    private function setDomain($domain) {
        $this->domain = $domain;
    }

    /**
     * Function to get domain
     *
     * @return mixed
     */
    private function getDomain() {
        return $this->domain;
    }

    /**
     * Function to set protocol
     *
     * @param $protocol
     */
    private function setProtocol($protocol) {
        $this->protocol = $protocol;
    }

    /**
     * Function to get protocol
     *
     * @return string
     */
    private function getProtocol() {
        return $this->protocol;
    }

    /**
     * Function to set tld
     *
     * @param $tld
     */
    private function setTLD($tld) {
        $this->tld = $tld;
    }

    /**
     * Function to get tld
     *
     * @return string
     */
    private function getTLD() {
        return $this->tld;
    }

    /**
     * Function to get Smaily API base URL
     *
     * @return mixed
     */
    private function getSmailyURL() {
        return $this->url;
    }

}

