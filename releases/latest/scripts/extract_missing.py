import re

backup_file = "backups/db_20260623_170906.sql"
output_file = "missing_tables.sql"

missing_tables = [
    "db_approval_logs", "db_approval_settings", "db_brevo",
    "db_debt_reminder_history", "db_debt_reminder_settings",
    "db_email_logs", "db_email_templates", "db_expiry_settings",
    "db_license_history", "db_license_otps",
    "db_online_order_items", "db_online_orders",
    "db_qr_codes", "db_report_schedules", "db_services",
    "db_storefront_analytics", "db_storefront_brands",
    "db_storefront_faqs", "db_storefront_instagram",
    "db_storefront_settings", "db_storefront_testimonials",
    "db_subscription_license"
]

with open(backup_file, 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

output = "SET FOREIGN_KEY_CHECKS = 0;\n\n"

for table in missing_tables:
    pattern = r"(DROP TABLE IF EXISTS `" + re.escape(table) + "`;\s*CREATE TABLE `" + re.escape(table) + "`.*?)(?=DROP TABLE IF EXISTS `[^`]+`;|$)"
    match = re.search(pattern, content, re.DOTALL)
    if match:
        block = match.group(1).strip()
        output += block + "\n\n"
        print(f"Extracted: {table}")
    else:
        print(f"WARNING: Could not extract {table}")

output += "SET FOREIGN_KEY_CHECKS = 1;\n"

with open(output_file, 'w', encoding='utf-8') as f:
    f.write(output)

print(f"\nDone! Saved to: {output_file}")
