<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/vars-budget.php');
    require ($base_dir . $functions_directory);
    $report_name = "Car Maintenance Breakdown";

    # Get latest date for budget in budget and set that in GET for category balances
    $settings = get_settings($ch, $base ,$budgetID);
    $oldest_budget_date = get_oldest_date($settings, $budgetID);
    $newest_budget_date = get_recent_date($settings, $budgetID);

    $this_month = date('n');
    $this_year = date('Y');

    $totals = array();

    $oldest_trans_year = null;
    $oldest_trans_month = null;

    $newest_trans_year = null;
    $newest_trans_month = null;

    # Endpoint to grab list of category
    $endpoint = "/$BUDGET_ID/transactions";

    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $result = json_decode(curl_exec($ch), true);

    foreach( $result["data"]["transactions"] as $transaction) {

        if ( $transaction["category_id"] == $CAR_MAINTENANCE_CATEGORY_ID ) {

            $date = date_parse_from_format("Y-m-d",$transaction["date"]);
            $year = $date["year"];
            $month = $date["month"];
            $amount = number_format(-$transaction["amount"] / 1000, 2, ".", "");
            $payee_name = $transaction["payee_name"];

            if ( array_key_exists($year, $totals) ) {

                $totals[$year] += $amount;

            } else {

                    $totals["$year"] = (float) $amount;

            }

            $date_array = set_date($oldest_trans_year, $oldest_trans_month, $newest_trans_year, $newest_trans_month, $year, $month);

            $oldest_trans_year = ("$date_array[0]");
            $oldest_trans_month = ("$date_array[1]");
            $newest_trans_year = ("$date_array[2]");
            $newest_trans_month = ("$date_array[3]");

        }

    }

/*

    ksort($totals);

    echo "\tCar Maintenance Breakout\n";
    $report_date = date('F Y', $newest_budget_date);
    echo "\t$report_date";

    $all = 0;

    foreach($totals as $year => $value) {

        echo "\n\n" . $year . "\n\tYear:  $" . budget_format($value);

        $all += $value;

    }

    echo "$all\n";

*/

    $oldest_trans_date = strtotime("$oldest_trans_year-$oldest_trans_month-01");
    $newest_trans_date = strtotime("$newest_trans_year-$newest_trans_month-01");

    print_totals($totals, $report_name, $oldest_trans_date, $newest_trans_date);
