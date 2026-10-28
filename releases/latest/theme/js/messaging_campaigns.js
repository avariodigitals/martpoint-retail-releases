/* Shared desktop/mobile cost confirmation. No send happens on cancellation. */
(function () {
    'use strict';
    var active = false;
    function post(url, data) {
        var c = window.MP_CAMPAIGN_BILLING, body = new FormData();
        Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
        body.append(c.csrf_name, c.csrf_hash);
        return fetch(url, { method: 'POST', credentials: 'same-origin', body: body, headers: { Accept: 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error('Your request could not be confirmed. Check Messaging balance before retrying.'); return r.json(); });
    }
    function money(kobo) { return '₦' + (Number(kobo) / 100).toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    window.mpCampaignConfirm = function (id, test, done) {
        if (active) return Promise.resolve();
        active = true;
        var c = window.MP_CAMPAIGN_BILLING;
        return post(c.estimate, {id: id, test: test ? '1' : ''}).then(function (quote) {
            if (!quote.success) throw new Error(quote.message || 'A cost estimate could not be prepared.');
            if (!quote.ready) throw new Error('MartPoint messaging is paused or its provider is not ready. Open Messaging balance for details.');
            if (Number(quote.balance_kobo) < Number(quote.total_kobo)) throw new Error('Your messaging balance cannot cover ' + money(quote.total_kobo) + '. Add credit from Messaging balance.');
            var prompt = (test ? 'Test send to the store owner' : 'Send this campaign') + '\n\n' + quote.recipients + ' recipient(s), ' + quote.units + ' billable unit(s)' + (quote.encoding ? ' — ' + quote.encoding : '') + '\nEstimated message cost: ' + money(quote.net_kobo) + '\nTax: ' + money(quote.tax_kobo) + '\nEstimated total: ' + money(quote.total_kobo) + '\nAvailable balance: ' + money(quote.balance_kobo) + '\n\n' + quote.note + '\n\nContinue?';
            if (!window.confirm(prompt)) return null;
            return post(c.send, {id: id, test: test ? '1' : '', approved_cost_kobo: quote.total_kobo});
        }).then(function (result) { if (result && done) done(result); return result; })
            .catch(function (error) { if (done) done({status:'error', message:error.message || 'Messaging request failed.'}); })
            .finally(function () { active = false; });
    };
})();
