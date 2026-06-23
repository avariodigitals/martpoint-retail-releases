<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MartPoint Knowledge Base</title>
<style>
  :root { --accent: #1a237e; --accent-light: #3949ab; --bg: #f8f9fa; --card: #fff; --text: #333; --muted: #666; --border: #e0e0e0; }
  * { box-sizing: border-box; }
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; background: var(--bg); color: var(--text); line-height: 1.6; }
  .container { max-width: 900px; margin: 0 auto; padding: 24px; }
  header { background: linear-gradient(135deg, var(--accent), var(--accent-light)); color: #fff; padding: 40px 24px; text-align: center; }
  header h1 { margin: 0 0 8px; font-size: 2em; }
  header p { margin: 0; opacity: 0.9; }
  .role-badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 14px; border-radius: 20px; font-size: 0.85em; margin-top: 12px; }
  nav { position: sticky; top: 0; background: var(--card); border-bottom: 1px solid var(--border); padding: 12px 24px; overflow-x: auto; white-space: nowrap; z-index: 10; }
  nav a { display: inline-block; padding: 8px 16px; margin-right: 6px; color: var(--accent); text-decoration: none; border-radius: 6px; font-size: 0.92em; font-weight: 500; }
  nav a:hover { background: #e8eaf6; }
  nav a.active { background: var(--accent); color: #fff; }
  section { background: var(--card); margin: 24px 0; padding: 28px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
  section h2 { color: var(--accent); margin-top: 0; padding-bottom: 10px; border-bottom: 2px solid var(--border); }
  section h3 { color: var(--accent-light); margin-top: 28px; }
  section h4 { color: var(--text); margin-top: 20px; }
  section p { margin: 10px 0; }
  section ol, section ul { margin: 10px 0; padding-left: 24px; }
  section li { margin: 6px 0; }
  section strong { color: var(--accent); }
  section code { background: #eceff1; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
  section blockquote { border-left: 4px solid var(--accent); margin: 16px 0; padding: 10px 16px; background: #e8eaf6; border-radius: 0 6px 6px 0; }
  table { width: 100%; border-collapse: collapse; margin: 16px 0; }
  th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border); }
  th { background: #e8eaf6; color: var(--accent); font-weight: 600; }
  tr:hover { background: #f5f5f5; }
  .hidden { display: none !important; }
  footer { text-align: center; padding: 40px 24px; color: var(--muted); font-size: 0.9em; }
  @media print { nav, header .role-badge { display: none; } section { box-shadow: none; border: 1px solid var(--border); } }
  @media (max-width: 600px) { header h1 { font-size: 1.4em; } section { padding: 18px; } }
</style>
</head>
<body>

<header>
  <h1>MartPoint Knowledge Base</h1>
  <p>Your complete guide to using MartPoint Retail</p>
  <div class="role-badge">Role: <?= htmlspecialchars($role_label ?? 'User') ?></div>
</header>

<nav id="kb-nav">
  <a href="#getting-started" class="active">Getting Started</a>
  <a href="#cashier-guide">Cashier Guide</a>
  <a href="#sales-pos">Sales & POS</a>
  <a href="#inventory-management">Inventory</a>
  <a href="#purchases">Purchases</a>
  <a href="#customers">Customers</a>
  <a href="#accounts-expenses">Accounts</a>
  <a href="#reports">Reports</a>
  <a href="#settings">Settings</a>
</nav>

<div class="container">

<?php if ($show_cashier): ?>
<section id="getting-started">
  <h2>Getting Started</h2>
  <h3>Logging In</h3>
  <ol>
    <li>Open your browser and go to your MartPoint URL.</li>
    <li>Enter your <strong>username</strong> and <strong>password</strong>.</li>
    <li>Click <strong>Sign In</strong>.</li>
  </ol>
  <h3>Dashboard Overview</h3>
  <p>The Dashboard shows:</p>
  <ul>
    <li><strong>Today's Sales</strong> — total sales for today</li>
    <li><strong>Low Stock Alerts</strong> — items running low</li>
    <li><strong>Recent Transactions</strong> — latest sales and purchases</li>
    <li><strong>Quick Stats</strong> — customers, items, invoices count</li>
  </ul>
  <h3>User Roles</h3>
  <ul>
    <li><strong>Cashier</strong> — can make sales, check stock, find customers, record payments</li>
    <li><strong>Business Owner / Manager</strong> — can manage inventory, purchases, accounts, reports, and settings</li>
    <li><strong>Admin</strong> — full system access</li>
  </ul>
</section>
<?php endif; ?>

<?php if ($show_cashier): ?>
<section id="cashier-guide">
  <h2>Cashier Guide</h2>
  <p>This section is for cashiers and front-desk staff. It focuses on daily tasks.</p>

  <h3>Making a Sale at POS</h3>
  <ol>
    <li><strong>Open POS</strong>: Click <strong>Sales → POS</strong> from the left sidebar.</li>
    <li><strong>Select a Customer</strong>: Click the customer search box and type a name or phone number. Or select <strong>Walk-in Customer</strong> for one-time buyers.</li>
    <li><strong>Add Items to Cart</strong>: Type the item name in the search box and press <strong>Enter</strong>. Or scan the barcode. Adjust quantity with <strong>+</strong> or <strong>-</strong>.</li>
    <li><strong>Apply Discount</strong> (if allowed): Click the discount field and enter a percentage or fixed amount.</li>
    <li><strong>Choose Payment Method</strong>: <strong>Cash</strong> — enter amount received; change is calculated. <strong>Card</strong> — enter card details. <strong>Transfer</strong> — record bank transfer. <strong>Credit</strong> — only for approved credit customers.</li>
    <li><strong>Complete Sale</strong>: Click <strong>Save</strong> or press <strong>F10</strong>.</li>
    <li><strong>Print Receipt</strong>: Prints automatically if a printer is configured.</li>
  </ol>
  <p><strong>Keyboard Shortcuts</strong>: F2 = item search, F4 = customer search, F10 = save/checkout, Esc = clear cart.</p>

  <h3>Handling Returns</h3>
  <ol>
    <li>Go to <strong>Sales → Sales Return</strong>.</li>
    <li>Find the original invoice by invoice number or customer name.</li>
    <li>Select the items being returned and enter the quantity.</li>
    <li>Choose the refund method: <strong>Cash back</strong> or <strong>Store credit</strong>.</li>
    <li>Click <strong>Save</strong>. Stock is automatically restored.</li>
  </ol>

  <h3>Finding a Customer</h3>
  <ol>
    <li><strong>Quick Search</strong>: Ask Azera — type "Find customer John".</li>
    <li><strong>Customer List</strong>: Go to <strong>Customers → Customers List</strong> and use the search box.</li>
    <li>Click on a customer to see their full purchase history and outstanding balance.</li>
  </ol>

  <h3>Checking Stock</h3>
  <ol>
    <li><strong>Ask Azera</strong>: Type "How many [item name] do we have?"</li>
    <li><strong>Items List</strong>: Go to <strong>Items → Items List</strong> to browse all stock.</li>
    <li><strong>Low Stock Alert</strong>: Go to <strong>Items → Low Stock</strong> to see items that need reordering.</li>
  </ol>

  <h3>Recording a Payment</h3>
  <ol>
    <li><strong>From Invoice</strong>: Open any unpaid invoice and click <strong>Receive Payment</strong>.</li>
    <li><strong>From Customer</strong>: Go to <strong>Customers → Customer Payments</strong>, select the customer.</li>
    <li>Enter the amount, payment mode, and date. Click <strong>Save</strong>.</li>
  </ol>

  <h3>Reprinting an Invoice</h3>
  <ol>
    <li>Go to <strong>Sales → Sales List</strong>.</li>
    <li>Find the invoice using the search box or date filter.</li>
    <li>Click the <strong>Print</strong> icon. Choose to print, download as PDF, or email.</li>
  </ol>

  <h3>Split Payment</h3>
  <p>When a customer pays with multiple methods (e.g., part cash, part card):</p>
  <ol>
    <li>At checkout, click <strong>Split Payment</strong>.</li>
    <li>Enter the amount for each payment method.</li>
    <li>The total must equal the invoice amount. Click <strong>Save</strong>.</li>
  </ol>
</section>
<?php endif; ?>

<?php if ($show_manager): ?>
<section id="sales-pos">
  <h2>Sales & POS</h2>

  <h3>Creating a Sale (Back Office)</h3>
  <ol>
    <li>Go to <strong>Sales → New Sale</strong>.</li>
    <li>Select the customer and branch.</li>
    <li>Add items by searching or scanning.</li>
    <li>Set quantities, discounts, and tax.</li>
    <li>Choose payment mode and click <strong>Save</strong>.</li>
  </ol>

  <h3>Sales Invoice Formats</h3>
  <p>MartPoint supports multiple layouts: <strong>Standard Invoice</strong> (full A4), <strong>POS Receipt</strong> (thermal), and <strong>GST Invoice</strong> (with tax breakdown). Change the default at <strong>Settings → Invoice Settings</strong>.</p>

  <h3>Credit Sales</h3>
  <p>Some customers are allowed to buy on credit. At checkout, select <strong>Credit</strong> as the payment mode. The sale is recorded with "Unpaid" status. Later, record a payment against this invoice to settle it. Only customers with a credit limit can use this feature.</p>
</section>
<?php endif; ?>

<?php if ($show_manager): ?>
<section id="inventory-management">
  <h2>Inventory Management</h2>

  <h3>Understanding Branches</h3>
  <p>A <strong>Branch</strong> is a physical location where stock is stored. Each branch has its own stock levels. You can transfer stock between branches.</p>

  <h3>Viewing Items</h3>
  <ol>
    <li>Go to <strong>Items → Items List</strong>.</li>
    <li>Use the search box to find items by name, code, or category.</li>
    <li>Columns show: item code, name, category, brand, stock, price, and status.</li>
    <li>Click <strong>Export</strong> to download as CSV or PDF.</li>
  </ol>

  <h3>Creating a New Item</h3>
  <ol>
    <li>Go to <strong>Items → New Item</strong>.</li>
    <li>Fill in details: Item Name (required), Item Code / Barcode, Category, Brand, Purchase Price, Sales Price, Stock, Alert Quantity, Tax, Branch, and Expiry Date (optional).</li>
    <li>Click <strong>Save</strong>.</li>
  </ol>

  <h3>Editing and Deleting Items</h3>
  <p>Go to <strong>Items → Items List</strong>. Find the item and click the <strong>Edit</strong> (pencil) or <strong>Delete</strong> (trash) icon. Deleting an item removes it from the catalog but does not affect past sales records.</p>

  <h3>Stock Transfer Between Branches</h3>
  <ol>
    <li>Go to <strong>Items → Stock Transfer</strong>.</li>
    <li>Click <strong>New Transfer</strong>.</li>
    <li>Select <strong>From Branch</strong> and <strong>To Branch</strong>.</li>
    <li>Add items and quantities. Click <strong>Save</strong>.</li>
  </ol>

  <h3>Low Stock Alerts</h3>
  <p>Go to <strong>Items → Low Stock</strong> to see all items where current stock is below the alert quantity. Click on an item to quickly create a purchase order.</p>

  <h3>Barcode Printing</h3>
  <p>Go to <strong>Items → Print Barcode/Labels</strong>. Select items, choose label size, and click <strong>Print</strong>.</p>

  <h3>Importing Items (CSV)</h3>
  <ol>
    <li>Go to <strong>Items → Import Items</strong>.</li>
    <li>Download the <strong>sample CSV template</strong>.</li>
    <li>Fill in your data and upload the CSV.</li>
    <li>Map the columns and click <strong>Import</strong>.</li>
  </ol>
</section>
<?php endif; ?>

<?php if ($show_manager): ?>
<section id="purchases">
  <h2>Purchases</h2>

  <h3>Creating a Purchase Order</h3>
  <ol>
    <li>Go to <strong>Purchases → New Purchase</strong>.</li>
    <li>Select the <strong>Supplier</strong> and <strong>Branch</strong>.</li>
    <li>Add items by searching or scanning. Enter quantity and purchase price.</li>
    <li>Set discount, other charges, and tax if applicable.</li>
    <li>Choose payment mode: <strong>Cash/Card/Transfer</strong> (paid now) or <strong>Credit</strong> (pay later).</li>
    <li>Click <strong>Save</strong>. Stock is automatically added to the selected branch.</li>
  </ol>

  <h3>Receiving Purchase</h3>
  <p>When stock arrives, go to <strong>Purchases → Purchase List</strong>, find the order, click <strong>Edit</strong>, change status to <strong>Received</strong>, and save.</p>

  <h3>Purchase Return</h3>
  <p>Go to <strong>Purchases → Purchase Return</strong>. Select the original invoice, choose items and quantities, enter the reason, and click <strong>Save</strong>.</p>

  <h3>Suppliers</h3>
  <p><strong>Add New Supplier</strong>: Go to <strong>Suppliers → New Supplier</strong>. Enter name, phone, tax number, opening balance, and address. <strong>View Payables</strong>: Go to <strong>Suppliers → Suppliers List</strong> and check the <strong>Purchase Due</strong> column.</p>
</section>
<?php endif; ?>

<?php if ($show_manager): ?>
<section id="customers">
  <h2>Customers</h2>

  <h3>Adding a New Customer</h3>
  <ol>
    <li>Go to <strong>Customers → New Customer</strong>.</li>
    <li>Enter: Name (required), Phone (required), Email, Tax Number, Credit Limit, and Shipping Address.</li>
    <li>Click <strong>Save</strong>.</li>
  </ol>

  <h3>Customer Groups</h3>
  <p>Organize customers into groups for discounts. Go to <strong>Customers → Customer Groups</strong>. Create groups like "VIP" or "Wholesale" with discount percentages. Assign customers to groups during creation.</p>

  <h3>Customer Payments</h3>
  <p>Go to <strong>Customers → Customer Payments</strong>. Select the customer, enter amount and mode, allocate to invoices, and save.</p>

  <h3>Customer Advance Payments</h3>
  <p>Customers can pay in advance at <strong>Customers → Customer Advance</strong>. The advance balance can be used during future sales.</p>

  <h3>Debt Reminders</h3>
  <p>Go to <strong>Customers → Debt Reminder</strong>. Select customers or filter by overdue days. Choose <strong>SMS</strong> or <strong>Email</strong>, customize the message, and send.</p>
</section>
<?php endif; ?>

<?php if ($show_manager): ?>
<section id="accounts-expenses">
  <h2>Accounts & Expenses</h2>

  <h3>Chart of Accounts</h3>
  <ul>
    <li><strong>Assets</strong> — Cash, Bank, Inventory, Equipment</li>
    <li><strong>Liabilities</strong> — Loans, Payables</li>
    <li><strong>Income</strong> — Sales, Services</li>
    <li><strong>Expenses</strong> — Rent, Salaries, Utilities, Supplies</li>
  </ul>

  <h3>Recording an Expense</h3>
  <ol>
    <li>Go to <strong>Accounts → New Expense</strong>.</li>
    <li>Select the <strong>Expense Category</strong> (e.g., Rent, Salary).</li>
    <li>Enter the amount and date.</li>
    <li>Choose the payment account (Cash or Bank).</li>
    <li>Add a note or upload a receipt image.</li>
    <li>Click <strong>Save</strong>.</li>
  </ol>

  <h3>Viewing the Ledger</h3>
  <p>Go to <strong>Accounts → Accounts List</strong>. See all accounts with balances. Click on an account to see all transactions.</p>

  <h3>Money Transfer</h3>
  <p>Transfer money between accounts at <strong>Accounts → Money Transfer</strong>. Select From/To accounts, enter amount and date, and save.</p>

  <h3>Cash Transactions</h3>
  <p>Go to <strong>Accounts → Cash Transactions</strong>. Record cash in (sales, deposits) and cash out (expenses, withdrawals) to reconcile the physical cash drawer.</p>
</section>
<?php endif; ?>

<?php if ($show_manager): ?>
<section id="reports">
  <h2>Reports</h2>

  <h3>Today's Sales Summary</h3>
  <p>Go to <strong>Reports → Today's Sales</strong>. See total invoices, revenue, payment breakdown, and top selling items.</p>

  <h3>Sales Report</h3>
  <p>Go to <strong>Reports → Sales Report</strong>. Filter by date range, branch, customer, or item. View detailed sales with profit margins. Export to PDF or Excel.</p>

  <h3>Purchase Report</h3>
  <p>Go to <strong>Reports → Purchase Report</strong>. Filter by date, supplier, or branch. See total purchases and costs.</p>

  <h3>Stock Report</h3>
  <p>Go to <strong>Reports → Stock Report</strong>. See current stock levels across all branches. Filter by category, brand, or branch.</p>

  <h3>Payment Report</h3>
  <p>Go to <strong>Reports → Payment Report</strong>. See all payments received and made. Filter by date, customer, or payment mode.</p>

  <h3>Expense Report</h3>
  <p>Go to <strong>Reports → Expense Report</strong>. See all expenses by category. Filter by date range and expense type.</p>

  <h3>Customer Orders / Debts Report</h3>
  <p>Go to <strong>Reports → Customer Orders</strong>. See all unpaid invoices grouped by customer. Filter by days overdue.</p>
</section>
<?php endif; ?>

<?php if ($show_manager): ?>
<section id="settings">
  <h2>Settings</h2>

  <h3>Store Profile</h3>
  <p>Go to <strong>Settings → Store Profile</strong>. Update Store Name, Phone, Email, Address, Logo, and Currency.</p>

  <h3>Invoice Settings</h3>
  <p>Go to <strong>Settings → Invoice Settings</strong>. Configure invoice prefix, footer text, show/hide fields, and round-off option.</p>

  <h3>Tax Settings</h3>
  <p>Go to <strong>Settings → Tax</strong>. Add tax rates applicable in your region and assign them to items.</p>

  <h3>Payment Modes</h3>
  <p>Go to <strong>Settings → Payment Types</strong>. Add custom payment methods or enable/disable existing ones.</p>

  <h3>Email & SMS Settings</h3>
  <p>Go to <strong>Settings → Email / SMS</strong>. Configure SMTP and SMS gateway. Test before using.</p>

  <h3>User Management</h3>
  <p>Go to <strong>Settings → Users</strong>. Create new users and assign roles. Roles control access to menus and actions.</p>

  <h3>Change Password</h3>
  <p>Click your profile name in the top right, select <strong>Change Password</strong>, enter current and new password, and save.</p>
</section>
<?php endif; ?>

<section id="faq">
  <h2>Frequently Asked Questions</h2>

  <p><strong>Q: Can I use MartPoint without internet?</strong><br>A: MartPoint requires internet for real-time sync. Some features work with cached data.</p>

  <p><strong>Q: How do I reset my password?</strong><br>A: Click <strong>Forgot Password</strong> on the login screen, or ask your admin to reset it from <strong>Settings → Users</strong>.</p>

  <p><strong>Q: Can I have multiple branches?</strong><br>A: Yes. Each branch has its own stock. You can transfer stock between branches and view branch-specific reports.</p>

  <p><strong>Q: How do I back up my data?</strong><br>A: Go to <strong>Settings → Backup</strong>. Download a full database backup daily.</p>

  <p><strong>Q: What happens if I delete an item?</strong><br>A: The item is removed from the catalog but past sales records remain intact.</p>

  <p><strong>Q: How do I add a new tax rate?</strong><br>A: Go to <strong>Settings → Tax</strong>, click <strong>New Tax</strong>, enter name and percentage, and save.</p>

  <p><strong>Q: Can customers buy online?</strong><br>A: Yes, if the Online Store feature is enabled. Customers can browse your storefront and place orders.</p>

  <p><strong>Q: How do I see what a cashier sold today?</strong><br>A: Go to <strong>Reports → Today's Sales</strong> and filter by the cashier's username.</p>
</section>

<section id="quick-reference">
  <h2>Quick Reference</h2>
  <table>
    <tr><th>Task</th><th>Menu Path</th><th>Shortcut</th></tr>
    <tr><td>Make a sale</td><td>Sales → POS</td><td>F2, F10</td></tr>
    <tr><td>Check stock</td><td>Items → Items List</td><td>Ask Azera</td></tr>
    <tr><td>Find customer</td><td>Customers → Customers List</td><td>Ask Azera</td></tr>
    <tr><td>Record payment</td><td>Customers → Customer Payments</td><td>—</td></tr>
    <tr><td>Add new item</td><td>Items → New Item</td><td>—</td></tr>
    <tr><td>Create purchase</td><td>Purchases → New Purchase</td><td>—</td></tr>
    <tr><td>View reports</td><td>Reports → (choose report)</td><td>—</td></tr>
    <tr><td>Print barcode</td><td>Items → Print Barcode</td><td>—</td></tr>
    <tr><td>Stock transfer</td><td>Items → Stock Transfer</td><td>—</td></tr>
    <tr><td>Record expense</td><td>Accounts → New Expense</td><td>—</td></tr>
  </table>
</section>

</div>

<footer>
  <p>MartPoint Retail — Knowledge Base &nbsp;|&nbsp; Last updated: June 2026</p>
  <p>For further help, use the Azera AI assistant or contact support.</p>
</footer>

<script>
  // Active nav link on scroll
  var sections = document.querySelectorAll('section[id]');
  var navLinks = document.querySelectorAll('nav a');
  window.addEventListener('scroll', function(){
    var current = '';
    sections.forEach(function(section){
      var sectionTop = section.offsetTop;
      if(pageYOffset >= sectionTop - 80){ current = section.getAttribute('id'); }
    });
    navLinks.forEach(function(a){
      a.classList.remove('active');
      if(a.getAttribute('href') === '#'+current){ a.classList.add('active'); }
    });
  });
</script>

</body>
</html>
