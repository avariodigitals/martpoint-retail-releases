import re

path = '/Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php'
with open(path, 'r') as f:
    lines = f.readlines()

# Find menu boundaries
for i, line in enumerate(lines):
    if '<ul class="sidebar-menu">' in line:
        menu_start = i
    if menu_start is not None and '</ul>' in line and i > menu_start:
        menu_end = i
        break

# Dashboard is a single line right after the header comment
dashboard = lines[menu_start:menu_start+3]  # ul tag, header comment, dashboard li

# The rest starts from menu_start+3
rest = lines[menu_start+3:menu_end]
rest_text = ''.join(rest)

# Split into blocks using blank-line-separated chunks, then identify each by its starting text
blocks_raw = re.split(r'\n\n+', rest_text)

# We need to map each block to its menu name
block_map = {}
for block in blocks_raw:
    block = block.strip()
    if not block:
        continue
    if '<!-- Users -->' in block:
        block_map['users'] = block + '\n\n'
    elif '<!-- Attendance -->' in block:
        block_map['attendance'] = block + '\n\n'
    elif '<!-- STORE MANAGEMENT -->' in block:
        block_map['stores'] = block + '\n\n'
    elif 'sales_add' in block and 'sales_view' in block:
        block_map['sales'] = block + '\n\n'
    elif 'customers_add' in block and 'suppliers_add' in block:
        block_map['contacts'] = block + '\n\n'
    elif 'cust_adv_payments' in block:
        block_map['advance'] = block + '\n\n'
    elif 'discountCouponView' in block:
        block_map['coupons'] = block + '\n\n'
    elif 'quotation_add' in block:
        block_map['quotation'] = block + '\n\n'
    elif 'purchase_add' in block:
        block_map['purchase'] = block + '\n\n'
    elif 'accounts_add' in block:
        block_map['accounts'] = block + '\n\n'
    elif 'items_add' in block or 'services_add' in block:
        block_map['items'] = block + '\n\n'
    elif 'stock_adjustment' in block:
        block_map['stock'] = block + '\n\n'
    elif 'expense_add' in block:
        block_map['expenses'] = block + '\n\n'
    elif 'special_access()' in block:
        block_map['places'] = block + '\n\n'
    elif '<!-- SMS -->' in block:
        block_map['messaging'] = block + '\n\n'
    elif '<!--<li class="header">REPORTS</li>-->' in block:
        block_map['reports'] = block + '\n\n'
    elif 'BRANCH MANAGEMENT' in block:
        block_map['branch'] = block + '\n\n'
    elif 'Online Store' in block and 'online-store-active-li' in block:
        block_map['online_store'] = block + '\n\n'
    elif 'fa fa-gears' in block and 'settings' in block.lower():
        block_map['settings'] = block + '\n\n'

order = ['sales','quotation','purchase','contacts','items','stock','accounts','expenses','advance','coupons','reports','online_store','branch','stores','users','attendance','messaging','settings','places']

reordered = ''.join(dashboard)
for key in order:
    if key in block_map:
        reordered += block_map[key]

new_content = lines[:menu_start] + [reordered] + lines[menu_end+1:]

with open(path, 'w') as f:
    f.writelines(new_content)

print('Done')
