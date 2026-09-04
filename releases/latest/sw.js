/* MartPoint Retail PWA Service Worker */
const CACHE_NAME = 'martpoint-v24';
const OFFLINE_PAGE = '/offline.html';

/* Core shell pages */
const SHELL_PAGES = [
  '/',
  '/login',
  '/dashboard',
  '/pos',
  OFFLINE_PAGE
];

/* Static theme assets — safe to cache aggressively */
const STATIC_ASSETS = [
  /* Bootstrap & core CSS */
  '/theme/bootstrap/css/bootstrap.min.css',
  '/theme/bootstrap/js/bootstrap.min.js',
  /* Fonts */
  '/theme/css/font-awesome-4.7.0/css/font-awesome.min.css',
  '/theme/css/ionicons-2.0.1/css/ionicons.min.css',
  /* AdminLTE */
  '/theme/dist/css/AdminLTE.min.css',
  '/theme/dist/css/newcustom.css',
  '/theme/dist/css/custom.css',
  '/theme/dist/css/martpoint-reskin.css',
  '/theme/dist/css/skins/_all-skins.min.css',
  /* jQuery */
  '/theme/plugins/jQuery/jquery-2.2.3.min.js',
  /* Select2 */
  '/theme/plugins/select2/select2.min.css',
  '/theme/plugins/select2/select2.full.min.js',
  /* iCheck */
  '/theme/plugins/iCheck/square/blue.css',
  '/theme/plugins/iCheck/square/orange.css',
  '/theme/plugins/iCheck/icheck.min.js',
  /* Toastr */
  '/theme/toastr/toastr.css',
  /* Daterangepicker */
  '/theme/plugins/daterangepicker/daterangepicker.css',
  /* Datepicker */
  '/theme/plugins/datepicker/datepicker3.css',
  /* Autocomplete */
  '/theme/plugins/autocomplete/autocomplete.css',
  /* Pace */
  '/theme/plugins/pace/pace.min.css',
  /* Slimscroll */
  '/theme/plugins/slimScroll/jquery.slimscroll.min.js',
  /* FastClick */
  '/theme/plugins/fastclick/fastclick.js',
  /* Shortcuts */
  '/theme/plugins/shortcuts/shortcuts.js',
  /* Assist */
  '/theme/css/assist.css',
  /* POS */
  '/theme/css/pos.css',
  /* App JS */
  '/theme/dist/js/bootstrap3-typeahead.min.js',
  '/theme/js/fullscreen.js',
  '/theme/js/modals.js',
  '/theme/js/modals/modal_item.js',
  '/theme/js/ajaxselect/customer_select_ajax.js',
  '/theme/js/coupons/coupon.js',
  '/theme/js/coupons/generate.js',
  '/theme/js/mp-offline-db.js',
  '/theme/js/pos.js',
  '/theme/js/approval-modal.js'
];

const ALL_CACHED = SHELL_PAGES.concat(STATIC_ASSETS);

/* ─── INSTALL ─── */
self.addEventListener('install', function(event) {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then(function(cache) {
      return cache.addAll(ALL_CACHED);
    }).catch(function(err) {
      console.warn('[SW] Precache failed for some assets:', err);
    })
  );
});

/* ─── ACTIVATE ─── */
self.addEventListener('activate', function(event) {
  event.waitUntil(
    caches.keys().then(function(keys) {
      return Promise.all(
        keys.filter(function(key) { return key !== CACHE_NAME; })
            .map(function(key) { return caches.delete(key); })
      );
    })
  );
  self.clients.claim();
});

/* ─── HELPERS ─── */
function isApiOrAjax(req) {
  // Skip API / AJAX calls — always go to network
  if (req.url.includes('/api/')) return true;
  if (req.headers.get('X-Requested-With') === 'XMLHttpRequest') return true;
  // Skip POST / PUT / DELETE entirely
  if (req.method !== 'GET') return true;
  // Skip CodeIgniter controller AJAX endpoints (GET requests to PHP methods)
  // These are dynamic and should never be cached
  var urlPath = new URL(req.url).pathname;
  // Match patterns like /items/get_json_items_details, /sales/search_item, etc.
  if (urlPath.match(/\/(items|sales|purchase|category|brand|customers|suppliers|pos|quotation|reports|warehouse|tax|units|payment_types|accounts|expense|stock_adjustment|stock_transfer|services|users|roles|dashboard|mobile)\/[a-z_]+/)) return true;
  return false;
}

function isNavigationRequest(req) {
  return req.mode === 'navigate';
}

function isStaticAsset(req) {
  var url = req.url;
  return STATIC_ASSETS.some(function(asset) {
    return url.endsWith(asset);
  });
}

/* ─── FETCH ─── */
self.addEventListener('fetch', function(event) {
  var req = event.request;

  // Pass through external requests
  if (!req.url.startsWith(self.location.origin)) {
    return;
  }

  // Pass through API/AJAX/non-GET
  if (isApiOrAjax(req)) {
    return;
  }

  // Navigation requests (page loads) — only handle core shell pages
  // so dynamic CRUD pages (customer_coupon, discount_coupon, etc.)
  // are always fetched fresh and never become stale.
  if (isNavigationRequest(req)) {
    var urlPath = new URL(req.url).pathname;
    var isShell = SHELL_PAGES.some(function(page) { return urlPath === page; });
    if (!isShell) {
      // Let the browser fetch dynamic pages directly from the network
      return;
    }
    event.respondWith(
      fetch(req).then(function(response) {
        // Only cache valid shell page responses
        if (response && response.status === 200 && response.type === 'basic') {
          var clone = response.clone();
          caches.open(CACHE_NAME).then(function(cache) {
            cache.put(req, clone).catch(function(){});
          });
        }
        return response;
      }).catch(function(err) {
        return caches.match(req).then(function(cached) {
          if (cached) return cached;
          return caches.match(OFFLINE_PAGE);
        }).then(function(response) {
          if (response) return response;
          return new Response(
            '<!DOCTYPE html><html><head><title>Offline</title></head><body><h1>Offline</h1><p>MartPoint is currently offline. Please check your connection and try again.</p></body></html>',
            { status: 503, statusText: 'Offline', headers: { 'Content-Type': 'text/html' } }
          );
        });
      })
    );
    return;
  }

  // Static assets — cache-first for performance
  if (isStaticAsset(req)) {
    event.respondWith(
      caches.match(req).then(function(cached) {
        if (cached) {
          // Background refresh
          fetch(req).then(function(response) {
            if (response && response.status === 200) {
              caches.open(CACHE_NAME).then(function(cache) {
                cache.put(req, response);
              });
            }
          }).catch(function(){});
          return cached;
        }
        return fetch(req).then(function(response) {
          if (response && response.status === 200) {
            var clone = response.clone();
            caches.open(CACHE_NAME).then(function(cache) {
              cache.put(req, clone);
            });
          }
          return response;
        });
      })
    );
    return;
  }

  // Everything else — pass through directly to the network.
  // Do not return synthetic 503s for resources we don't explicitly cache.
  return;
});
