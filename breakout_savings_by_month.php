<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . $functions_directory);
    $report_name = "Savings By Month";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_parsed_date = get_oldest_date($settings);
    $recent_parsed_date = get_recent_date($settings);

    $this_month = (float) date('m', $recent_parsed_date);
    $this_year = (float) date('Y', $recent_parsed_date);
    $last_year = $this_year - 1;

    $years = array($this_year, $last_year);
    $months = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);

    $totals = array();

    foreach ($years as $year) {

        foreach ($months as $month) {

            if ($year === $this_year && $this_month < $month) {

                break;

            } else {

                if ( $month < 10) {

                    $month_string = "0" . $month;

                } else {

                    $month_string = $month;
                
                }

                foreach ($CATEGORY_IDS as $name => $id) {

                    # Endpoint to grab all transactions for 'Interest Earned/Paid'
                    $endpoint = "/$BUDGET_ID/months/$year-$month_string-01/categories/$id";
                    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
                    $transaction = json_decode(curl_exec($ch), true);
                    $monthly_balance = $transaction["data"]["category"]["balance"] / 1000;
                    $transaction_date = $year . "-" . $month_string;

                    if (array_key_exists($transaction_date, $totals)) {

                        $totals[$transaction_date] += $monthly_balance;

                    } else {

                        $totals[$transaction_date] = $monthly_balance;

                    }

                }

            }

        }

    }

    $total = 0;
    $max_amount_strlen = 0;
    $max_category_strlen = 0;
    $previous_balance = 0;
    $counter = 0;
    ksort($totals);

    echo "   " . "Savings by Month\n   " . date("F Y", $recent_parsed_date)  . "\n";

    foreach($totals as $month => $monthly_balance) {

        if ( $counter === 0) {

            $previous_balance = $monthly_balance;
            $counter++;
            continue;

        } else {

            $delta = $monthly_balance - $previous_balance;
            $delta_formatted = ynab_format($delta);

            echo $month . ":   $" . $delta_formatted . "\n";

            $total += $delta;
            $previous_balance = $monthly_balance;
            $counter++;

            # Calculation for Max Length String of Amount
            $amount_strlen = strlen($delta_formatted);
            ($amount_strlen > $max_amount_strlen) ? $max_amount_strlen = $amount_strlen : $amount_strlen = $amount_strlen;

            # Calculation for Max Length string for Month
            $month_strlen = strlen($month);
            ($month_strlen > $max_category_strlen) ? $max_month_strlen = $month_strlen : $month_strlen = $month_strlen;

        }

    }

    for ($i = 0; $i <= $max_month_strlen; $i++) {

        echo "=";

    }

    echo "   ";

    for ($i = 0; $i <= $max_amount_strlen; $i++) {

        echo "=";

    }

    echo "\n";

    echo "TOTAL:" . "\t" .  "$" . ynab_format($total) . "\n";
    echo "Number of months: " . sizeof($totals) . "\n";
    echo "Per month: " . "$" . ynab_format(($total / sizeof($totals))) . "\n";
    echo "\n";
