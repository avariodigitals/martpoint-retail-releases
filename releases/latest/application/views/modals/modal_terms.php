 <!-- **********************MODALS***************** -->
              <div class="modal fade" id="terms-modal" tabindex='-1'>
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                      <h4 class="modal-title"><?= $this->lang->line('terms_and_conditions'); ?></h4>
                    </div>
                    <div class="modal-body">
                      
                        <div class="row">
                          <div class="col-md-12">
                            <div class="box-body">
                              <div class="form-group">

                                <textarea class="form-control" id="invoice_terms" name="invoice_terms" placeholder="Enter Invoice Terms and Conditions" ><?php echo get_invoice_terms_for_pos()?></textarea>
                              </div>
                            </div>
                          </div>
                        </div>
                     
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-primary" data-dismiss="modal">Add</button>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->
              <!-- **********************MODALS END***************** -->