#!/bin/bash
budgetid=$(<budgetid.txt)
token=$(<token.txt)
date=$(date +"%Y%m%d")
budgetfile="$date-budget.json"
if [ ! -e "$budgetfile.xz" ]
then
curl --silent --output "$budgetfile" -H "Authorization: Bearer $token" "https://api.youneedabudget.com/v1/budgets/$budgetid"
sleep 1
xz "$budgetfile"
fi
