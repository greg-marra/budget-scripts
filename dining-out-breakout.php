<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "Dining Out Breakdown";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $newest_budget_date = get_recent_date($settings, $budgetID);
    
    $this_month = date('n');
    $this_year = date('Y');

    $totals = array();

    # Endpoint to grab all transactions for the category
    $endpoint = "/$BUDGET_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);

    foreach ( $result["data"]["transactions"] as $transaction) {

        if ( in_array($transaction["category_id"], $DINING_OUT_CATEGORY_ID) ) {

            $date = date_parse_from_format("Y-m-d", $transaction["date"]);
            $year = $date["year"];
            $month = $date["month"];
            $amount = number_format(-$transaction["amount"] / 1000, 2, ".", "");
            $payee_name = $transaction["payee_name"];

            if ( $this_year == $year ) {

                if ( array_key_exists($payee_name, $totals) ) {

                    $totals["$payee_name"]["year"] += $amount;

                } else {

                    $totals["$payee_name"] = array( "month" => (float) 0.00 , "year" => (float) $amount);
                
                }

                if ( $this_month == $month ) {

                    $totals["$payee_name"]["month"] += $amount;

                }

            }

        }

    }

    ksort($totals);

    $monthly_total = 0;
    $yearly_total = 0;

    foreach($totals as $name => $values) {

        $monthly_total += $values["month"];
        $yearly_total += $values["year"];

    }

    echo "\tDining Out Breakout\n";
    $report_date = date('F Y', $newest_budget_date);
    echo "\t$report_date\n";

    echo "\nMonth Total: $" . budget_format($monthly_total) . "\n" . "Year Total: $" . budget_format($yearly_total) . "\n\n";

    file_put_contents(
        "$base_dir/Desktop/dining-out.csv",
        "Name,this-month,this-year\n",
        LOCK_EX
    );

    foreach($totals as $name => $values) {
        
        echo "\n" . $name . "\n\tMonth: $" . budget_format($values["month"]) . "\n\tYear:  $" . budget_format($values["year"]);

        file_put_contents(
            "$base_dir/Desktop/dining-out.csv",
            "$name,{$values['month']},{$values['year']}\n",
            FILE_APPEND | LOCK_EX
        );

    }

    echo "\n";
