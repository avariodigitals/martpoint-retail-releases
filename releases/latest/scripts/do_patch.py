import re

# 1. Patch Roles_model.php
with open('application/models/Roles_model.php', 'r') as f:
    content = f.read()

if "'loyalty_view'" not in content:
    content = content.replace(
        "                    'nin_logs',\n\n",
        "                    'nin_logs',\n\n                    'loyalty_view',\n                    'loyalty_add',\n                    'loyalty_edit',\n                    'loyalty_delete',\n                    'gift_cards_view',\n                    'gift_cards_add',\n                    'gift_cards_edit',\n                    'gift_cards_delete',\n                    'store_credit_view',\n                    'store_credit_add',\n                    'store_credit_edit',\n                    'store_credit_delete',\n\n"
    )
    with open('application/models/Roles_model.php', 'w') as f:
        f.write(content)
    print("Patched Roles_model.php")
else:
    print("Roles_model.php already has loyalty permissions")

# 2. Patch Customers_model.php
with open('application/models/Customers_model.php', 'r') as f:
    content = f.read()

# Add birthday to verify_and_save info array
if "'birthday'" not in content:
    content = content.replace(
        "                'credit_limit'         => \$credit_limit,\n              );",
        "                'credit_limit'         => \$credit_limit,\n                'birthday'             => (!empty(\$birthday)) ? \$birthday : NULL,\n              );",
        1  # Only replace first occurrence (verify_and_save)
    )
    print("Patched verify_and_save info array")

# Add birthday to update_customers info array
if "'birthday'" in content:
    # Check if update_customers has it
    update_section = content[content.find("public function update_customers()"):content.find("public function update_status")]
    if "'birthday'" not in update_section:
        # Add birthday variable in update_customers
        content = content.replace(
            "\t\t$shipping_location_link = \$this->input->post('shipping_location_link', TRUE);\n\n\t\tif(\$q_id==1)",
            "\t\t$shipping_location_link = \$this->input->post('shipping_location_link', TRUE);\n\t\t$birthday = \$this->input->post('birthday', TRUE);\n\n\t\tif(\$q_id==1)"
        )
        # Add to info array in update_customers - need to be careful here
        # The second occurrence of credit_limit in info array
        parts = content.split("                'credit_limit'         => empty(\$credit_limit) ? null : \$credit_limit,\n              );", 1)
        if len(parts) == 2:
            content = parts[0] + "                'credit_limit'         => empty(\$credit_limit) ? null : \$credit_limit,\n                'birthday'             => (!empty(\$birthday)) ? \$birthday : NULL,\n              );" + parts[1]
            print("Patched update_customers")
    else:
        print("update_customers already has birthday")

# Add loyalty JSON fields to getCustomersArray
if '"loyalty_points"' not in content:
    content = content.replace(
        '\t\t\t\t$json_arr["delete_bit"] \t\t\t\t = \$res->delete_bit;\n\t\t  \t\n\t\t  \tarray_push(\$display_json, \$json_arr);',
        '\t\t\t\t$json_arr["delete_bit"] \t\t\t\t = \$res->delete_bit;\n\t\t  \t\$json_arr["loyalty_points"] \t\t\t = \$res->loyalty_points;\n\t\t  \t\$json_arr["loyalty_tier"] \t\t\t = \$res->loyalty_tier;\n\t\t  \t\$json_arr["store_credit_balance"] \t = \$res->store_credit_balance;\n\t\t  \t\$json_arr["gift_card_balance"] \t\t = \$res->gift_card_balance;\n\t\t  \t\n\t\t  \tarray_push(\$display_json, \$json_arr);'
    )
    print("Patched getCustomersArray")

with open('application/models/Customers_model.php', 'w') as f:
    f.write(content)

print("Done")
