<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('finalize_count'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'stForm');
                echo form_open_multipart('products/finalize_count/'.$stock_count->id, $attrib);
                echo form_hidden('count_id', $stock_count->id);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("warehouse", "warehouse"); ?>
                                    <?php
                                    $wh[''] = '';
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="warehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
                                    ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="col-md-4">
								 <div class="form-group">
									 <?= lang("warehouse", "warehouse"); ?>
									<select name="warehouse" id="warehouse" class="form-control warehouse">
										<?php
											foreach($warehouses as $warehouse){
												if($this->session->userdata('warehouse_id') == $warehouse->id){
													echo '<option value="'.$this->session->userdata('warehouse_id').'" selected>'.$warehouse->name.'</option>';
												}else{
													echo '<option value="'.$warehouse->id.'">'. $warehouse->name . '</option>';
												}
											}
										?>
									</select>
								</div>
                            </div>
						<?php } ?>
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php                               
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category[]', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%" multiple="multiple"')
                                ?>
                            </div>
                        </div>
						<?php if ($Owner || $Admin || $this->session->userdata('user_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "date"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->erp->hrld(date('Y-m-d H:i:s'))), 'class="form-control input-tip" id="date" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference", "ref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $stoct_count_ref), 'class="form-control input-tip" id="ref"'); ?>
                            </div>
                        </div>
						
						<div class="col-md-12">
							<div class="form-group">
								<label for="csv_file"><?= lang("upload_file"); ?></label>
								<input type="file" data-browse-label="<?= lang('browse'); ?>" name="csv_file" class="form-control file" data-show-upload="false" data-show-preview="false" id="csv_file" required="required"/>
							</div>
							<div class="form-group">
								<?= lang("note", "qanote"); ?>
								<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="qanote" style="margin-top: 10px; height: 100px;"'); ?>
							</div>
						</div>
						
						<div class="clearfix"></div>

						<div class="col-md-12">
							<div class="fprom-group">
								<?= form_submit('stock_count', lang("submit"), 'id="stock_count" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
								<button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
						</div>
                    </div>
                </div>
				
                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {		
        $("#brand option[value=''], #category option[value='']").remove();
        $('.type').on('ifChecked', function(e){
            var type_opt = $(this).val();
            if (type_opt == 'partial')
                $('.partials').slideDown();
            else
                $('.partials').slideUp();
            $('.download_csv').slideDown();
        });
        $("#date").datetimepicker({format: site.dateFormats.js_ldate, fontAwesome: true, language: 'erp', weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0, startDate: "<?= $this->erp->hrld(date('Y-m-d H:i:s')); ?>"});
        
    });
</script>
