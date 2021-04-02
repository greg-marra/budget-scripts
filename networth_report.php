<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . '/Documents/ynab/ynab_functions.php');
    $report_name = "Networth Report";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_parsed_date = get_oldest_date($settings);
    $recent_parsed_date = get_recent_date($settings);

    # Endpoint to grab list of category
    $endpoint = "/$BUDGET_ID/accounts";

    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $array = json_decode(curl_exec($ch), true);

    $yearly_totals = array();
    $balance = 0;

    foreach ($array["data"]["accounts"] as $account) {

        if (!in_array($account["id"], $NETWORTH_ACCTS)) {
                
            $balance += ($account["balance"]/1000);
            #echo $account["name"] . " $" . $account["balance"]/1000 . "\n";

        }
        
    }

    $yearly_totals[date('Y', $recent_parsed_date)] = $balance;
    print_totals($yearly_totals, $report_name, $oldest_parsed_date, $recent_parsed_date);
