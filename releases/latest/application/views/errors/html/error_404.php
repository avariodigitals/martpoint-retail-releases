<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Page Not Found</title>
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
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
}
.code {
    font-size: 6rem;
    font-weight: 800;
    color: #dee2e6;
    line-height: 1;
    margin-bottom: 0.5rem;
    letter-spacing: -2px;
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
.btn {
    display: inline-block;
    background: #0d6efd;
    color: #fff;
    text-decoration: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    font-weight: 500;
    transition: background 0.2s ease;
}
.btn:hover { background: #0b5ed7; }
.footer {
    margin-top: 2.5rem;
    font-size: 0.8rem;
    color: #adb5bd;
}
@media (max-width: 480px) {
    .code { font-size: 4rem; }
    .heading { font-size: 1.25rem; }
}
</style>
</head>
<body>
    <div class="container">
        <div class="illustration">&#128269;</div>
        <div class="code">404</div>
        <div class="heading">Page Not Found</div>
        <div class="message">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </div>
        <a href="<?php echo base_url(); ?>" class="btn">Go to Dashboard</a>
        <div class="footer">&copy; <?php echo date('Y'); ?> MartPoint</div>
    </div>
</body>
</html>