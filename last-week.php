<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "Last Week's Transactions";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $recent_budget_date = get_recent_date($settings, $budgetID);

    # Date 7 days ago
    $seven_day = new DateTime('7 days ago');
    $day7 = date_format($seven_day, 'Y-m-d');
    $day7 = strtotime($day7);
#    $day0 = new DateTime('today');

    # Endpoint to grab all transactions
    $endpoint = "/$BUDGET_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $transactions = json_decode(curl_exec($ch), true);

    foreach ($transactions["data"]["transactions"] as $transaction) {

        $amount = $transaction["amount"] / 1000;
        $transaction_year = (int) explode("-", $transaction["date"])[0];
        $transaction_month = (int) explode("-", $transaction["date"])[1];
        $transaction_day = (int) explode("-", $transaction["date"])[2];

        $transaction_date = strtotime($transaction['date']);

        if ( 

            $transaction_date >= $day7 && 
            $amount < 0 &&
            $transaction["transfer_account_id"] == null && 
            in_array($transaction["account_id"], $lastweek) &&
            !in_array($transaction["flag_color"], $lastweekflags)

        ) {

            echo date('D', $transaction_date) . " - $" . 
            str_pad(substr(-$amount, 0, 7), 7) . 
            " -\t" . str_pad(substr($transaction["payee_name"], 0, 20), 20) . 
            " -\t" . str_pad(substr($transaction["category_name"], 0, 20), 20) . 
            " -\t" . str_pad(substr($transaction["memo"], 0, 20), 20) . 
            "\n";
        
        } 

    }