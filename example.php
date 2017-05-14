<?php

spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

$smaily = new Smaily("frukt", "dux2k6thuMecRacH", "frukt");

$page = filter_input(INPUT_GET, "page", FILTER_SANITIZE_STRING);
$email = "test@test.com";

echo "<b>GET request:</b> ";
echo "<a href='?page=contact'>Subscriber Data</a> | ";
echo "<a href='?page=contacts'>Subscribers list</a> | ";
echo "<a href='?page=history'>Subscriber history</a> | ";
echo "<a href='?page=filters'>User filters</a> | ";
echo "<a href='?page=campaigns'>Campaigns</a> | ";
echo "<a href='?page=campaign_stats'>Campaign Stats</a> | ";
echo "<a href='?page=autoresponders'>Autoresponders</a> | ";
echo "<a href='?page=split_campaign_statistics'>Split Campaign Statistics</a> | ";

echo "<br>";

echo "<b>POST request:</b> ";
echo "<a href='?page=add_contact'>Add subscriber</a> | ";
echo "<a href='?page=add_contacts'>Add subscribers</a> | ";
echo "<a href='?page=add_filter'>Add filter</a> | ";
echo "<hr>";

switch ($page) {
    case "add_contact":
        echo "<b>Add subscriber</b><br>";
        $contactData = $smaily->saveSubscriber("janar153@gmail.com", 0, array("name" => "Janar Nagel"));
        var_dump($contactData);

        break;
    case "add_contacts":
        echo "<b>Add subscribers</b><br>";
        $query = array(array('email' => 'janar153@gmail.com', 'is_unsubscribed' => 0, 'name' => 'Nagel Janar'));
        $contactData = $smaily->saveSubscribers($query);
        var_dump($contactData);

        break;
    case "add_filter":
        $smaily->buildFilterSegmentsData("email", "BeginsWith", "info@");
        $filter_data = $smaily->getFilterSegementsData();
        var_dump($filter_data);
        $filterResponse = $smaily->saveFilter("Janari test filter", "ANY", $filter_data);
        var_dump($filterResponse);

        break;
    case "contact":
        echo "<b>Subscriber Data</b><br>";
        $contactData = $smaily->getSubscriberData($email);
        var_dump($contactData);

        break;
    case "contacts":
        echo "<b>Subscribers list</b><br>";
        $listData = $smaily->getSubscribersList(35);
        var_dump($listData);

        break;
    case "history":
        echo "<b>Subscriber history</b><br>";
        $start_date = new DateTime("2017-05-01");
        $end_date = new DateTime();
        $historyData = $smaily->getSubscribersHistory($email, $start_date->getTimestamp(), $end_date->getTimestamp());
        var_dump($historyData);

        break;
    case "filters":
        echo "<b>User filters list</b><br>";
        $listData = $smaily->getFilters();
        var_dump($listData);

        break;
    case "campaigns":
        echo "<b>Campaigns list</b><br>";
        $listData = $smaily->getCampaigns();
        var_dump($listData);

        break;

    case "campaign_stats":
        echo "<b>Campaign stats</b><br>";
        $listData = $smaily->getCampaignStatistics(4);

        var_dump($listData);

        break;
    case "autoresponders":
        echo "<b>Autoresponders list</b><br>";
        $listData = $smaily->getAutoResponders();
        var_dump($listData);

        break;
    case "split_campaign_statistics":
        echo "<b>Split Campaign Statistics</b><br>";
        $listData = $smaily->getSplitCampaignStatistics(8);
        var_dump($listData);

        break;

}

//37

