#!/usr/bin/env python3
"""Fix duplicate delete_bit line in Customers_model.php"""
import re

filepath = "application/models/Customers_model.php"
with open(filepath, "r") as f:
    content = f.read()

# Remove the duplicate delete_bit line that appears after gift_card_balance
# We want to keep the first delete_bit (after tot_advance) and remove the second one (after gift_card_balance)
lines = content.splitlines()
new_lines = []
skip_next = False
for i, line in enumerate(lines):
    if skip_next:
        skip_next = False
        continue
    # Look for the pattern where delete_bit is followed by loyalty_points (keep it)
    # and where gift_card_balance is followed by delete_bit (remove the delete_bit)
    if '$json_arr["gift_card_balance"]' in line and i + 1 < len(lines) and '$json_arr["delete_bit"]' in lines[i + 1]:
        new_lines.append(line)
        skip_next = True  # Skip the duplicate delete_bit line
        continue
    new_lines.append(line)

new_content = "\n".join(new_lines)
with open(filepath, "w") as f:
    f.write(new_content)

print("Fixed duplicate delete_bit in Customers_model.php")
