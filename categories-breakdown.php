<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $newest_budget_date = get_recent_date($settings, $budgetID);
    
    $this_month = date('n');
    $this_year = date('Y');

    # Endpoint to grab all transactions for the category
    $endpoint = "/$BUDGET_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);

    foreach ( $BREAKDOWN_CATEGORY_IDS as $report_category_name => $report_category_id) {

        $totals = array();

        foreach ( $result["data"]["transactions"] as $transaction) {

            $date = date_parse_from_format("Y-m-d",$transaction["date"]);
            $year = $date["year"];
            $month = $date["month"];
            $payee = $transaction["payee_name"];

            if ( $transaction["category_id"] == $SPLIT_CATEGORY_ID) {

                foreach ($transaction["subtransactions"] as $subtrans) {

                    $totals = evaluate_transaction($subtrans, $report_category_id, $payee, $date, $year, $month, $this_month, $this_year, $totals);
                
                }

            }

            $totals = evaluate_transaction($transaction, $report_category_id, $payee, $date, $year, $month, $this_month, $this_year, $totals);

        }

        ksort($totals);

        echo "\t$report_category_name Breakout\n";
        $report_date = date('F Y', $newest_budget_date);
        echo "\t$report_date\n\t";
        for ($i = 0; $i < strlen($report_date); $i++) {

            echo "=";

        }

        $monthly_total = 0;
        $yearly_total = 0;

        foreach($totals as $name => $values) {
            
            echo "\n\n" . $name . "\n\tMonth: $" . budget_format($values["month"]) . "\n\tYear:  $" . budget_format($values["year"]);

            $monthly_total += $values["month"];
            $yearly_total += $values["year"];

        }

        $average = ( $yearly_total / $this_month); 

        echo "\n" . $report_category_name . " Monthly Total: $" . budget_format($monthly_total) . "\n";
        echo $report_category_name . " Yearly Total: $" . budget_format($yearly_total). "\n";
        echo $report_category_name . " Monthly Average : $" . budget_format($average);
        echo "\n=====================\n\n";

        unset($totals);

    }

    function evaluate_transaction($transaction, $report_category_id, $payee, $date, $year, $month, $this_month, $this_year, $totals) {

        if ( $transaction["category_id"] == $report_category_id) {

            $amount = number_format(-$transaction["amount"] / 1000, 2, ".", "");

            if ( $this_year == $year ) {

                if ( array_key_exists($payee, $totals) ) {

                    $totals["$payee"]["year"] += $amount;

                } else {

                    $totals["$payee"] = array( "month" => (float) 0.00 , "year" => (float) $amount);
                
                }

                if ( $this_month == $month ) {

                    $totals["$payee"]["month"] += $amount;

                }

            }

        }

        return($totals);

    }
