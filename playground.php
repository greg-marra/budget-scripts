<?php

    $base_dir = $_SERVER['HOME'];
    require ($base_dir . '/Documents/ynab_vars.php');
    require ($base_dir . '/Documents/ynab/ynab_functions.php');

    # Endpoint to grab list of category
    $endpoint = "/$BUDGET_ID/transactions";

    curl_setopt($ch, CURLOPT_URL, $base . $endpoint);
    $array = json_decode(curl_exec($ch), true);
    var_dump($array);

    echo "\n" . $base . $endpoint . "\n";
