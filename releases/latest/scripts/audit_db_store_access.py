#!/usr/bin/env python3
"""
Audit db_store and modular store settings access in the application folder.

Run:
    python3 /Users/ralphmore/Herd/martpointretailapp/audit_db_store_access.py

The report distinguishes:
  - Core db_store reads (expected)
  - Modular settings reads (expected)
  - Legacy reads requiring future refactor
  - Runtime schema violations (must be zero)
"""

import os
import re

APP = "/Users/ralphmore/Herd/martpointretailapp/application"
EXCLUDE_DIRS = {"logs", "cache", "views/errors", "migrations"}

# Legacy update controller that performs old version-by-version ALTER TABLE
# migrations. These are excluded from the active runtime schema violation count
# but reported separately so they can be migrated into SQL files later.
LEGACY_UPDATE_CONTROLLER = "controllers/Updates.php"

CORE_IDENTITY_COLUMNS = {
    "id", "store_code", "store_name", "mobile", "phone", "email", "country",
    "state", "city", "address", "postcode", "currency_id", "currency_placement",
    "timezone", "date_format", "time_format", "created_date", "created_time",
    "created_by", "system_ip", "system_name", "status", "user_id",
    "current_subscriptionlist_id", "package_id", "expire_date", "deleted",
    "location_lat", "location_lng",
}

MODULAR_COLUMNS = {
    # inventory
    "category_init", "item_init", "supplier_init", "purchase_init", "purchase_return_init",
    "customer_init", "sales_init", "sales_return_init", "expense_init", "quotation_init",
    "money_transfer_init", "accounts_init", "sales_payment_init", "sales_return_payment_init",
    "purchase_payment_init", "purchase_return_payment_init", "expense_payment_init", "cust_advance_init",
    # receipt
    "invoice_view", "sales_invoice_format_id", "pos_invoice_format_id",
    "sales_invoice_footer_text", "invoice_terms", "previous_balance_bit", "round_off",
    "change_return", "decimals", "qty_decimals", "t_and_c_status", "t_and_c_status_pos",
    "number_to_words",
    # pos
    "sales_discount", "mrp_column", "show_signature", "default_account_id", "cash_account_id",
    # notification
    "sms_status", "language_id", "smtp_host", "smtp_port", "smtp_user", "smtp_pass",
    "smtp_status", "e_invoice_enabled",
    # tax
    "gst_no", "vat_no", "pan_no", "tin_no", "tax_number",
    # theme
    "store_logo", "signature", "theme_key", "primary_color", "secondary_color",
    # storefront
    "store_website", "storefront_theme_key", "store_description", "store_banner",
    # payment
    "bank_details", "payment_preferences_json",
    # industry / business profile
    "industry_type", "business_model", "feature_flags_json", "workflow_template_key",
    "dashboard_template_key", "label_overrides_json", "industry_settings_json",
    # settings
    "nin_api_enabled", "nin_api_url", "nin_api_key", "nin_api_provider",
}

MODULAR_TABLE_PREFIX = "db_store_"
MODULAR_TABLES = {
    "db_store_settings", "db_store_inventory_settings", "db_store_receipt_settings",
    "db_store_pos_settings", "db_store_notification_settings", "db_store_theme_settings",
    "db_store_tax_settings", "db_store_payment_settings", "db_store_storefront_settings",
    "db_store_business_profile", "db_store_industry_settings",
}


def is_comment(line):
    text = line.strip()
    return text.startswith("//") or text.startswith("*") or text.startswith("/*")


def classify_db_store(line):
    """Classify a single line of PHP code that touches db_store."""
    text = line.strip()
    if is_comment(text):
        return "comment"
    if re.search(r'->query\(\s*[\'"]\s*SELECT\s+\*\s+FROM\s+db_store', text, re.I):
        return "legacy_select_star"
    if re.search(r'->query\(\s*[\'"]\s*SELECT\s+[^\'"]+\s+FROM\s+db_store', text, re.I):
        cols = re.findall(r"[a-z_][a-z0-9_]*", text.lower())
        if any(c in MODULAR_COLUMNS for c in cols):
            return "legacy_modular_read"
        return "core_read"
    if re.search(r'->get\(\s*[\'"]db_store[\'"]', text):
        if re.search(r'->select\s*\(', text):
            return "core_or_legacy_read"
        return "core_read"
    if re.search(r'->update\(\s*[\'"]db_store[\'"]', text):
        return "update"
    if re.search(r'->insert\(\s*[\'"]db_store[\'"]', text):
        return "insert"
    if re.search(r'->set\s*\(', text) and "db_store" in text:
        return "update"
    if re.search(r'->where\(\s*[\'"]id[\'"]', text) and "db_store" in text:
        return "core_read"
    if "db_store" in text:
        return "other"
    return None


def classify_modular_table(line):
    """Classify a line that touches a modular db_store_*_settings table."""
    text = line.strip()
    if is_comment(text):
        return None
    # Detect which modular table is referenced
    table = None
    for t in MODULAR_TABLES:
        if t in text:
            table = t
            break
    if not table:
        return None
    # Read operations
    if re.search(r'->query\(\s*[\'"]\s*SELECT', text, re.I):
        return "modular_read"
    if re.search(r'->get\(\s*[\'"]' + re.escape(table) + r'[\'"]', text):
        return "modular_read"
    if re.search(r'->where\(', text) and re.search(r'->(get|select|row|result)', text):
        return "modular_read"
    # Write operations
    if re.search(r'->update\(\s*[\'"]' + re.escape(table) + r'[\'"]', text):
        return "modular_write"
    if re.search(r'->insert\(\s*[\'"]' + re.escape(table) + r'[\'"]', text):
        return "modular_write"
    if re.search(r'->set\s*\(', text) and table in text:
        return "modular_write"
    if table in text:
        return "modular_other"
    return None


def detect_runtime_schema_violation(line):
    """Return a violation type if the line is a runtime schema mutation."""
    text = line.strip()
    if is_comment(text):
        return None
    # SHOW CREATE TABLE is inspection, not mutation
    if re.search(r'SHOW\s+CREATE\s+TABLE', text, re.I):
        return None
    # Reading the 'Create Table' / 'Create View' keys from SHOW CREATE TABLE result
    if re.search(r"\['Create Table'\]|\['Create View'\]", text, re.I):
        return None
    if re.search(r'ALTER\s+TABLE', text, re.I):
        return "runtime_alter_table"
    if re.search(r'CREATE\s+TABLE', text, re.I):
        return "runtime_create_table"
    if re.search(r'AUTO_INCREMENT\s*=\s*1', text, re.I):
        return "runtime_auto_increment_reset"
    return None


def walk_php_files():
    for root, dirs, files in os.walk(APP):
        dirs[:] = [d for d in dirs if d not in EXCLUDE_DIRS]
        for fname in files:
            if not fname.endswith(".php"):
                continue
            path = os.path.join(root, fname)
            rel = os.path.relpath(path, APP)
            try:
                with open(path, "r", encoding="utf-8", errors="ignore") as f:
                    lines = f.readlines()
            except Exception:
                continue
            yield rel, lines


def main():
    db_store_results = []
    modular_results = []
    violation_results = []
    legacy_violation_results = []

    for rel, lines in walk_php_files():
        for i, line in enumerate(lines, 1):
            # db_store access
            if "db_store" in line:
                cls = classify_db_store(line)
                if cls and cls != "comment":
                    db_store_results.append({
                        "file": rel, "line": i, "type": cls, "code": line.strip()
                    })

            # Modular settings table access
            modular_cls = classify_modular_table(line)
            if modular_cls:
                modular_results.append({
                    "file": rel, "line": i, "type": modular_cls, "code": line.strip()
                })

            # Runtime schema violations
            violation = detect_runtime_schema_violation(line)
            if violation:
                entry = {"file": rel, "line": i, "type": violation, "code": line.strip()}
                if rel == LEGACY_UPDATE_CONTROLLER:
                    legacy_violation_results.append(entry)
                else:
                    violation_results.append(entry)

    def print_table(title, rows):
        print(f"\n# {title}\n")
        print("| File | Line | Type | Code |")
        print("|------|------|------|------|")
        for r in rows:
            print(f"| {r['file']} | {r['line']} | {r['type']} | `{r['code'][:80]}` |")

    print_table("db_store Access", db_store_results)
    print_table("Modular Settings Access", modular_results)
    print_table("Active Runtime Schema Violations", violation_results)
    print_table("Legacy Update Controller Schema Mutations", legacy_violation_results)

    # Summary buckets
    core_reads = sum(1 for r in db_store_results if r["type"] in ("core_read", "core_or_legacy_read"))
    legacy_reads = sum(1 for r in db_store_results if r["type"] in (
        "legacy_select_star", "legacy_modular_read", "other", "update", "insert"
    ))
    modular_reads = sum(1 for r in modular_results if r["type"] == "modular_read")
    modular_writes = sum(1 for r in modular_results if r["type"] == "modular_write")
    active_violations = len(violation_results)
    legacy_violations = len(legacy_violation_results)

    print("\n## Summary")
    print(f"- **Core db_store reads (expected)**: {core_reads}")
    print(f"- **Modular settings reads (expected)**: {modular_reads}")
    print(f"- **Modular settings writes (expected)**: {modular_writes}")
    print(f"- **Legacy db_store reads requiring future refactor**: {legacy_reads}")
    print(f"- **Runtime schema violations (active application code)**: {active_violations}")
    if active_violations == 0:
        print("  - OK: zero active runtime schema violations.")
    print(f"- **Legacy update controller schema mutations (separate migration work)**: {legacy_violations}")

    # Exit non-zero if active violations are present
    if active_violations > 0:
        print("\n**ERROR: Active runtime schema violations found. They must be zero.**")
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
