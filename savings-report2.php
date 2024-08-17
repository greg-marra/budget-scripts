<?php
    
    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "Savings Report";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $newest_budget_date = get_recent_date($settings, $budgetID);
    $date = date($budget_DATE_FORMAT, $newest_budget_date);

    # Initialize Array to hold Category budgeted Values
    $category_balances = array();

    $account_ids = array(

        "ally" => $SAVINGS_ACCOUNT_ID,
        "checking" => $CHECKING_ACCOUNT_ID,
        "chase" => $CHASE_ACCOUNT_ID,
        "apple" => $APPLE_ACCOUNT_ID,
        "verizon" => $VERIZON_ACCOUNT_ID,
        "cc_savings" => $APPLE_ACCOUNT_ID,
        "target" => $TARGET_ACCOUNT_ID,

    );

    # Get All Account Data
    $endpoint = "/$BUDGET_ID/accounts";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $all_account_data = json_decode(curl_exec($ch), true);

    foreach ($account_ids as $name => $id) {

        foreach ($all_account_data["data"]["accounts"] as $key => $value) {

            if ($id == $value["id"]) {

                ${$name . "_balance"} = $value["balance"]/1000;

            }

        }

    }

    # "Net Cash" Calculation
    $netcash = $checking_balance + $target_balance + $apple_balance + $chase_balance + $verizon_balance;
    echo "Net Cash: $" . budget_format($netcash) . "\n\n";
#    echo "checking: $checking_balance\ntarget: $target_balance\napple: $apple_balance\nchase: $chase_balance\nVerizon: $verizon_balance\nnetcash: $netcash\n\n\n";

    # Net Savings
    $savings_balance = $ally_balance; // + $cc_savings_balance;
#    echo "savings_balance: $savings_balance\nAlly Balance: $ally_balance\ncc_savings_balance: $cc_savings_balance\n\n";

    # Endpoint to grab category data
    $endpoint = "/$BUDGET_ID/categories/";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $all_category_data = json_decode(curl_exec($ch), true);

    # For loop to get balances of each category and compare to array of selected categories

    foreach ( $all_category_data["data"]["category_groups"] as $key1 => $group ) {

        foreach ( $group["categories"] as $key2 => $category) {

            foreach ( $BALANCE_IDS as $name => $id) {

                if ( $id == $category["id"] ) {

                    $balance = $category["balance"]/1000;
                    echo $name . " : $" . budget_format($balance) . "\n";

                    array_push( $category_balances, array($name => $balance));

                }

            }

        }

    }

    echo "==============\n";

    #print_totals($category_balances, $report_name, $oldest_budget_date, $newest_budget_date);

    $budget_total = 0;

    foreach( $category_balances as $key4 => $array ) {

        foreach( $array as $name => $balance ) {

            $budget_total += $balance;

        }
    
    }

    $difference = $budget_total - $savings_balance;
    $abs_difference = abs($difference);
    $direction_string = $budget_total > $savings_balance ? " to savings." : " to checking";

    $projected_checkings = ($netcash - $difference);
    $projected_savings = ($savings_balance + $difference);

#    echo "Data Dump:\n====\nCurrent Checking: $checking_balance\nCurrent NetCash: $netcash\nProjected Checkings: $projected_checkings\nProjected Savings: $projected_savings\nYNAB total: $budget_total\nSavings Accts Total: $savings_balance\nDifference: $difference\n\n";

    if ( $projected_checkings < $CHECKING_FLOOR ) {

        $amt = $CHECKING_FLOOR - $projected_checkings;

        echo "Projected NetCash Balance: $" . budget_format($projected_checkings) . "\n\nWould be below your minimum of: $" . budget_format($CHECKING_FLOOR) . "\n\nMove: $" . budget_format($amt) . " to checking\n\n";

        exit;

    }

    if ( $abs_difference != 0 ) {

        echo
        "Ally Balance: $" . budget_format($savings_balance) . "\n" . 
        "Envelope Balance: $" . budget_format($projected_savings) . "\n\n" . 
        "Projected NetCash Balance: $" . budget_format($projected_checkings) . "\n\n" . 
        "Move $" . budget_format($abs_difference) . $direction_string . "\n\n";
        

    }

    if ( $abs_difference == 0 ) { 

        echo "All set.\n\n";
    
    }
