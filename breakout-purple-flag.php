<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "Purple Report";
    $flagged_color = "purple";

    $AMOUNT_LENGTH = 6;
    $PAYEE_LENGTH = 15;
    $MEMO_LENGTH = 25;

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $newest_budget_date = get_recent_date($settings, $budgetID);

    # Endpoint to grab all transactions for 'Credit Card Cash Rewards'
    $endpoint = "/$BUDGET_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $transactions = json_decode(curl_exec($ch), true);

    $yearly_totals = array();

    $oldest_trans_year = null;
    $oldest_trans_month = null;

    $newest_trans_year = null;
    $newest_trans_month = null;

    foreach ($transactions["data"]["transactions"] as $transaction) {

        if ($transaction["flag_color"] == $flagged_color) {

            $transaction_year = (int) explode("-", $transaction["date"])[0];
            $transaction_month = (int) explode("-", $transaction["date"])[1];

            $amount = budget_format(-$transaction["amount"] / 1000);
            $amount = substr($amount, 0, $AMOUNT_LENGTH);
            $amount = (strlen($amount) < $AMOUNT_LENGTH ) ? str_pad($amount, $AMOUNT_LENGTH, " ", STR_PAD_RIGHT) : $amount ;

            $payee_name = substr($transaction["payee_name"], 0, $PAYEE_LENGTH);
            $payee_name = (strlen($payee_name) < $PAYEE_LENGTH ) ? str_pad($payee_name, $PAYEE_LENGTH, " ", STR_PAD_RIGHT) : $payee_name ;

            $transaction_date = strtotime($transaction['date']);

            $memo = $transaction["memo"];

            if ( $memo == null) {

                $memo = "";

            }

            $memo = substr($memo, 0, $MEMO_LENGTH);
            $memo = (strlen($memo) < $MEMO_LENGTH) ? str_pad($memo, $MEMO_LENGTH, " ", STR_PAD_RIGHT) : $memo ;

            echo date('Y/m/d', $transaction_date) . " - " .
            "$" . $amount ." - " .
            $payee_name . " - " .
            $memo .
            "\n";

            $transaction_year= explode("-", $transaction["date"])[0];

            if (array_key_exists($transaction_year, $yearly_totals)) {
                
                $yearly_totals[$transaction_year] += $amount;

            } else {

                $yearly_totals[$transaction_year] = $amount;

            }

            $date_array = set_date($oldest_trans_year, $oldest_trans_month, $newest_trans_year, $newest_trans_month, $transaction_year, $transaction_month);

            $oldest_trans_year = ("$date_array[0]");
            $oldest_trans_month = ("$date_array[1]");
            $newest_trans_year = ("$date_array[2]");
            $newest_trans_month = ("$date_array[3]");
    
        }

    }

    $oldest_trans_date = strtotime("$oldest_trans_year-$oldest_trans_month-01");
    $newest_trans_date = strtotime("$newest_trans_year-$newest_trans_month-01");

    echo "\n\n";

    print_totals($yearly_totals, $report_name, $oldest_trans_date, $newest_trans_date);
