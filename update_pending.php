<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . '/Documents/ynab/ynab_functions.php');

    # Imports Name and Colors of each flag
    require ($base_dir . '/Documents/ynab_flags.php');
    $report_name = "Flagged Report";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_parsed_date = get_oldest_date($settings);
    $recent_parsed_date = get_recent_date($settings);

    # Endpoint to grab all transactions for 'Credit Card Cash Rewards'
    $endpoint = "/$BUDGET_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $color = "orange";
    $transaction_ids = array();
    $transactions_to_update = array();

    $transactions = json_decode(curl_exec($ch), true);

    foreach ($transactions["data"]["transactions"] as $transaction) {

        if ($transaction["flag_color"] === $color) {

                array_push($transaction_ids, $transaction["id"]);
                array_push($transactions_to_update, $transaction);

            }

    }

    foreach ($transactions_to_update as &$value) {

        $value["date"] = date("Y-m-d");
        #$data_json = "{\"transaction\": " . json_encode($value);
#var_dump($data_json);exit;
        $data_json = "{ \"transaction\": {" .
            "\"id\":\"" . $value['id'] . "\"," .
            "\"date\":\"" . $value['date'] . "\"" . 
            "}";
        
        $ch_put = set_curl_put($data_json, $YNAB_TOKEN, $base . $endpoint . "/" . $value["id"]);
        $response = put_curl($ch_put);

    }