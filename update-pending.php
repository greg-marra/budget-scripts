<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $newest_budget_date = get_recent_date($settings, $budgetID);

    # Endpoint to grab all transactions for 'Credit Card Cash Rewards'
    $endpoint = "/$BUDGET_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    # Pending flag is orange
    $color = "orange";
    $transaction_ids = array();
    $transactions_to_update = array();
    $count = 0;

    $transactions = json_decode(curl_exec($ch), true);
    curl_close($ch);

    foreach ($transactions["data"]["transactions"] as $transaction) {

        if ($transaction["flag_color"] === $color) {

                $count++;
                array_push($transaction_ids, $transaction["id"]);
                $wrapped = array("transaction" => $transaction);
                array_push($transactions_to_update, $wrapped);

            }

    }

    foreach ($transactions_to_update as &$value) {

        $value["transaction"]["date"] = date("Y-m-d");
        $data_json = json_encode($value);
    
        $ch_put = set_curl_put($data_json, $budget_TOKEN, $base . $endpoint . "/" . $value["transaction"]["id"]);
        $response = put_curl($ch_put);
        curl_close($ch_put);

    }

    echo "Transactions updated: $count\n\n";
