<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "All Transactions";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base, $BUDGET_ID);
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

    $transactions = json_decode(curl_exec($ch), true);

    $number_of_transactions = count($transactions["data"]["transactions"]);

    $yearly_totals[date('Y', $newest_budget_date)] = $number_of_transactions;

    print_number_totals($yearly_totals, $report_name . " Report", strtotime($oldest_budget_date), $newest_budget_date);
