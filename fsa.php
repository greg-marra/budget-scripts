<?php

    $filtered_year = 2024;

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "FSA.csv";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $newest_budget_date = get_recent_date($settings, $budgetID);
    
    $this_month = date('n');
    $this_year = date('Y');
    $last_year = $this_year - 1;

    $totals = array();
    $total = 0;

    # Endpoint to grab all transactions for each Payee
    $endpoint = "/$BUDGET_ID/categories/$fsa_id/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);

    foreach ( $result["data"]["transactions"] as $transaction) {

        $date = date_parse_from_format("Y-m-d",$transaction["date"]);
        $year = $date["year"];

        if ($filtered_year === $year && $transaction["payee_id"] != "481086e4-00b4-43fe-ad13-44b59b05c3e9" /*Transfer*/) {

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
            "$" . str_pad(substr($amount, 0, 7), 7) .
            " -\t" . str_pad(substr($payee, 0, 20), 20) .
            " -\t" . str_pad(substr($notes, 0, 20), 20) .
            "\n";

            file_put_contents(
                "$base_dir/Documents/fsa-$year.csv",
                "$year-$month-$day,$payee,$amount,$notes\n",
                FILE_APPEND | LOCK_EX
            );

        }

    }

    echo "\n" . "Total: $$total" . "\n" . "File at: " . "$base_dir/Documents/fsa-$year.csv" . "\n";
