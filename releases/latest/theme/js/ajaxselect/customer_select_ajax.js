//var selectionBoxId = $('#customer_id');

var base_url = $("#base_url").val();

var url_ = base_url+"customers/getCustomers/";

var searchFor = "Search Name/Mobile";

$(document).ready(function(){

         let init_customer_select2 = (typeof load_customer_select2 === 'function') ? load_customer_select2() : true;

         //If don't want to initiate customer select2 selection box
         if(init_customer_select2 == false){
            return true;
         }

         var selectionBoxId = $(getCustomerSelectionId());

         

         selectionBoxId.select2({
            allowClear: true,
            ajax: {
                  url: url_,
                  type: "post",
                  dataType: 'json',
                  delay: 250,
                  data: function (params) {
                     return {
                           searchTerm: params.term, // search term
                           store_id:$("#store_id").val(),
                     };
                  },
                  processResults: function (response) {
                     // Cache successful results for offline use
                     if (typeof MPOfflineDB !== 'undefined' && response && response.length) {
                        MPOfflineDB.saveCustomers(response).catch(function(e){ /* silent */ });
                     }
                     return { results: response };
                  },
                  transport: function(params, success, failure) {
                     var $request = $.ajax(params);
                     $request.then(success);
                     $request.fail(function() {
                        // Network failed — try offline cache
                        if (typeof MPOfflineDB !== 'undefined') {
                           var term = (params.data && params.data.searchTerm) ? params.data.searchTerm : '';
                           MPOfflineDB.searchCustomers(term, 10).then(function(items) {
                              success(items);
                           }).catch(function() {
                              failure();
                           });
                        } else {
                           failure();
                        }
                     });
                     return $request;
                  },
                  cache: false
            },
              placeholder: searchFor,
              minimumInputLength: 0,
              templateResult: formatRepo ,
              templateSelection: formatRepoSelection,
              current : testFun,

         });

         function testFun(element, callback){
            var data = [];
             $(element.val()).each(function () {
               data.push({id: this, text: this});

             });
             callback(data);

         }

         //After selection event
         selectionBoxId.on('select2:select', function(e) {
            if(e.params!=undefined){
                  var selectedOption = e.params.data;
                  // Customize behavior when an option is selected
                  previous_due = selectedOption.previous_due;
                  tot_advance = selectedOption.tot_advance;
                  // Store customer mobile for WhatsApp sharing
                  window.pos_customer_mobile = selectedOption.mobile || '';

                  set_the_previous_due(previous_due,tot_advance);

            }
           });



         //Searching data format
         function formatRepo (repo){

            if (repo.loading) {
               return repo.text;
            }

            return (repo.mobile!='' && repo.mobile!=null) ? repo.text +" - "+ repo.mobile : repo.text;
         }

         //Selected data view
         function formatRepoSelection (repo) {

             //$(repo.element).attr("data-mobile", repo.mobile);

              return repo.text;// + " - "+ repo.mobile;
         }
         

});//ready

function set_the_previous_due(previous_due,tot_advance){
   /**
    * Verify is function is function exist or not
    * 
    * */
   if(typeof set_previous_due === 'function'){
      set_previous_due(previous_due,tot_advance)
   } 
}

function autoLoadFirstCustomer(customer_id='') {

      var selectionBoxId = $(getCustomerSelectionId());

      function loadCustomerData(customer) {
         var option = new Option(customer.text, customer.id, true, true);
         selectionBoxId.append(option).trigger('change');
         selectionBoxId.trigger({
             type: 'select2:select',
             params: { data: [customer] }
         });
         set_the_previous_due(customer.previous_due, customer.tot_advance);
      }

      $.ajax({
          type: 'POST',
          url: url_+customer_id,
          dataType: 'json',
          delay: 250,
          async: false,
          data:{ store_id : $("#store_id").val() }
      }).then(function (serverResponse) {
         // Cache for offline
         if (typeof MPOfflineDB !== 'undefined' && serverResponse && serverResponse.length) {
            MPOfflineDB.saveCustomers(serverResponse).catch(function(e){ /* silent */ });
         }
         $.each(serverResponse, function(index, customer) {
            if(index == 0){ loadCustomerData(customer); }
         });
      }).fail(function(){
         // Network failed — try offline cache
         if (typeof MPOfflineDB !== 'undefined' && customer_id) {
            MPOfflineDB.getCustomerById(parseInt(customer_id)).then(function(cached){
               if (cached) { loadCustomerData(cached); }
            }).catch(function(){});
         }
      });
}