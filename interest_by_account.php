<?php
    
    # This report will take your Interest Payee ID
    # transactions and show you month by month
    # what you paid/earned in interest
    # You need to use an Interest Payee for
    # This report to be meaningful

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . $functions_directory);
    $report_name = "Interest By Account";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_parsed_date = get_oldest_date($settings);
    $recent_parsed_date = get_recent_date($settings);
    
    $ynab_year = date('Y', $recent_parsed_date);
    $ynab_month = date('m', $recent_parsed_date);
    $ynab_month--; 

    # Endpoint to grab all transactions for 'Interest Earned/Paid'
    $endpoint = "/$BUDGET_ID/payees/$INTEREST_PAYEE_ID/transactions";

    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $transactions = json_decode(curl_exec($ch), true);
    

    $totals = array();

    foreach ($transactions["data"]["transactions"] as $transaction) {

        $amount = $transaction["amount"] / 1000;
        $transaction_year = explode("-", $transaction["date"])[0];
        $transaction_month = explode("-", $transaction["date"])[1];
        $transaction_date = $transaction_year . "-" . $transaction_month;

        if ( $transaction_year == $ynab_year) {

            if ( $transaction_month == $ynab_month) {

                if (array_key_exists($transaction["account_name"], $totals)) {
                    
                    $totals[$transaction["account_name"]] += $amount;

                } else {

                    $totals[$transaction["account_name"]] = $amount;

                }

            }

        }

    }

    #var_dump($totals);

    print_totals($totals, $report_name, $oldest_parsed_date, $recent_parsed_date);
