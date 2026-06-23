<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/* Online Storefront Routes */
$route['store'] = 'storefront/index';
$route['store/(:any)'] = 'storefront/index/$1';
$route['store/(:any)/products'] = 'storefront/products/$1';
$route['store/(:any)/services'] = 'storefront/services/$1';
$route['store/(:any)/product/(:num)'] = 'storefront/product/$1/$2';
$route['store/(:any)/service/(:num)'] = 'storefront/service/$1/$2';
$route['store/(:any)/cart'] = 'storefront/cart/$1';
$route['qr/(:num)'] = 'storefront/qr/$1';
$route['storefront/place_order'] = 'storefront/place_order';
$route['storefront/paystack_callback'] = 'storefront/paystack_callback';
$route['sitemap.xml'] = 'storefront/sitemap';
$route['robots.txt'] = 'storefront/robots';

/* Online Store Admin Routes */
$route['online_store'] = 'online_store';
$route['online_store/settings'] = 'online_store/settings';
$route['online_store/save_settings'] = 'online_store/save_settings';
$route['online_store/appearance'] = 'online_store/appearance';
$route['online_store/save_appearance'] = 'online_store/save_appearance';
$route['online_store/banners'] = 'online_store/banners';
$route['online_store/banner_form'] = 'online_store/banner_form';
$route['online_store/banner_form/(:num)'] = 'online_store/banner_form/$1';
$route['online_store/save_banner'] = 'online_store/save_banner';
$route['online_store/delete_banner/(:num)'] = 'online_store/delete_banner/$1';
$route['online_store/homepage_builder'] = 'online_store/homepage_builder';
$route['online_store/save_homepage_sections'] = 'online_store/save_homepage_sections';
$route['online_store/preview_store'] = 'online_store/preview_store';
$route['online_store/domains'] = 'online_store/domains';
$route['online_store/save_domain'] = 'online_store/save_domain';
$route['online_store/update_domain_status'] = 'online_store/update_domain_status';
$route['online_store/delete_domain/(:num)'] = 'online_store/delete_domain/$1';
$route['online_store/orders'] = 'online_store/orders';
$route['online_store/order/(:num)'] = 'online_store/order_detail/$1';
$route['online_store/services'] = 'online_store/services';
$route['online_store/qr_codes'] = 'online_store/qr_codes';
$route['online_store/products_online'] = 'online_store/products_online';
$route['online_store/seed_permissions'] = 'online_store/seed_permissions';
$route['online_store/debug_storefront'] = 'online_store/debug_storefront';

/* New Storefront Section Routes */
$route['online_store/brands'] = 'online_store/brands';
$route['online_store/save_brand'] = 'online_store/save_brand';
$route['online_store/delete_brand/(:num)'] = 'online_store/delete_brand/$1';

/* Attendance Routes */
$route['attendance/shifts'] = 'attendance/shifts';
$route['attendance/shift_form'] = 'attendance/shift_form';
$route['attendance/shift_form/(:num)'] = 'attendance/shift_form/$1';
$route['attendance/save_shift'] = 'attendance/save_shift';
$route['attendance/delete_shift/(:num)'] = 'attendance/delete_shift/$1';
$route['attendance/assign_shifts'] = 'attendance/assign_shifts';
$route['attendance/get_user_shifts_ajax'] = 'attendance/get_user_shifts_ajax';
$route['attendance/save_user_shift'] = 'attendance/save_user_shift';
$route['attendance/daily'] = 'attendance/daily';
$route['attendance/report'] = 'attendance/report';
$route['attendance/clock_in'] = 'attendance/clock_in';
$route['attendance/clock_out'] = 'attendance/clock_out';
$route['attendance/status_ajax'] = 'attendance/status_ajax';
$route['attendance/clockin'] = 'attendance/clockin';
$route['online_store/testimonials'] = 'online_store/testimonials';
$route['online_store/save_testimonial'] = 'online_store/save_testimonial';
$route['online_store/delete_testimonial/(:num)'] = 'online_store/delete_testimonial/$1';
$route['online_store/instagram'] = 'online_store/instagram';
$route['online_store/save_instagram'] = 'online_store/save_instagram';
$route['online_store/delete_instagram/(:num)'] = 'online_store/delete_instagram/$1';
$route['online_store/fetch_instagram'] = 'online_store/fetch_instagram';
$route['online_store/faqs'] = 'online_store/faqs';
$route['online_store/save_faq'] = 'online_store/save_faq';
$route['online_store/delete_faq/(:num)'] = 'online_store/delete_faq/$1';
$route['online_store/fetch_gmb_reviews'] = 'online_store/fetch_gmb_reviews';

/* Approval Routes */
$route['approvals/settings'] = 'approvals/settings';
$route['approvals/save_settings'] = 'approvals/save_settings';
$route['approvals/check_ajax'] = 'approvals/check_ajax';
$route['approvals/validate'] = 'approvals/validate';
$route['approvals/logs'] = 'approvals/logs';
