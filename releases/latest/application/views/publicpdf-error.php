<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Link Expired</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    background: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #343a40;
}
.container {
    text-align: center;
    padding: 2rem;
    max-width: 480px;
}
.illustration {
    width: 120px;
    height: 120px;
    margin: 0 auto 2rem;
    background: #fff3cd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
}
.heading {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #212529;
}
.message {
    font-size: 1rem;
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 2rem;
}
.contact-box {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.store-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
}
.phone {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0d6efd;
    margin: 0.5rem 0;
}
.phone a {
    color: #0d6efd;
    text-decoration: none;
}
.label {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.footer {
    margin-top: 2rem;
    font-size: 0.8rem;
    color: #adb5bd;
}
@media (max-width: 480px) {
    .heading { font-size: 1.25rem; }
    .phone { font-size: 1.1rem; }
}
</style>
</head>
<body>
    <div class="container">
        <div class="illustration">&#128274;</div>
        <div class="heading">Link Expired or Invalid</div>
        <div class="message">
            This PDF link is no longer valid or has been accessed incorrectly.
        </div>
        <?php if(!empty($store_name) || !empty($store_phone)): ?>
        <div class="contact-box">
            <?php if(!empty($store_name)): ?>
            <div class="store-name"><?php echo htmlspecialchars($store_name); ?></div>
            <?php endif; ?>
            <div class="label">Need help? Contact us</div>
            <?php if(!empty($store_phone)): ?>
            <div class="phone">
                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $store_phone); ?>"><?php echo htmlspecialchars($store_phone); ?></a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="footer">&copy; <?php echo date('Y'); ?> MartPoint</div>
    </div>
</body>
</html>
