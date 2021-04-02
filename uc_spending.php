<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . '/Documents/ynab/ynab_functions.php');
    $report_name = "Uncommon Grounds Report";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_parsed_date = get_oldest_date($settings);
    $recent_parsed_date = get_recent_date($settings);

    # Endpoint to grab all transactions for 'Credit Card Cash Rewards'
    $endpoint = "/$BUDGET_ID/payees/$UC_PAYEE_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $transactions = json_decode(curl_exec($ch), true);

    $yearly_totals = array();
    $subtotal = 0;
    $rewards = 0;

    foreach ($transactions["data"]["transactions"] as $transaction) {

        $amount = $transaction["amount"] / 1000;
    
        # Spits out date and transaction amounts for logs
        #echo $transaction["date"] . ": " . $amount . "\n";
        
        $year = explode("-", $transaction["date"])[0];

        if (array_key_exists($year, $yearly_totals)) {
            
            $yearly_totals[$year] += $amount;

        } else {

            $yearly_totals[$year] = $amount;

        }

        $remove_taxes = $amount / 1.07;
        $rewards += ($remove_taxes * .05);

    }

    print_totals($yearly_totals, $report_name, $oldest_parsed_date, $recent_parsed_date);
    #echo "Target Rewards: $" . ynab_format($rewards) . "\n";

