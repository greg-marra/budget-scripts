<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . '/Documents/ynab/ynab_functions.php');

    # Endpoint to grab all transactions for 'Credit Card Cash Rewards'
    $endpoint = "/$BUDGET_ID";
    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);

    $backup_text = json_decode(curl_exec($ch), true);
    curl_close($ch);

    

    var_dump($backup_file);exit;
