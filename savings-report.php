<?php
    
    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "Savings Report";

    # 2 paychecks
    $CHECKING_FLOOR = 3000;

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $newest_budget_date = get_recent_date($settings, $budgetID);
    $date = date($budget_DATE_FORMAT, $newest_budget_date);

    # Initialize Array to hold Category budgeted Values
    $category_balances = array();

    # Get Current Savings Account Balance
    $endpoint = "/$BUDGET_ID/accounts/$SAVINGS_ACCOUNT_ID";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);
    $savings_balance = ($result["data"]["account"]["balance"] / 1000);

    # Get Current Checking Account Balance
    $endpoint = "/$BUDGET_ID/accounts/$CHECKING_ACCOUNT_ID";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);
    $checking_balance = ($result["data"]["account"]["balance"] / 1000);

    # Get Current Chase Account Balance
    $endpoint = "/$BUDGET_ID/accounts/$CHASE_ACCOUNT_ID";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);
    $chase_balance = ($result["data"]["account"]["balance"] / 1000);

    # Get Current Checking Account Balance
    $endpoint = "/$BUDGET_ID/accounts/$APPLE_ACCOUNT_ID";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);
    $apple_balance = ($result["data"]["account"]["balance"] / 1000);

    # Get Current TargetRCAM Account Balance
    $endpoint = "/$BUDGET_ID/accounts/$TARGET_ACCOUNT_ID";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);
    $target_balance = ($result["data"]["account"]["balance"] / 1000);

    # "Net Cash" Calculation
    $checking_balance = $checking_balance + $target_balance + $apple_balance + $chase_balance;

    # Endpoint to grab category values
    $endpoint = "/$BUDGET_ID/months/" . $date . "/categories/";

    # For loop to get balances by budget, for specified month, store in an array and total and echo while looping
    foreach ($BALANCE_IDS as $name => $id) {

        curl_setopt($ch, CURLOPT_URL, $base . $endpoint . $id);
        $array = json_decode(curl_exec($ch), true);
        $balance = ($array["data"]["category"]["balance"] / 1000);

        $category_balances[$name] = $balance;

    }
    
    print_totals($category_balances, $report_name, $oldest_budget_date, $newest_budget_date);

    $budget_total = 0;

    foreach($category_balances as $category => $balance) {

        $budget_total += $balance;
    
    }

    $difference = $budget_total - $savings_balance;
    $abs_difference = abs($difference);
    $direction = $budget_total > $savings_balance ? -1 : 1;
    $direction_string = $budget_total > $savings_balance ? " to savings." : " to checking";

    $projected_checkings = ($checking_balance - $difference);
    $projected_savings = ($savings_balance + $difference);
    
    if ( $projected_checkings < $CHECKING_FLOOR ) {

        $amt = $CHECKING_FLOOR - $projected_checkings;

        echo "Projected NetCash Balance: $" . budget_format($projected_checkings) . "\n\nWould be below your minimum of: $" . budget_format($CHECKING_FLOOR) . "\n\nMove: $" . budget_format($amt) . " to checking\n\n";

        exit;

    }

    if ( $abs_difference != 0 ) {

        echo
        "Current   Savings   Balance: $" . budget_format($savings_balance) . "\n" . 
        "Projected Savings   Balance: $" . budget_format($projected_savings) . "\n\n" . 
        "Projected NetCash Balance: $" . budget_format($projected_checkings) . "\n\n" . 
        "Move $" . budget_format($abs_difference) . $direction_string . "\n\n";
        

    }
