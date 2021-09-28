<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . $functions_directory);
    $report_name = "Retirement Report";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_parsed_date = get_oldest_date($settings);
    $recent_parsed_date = get_recent_date($settings);
    $date = date($YNAB_DATE_FORMAT, $recent_parsed_date);

    # Endpoint to grab list of category
    $endpoint = "/$BUDGET_ID/accounts/";

    # Total Values
    $account_balances = array();
    $total_balance = 0;

    # % Variables
    $interest_total = 0;
    $contributions = 0;

    foreach ($RETIREMENT_IDS as $stored_account_name => $id) { 

        # Account Balance
        curl_setopt($ch, CURLOPT_URL, $base . $endpoint . $id);
        $array = json_decode(curl_exec($ch), true);
        $account_name = $array["data"]["account"]["name"];
        $account_balance = $array["data"]["account"]["balance"]/1000;

        $account_balances[$stored_account_name] = $account_balance;
        $total_balance += $account_balance;

        # % Calculation
        curl_setopt($ch, CURLOPT_URL, $base . $endpoint . $id . "/transactions");
        $array = json_decode(curl_exec($ch), true);

        # This line excludes Pension
        if ($id !== "33ae8259-551d-46be-889e-b406caaf7528") {
        #if (true) {
        
            foreach ($array["data"]["transactions"] as $transaction) {

                if ($transaction["payee_id"] === $INTEREST_PAYEE_ID) {

                    $interest_total += $transaction["amount"]/1000;                

                }
                else {

                    $contributions += $transaction["amount"]/1000;
                    
                }

            }

        }

    }

    ksort($account_balances);

    print_totals($account_balances, $report_name, $oldest_parsed_date, $recent_parsed_date);


    $combined = $contributions + $interest_total;
    $percent_of_account_interest = 100 * $interest_total / $combined;
    $total_months = number_of_months($oldest_parsed_date, $recent_parsed_date);

    $percent_earned = 100 * ($combined - $contributions) / ($contributions * ( ($total_months) /12 ));

    echo
        "Contributions: $" . ynab_format($contributions) . "\n" . 
        "Interest: $" . ynab_format($interest_total) . "\n" . 
        "Total: $" . ynab_format($combined) . "\n" .
        #"Percent Earned: " . number_format($percent_earned, 2 , $US_DECIMAL_FORMAT, $US_THOUSANDS_FORMAT) . " %\n";
        "Interest Base: " . number_format($percent_of_account_interest, 2, $US_DECIMAL_FORMAT, $US_THOUSANDS_FORMAT) . " %" . "\n" . 
        "Percent Earned: " . number_format($percent_earned, 2, $US_DECIMAL_FORMAT, $US_THOUSANDS_FORMAT) . " %" . "\n\n";


