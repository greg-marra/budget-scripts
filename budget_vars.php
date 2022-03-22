<?php

    # API Token
    $budget_TOKEN = '';

    # FSA Category ID
    $fsa_id = "";

    # Wage Payee ID's
    $wage_ids = array(


    );

    # The directory, in the user's home directory, that budget_functions.php is in. Include the leading /
    $functions_directory = "";

    # sets php Date formating to pull in budgets date
    $budget_DATE_FORMAT = 'Y-m-d';

    # sets php output of currency amounts. Change based on
    # prefernce or currency
    (int) $US_NUMBER_OF_DECIMALS = (int) 2;
    $US_DECIMAL_FORMAT = ".";
    $US_THOUSANDS_FORMAT = ",";

    # budget Budget ID (same for all)
    $BUDGET_ID = "";

    # Account IDs
    # Savings account ID for Savings Script
    $SAVINGS_ACCOUNT_ID = "";

    # Interest Report Category IDs
    # Any Interest (earned or paid) transactions
    # gets their own category
    $INTEREST_PAYEE_ID = "";

    # Credit Card Rewards Report Category IDs
    # Any Credit Card Rewards
    # get their own category
    $REWARDS_PAYEE_ID = "";

    # Amazon Payee ID
    # for amazon report
    $AMAZON_PAYEE_ID = "";

    # Uncommon Grounds Payee ID
    # For Uncommon Grounds Report
    # Uncommon Grounds is a
    # Local Coffee shop
    $UC_PAYEE_ID = ""; 

    # Target IDs
    # Both Payee and Account ID
    # are required for spending
    # and rewards report
    $TARGET_PAYEE_ID = ""; 
    $TARGET_ACCOUNT_ID = ""; 

    # Investment Contributions Account ID
    $ETRADE_ACCOUNT_ID = "";

    # Investment Contributions Transfer Payee ID
    $ETRADE_TRANSFER_PAYEE_ID = "";

    # Fastfood Account IDs
    $FASTFOOD_PAYEE_IDS = array(
        
        "Name Of FastFood Place" => "budget_Account_ID",
        "Name Of FastFood Place" => "budget_Account_ID",
        
    );

    # Retirement Account IDs
    $RETIREMENT_IDS = array(

        "Name of Retirement Account" => "budget_Account_ID",
        "Name of Retirement Account" => "budget_Account_ID",
    );

    # Savings Monthly Report Categories
    $CATEGORY_IDS = array(

        "Name of Category" => "budget_Category_ID",
        "Name of Category" => "budget_Category_ID",

    );

    # These Accounts are EXCLUDED from
    # Networth Report
    # Incase you use budget
    # to Track non-financial items
    $NETWORTH_ACCTS = array(

        "budget_Account_ID", # Note about name of account
        "budget_Account_ID", # These are my PTO hours

    );

    # Savings Balance Report Category IDs
    $BALANCE_IDS = array(

        "Name" => "budget_Category_ID",
        "Name" => "budget_Category_ID",

    );

    $flags = array(

        "blue" => "",
        "orange" => "",
        "red" => "",
        "green" => "",
        "purple" => "",
        "yellow" => "",

    );
