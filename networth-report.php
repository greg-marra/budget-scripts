<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "Networth Report";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $newest_budget_date = get_recent_date($settings, $budgetID);

    # Endpoint to grab list of category
    $endpoint = "/$BUDGET_ID/accounts";

    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $array = json_decode(curl_exec($ch), true);

    $yearly_totals = array();
    $balance = 0;

    foreach ($array["data"]["accounts"] as $account) {

        if (!in_array($account["id"], $NON_NETWORTH_ACCTS)) {
                
            $balance += ($account["balance"]/1000);
            #echo $account["name"] . " $" . $account["balance"]/1000 . "\n";

        }
        
    }

    $yearly_totals[date('Y', $newest_budget_date)] = $balance;
    print_totals($yearly_totals, $report_name, $oldest_budget_date, $newest_budget_date);
