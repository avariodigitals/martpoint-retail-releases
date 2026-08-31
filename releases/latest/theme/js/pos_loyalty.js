// MartPoint POS Loyalty Integration
// Handles customer loyalty info display and redemption

var base_url = $("#base_url").val();

// Hook into customer selection to load loyalty data
$(document).ready(function(){
    // Listen for select2 customer selection
    var customerSelect = $('#customer_id');
    if(customerSelect.length){
        customerSelect.on('select2:select', function(e){
            var data = e.params ? e.params.data : null;
            if(data && data.id){
                loadLoyaltyData(data.id);
            }
        });
        // Also handle on change for direct value changes
        customerSelect.on('change', function(){
            var val = $(this).val();
            if(val && val != getWalkInCustomerId()){
                loadLoyaltyData(val);
            } else {
                hideLoyaltyInfo();
            }
        });
    }
});

function getWalkInCustomerId(){
    // This should match the PHP walk-in customer ID
    return 1; // Default walk-in
}

function loadLoyaltyData(customer_id){
    if(!customer_id || customer_id == getWalkInCustomerId()){
        hideLoyaltyInfo();
        return;
    }
    $.post(base_url + 'loyalty/get_customer_loyalty_json', { customer_id: customer_id }, function(res){
        if(res && res.loyalty_points !== undefined){
            showLoyaltyInfo(res);
        } else {
            hideLoyaltyInfo();
        }
    }, 'json');
}

function showLoyaltyInfo(data){
    $('#loyalty_customer_info').show();
    $('#customer_tier_display').text(data.loyalty_tier || 'Bronze');
    $('#customer_points_display').text(data.loyalty_points || 0);
    $('#customer_store_credit_display').text(data.store_credit_balance || 0);
    $('#customer_gift_card_display').text(data.gift_card_balance || 0);
}

function hideLoyaltyInfo(){
    $('#loyalty_customer_info').hide();
}

// Redemption functions called from payment modal
function applyStoreCredit(){
    var customer_id = $('#customer_id').val();
    var amount = parseFloat($('#store_credit_redeem_amount').val() || 0);
    if(amount <= 0) return;
    
    $.post(base_url + 'store_credit/redeem_ajax', {
        customer_id: customer_id,
        amount: amount,
        sales_id: 0 // Will be updated after sale save
    }, function(res){
        if(res == 'success'){
            success_show('Store credit applied');
            // Deduct from grand total
            var current = parseFloat($('#grand_total').val() || $('#tot_grand').val() || 0);
            var newTotal = Math.max(0, current - amount);
            updateGrandTotal(newTotal);
            loadLoyaltyData(customer_id);
        } else {
            error_show('Failed to apply store credit');
        }
    });
}

function redeemPoints(){
    var customer_id = $('#customer_id').val();
    var points = parseFloat($('#points_redeem_amount').val() || 0);
    if(points <= 0) return;
    
    $.post(base_url + 'loyalty/redeem_points_ajax', {
        customer_id: customer_id,
        points: points,
        sales_id: 0
    }, function(res){
        if(res == 'success'){
            success_show('Points redeemed');
            // Get settings to calculate discount
            $.post(base_url + 'loyalty/get_settings_json', {}, function(settings){
                if(settings && settings.redemption_rate){
                    var discount = (points / 100) * settings.redemption_rate;
                    var current = parseFloat($('#grand_total').val() || $('#tot_grand').val() || 0);
                    var newTotal = Math.max(0, current - discount);
                    updateGrandTotal(newTotal);
                }
            }, 'json');
            loadLoyaltyData(customer_id);
        } else {
            error_show('Failed to redeem points');
        }
    });
}

function applyGiftCard(){
    var card_number = $('#gift_card_number').val();
    var amount = parseFloat($('#gift_card_redeem_amount').val() || 0);
    if(!card_number || amount <= 0) return;
    
    // First validate card
    $.post(base_url + 'gift_cards/validate_card_ajax', { card_number: card_number }, function(card){
        if(card.valid && card.balance >= amount){
            $.post(base_url + 'gift_cards/redeem_ajax', {
                card_id: card.card_id,
                amount: amount,
                customer_id: $('#customer_id').val(),
                sales_id: 0
            }, function(res){
                if(res == 'success'){
                    success_show('Gift card applied');
                    var current = parseFloat($('#grand_total').val() || $('#tot_grand').val() || 0);
                    var newTotal = Math.max(0, current - amount);
                    updateGrandTotal(newTotal);
                    loadLoyaltyData($('#customer_id').val());
                } else {
                    error_show('Failed to apply gift card');
                }
            });
        } else {
            error_show('Invalid card or insufficient balance');
        }
    }, 'json');
}

function updateGrandTotal(newTotal){
    // Update the visible total - this is a helper that may need POS-specific implementation
    if(typeof final_total === 'function'){
        // If POS has a final_total function, use it
        // Otherwise just update the display
    }
    if($('#tot_grand').length) $('#tot_grand').val(newTotal.toFixed(2));
    if($('#grand_total').length) $('#grand_total').val(newTotal.toFixed(2));
}
