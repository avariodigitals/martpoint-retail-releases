#!/usr/bin/env python3
import re

filepath = "/Users/ralphmore/Sites/localhost/martpoint retail/application/models/Customers_model.php"

with open(filepath, "r") as f:
    content = f.read()

# Replace remaining three lines
content = content.replace('$json_arr["loyalty_tier"] 				 = $res->loyalty_tier;', 
                          '$json_arr["loyalty_tier"] 				 = property_exists($res, "loyalty_tier") ? $res->loyalty_tier : "";')
content = content.replace('$json_arr["store_credit_balance"] 	 = $res->store_credit_balance;', 
                          '$json_arr["store_credit_balance"] 	 = property_exists($res, "store_credit_balance") ? $res->store_credit_balance : 0;')
content = content.replace('$json_arr["gift_card_balance"] 		 = $res->gift_card_balance;', 
                          '$json_arr["gift_card_balance"] 		 = property_exists($res, "gift_card_balance") ? $res->gift_card_balance : 0;')

with open(filepath, "w") as f:
    f.write(content)

print("Fixed Customers_model.php")
