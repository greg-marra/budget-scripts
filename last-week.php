<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "Last Week's Transactions";

    $pad = 18;
    $sub = 12;

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

    $total = 0;


    foreach ($transactions["data"]["transactions"] as $transaction) {

        $amount = $transaction["amount"] / 1000;
        $transaction_year = (int) explode("-", $transaction["date"])[0];
        $transaction_month = (int) explode("-", $transaction["date"])[1];
        $transaction_day = (int) explode("-", $transaction["date"])[2];

        $transaction_date = strtotime($transaction['date']);

        $transaction["memo"] === null ? $transaction["memo"] = "" : $transaction["memo"] = $transaction["memo"] ;
        
        if ( 

            $transaction_date >= $day7 && 
            $transaction["amount"] < 0 &&
            $transaction["transfer_account_id"] === null && 
            in_array($transaction["account_id"], $last_week_accounts) &&
            !in_array($transaction["category_id"], $last_week_categories) &&
            !in_array($transaction["flag_color"], $last_week_flags) &&
            $transaction['transfer_account_id'] === null

        ) {

            echo date('D', $transaction_date) . " - $" . 
            str_pad(substr(-$amount, 0, 7), 7) . 
            " -\t" . str_pad(substr($transaction["payee_name"], 0, $sub), $pad) . 
            " -\t" . str_pad(substr($transaction["category_name"], 0, $sub), $pad) . 
            " -\t" . str_pad(substr($transaction["memo"], 0, $sub), $pad) . 
            "\n";

            $total += -$amount;

            $csv_string = NULL;
            $csv_string = 
                date("m/d/Y", $transaction_date) . "," .
                -$amount . "," . 
                $transaction["payee_name"] . "," . 
                $transaction["category_name"] . "," .
                $transaction["memo"] . "," .
                "\n";

            #echo $csv_string;

/*
            file_put_contents(
                "$base_dir/Desktop/last_week.csv",
                $csv_string,
                FILE_APPEND | LOCK_EX);
 */

        } 

    }

    echo "\n" . "Total: $" . str_pad(substr($total, 0, 7), 7) . "\n";
