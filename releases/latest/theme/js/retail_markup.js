(function (root) {
    'use strict';
    function calculate(wholesale, config) {
        if (!config || config.percent === null || config.percent === undefined) return null;
        var raw = String(wholesale == null ? '' : wholesale).replace(/,/g, '').trim();
        var value = Number(raw), percent = Number(config.percent);
        if (raw === '' || !Number.isFinite(value) || value < 0 || !Number.isFinite(percent) || percent < 0 || percent > 10000) return null;
        var decimals = Math.max(0, Math.min(4, Number(config.decimals)));
        var scale = Math.pow(10, decimals);
        return (Math.round((value * (1 + percent / 100) + Number.EPSILON) * scale) / scale).toFixed(decimals);
    }
    function targetName(name) {
        if (name === 'sales_price') return 'mrp';
        if (name === 'm_sales_price') return 'm_mrp';
        if (name === 'barcode_sprice[]') return 'barcode_mrp[]';
        if (name === 'selling_unit_wholesale_price[]') return 'selling_unit_selling_price[]';
        var match = /^td_data_(\d+)_6$/.exec(name);
        return match ? 'td_data_' + match[1] + '_10' : null;
    }
    function install(doc, config) {
        var busy = false;
        function update(source) {
            var name = targetName(source.name || '');
            if (!name || source.disabled) return;
            var scope = source.closest('tr') || source.form || doc;
            // Services do not have a wholesale/retail relationship.
            var form = source.form;
            var service = form && form.querySelector('[name="item_type"]:checked');
            var target = scope.querySelector('[name="' + name + '"]');
            if (!target || target.disabled) return;
            if (service && service.value === 'service') { target.readOnly = false; return; }
            var price = calculate(source.value, config);
            if (price === null) return;
            target.readOnly = true;
            target.title = 'Automatically calculated from wholesale + ' + config.percent + '%';
            target.value = price;
            // The form's existing handlers keep batch values and totals in sync.
            target.dispatchEvent(new Event('change', {bubbles: true}));
        }
        function refresh() {
            if (busy) return;
            busy = true;
            doc.querySelectorAll('input[name]').forEach(update);
            busy = false;
        }
        function changed(event) {
            if (busy) return;
            var name = event.target.name || '';
            if (!targetName(name)) {
                if (name === 'item_type' || /^(m_)?(price|purchase_price|profit_margin)$/.test(name) || /^td_data_\d+_[345]$/.test(name)) refresh();
                return;
            }
            busy = true;
            update(event.target);
            busy = false;
        }
        doc.addEventListener('input', changed);
        doc.addEventListener('change', changed);
        // Cost/margin calculators sometimes set wholesale programmatically.
        // Read it again before the existing form's submit/click save handler.
        doc.addEventListener('submit', refresh, true);
        doc.addEventListener('click', function (event) {
            if (event.target.closest('button, input[type="submit"]')) refresh();
        }, true);
        if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', refresh);
        else refresh();
        // Variant generators and pack rows are inserted after the page loads.
        if (root.MutationObserver) {
            new root.MutationObserver(function (records) {
                if (records.some(function (record) {
                    return Array.from(record.addedNodes).some(function (node) {
                        return node.nodeType === 1 && (node.matches('input') || node.querySelector('input'));
                    });
                })) refresh();
            }).observe(doc.body, {childList: true, subtree: true});
        }
        return {refresh: refresh};
    }
    if (typeof module === 'object' && module.exports) module.exports = {calculate: calculate, targetName: targetName, install: install};
    if (root.document && root.mpRetailMarkupConfig && !root.mpRetailMarkupInstalled) {
        root.mpRetailMarkupInstalled = install(root.document, root.mpRetailMarkupConfig);
    }
}(typeof window !== 'undefined' ? window : globalThis));
