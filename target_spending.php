<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . $functions_directory);
    $report_name = "Target Report";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_ynab_date = get_oldest_date($settings);
    $newest_ynab_date = get_recent_date($settings);

    # Endpoint to grab all transactions for 'Credit Card Cash Rewards'
    $endpoint = "/$BUDGET_ID/payees/$TARGET_PAYEE_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $transactions = json_decode(curl_exec($ch), true);

    $yearly_totals = array();
    $subtotal = 0;
    $rewards = 0;

    $oldest_trans_year = null;
    $oldest_trans_month = null;

    $newest_trans_year = null;
    $newest_trans_month = null;

    foreach ($transactions["data"]["transactions"] as $transaction) {

        $amount = $transaction["amount"] / 1000;

        $transaction_year = (int) explode("-", $transaction["date"])[0];
        $transaction_month = (int) explode("-", $transaction["date"])[1];
    
        # Spits out date and transaction amounts for logs
        #echo $transaction["date"] . ": " . $amount . "\n";

        if (array_key_exists($transaction_year, $yearly_totals)) {
            
            $yearly_totals[$transaction_year] += $amount;

        } else {

            $yearly_totals[$transaction_year] = $amount;

        }

        $remove_taxes = $amount / 1.07;
        $rewards += ($remove_taxes * .05);

        $date_array = set_date($oldest_trans_year, $oldest_trans_month, $newest_trans_year, $newest_trans_month, $transaction_year, $transaction_month);

        $oldest_trans_year = ("$date_array[0]");
        $oldest_trans_month = ("$date_array[1]");
        $newest_trans_year = ("$date_array[2]");
        $newest_trans_month = ("$date_array[3]");

    }

    $oldest_trans_date = strtotime("$oldest_trans_year-$oldest_trans_month-01");
    $newest_trans_date = strtotime("$newest_trans_year-$newest_trans_month-01");

    print_totals($yearly_totals, $report_name, $oldest_trans_date, $newest_trans_date);