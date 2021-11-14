<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . $functions_directory);
    $report_name = "All Transactions";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_ynab_date = get_oldest_date($settings);
    $newest_ynab_date = get_recent_date($settings);

    # Endpoint to grab all transactions
    $endpoint = "/$BUDGET_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $transactions = json_decode(curl_exec($ch), true);

    $number_of_transactions = count($transactions["data"]["transactions"]);

    $yearly_totals[date('Y', $newest_ynab_date)] = $number_of_transactions;

    print_number_totals($yearly_totals, $report_name . " Report", $oldest_ynab_date, $newest_ynab_date);
