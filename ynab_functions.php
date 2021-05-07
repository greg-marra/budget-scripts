<?php

    require ($base_dir . '/Documents/ynab_vars.php');

    # Initialize curl
    $ch = curl_init();
#curl_setopt($ch_put, CURLOPT_VERBOSE, true);
    $base = "https://api.youneedabudget.com/v1/budgets";
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(

        "Authorization: Bearer $YNAB_TOKEN",

        )
    );

    function get_settings($ch, $base) {

        curl_setopt($ch, CURLOPT_URL, $base);
        $settings = json_decode(curl_exec($ch), true);

        if (array_key_exists("error", $settings)) {

            echo "API Error:\n";
            echo $settings["error"]["id"] . ": ";
            echo $settings["error"]["name"] . "\n";
            echo $settings["error"]["detail"] . "\n";
            exit;

        } else {

            return $settings;

        }

    }


    function set_curl_put($data_json, $YNAB_TOKEN, $url) {

        $ch_put = curl_init();
#curl_setopt($ch_put, CURLOPT_VERBOSE, true);
        curl_setopt($ch_put, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_put, CURLOPT_HTTPHEADER, array(

            "Authorization: Bearer $YNAB_TOKEN",
            "Content-Type: application/json",
#            "Content-Length: " . strlen($data_json)

            )

        );
        curl_setopt($ch_put, CURLOPT_URL, $url);
        curl_setopt($ch_put, CURLOPT_CUSTOMREQUEST, 'PUT');
        #curl_setopt($ch_put, CURLOPT_POSTFIELDS, http_build_query($data_json));
        curl_setopt($ch_put, CURLOPT_POSTFIELDS, $data_json);

        return $ch_put;

    }

    function put_curl($ch) {

        $response = json_decode(curl_exec($ch), true);

        if (array_key_exists("error", $response)) {

            echo "API Error:\n";
            echo $response["error"]["id"] . ": ";
            echo $response["error"]["name"] . "\n";
            echo $response["error"]["detail"] . "\n";

        } else {

            return $response;

        }

    }

    function number_of_months($oldest_parsed_date, $recent_parsed_date) {

        $oldest_year = date('Y', $oldest_parsed_date);
        $recent_year = date('Y', $recent_parsed_date);
        $oldest_month = date('n', $oldest_parsed_date);
        $recent_month = date('n', $recent_parsed_date);

        $diff = (($recent_year - $oldest_year) * 12) + ($recent_month - $oldest_month);

        return $diff;

    }

    function get_oldest_date($settings) {

        $oldest_date = $settings["data"]["budgets"][0]["first_month"];
        $oldest_parsed_date = strtotime($oldest_date);
        return $oldest_parsed_date;

    }

    function get_recent_date($settings) {

        $recent_date = $settings["data"]["budgets"][0]["last_month"];
        $recent_parsed_date = strtotime($recent_date);
        return $recent_parsed_date;

    }

    function ynab_format($number) {

        global $US_NUMBER_OF_DECIMALS, $US_DECIMAL_FORMAT, $US_THOUSANDS_FORMAT;
        return number_format($number, $US_NUMBER_OF_DECIMALS, $US_DECIMAL_FORMAT, $US_THOUSANDS_FORMAT);

    }

    function print_totals($totals, $report_name, $oldest_parsed_date, $recent_parsed_date) {

        global $US_NUMBER_OF_DECIMALS, $US_DECIMAL_FORMAT, $US_THOUSANDS_FORMAT;
        $max_amount_strlen = 0;
        $max_category_strlen = 0;
        $total = 0;

        echo "   " . $report_name . "\n   " . date("F Y", $recent_parsed_date)  . "\n";

        foreach ($totals as $category_name => $amount) {

            $amount_formatted = ynab_format($amount);

            echo $category_name . ":" . "\t" . "$" . $amount_formatted . "\n";
            $total += $amount;

            # Calculation for Max Length String of Amount
            $amount_strlen = strlen($amount_formatted);
            ($amount_strlen > $max_amount_strlen) ? $max_amount_strlen = $amount_strlen : $amount_strlen = $amount_strlen;

            # Calculation for Max Length String for Category
            $category_strlen = strlen($category_name);
            ($category_strlen > $max_category_strlen) ? $max_category_strlen = $category_strlen : $category_strlen = $category_strlen;

        }

        for ($i = 0; $i <= $category_strlen; $i++) {

            echo "=";

        }

        echo "\t";

        (float) $total_formatted = ynab_format($total);

        (strlen($total_formatted) > $max_amount_strlen) ? $max_amount_strlen = strlen($total_formatted) : $max_amount_strlen = $max_amount_strlen; 

        for ($i = 0; $i <= $max_amount_strlen; $i++) {
            
            echo "=";

        }

        $diff = number_of_months($oldest_parsed_date, $recent_parsed_date);
        $per_month = ynab_format(($total / $diff));

        echo "\n";
        echo "TOTAL:" . "\t" . "$" . $total_formatted . "\n";
        if ($report_name !== "Networth Report") {

            echo "# of Months: " . $diff . "\n";
            echo "Per Month: " . "$"  . $per_month . "\n";

        }
        echo "\n";

    }
