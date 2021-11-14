<?php
    
    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/budget_vars.php');
    require ($base_dir . $functions_directory);
    $report_name = "Savings Report";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_budget_date = get_oldest_date($settings);
    $newest_budget_date = get_recent_date($settings);
    $date = date($budget_DATE_FORMAT, $newest_budget_date);

    # Initialize Array to hold Category budgeted Values
    $category_balances = array();

    # Get Current Savings Account Balance
    $endpoint = "/$BUDGET_ID/accounts/$SAVINGS_ACCOUNT_ID";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);
    $savings_balance = ($result["data"]["account"]["balance"] / 1000);

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
    $direction = $budget_total > $savings_balance ? " to savings." : " to checking";
    
    if ($difference != 0) {
        
        echo "Move $" . budget_format(abs($difference)) . $direction . "\n\n";

    }
