<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "Shed Report";
    $flagged_color = "purple";

    $AMOUNT_LENGTH = 6;
    $PAYEE_LENGTH = 15;
    $MEMO_LENGTH = 25;

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$BUDGET_ID);
    $oldest_budget_date = get_oldest_date($settings, $BUDGET_ID);
    $newest_budget_date = get_recent_date($settings, $BUDGET_ID);

    # Endpoint to grab all transactions
    $endpoint = "/$BUDGET_ID/transactions";
    if (!empty($oldest_budget_date)) {
// If it's a numeric timestamp, convert it to YYYY-MM-DD
        if (is_numeric($oldest_budget_date)) {
            $oldest_budget_date = date('Y-m-d', $oldest_budget_date);
        }
        $endpoint .= "?since_date=" . urlencode($oldest_budget_date);
    }

    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $response = curl_exec($ch);
    $transactions = json_decode($response, true);

    # --- SAFETY CHECK ---
    # If the API returns an error or invalid structure, catch it here instead of crashing
    if (!isset($transactions["data"]["transactions"])) {
        echo "⚠️ API Request Failed!\n";
        echo "Endpoint used: " . $endpoint . "\n";
        echo "Response Data:\n";
        print_r($transactions ? $transactions : $response);
        exit;
    }

    $yearly_totals = array();

    $oldest_trans_year = null;
    $oldest_trans_month = null;

    $newest_trans_year = null;
    $newest_trans_month = null;

    foreach ($transactions["data"]["transactions"] as $transaction) {

        if ($transaction["flag_color"] == $flagged_color) {

            $transaction_year = (int) explode("-", $transaction["date"])[0];
            $transaction_month = (int) explode("-", $transaction["date"])[1];

            // 1. Keep a clean floating point number for math totals
            $raw_amount = -$transaction["amount"] / 1000;

            // 2. Keep the padded/truncated string strictly for visual display
            $amount_str = budget_format($raw_amount);
            $amount_str = substr($amount_str, 0, $AMOUNT_LENGTH);
            $amount_str = (strlen($amount_str) < $AMOUNT_LENGTH ) ? str_pad($amount_str, $AMOUNT_LENGTH, " ", STR_PAD_RIGHT) : $amount_str ;

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
            "$" . $amount_str ." - " .
            $payee_name . " - " .
            $memo .
            "\n";

            $transaction_year = explode("-", $transaction["date"])[0];

            // Use the clean numeric value here so padded spaces don't break calculations
            if (array_key_exists($transaction_year, $yearly_totals)) {
                $yearly_totals[$transaction_year] += $raw_amount;
            } else {
                $yearly_totals[$transaction_year] = $raw_amount;
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
