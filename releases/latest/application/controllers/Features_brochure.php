<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Features Brochure Controller
 * Generates a downloadable PDF of MartPoint features.
 * Usage: Visit /features_brochure/pdf in your browser
 */
class Features_brochure extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}

	public function index(){
		redirect('features_brochure/pdf');
	}

	public function pdf(){
		require_once APPPATH . 'libraries/tcpdf/tcpdf.php';

		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

		$pdf->SetCreator('MartPoint Retail');
		$pdf->SetAuthor('Avario Digitals');
		$pdf->SetTitle('MartPoint Retail — Complete Feature Overview');
		$pdf->SetSubject('MartPoint Retail Features');

		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->setFontSubsetting(true);

		// === COVER ===
		$pdf->AddPage();
		$pdf->SetFillColor(98, 0, 234);
		$pdf->Rect(0, 0, 210, 297, 'F');
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetFont('helvetica', 'B', 36);
		$pdf->SetY(80);
		$pdf->Cell(0, 20, 'MartPoint Retail', 0, 1, 'C');
		$pdf->SetFont('helvetica', '', 14);
		$pdf->Cell(0, 10, 'Everything your retail business needs to', 0, 1, 'C');
		$pdf->Cell(0, 10, 'sell smarter, manage better, and grow faster.', 0, 1, 'C');
		$pdf->SetY(180);
		$pdf->SetFont('helvetica', 'I', 11);
		$pdf->Cell(0, 10, 'Complete Feature Overview — ' . date('F Y'), 0, 1, 'C');
		$pdf->SetY(250);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->Cell(0, 10, 'Powered by Avario Digitals', 0, 1, 'C');

		// === MODULES ===
		$modules = [
			['💳 POS', 'Sell faster. Sell smarter.', [
				'Touch-Friendly POS Screen — Fast, responsive checkout designed for real-world retail speed.',
				'Barcode Scanning — Instant product lookup by barcode. No typing, no delays.',
				'Multi-Payment Support — Accept Cash, Bank Transfer, POS Terminal, and Credit sales.',
				'Sales Orders & Invoicing — Generate professional invoices with auto-numbering.',
				'Quotations & Estimates — Create quotes and convert to sales orders with one click.',
				'Sales Returns & Refunds — Handle partial or full returns with automatic stock updates.',
				'Customer Profiles — Track purchase history, outstanding balances, and behavior.',
				'Receipt Printing — Print thermal receipts instantly. Compatible with standard POS printers.',
				'Hold & Recall Sales — Park a sale, serve another customer, return and complete anytime.',
				'Offline Mode — POS works without internet. Sales queue and sync when back online.',
			], [98, 0, 234]],

			['📦 Inventory', 'Know exactly what you have, where it is, and when it expires.', [
				'Product Catalog — Organize products by category, brand, and unit of measure.',
				'Barcode Generation — Auto-generate and print barcodes for every product.',
				'Batch Tracking — Track every batch number from purchase to sale. Full traceability.',
				'Expiry Date Management — Set expiry dates per batch. Get alerts before stock expires.',
				'Expired Stock Blocking — Automatically block expired items from being sold.',
				'Multi-Warehouse Support — Manage stock across multiple warehouses.',
				'Stock Transfers — Move stock between warehouses with full audit trails.',
				'Stock Adjustments — Add, remove, or correct stock levels with reason tracking.',
				'Low Stock Alerts — Get notified when items fall below reorder levels.',
				'Product Variants — Support for size, color, and other product variations.',
			], [16, 185, 129]],

			['🛒 Purchasing', 'Buy better. Track every order.', [
				'Purchase Orders — Create, send, and track purchase orders from draft to receipt.',
				'Multi-Status Purchases — Draft to Ordered to Partially Received to Received.',
				'Purchase Returns — Return defective or excess stock to suppliers.',
				'Supplier Management — Maintain supplier profiles, contact info, and payment history.',
				'Cost Price & Profit Margin — Auto-calculate profit margins on purchase.',
				'Batch & Expiry on Receipt — Capture batch numbers and expiry dates when receiving stock.',
				'Supplier Payment Tracking — Record payments and track outstanding purchase dues.',
				'Purchase List & Filtering — Search and filter by status, date, supplier, or reference.',
			], [245, 158, 11]],

			['🌐 Online Store', 'Sell online without building a separate website.', [
				'Built-In Online Store — Launch a branded storefront directly from MartPoint. No coding.',
				'Homepage Builder — Drag-and-drop builder with banners, featured products, and categories.',
				'Product Showcase — Choose which products appear online with custom descriptions.',
				'Service Listings — List services for booking or inquiry.',
				'Online Orders Management — Receive, process, and fulfill online orders from the same dashboard.',
				'Order Tracking — Customers can track order status from placement to delivery.',
				'Brand Pages — Dedicated pages for each brand you stock.',
				'Testimonials — Collect and display customer reviews.',
				'Instagram Integration — Link your Instagram feed to your online store.',
				'FAQ Section — Built-in FAQ builder for common customer questions.',
				'Custom Domains — Use your own domain name for the online store.',
				'Store Analytics — Track visits, orders, conversion rates, and popular products.',
				'Appearance Customization — Customize colors, fonts, layout, and branding.',
				'PDF Catalog Export — Generate downloadable product catalogs.',
			], [139, 92, 246]],

			['📱 QR Menu & Ordering', 'Let customers scan, browse, and order — contactless and modern.', [
				'QR Code Generator — Store Front, Product Pages, Service Bookings, Category Browsing, Table Ordering, Attendance.',
				'Print-Ready QR Codes — Download high-resolution QR codes for menus, tents, posters, and flyers.',
				'Dynamic QR Updates — Change what the QR links to without reprinting.',
			], [239, 68, 68]],

			['🏢 Multi-Branch', 'One system. Many locations.', [
				'Multi-Store Support — Run multiple stores from a single MartPoint instance.',
				'Centralized Reporting — View consolidated reports across all branches.',
				'Branch-Based Inventory — Each branch manages its own stock, warehouses, and suppliers.',
				'Inter-Branch Stock Transfers — Move inventory between branches with full tracking.',
				'Role-Based Access Per Branch — Assign users to specific branches with tailored permissions.',
				'Store-Specific Settings — Currency, tax rates, branding, and payment methods per store.',
				'Warehouse Privileges — Control which staff can access which warehouses.',
			], [6, 182, 212]],

			['📊 Reports', 'Your business heartbeat, every single day.', [
				'Daily Business Summary — One-page overview: Sales, Profit, Expenses, Net Position, Debts, Cash, Attendance, Top Products, Low Stock, Expiry, Payments.',
				'Date Range Filtering — View summaries for any date range.',
				'One-Click Sharing — Share via WhatsApp, Email, or PDF in seconds.',
				'Sales Reports — Filter by date, product, customer, payment method, or staff.',
				'Purchase Reports — Analyze purchasing patterns.',
				'Stock Movement Reports — Track every stock-in, stock-out, and adjustment.',
				'Profit & Loss Analysis — Understand true profitability after all costs.',
				'Expiry & Low Stock Reports — Proactive inventory health monitoring.',
				'Attendance Reports — Staff presence and punctuality tracking.',
				'PDF Export — Generate print-ready PDFs of any report.',
			], [236, 72, 153]],

			['🔒 Approvals', 'Control who can do what. Protect your business.', [
				'Approval Workflow System — Discounts, expenses, stock adjustments, price changes, purchase orders, sales returns.',
				'Multi-Level Approvers — Assign specific approvers by role or individual user.',
				'Approval Logs — Full audit trail of every request, approval, and rejection.',
				'Business Control Modes — Simple mode or Advanced mode.',
				'Self-Approval Control — Prevent staff from approving their own requests.',
				'Discount Limits — Cap the maximum discount any staff member can give.',
				'Expense Thresholds — Require approval for expenses above a set amount.',
			], [249, 115, 22]],

			['📢 Communication', 'Stay connected to your customers and your team.', [
				'Email System (SMTP + Resend) — Send professional emails via SMTP or Resend.com API.',
				'Email Templates — Pre-built templates for invoices, receipts, reports, and alerts.',
				'Email Logs & Retry — Track every email sent. Retry failed deliveries.',
				'Test Email Function — Verify email settings before going live.',
				'WhatsApp Integration — Share reports, invoices, and alerts directly to WhatsApp.',
				'SMS Alerts — Send SMS notifications for low stock, payments due, and more.',
				'Scheduled Reports — Automate daily summary emails and low stock alerts. Daily, Weekly, or Monthly.',
			], [59, 130, 246]],

			['🤖 MartPoint Assist', 'Your virtual business assistant, always online.', [
				'Smart Chat Interface — Floating chat panel accessible from any screen.',
				'Natural Language Queries — Ask "What were my sales today?" or "Show low stock items."',
				'Quick Actions — One-click shortcuts for creating sales, adding products, viewing summaries, checking stock.',
				'Knowledge Base Search — Search help articles without leaving the app.',
				'Role-Based Responses — Cashiers see cashier guides. Admins see admin guides.',
				'Support Request Form — Submit support tickets directly to MartPoint support.',
				'WhatsApp Support Link — Direct connection to MartPoint support via WhatsApp.',
				'Session Memory — Assist remembers context across your conversation.',
				'Draft Execution — Assist can prepare actions for your confirmation.',
			], [20, 184, 166]],

			['👥 Attendance', 'Track your team. Automatically.', [
				'Staff Management — Maintain employee profiles with roles and contact info.',
				'Daily Attendance Marking — Mark staff as Present or Absent each day.',
				'Attendance in Daily Reports — See who is present in your daily summary.',
				'Attendance QR Codes — Staff scan a QR code to mark themselves present.',
				'Staff Performance Context — Correlate attendance with sales and operations.',
			], [99, 102, 241]],

			['💰 Accounting', 'Know your numbers. All of them.', [
				'Chart of Accounts — Full double-entry accounting structure.',
				'Account Transactions — Journal entries, transfers between accounts.',
				'Expense Tracking — Record and categorize all business expenses.',
				'Payment Recording — Log every incoming and outgoing payment.',
				'Customer Debt Tracking — See who owes what, for how long.',
				'Supplier Balance Tracking — Know what you owe to each supplier.',
				'Cash Flow Monitoring — Track cash in hand vs bank/POS collections.',
				'Daily Net Position — Instant view of profit after expenses.',
				'Financial Reports — Profit & Loss, balance sheet, and cash flow statements.',
			], [132, 204, 22]],

			['⚙️ Administration', 'Complete control over your system.', [
				'One-Click Auto-Updates — Update MartPoint directly from GitHub. No technical skills needed.',
				'Update Channel Configuration — Point to your own GitHub repository for custom updates.',
				'Database Backup & Restore — Manual and scheduled backups. One-click restore.',
				'Company Branding — Upload your logo, set company name, customize POS backgrounds.',
				'Currency & Tax Settings — Configure per-store currency, tax rates, and number formats.',
				'Multi-Language Support — Run the system in your preferred language.',
				'User Management — Create, edit, deactivate, and assign roles to users.',
				'Role & Permission System — Granular control over what each user can see and do.',
				'System Activity Logs — Track who did what, and when.',
				'POS Background Customization — Upload custom backgrounds for the POS screen.',
			], [100, 116, 139]],

			['🛍️ Customer Experience', 'Give your customers a modern experience.', [
				'Online Storefront — Customers browse and buy from any device.',
				'QR Code Menus — Scan and browse. No app download needed.',
				'Table Ordering (QR) — Dine-in customers order directly from their phones.',
				'Order Status Tracking — Customers see real-time updates on their orders.',
				'WhatsApp Ordering — Customers can inquire or place orders via WhatsApp.',
				'Testimonials & Reviews — Build trust with public customer feedback.',
				'FAQ Section — Reduce support load with self-service answers.',
			], [244, 63, 94]],
		];

		foreach ($modules as $mod) {
			$pdf->AddPage();
			$pdf->SetFillColor($mod[3][0], $mod[3][1], $mod[3][2]);
			$pdf->Rect(0, 0, 210, 4, 'F');

			$pdf->SetTextColor(30, 41, 59);
			$pdf->SetFont('helvetica', 'B', 16);
			$pdf->SetY(14);
			$pdf->Cell(0, 10, $mod[0], 0, 1, 'L');

			$pdf->SetFont('helvetica', 'I', 9);
			$pdf->SetTextColor(100, 116, 139);
			$pdf->Cell(0, 5, $mod[1], 0, 1, 'L');
			$pdf->Ln(3);

			$pdf->SetTextColor(30, 41, 59);
			$pdf->SetFont('helvetica', '', 9);

			foreach ($mod[2] as $feat) {
				if ($pdf->GetY() > 275) {
					$pdf->AddPage();
					$pdf->SetFillColor($mod[3][0], $mod[3][1], $mod[3][2]);
					$pdf->Rect(0, 0, 210, 2, 'F');
					$pdf->SetY(10);
					$pdf->SetFont('helvetica', 'B', 11);
					$pdf->Cell(0, 6, $mod[0] . ' (continued)', 0, 1, 'L');
					$pdf->SetFont('helvetica', '', 9);
				}

				$parts = explode(' — ', $feat, 2);
				if (count($parts) === 2) {
					$pdf->SetFont('helvetica', 'B', 9);
					$pdf->Cell(4, 5, '•', 0, 0, 'L');
					$pdf->Cell(0, 5, $parts[0], 0, 1, 'L');
					$pdf->SetFont('helvetica', '', 9);
					$pdf->Cell(4, 4.5, '', 0, 0);
					$pdf->MultiCell(0, 4.5, $parts[1], 0, 'L');
					$pdf->Ln(0.5);
				} else {
					$pdf->Cell(4, 5, '•', 0, 0, 'L');
					$pdf->MultiCell(0, 5, $feat, 0, 'L');
					$pdf->Ln(0.5);
				}
			}
		}

		// === SUMMARY PAGE ===
		$pdf->AddPage();
		$pdf->SetFillColor(30, 41, 59);
		$pdf->Rect(0, 0, 210, 297, 'F');
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetFont('helvetica', 'B', 20);
		$pdf->SetY(30);
		$pdf->Cell(0, 12, 'What Makes MartPoint Different?', 0, 1, 'C');
		$pdf->Ln(6);

		$pdf->SetFont('helvetica', '', 9);
		$pdf->SetTextColor(203, 213, 225);

		$diffs = [
			['All-in-One', 'POS + Inventory + Online Store + Accounting in one platform'],
			['No Coding Required', 'Online store builder, QR codes, and workflows — all point-and-click'],
			['AI Assistant', 'MartPoint Assist helps you navigate, query, and act faster'],
			['Offline Capable', 'POS works even without internet; syncs when back online'],
			['Approval Workflows', 'Built-in security gates to prevent fraud and errors'],
			['Multi-Everything', 'Multi-store, multi-warehouse, multi-user, multi-currency'],
			['Communication Hub', 'Email, WhatsApp, and SMS — all integrated'],
			['Auto-Updates', 'Stay current with one-click updates from GitHub'],
			['QR-First', 'QR menus, QR ordering, QR attendance — modern and contactless'],
			['Daily Insights', 'Know your business health every single morning'],
		];

		foreach ($diffs as $d) {
			$pdf->SetFont('helvetica', 'B', 9);
			$pdf->SetTextColor(226, 232, 240);
			$pdf->Cell(50, 6, $d[0], 0, 0, 'L');
			$pdf->SetFont('helvetica', '', 9);
			$pdf->SetTextColor(203, 213, 225);
			$pdf->MultiCell(0, 6, $d[1], 0, 'L');
			$pdf->Ln(1);
		}

		$pdf->SetY(260);
		$pdf->SetFont('helvetica', 'B', 14);
		$pdf->SetTextColor(255, 255, 255);
		$pdf->Cell(0, 10, 'MartPoint Retail — Built for retailers who want to grow.', 0, 1, 'C');
		$pdf->SetFont('helvetica', '', 10);
		$pdf->Cell(0, 8, 'Powered by Avario Digitals', 0, 1, 'C');

		$pdf->Output('MartPoint_Features.pdf', 'D');
		exit;
	}
}
