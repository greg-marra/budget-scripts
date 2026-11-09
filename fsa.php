<?php

    $filtered_year = 2026;

    $base_dir = $_SERVER['HOME'];
    require_once ($base_dir . '/Documents/vars-budget.php');
    require_once ($base_dir . $functions_directory);
    $output_file = "$base_dir/Documents/fsa-$filtered_year.csv";

    if (file_exists($output_file)) {
        unlink($output_file);
    }

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$BUDGET_ID);
    $oldest_budget_date = get_oldest_date($settings, $BUDGET_ID);
    $newest_budget_date = get_recent_date($settings, $BUDGET_ID);
    
    $this_month = date('n');
    $this_year = date('Y');
    $last_year = $this_year - 1;

    $totals = array();
    $total = 0;

    # Endpoint to grab all transactions for each Payee
    $endpoint = "/$BUDGET_ID/categories/$fsa_id/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $raw_json = curl_exec($ch);
    $result = json_decode($raw_json, true);

    if (!isset($result["data"]["transactions"])) {
        $detail = $result["error"]["detail"] ?? "No transaction data returned from API.";
        fwrite(STDERR, "YNAB API Error: $detail\n");
        exit(1);
    }

    foreach ( $result["data"]["transactions"] as $transaction) {

        $date = date_parse_from_format("Y-m-d",$transaction["date"]);
        $year = $date["year"];

        if ($filtered_year === $year && ($transaction["payee_id"] ?? '') !== $TRANSFER_PAYEE_ID) {          
            $month = $date["month"];
            $day = $date["day"];
            $amount = -($transaction["amount"] / 1000);
            $payee = $transaction["payee_name"];
            $notes = $transaction["memo"];

            if ( $payee === null ) {

                $payee = "Split Transaction";

            }

            if ( $notes === null ) {

                $notes = "";

            }

            $total += $amount;

            echo
            "$" . str_pad(number_format($amount, 2), 8) .
            " -\t" . str_pad(substr($payee, 0, 20), 20) .
            " -\t" . str_pad(substr($notes, 0, 20), 20) .
            "\n";

            $csv_payee = '"' . str_replace('"', '""', $payee) . '"';
            $csv_notes = '"' . str_replace('"', '""', $notes) . '"';
            $csv_row = "$year-$month-$day,$csv_payee," . number_format($amount, 2, '.', '') . ",$csv_notes\n";

            file_put_contents($output_file, $csv_row, FILE_APPEND | LOCK_EX);

        }

    }

$formatted_total = number_format($total, 2);
echo "\nTotal: $$formatted_total\n";
echo "File at: $output_file\n";