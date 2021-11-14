<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/budget_vars.php');
    require ($base_dir . $functions_directory);
    $report_name = "FSA.csv";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_budget_date = get_oldest_date($settings);
    $newest_budget_date = get_recent_date($settings);
    
    $this_month = date('n');
    $this_year = date('Y');
    $last_year = $this_year - 1;

    $filtered_year = 2021;

    $totals = array();

    # Endpoint to grab all transactions for each Payee
    $endpoint = "/$BUDGET_ID/categories/$fsa_id/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);

    foreach ( $result["data"]["transactions"] as $transaction) {

        $date = date_parse_from_format("Y-m-d",$transaction["date"]);
        $year = $date["year"];

        if ($filtered_year === $year) {

            $month = $date["month"];
            $day = $date["day"];
            $amount = -($transaction["amount"] / 1000);
            $payee = $transaction["payee_name"];
            $notes = $transaction["memo"];

            file_put_contents(
                "$base_dir/Documents/fsa-$year.csv",
                "$year-$month-$day,$payee,$amount,$notes\n",
                FILE_APPEND | LOCK_EX
            );

        }

    }
