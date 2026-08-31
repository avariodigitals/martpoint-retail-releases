/**
 * MartPoint Offline IndexedDB
 * Caches item search results and item details for offline POS use
 */
var MPOfflineDB = (function() {
  'use strict';

  var DB_NAME = 'MartPointOffline';
  var DB_VERSION = 5;
  var db = null;

  function openDatabase() {
    return new Promise(function(resolve, reject) {
      if (db) { resolve(db); return; }
      var request = indexedDB.open(DB_NAME, DB_VERSION);
      request.onerror = function() { reject(request.error); };
      request.onsuccess = function() { db = request.result; resolve(db); };
      request.onupgradeneeded = function(event) {
        var database = event.target.result;
        // Items store: caches autocomplete results
        if (!database.objectStoreNames.contains('items')) {
          var itemStore = database.createObjectStore('items', { keyPath: 'id' });
          itemStore.createIndex('name', 'value', { unique: false });
          itemStore.createIndex('item_code', 'item_code', { unique: false });
          itemStore.createIndex('barcode', 'barcode', { unique: false });
        }
        // Item details store: caches full item details from pos/get_item_details
        if (!database.objectStoreNames.contains('itemDetails')) {
          var detailStore = database.createObjectStore('itemDetails', { keyPath: 'id' });
          detailStore.createIndex('timestamp', 'timestamp', { unique: false });
        }
        // Customers store: caches customer search results
        if (!database.objectStoreNames.contains('customers')) {
          var custStore = database.createObjectStore('customers', { keyPath: 'id' });
          custStore.createIndex('name', 'text', { unique: false });
          custStore.createIndex('mobile', 'mobile', { unique: false });
        }
        // Suppliers store: caches suppliers for offline PO
        if (!database.objectStoreNames.contains('suppliers')) {
          var supStore = database.createObjectStore('suppliers', { keyPath: 'id' });
          supStore.createIndex('name', 'supplier_name', { unique: false });
        }
        // Sales queue: stores completed sales for background sync
        if (!database.objectStoreNames.contains('salesQueue')) {
          var queueStore = database.createObjectStore('salesQueue', { keyPath: 'queueId', autoIncrement: true });
          queueStore.createIndex('timestamp', 'timestamp', { unique: false });
          queueStore.createIndex('status', 'status', { unique: false });
        }
        // Purchase queue: stores purchase orders for background sync
        if (!database.objectStoreNames.contains('purchaseQueue')) {
          var pqStore = database.createObjectStore('purchaseQueue', { keyPath: 'queueId', autoIncrement: true });
          pqStore.createIndex('timestamp', 'timestamp', { unique: false });
          pqStore.createIndex('status', 'status', { unique: false });
        }
        // Hold invoices: stored locally when offline
        if (!database.objectStoreNames.contains('holdInvoices')) {
          var holdStore = database.createObjectStore('holdInvoices', { keyPath: 'holdId', autoIncrement: true });
          holdStore.createIndex('reference_id', 'reference_id', { unique: false });
          holdStore.createIndex('timestamp', 'timestamp', { unique: false });
        }
        // Metadata store
        if (!database.objectStoreNames.contains('meta')) {
          database.createObjectStore('meta', { keyPath: 'key' });
        }
      };
    });
  }

  /* ─── ITEMS (autocomplete cache) ─── */
  function saveItems(items) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('items', 'readwrite');
        var store = tx.objectStore('items');
        var count = 0;
        items.forEach(function(item) {
          // Normalize barcode to string for index
          if (!item.barcode) item.barcode = '';
          if (!item.batch_lot) item.batch_lot = '';
          if (!item.barcode_price) item.barcode_price = '';
          if (!item.barcode_mrp) item.barcode_mrp = '';
          if (!item.barcode_pprice) item.barcode_pprice = '';
          var req = store.put(item);
          req.onsuccess = function() {
            count++;
            if (count === items.length) resolve(count);
          };
          req.onerror = function() { /* ignore single-item failures */ };
        });
        if (items.length === 0) resolve(0);
        tx.onerror = function() { reject(tx.error); };
      });
    });
  }

  function searchItems(term, limit) {
    limit = limit || 20;
    term = (term || '').toLowerCase().trim();
    return openDatabase().then(function(database) {
      return new Promise(function(resolve) {
        var tx = database.transaction('items', 'readonly');
        var store = tx.objectStore('items');
        var results = [];
        var cursor = store.openCursor();
        cursor.onsuccess = function(event) {
          var cur = event.target.result;
          if (!cur) { resolve(results.slice(0, limit)); return; }
          var item = cur.value;
          if (term === '' ||
              (item.value && item.value.toLowerCase().indexOf(term) !== -1) ||
              (item.item_code && item.item_code.toLowerCase().indexOf(term) !== -1) ||
              (item.barcode && item.barcode.toLowerCase().indexOf(term) !== -1)) {
            results.push(item);
          }
          cur.continue();
        };
        cursor.onerror = function() { resolve(results.slice(0, limit)); };
      });
    });
  }

  function getItemById(id) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('items', 'readonly');
        var store = tx.objectStore('items');
        var req = store.get(id);
        req.onsuccess = function() { resolve(req.result); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  /* ─── ITEM DETAILS (full data cache) ─── */
  function saveItemDetails(item) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('itemDetails', 'readwrite');
        var store = tx.objectStore('itemDetails');
        item.timestamp = Date.now();
        var req = store.put(item);
        req.onsuccess = function() { resolve(); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  function getItemDetails(id) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('itemDetails', 'readonly');
        var store = tx.objectStore('itemDetails');
        var req = store.get(id);
        req.onsuccess = function() { resolve(req.result); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  /* ─── CUSTOMERS ─── */
  function saveCustomers(customers) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('customers', 'readwrite');
        var store = tx.objectStore('customers');
        var count = 0;
        customers.forEach(function(cust) {
          if (!cust.mobile) cust.mobile = '';
          if (!cust.previous_due) cust.previous_due = '0';
          if (!cust.tot_advance) cust.tot_advance = '0';
          if (typeof cust.delete_bit === 'undefined') cust.delete_bit = 0;
          var req = store.put(cust);
          req.onsuccess = function() {
            count++;
            if (count === customers.length) resolve(count);
          };
          req.onerror = function() { /* ignore single-item failures */ };
        });
        if (customers.length === 0) resolve(0);
        tx.onerror = function() { reject(tx.error); };
      });
    });
  }

  function searchCustomers(term, limit) {
    limit = limit || 20;
    term = (term || '').toLowerCase().trim();
    return openDatabase().then(function(database) {
      return new Promise(function(resolve) {
        var tx = database.transaction('customers', 'readonly');
        var store = tx.objectStore('customers');
        var results = [];
        var cursor = store.openCursor();
        cursor.onsuccess = function(event) {
          var cur = event.target.result;
          if (!cur) { resolve(results.slice(0, limit)); return; }
          var cust = cur.value;
          if (term === '' ||
              (cust.text && cust.text.toLowerCase().indexOf(term) !== -1) ||
              (cust.mobile && cust.mobile.toLowerCase().indexOf(term) !== -1)) {
            results.push(cust);
          }
          cur.continue();
        };
        cursor.onerror = function() { resolve(results.slice(0, limit)); };
      });
    });
  }

  function getCustomerById(id) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('customers', 'readonly');
        var store = tx.objectStore('customers');
        var req = store.get(id);
        req.onsuccess = function() { resolve(req.result); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  /* ─── SALES QUEUE ─── */
  function queueSale(saleData) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('salesQueue', 'readwrite');
        var store = tx.objectStore('salesQueue');
        saleData.timestamp = Date.now();
        saleData.status = 'pending';
        var req = store.add(saleData);
        req.onsuccess = function() { resolve(req.result); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  function getPendingSales() {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve) {
        var tx = database.transaction('salesQueue', 'readonly');
        var store = tx.objectStore('salesQueue');
        var index = store.index('status');
        var results = [];
        var req = index.openCursor('pending');
        req.onsuccess = function(event) {
          var cur = event.target.result;
          if (!cur) { resolve(results); return; }
          results.push(cur.value);
          cur.continue();
        };
        req.onerror = function() { resolve(results); };
      });
    });
  }

  function removeQueuedSale(queueId) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('salesQueue', 'readwrite');
        var store = tx.objectStore('salesQueue');
        var req = store.delete(queueId);
        req.onsuccess = function() { resolve(); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  function countPendingSales() {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve) {
        var tx = database.transaction('salesQueue', 'readonly');
        var store = tx.objectStore('salesQueue');
        var index = store.index('status');
        var req = index.count('pending');
        req.onsuccess = function() { resolve(req.result); };
        req.onerror = function() { resolve(0); };
      });
    });
  }

  /* ─── HOLD INVOICES ─── */
  function saveHoldInvoice(holdData) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('holdInvoices', 'readwrite');
        var store = tx.objectStore('holdInvoices');
        holdData.timestamp = Date.now();
        var req = store.put(holdData);
        req.onsuccess = function() { resolve(req.result); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  function getHoldInvoices() {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve) {
        var tx = database.transaction('holdInvoices', 'readonly');
        var store = tx.objectStore('holdInvoices');
        var results = [];
        var req = store.openCursor(null, 'prev'); // newest first
        req.onsuccess = function(event) {
          var cur = event.target.result;
          if (!cur) { resolve(results); return; }
          results.push(cur.value);
          cur.continue();
        };
        req.onerror = function() { resolve(results); };
      });
    });
  }

  function deleteHoldInvoice(holdId) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('holdInvoices', 'readwrite');
        var store = tx.objectStore('holdInvoices');
        var req = store.delete(holdId);
        req.onsuccess = function() { resolve(); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  function getHoldInvoiceByRef(refId) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('holdInvoices', 'readonly');
        var store = tx.objectStore('holdInvoices');
        var index = store.index('reference_id');
        var req = index.get(refId);
        req.onsuccess = function() { resolve(req.result); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  function countHoldInvoices() {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve) {
        var tx = database.transaction('holdInvoices', 'readonly');
        var store = tx.objectStore('holdInvoices');
        var req = store.count();
        req.onsuccess = function() { resolve(req.result); };
        req.onerror = function() { resolve(0); };
      });
    });
  }

  /* ─── METADATA ─── */
  function setMeta(key, value) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('meta', 'readwrite');
        var store = tx.objectStore('meta');
        var req = store.put({ key: key, value: value });
        req.onsuccess = function() { resolve(); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  function getMeta(key) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('meta', 'readonly');
        var store = tx.objectStore('meta');
        var req = store.get(key);
        req.onsuccess = function() { resolve(req.result ? req.result.value : null); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }

  /* ─── SUPPLIERS ─── */
  function saveSuppliers(suppliers) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('suppliers', 'readwrite');
        var store = tx.objectStore('suppliers');
        var count = 0;
        suppliers.forEach(function(s) {
          var req = store.put(s);
          req.onsuccess = function() { count++; if(count === suppliers.length) resolve(count); };
          req.onerror = function() {};
        });
        if(suppliers.length === 0) resolve(0);
      });
    });
  }
  function searchSuppliers(term, limit) {
    limit = limit || 20;
    term = (term || '').toLowerCase().trim();
    return openDatabase().then(function(database) {
      return new Promise(function(resolve) {
        var tx = database.transaction('suppliers', 'readonly');
        var store = tx.objectStore('suppliers');
        var results = [];
        var cursor = store.openCursor();
        cursor.onsuccess = function(event) {
          var cur = event.target.result;
          if(!cur) { resolve(results.slice(0, limit)); return; }
          var s = cur.value;
          if(term === '' || (s.supplier_name && s.supplier_name.toLowerCase().indexOf(term) !== -1)) {
            results.push(s);
          }
          cur.continue();
        };
        cursor.onerror = function() { resolve(results.slice(0, limit)); };
      });
    });
  }

  /* ─── PURCHASE QUEUE ─── */
  function queuePurchase(purchaseData) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('purchaseQueue', 'readwrite');
        var store = tx.objectStore('purchaseQueue');
        purchaseData.timestamp = Date.now();
        purchaseData.status = 'pending';
        var req = store.add(purchaseData);
        req.onsuccess = function() { resolve(req.result); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }
  function getPendingPurchases() {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve) {
        var tx = database.transaction('purchaseQueue', 'readonly');
        var store = tx.objectStore('purchaseQueue');
        var index = store.index('status');
        var results = [];
        var req = index.openCursor('pending');
        req.onsuccess = function(event) {
          var cur = event.target.result;
          if(!cur) { resolve(results); return; }
          results.push(cur.value);
          cur.continue();
        };
        req.onerror = function() { resolve(results); };
      });
    });
  }
  function removeQueuedPurchase(queueId) {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction('purchaseQueue', 'readwrite');
        var store = tx.objectStore('purchaseQueue');
        var req = store.delete(queueId);
        req.onsuccess = function() { resolve(); };
        req.onerror = function() { reject(req.error); };
      });
    });
  }
  function countPendingPurchases() {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve) {
        var tx = database.transaction('purchaseQueue', 'readonly');
        var store = tx.objectStore('purchaseQueue');
        var index = store.index('status');
        var req = index.count('pending');
        req.onsuccess = function() { resolve(req.result); };
        req.onerror = function() { resolve(0); };
      });
    });
  }

  /* ─── CLEAR ─── */
  function clearAll() {
    return openDatabase().then(function(database) {
      return new Promise(function(resolve, reject) {
        var tx = database.transaction(['items', 'itemDetails', 'customers', 'suppliers', 'salesQueue', 'purchaseQueue', 'holdInvoices', 'meta'], 'readwrite');
        tx.objectStore('items').clear();
        tx.objectStore('itemDetails').clear();
        tx.objectStore('customers').clear();
        tx.objectStore('suppliers').clear();
        tx.objectStore('salesQueue').clear();
        tx.objectStore('purchaseQueue').clear();
        tx.objectStore('holdInvoices').clear();
        tx.objectStore('meta').clear();
        tx.oncomplete = function() { resolve(); };
        tx.onerror = function() { reject(tx.error); };
      });
    });
  }

  /* ─── PUBLIC API ─── */
  return {
    open: openDatabase,
    saveItems: saveItems,
    searchItems: searchItems,
    getItemById: getItemById,
    saveItemDetails: saveItemDetails,
    getItemDetails: getItemDetails,
    saveCustomers: saveCustomers,
    searchCustomers: searchCustomers,
    getCustomerById: getCustomerById,
    queueSale: queueSale,
    getPendingSales: getPendingSales,
    removeQueuedSale: removeQueuedSale,
    countPendingSales: countPendingSales,
    saveHoldInvoice: saveHoldInvoice,
    getHoldInvoices: getHoldInvoices,
    deleteHoldInvoice: deleteHoldInvoice,
    getHoldInvoiceByRef: getHoldInvoiceByRef,
    countHoldInvoices: countHoldInvoices,
    saveSuppliers: saveSuppliers,
    searchSuppliers: searchSuppliers,
    queuePurchase: queuePurchase,
    getPendingPurchases: getPendingPurchases,
    removeQueuedPurchase: removeQueuedPurchase,
    countPendingPurchases: countPendingPurchases,
    setMeta: setMeta,
    getMeta: getMeta,
    clearAll: clearAll
  };
})();
