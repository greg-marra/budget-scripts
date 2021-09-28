<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . $functions_directory);
    $report_name = "Fastfood By Month";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_parsed_date = get_oldest_date($settings);
    $recent_parsed_date = get_recent_date($settings);
    
    $this_month = date('n');
    $this_year = date('Y');
    $last_year = $this_year - 1;

    $totals = array();

    foreach ($FASTFOOD_PAYEE_IDS as $name => $id) {
    #foreach (array("7c0bcbad-8024-4304-94ac-7aaf587b0dc3", "UC") as $name => $id) {

        # Endpoint to grab all transactions for each Payee
        $endpoint = "/$BUDGET_ID/payees/$id/transactions";
        curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
        $result = json_decode(curl_exec($ch), true);

        (float) $payee_total = 0;
        (float) $this_month_total = 0;
        (float) $this_year_total = 0;

        foreach ( $result["data"]["transactions"] as $transaction) {

            # Skips Stewart's Gas
            if ($transaction["category_id"] !== "5b7b846f-b662-4253-a483-16016d09fe44") {
            
                $date = date_parse_from_format("Y-m-d",$transaction["date"]);
                $year = $date["year"];
                $month = $date["month"];
                $amount = -($transaction["amount"] / 1000);
                #echo "year: " . $year . " month: "  . $month . " amount: " . "$" . $amount . "\n";
                
                if ($this_year == $year) {

                    $this_year_total += $amount;

                    if ($this_month == $month) {

                        $this_month_total += $amount;
    
                    }

                }

                $payee_total += $amount;

                #echo $this_month_total . '     ' . $payee_total;

            }

        }

        array_push($totals, array("name" => $name, "month" => $this_month_total, "year" => $this_year_total, "total" => $payee_total));

    }

    #var_dump($totals);exit;

    $print_total = 0;
    $month_total = 0;
    $year_total = 0;

    #ksort($totals, "months");

    echo "\tFast Food Report\n";

    foreach($totals as $total) {
        
        #var_dump($total);exit;
        $print_total += $total["total"];
        $month_total += $total["month"];
        $year_total += $total["year"];

        if ($total["total"] != 0){

            echo $total["name"] . "\n\tMonth: $" . ynab_format($total["month"]) . "\n\tYear:  $" . ynab_format($total["year"]) . "\n";

        }

    }

    echo "====";
    echo "This Month's total: $" . ynab_format($month_total) . "\n";

    echo "====";
    echo "This Year's total:  $" . ynab_format($year_total) . "\n\n";
