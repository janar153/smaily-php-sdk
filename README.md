Smaily PHP SDK
===============

This is Smaily PHP SDK to help you use Smaily API and integrate it into your system.

How to use it
--------------

First you need autoloader
```php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});
```

Setup some variable for Smaily PHP SDK to use
```
$username = "your_username";
$password = "your_password";
$domain = "yours_smaily_domain";
```

Now make varibale for Smaily PHP SDK
```
$smaily = new Smaily($username, $password, $domain);
```

Now just call whatever function you need!

Available functions
-------------------

- **$smaily->getSubscriberData($email)** - get subscriber data
- **$smaily->getSubscribersList($list, $offset = 0, $limit = 25000)** - get subscribers list
- **$smaily->getSubscribersHistory($email, $start_at, $end_at, $offset = 0, $limit = 25000)** - get subscribers history
- **$smaily->saveSubscriber($email, $is_unsubscribed = 0, $extra_fields = array())** - add or update subscriber data
- **$smaily->saveSubscribers($array)** - add or update multiple subscribers data at once
- **$smaily->getFilters()** - get user-made filters
- **$smaily->buildFilterSegmentsData($field, $condition, $value)** - build filter segmentation rules data
- **$smaily->getFilterSegementsData()** - filter segmentation rules built by segmentation rules helper function
- **$smaily->saveFilter($name, $filter_type, $filter_data, $id = null)** - add or update user-made filter
- **$smaily->getCampaigns($limit = 0, $page = 1, $status = null, $tags = null, $sort_by = "created_at", $sort_order = "ASC")** - get campaigns list
- **$smaily->getCampaignStatistics($campaign_id, $detailed = 1, $offset = 0, $limit = 10000)** - get campaign statistics
- **$smaily->saveCampaign($subject, $from, $from_name, $html, $text, $list, $due = null)** - launch a campaign
- **$smaily->removeFromCampaign($campaign_id, $email)** - unsubscribing subscriber from a specific campaign
- **$smaily->getAutoResponders($limit = 0, $page = 1, $status = null, $sort_by = "created_at", $sort_order = "ASC")** - get list of autoresponders
- **$smaily->saveAutoResponder($autoresponder_id, $addresses, $from, $from_name)** - save AutoResponder
- **$smaily->getSplitCampaignStatistics($campaign_id, $detailed = 1)** - get Statistics of an A/B split test campaign
- **$smaily->saveSplitCampaign($splits, $list, $size, $win_at, $condition = "clicks")** - launch an A/B split test campaign
