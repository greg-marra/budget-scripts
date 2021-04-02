<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . '/Documents/ynab/ynab_functions.php');
    $report_name = "Rewards Report";

    # Get latest date for budget in YNAB and set that in GET for category balances
    $settings = get_settings($ch, $base);
    $oldest_parsed_date = get_oldest_date($settings);
    $recent_parsed_date = get_recent_date($settings);

    # Endpoint to grab all transactions for 'Credit Card Cash Rewards'
    $endpoint = "/$BUDGET_ID/payees/$REWARDS_PAYEE_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $transactions = json_decode(curl_exec($ch), true);

    $yearly_totals = array();

    foreach ($transactions["data"]["transactions"] as $transaction) {

        $amount = round($transaction["amount"] / 1000, 2);
        $year = explode("-", $transaction["date"])[0];
        $account_name = $transaction["account_name"];

        if (array_key_exists($account_name, $yearly_totals)) {

            if (array_key_exists($year, $yearly_totals[$account_name])) {

                $yearly_totals[$account_name][$year] += $amount;

            } else {

                $yearly_totals[$account_name][$year] = $amount;

            }

        } else {

            $yearly_totals[$account_name] = array($year => $amount);

        }

    }

    # Endpoint to grab all transactions for 'Target Payee'
    $endpoint = "/$BUDGET_ID/payees/$TARGET_PAYEE_ID/transactions";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $transactions = json_decode(curl_exec($ch), true);

    $subtotal = 0;

    $account = "Target";

    foreach ($transactions["data"]["transactions"] as $transaction) {

        $reward = 0;
        $amount = -$transaction["amount"] / 1000;
        $year = explode("-", $transaction["date"])[0];
        $account_name = $transaction["account_name"];

        $remove_taxes = $amount / 1.07;
        $reward = round(($remove_taxes * .05),2);

        if (array_key_exists($account_name, $yearly_totals)) {

            if (array_key_exists($year, $yearly_totals[$account_name])) {

                $yearly_totals[$account_name][$year] += $reward;

            } else {

                $yearly_totals[$account_name][$year] = $reward;

            }

        } else {

            $yearly_totals[$account_name] = array($year => $reward);

        }

    }

var_dump($yearly_totals);exit;

#    print_totals($yearly_totals, $report_name, $oldest_parsed_date, $recent_parsed_date);
