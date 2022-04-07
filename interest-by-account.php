<?php
    
    # This report will take your Interest Payee ID
    # transactions and show you month by month
    # what you paid/earned in interest
    # You need to use an Interest Payee for
    # This report to be meaningful

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "Interest By Account";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $newest_budget_date = get_recent_date($settings, $budgetID);
    
    $budget_year = date('Y', $newest_budget_date);
    $budget_month = date('m', $newest_budget_date);
    $budget_month--; 

    # Endpoint to grab all transactions for 'Interest Earned/Paid'
    $endpoint = "/$BUDGET_ID/payees/$INTEREST_PAYEE_ID/transactions";

    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $transactions = json_decode(curl_exec($ch), true);
    
    $totals = array();

    $oldest_trans_year = null;
    $oldest_trans_month = null;

    $newest_trans_year = null;
    $newest_trans_month = null;

    foreach ($transactions["data"]["transactions"] as $transaction) {

        $amount = $transaction["amount"] / 1000;
        $transaction_year = (int) explode("-", $transaction["date"])[0];
        $transaction_month = (int) explode("-", $transaction["date"])[1];
        $transaction_date = $transaction_year . "-" . $transaction_month;

        if ( $transaction_year == $budget_year) {

            if ( $transaction_month == $budget_month) {

                if (array_key_exists($transaction["account_name"], $totals)) {
                    
                    $totals[$transaction["account_name"]] += $amount;

                } else {

                    $totals[$transaction["account_name"]] = $amount;

                }

            }

        }

        $date_array = set_date($oldest_trans_year, $oldest_trans_month, $newest_trans_year, $newest_trans_month, $transaction_year, $transaction_month);

        $oldest_trans_year = ("$date_array[0]");
        $oldest_trans_month = ("$date_array[1]");
        $newest_trans_year = ("$date_array[2]");
        $newest_trans_month = ("$date_array[3]");

    }

    $oldest_trans_date = strtotime("$oldest_trans_year-$oldest_trans_month-01");
    $newest_trans_date = strtotime("$newest_trans_year-$newest_trans_month-01");

    print_totals($totals, $report_name, $oldest_trans_date, $newest_trans_date);
