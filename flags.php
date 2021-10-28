<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . $functions_directory);

    # Imports Name and Colors of each flag
    require ($base_dir . '/Documents/ynab_flags.php');
    $report_name = "Flagged Report";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_parsed_date = get_oldest_date($settings);
    $recent_parsed_date = get_recent_date($settings);

    # Endpoint to grab all transactions for 'Credit Card Cash Rewards'
    $endpoint = "/$BUDGET_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $transactions = json_decode(curl_exec($ch), true);

    foreach ($flags as $color => $report_name) {

        $yearly_totals = array();

        $flag_count = 0;

        $oldest_trans_year = null;
        $oldest_trans_month = null;

        $newest_trans_year = null;
        $newest_trans_month = null;

        foreach ($transactions["data"]["transactions"] as $transaction) {

            if ($transaction["flag_color"] == $color) {

                $flag_count++;
                $amount = $transaction["amount"] / 1000;
            
                # Spits out date and transaction amounts for logs
                #echo $transaction["date"] . ": " . $amount . "\n";
                
                $year = explode("-", $transaction["date"])[0];
                $month = explode("-", $transaction["date"])[1];

                if (array_key_exists($year, $yearly_totals)) {
                    
                    $yearly_totals[$year] += $amount;

                } else {

                    $yearly_totals[$year] = $amount;

                }

                if ($oldest_trans_year === null) {

                    $oldest_trans_year = $year;
                    $oldest_trans_month = $month;

                    $newest_trans_year = $year;
                    $newest_trans_month = $month;

                }

                if ($oldest_trans_year > $year) {

                    $oldest_trans_year = $year;

                    if ($oldest_trans_month > $month) {

                        $oldest_trans_month = $month;

                    }

                }

                if ($newest_trans_year < $year) {

                    $newest_trans_year = $year;

                    if ($newest_trans_month < $month) {

                        $newest_trans_month = $month;

                    }

                }

            }

        }

        $oldest_trans_date = strtotime("$oldest_trans_year-$oldest_trans_month-1");
        $newest_trans_date = strtotime("$newest_trans_year-$newest_trans_month-1");

        if ( $flag_count == 0)  {

            $yearly_totals = array(date('Y') => 0);

        }
            print_totals($yearly_totals, $report_name . " Report", $oldest_trans_date, $newest_trans_date);

    }