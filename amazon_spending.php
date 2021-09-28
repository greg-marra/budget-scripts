<?php

    # I wrote this report to get Amazon Spending by year
    # I wanted to track this to monitor and minimize
    # Amazon spending
    # I don't like their business practices
    # and it can be a black hole for my money

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . $functions_directory);
    $report_name = "Amazon Report";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_parsed_date = get_oldest_date($settings);
    $recent_parsed_date = get_recent_date($settings);

    # Endpoint to grab all transactions for under Amazon Payee ID
    $endpoint = "/$BUDGET_ID/payees/$AMAZON_PAYEE_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $transactions = json_decode(curl_exec($ch), true);

    $yearly_totals = array();

    foreach ($transactions["data"]["transactions"] as $transaction) {

        $amount = $transaction["amount"] / 1000;        
        $year = explode("-", $transaction["date"])[0];

        if (array_key_exists($year, $yearly_totals)) {
            
            $yearly_totals[$year] += $amount;

        } else {

            $yearly_totals[$year] = $amount;

        }

    }

    print_totals($yearly_totals, $report_name, $oldest_parsed_date, $recent_parsed_date);
