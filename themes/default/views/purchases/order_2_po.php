<script type="text/javascript">
    var count = 1, an = 1, po_edit = true, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, DC = '<?=$default_currency->code?>', shipping = 0,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>, poitems = {},
        audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(window).bind("load", function() {
        <?= ($inv->status == 'received' || $inv->status == 'partial') ? '$(".rec_con").show();' : '$(".rec_con").hide();'; ?>
    });
    $(document).ready(function () {
        <?= ($inv->status == 'received' || $inv->status == 'partial') ? '$(".rec_con").show();' : '$(".rec_con").hide();'; ?>
        $('#postatus').change(function(){
            var st = $(this).val();
            if (st == 'received' || st == 'partial') {
                $(".rec_con").show();
            } else {
                $(".rec_con").hide();
            }
        });
        <?php if ($inv) { ?>
        localStorage.setItem('podate', '<?= date($dateFormats['php_ldate'], strtotime($inv->date))?>');
        localStorage.setItem('posupplier', '<?=$inv->supplier_id?>');
        localStorage.setItem('poref', '<?=$inv->reference_no?>');
        localStorage.setItem('powarehouse', '<?=$inv->warehouse_id?>');
        localStorage.setItem('postatus', '<?=$inv->status?>');
        localStorage.setItem('ponote', '<?= str_replace(array("\r", "\n"), "", $this->erp->decode_html($inv->note)); ?>');
        localStorage.setItem('podiscount', '<?=$inv->order_discount_id?>');
        localStorage.setItem('potax2', '<?=$inv->order_tax_id?>');
        localStorage.setItem('poshipping', '<?=$inv->shipping?>');
        localStorage.setItem('popayment_term', '<?=$inv->payment_term?>');
        localStorage.setItem('slpayment_status', '<?=$inv->payment_status?>');
        if (parseFloat(localStorage.getItem('potax2')) >= 1 || localStorage.getItem('podiscount').length >= 1 || parseFloat(localStorage.getItem('poshipping')) >= 1) {
            localStorage.setItem('poextras', '1');
        }
        //localStorage.setItem('posupplier', '<?=$inv->supplier_id?>');
        localStorage.setItem('poitems', JSON.stringify(<?=$inv_items;?>));
        <?php } ?>

        <?php if ($Owner || $Admin) { ?>
        $(document).on('change', '#podate', function (e) {
            localStorage.setItem('podate', $(this).val());
        });
        if (podate = localStorage.getItem('podate')) {
            $('#podate').val(podate);
        }
        <?php } ?>
        ItemnTotals();
        $("#add_item").autocomplete({
            source: '<?= site_url('purchases/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 200,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_purchase_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        $('#add_item').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $(this).autocomplete("search");
            }
        });

        $(document).on('click', '#addItemManually', function (e) {
            if (!$('#mcode').val()) {
                $('#mError').text('<?=lang('product_code_is_required')?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mname').val()) {
                $('#mError').text('<?=lang('product_name_is_required')?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcategory').val()) {
                $('#mError').text('<?=lang('product_category_is_required')?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#munit').val()) {
                $('#mError').text('<?=lang('product_unit_is_required')?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcost').val()) {
                $('#mError').text('<?=lang('product_cost_is_required')?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mprice').val()) {
                $('#mError').text('<?=lang('product_price_is_required')?>');
                $('#mError-con').show();
                return false;
            }

            var msg, row = null, product = {
                type: 'standard',
                code: $('#mcode').val(),
                name: $('#mname').val(),
                tax_rate: $('#mtax').val(),
                tax_method: $('#mtax_method').val(),
                category_id: $('#mcategory').val(),
                unit: $('#munit').val(),
                cost: $('#mcost').val(),
                price: $('#mprice').val()
            };

            $.ajax({
                type: "get", async: false,
                url: site.base_url + "products/addByAjax",
                data: {token: "<?= $csrf; ?>", product: product},
                dataType: "json",
                success: function (data) {
                    if (data.msg == 'success') {
                        row = add_purchase_item(data.result);
                    } else {
                        msg = data.msg;
                    }
                }
            });
            if (row) {
                $('#mModal').modal('hide');
                //audio_success.play();
            } else {
                $('#mError').text(msg);
                $('#mError-con').show();
            }
            return false;

        });
        $(window).bind('beforeunload', function (e) {
            $.get('<?=site_url('welcome/set_data/remove_pols/1');?>');
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function (e) {
            $(window).unbind('beforeunload');
        });
        $('#edit_pruchase').click(function () {
            $(window).unbind('beforeunload');
            $('form.edit-po-form').submit();
        });

    });


</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_purchase'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-po-form');
                echo form_open_multipart("purchases/order_2_po/" . $inv->id, $attrib)
                ?>


                <div class="row">
                    <div class="col-lg-12">

                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "podate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->erp->hrld($purchase->date)), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "poref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $purchase->reference_no), 'class="form-control input-tip" id="poref" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("warehouse", "powarehouse"); ?>
                                <?php
                                $wh[''] = '';
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $purchase->warehouse_id), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("status", "postatus"); ?>
                                <?php
                                $post = array('received' => lang('received'), 'partial' => lang('partial'), 'pending' => lang('pending'), 'ordered' => lang('ordered'));
                                echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : $purchase->status), 'id="postatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("status") . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("payment_term", "popayment_term"); ?>
                                <?php echo form_input('payment_term', (isset($_POST['payment_term']) ? $_POST['payment_term'] : $purchase->payment_term), 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div
                                    class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("supplier", "posupplier"); ?>
                                            <div class="input-group">
                                                <input type="hidden" name="supplier" value="" id="posupplier"
                                                       class="form-control" style="width:100%;"
                                                       placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">

                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="#" id="removeReadonly">
                                                        <i class="fa fa-unlock" id="unLock"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually"><i class="fa fa-2x fa-plus-circle addIcon"
                                                                            id="addIcon"></i></a></div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?></label>

                                <div class="controls table-controls">
                                    <table id="poTable"
                                           class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
											<th  class=""><?= lang("no"); ?></th>
                                            <th class="col-md-4"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                            <?php
                                            if ($Settings->product_expiry) {
                                                echo '<th class="col-md-1">' . $this->lang->line("expiry_date") . '</th>';
                                            }
                                            ?>
											<th class="col-md-1"><?= lang("price"); ?></th>
                                            <th class="col-md-1"><?= lang("net_unit_cost"); ?></th>
                                            <th class="col-md-1"><?= lang("quantity"); ?></th>
											<th class="col-md-1"><?= lang("stock_in_hand"); ?></th>
                                            <th class="col-md-1 rec_con"><?= lang("received"); ?></th>
                                            <?php
                                            if ($Settings->product_discount) {
                                                echo '<th class="col-md-1">' . $this->lang->line("discount") . '</th>';
                                            }
                                            ?>
                                            <?php
                                            if ($Settings->tax1) {
                                                echo '<th class="col-md-1">' . $this->lang->line("product_tax") . '</th>';
                                            }
                                            ?>
                                            <th><?= lang("subtotal"); ?> (<span
                                                    class="currency"><?= $default_currency->code ?></span>)
                                            </th>
                                            <th style="width: 30px !important; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>

                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="checkbox" class="checkbox" id="extras" value=""/>
								<label for="extras" class="padding05"><?= lang('more_options') ?></label>
                            </div>
                            <div class="row" id="extras-con" style="display: none;">
                                
								<div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("discount_label", "podiscount"); ?>
                                        <?php echo form_input('discount', '', 'class="form-control input-tip" id="podiscount"'); ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("shipping", "poshipping"); ?>
                                        <?php echo form_input('shipping', '', 'class="form-control input-tip" id="poshipping"'); ?>
                                    </div>
                                </div>
								
								<?php if ($Settings->tax1) { ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('order_tax', 'potax2') ?>
                                            <?php
                                            $tr[""] = "";
                                            foreach ($tax_rates as $tax) {
                                                $tr[$tax->id] = $tax->name;
                                            }
                                            echo form_dropdown('order_tax', $tr, "", 'id="potax2" class="form-control input-tip select" style="width:100%;"');
                                            ?>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("payment_status", "slpayment_status"); ?>
                                        <?php $pst = array('pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid'));
                                        echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" id="slpayment_status"'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="clearfix"></div>
                            <div id="payments" style="display: none;">
                                
                                <div class="well well-sm well_1">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?= lang("payment_reference_no", "payment_reference_no"); ?>
                                                    <?= form_input('payment_reference_no', (isset($_POST['payment_reference_no']) ? $_POST['payment_reference_no'] : $payment_ref), 'class="form-control tip" id="payment_reference_no" required="required"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="payment">
                                                    <div class="form-group ngc">
                                                        <?= lang("amount", "amount_1"); ?>
                                                        <input name="amount-paid" type="text" id="amount_1" class="pa form-control kb-pad amount" value="<?= $inv->paid;?>" />
                                                    </div>
                                                    <div class="form-group gc" style="display: none;">
                                                        <?= lang("gift_card_no", "gift_card_no"); ?>
                                                        <input name="gift_card_no" type="text" id="gift_card_no"
                                                               class="pa form-control kb-pad"/>

                                                        <div id="gc_details"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <?= lang("paying_by", "paid_by_1"); ?>
                                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                                        <option value="cash"><?= lang("cash"); ?></option>
                                                        <option value="gift_card"><?= lang("gift_card"); ?></option>
                                                        
                                                        <option value="CC"><?= lang("cc"); ?></option>
                                                        <option value="Cheque"><?= lang("cheque"); ?></option>
                                                        <option value="other"><?= lang("other"); ?></option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="pcc_1" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_no" type="text" id="pcc_no_1"
                                                               class="form-control" placeholder="<?= lang('cc_no') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_holder" type="text" id="pcc_holder_1"
                                                               class="form-control"
                                                               placeholder="<?= lang('cc_holder') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <select name="pcc_type" id="pcc_type_1"
                                                                class="form-control pcc_type"
                                                                placeholder="<?= lang('card_type') ?>">
                                                            <option value="Visa"><?= lang("Visa"); ?></option>
                                                            <option
                                                                value="MasterCard"><?= lang("MasterCard"); ?></option>
                                                            <option value="Amex"><?= lang("Amex"); ?></option>
                                                            <option value="Discover"><?= lang("Discover"); ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_month" type="text" id="pcc_month_1"
                                                               class="form-control" placeholder="<?= lang('month') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_year" type="text" id="pcc_year_1"
                                                               class="form-control" placeholder="<?= lang('year') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_ccv" type="text" id="pcc_cvv2_1"
                                                               class="form-control" placeholder="<?= lang('cvv2') ?>"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group dp" style="display: none;">
                                            <?= lang("customer", "customer1"); ?>
                                                    <?php
                                                    $customers1[] = array();
                                                    foreach($customers as $customer){
                                                        $customers1[$customer->id] = $customer->name;
                                                    }
                                                echo form_dropdown('customer', $customers1, '' , 'class="form-control" id="customer1"');
                                            ?>
                                            <?= lang("deposit_amount", "deposit_amount"); ?>
                                            
                                            <div id="dp_details"></div>
                                        </div>
                                        
                                        
                                        <div class="depreciation_1" style="display:none;">
                                            <div class="form-group">
                                                <?= lang("depre_term", "depreciation_1"); ?>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <input name="depreciation_rate1" type="text" id="depreciation_rate_1"
                                                               class="form-control depreciation_rate1"
                                                               placeholder="<?= lang('rate (%)') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">

                                                        <input name="depreciation_term" type="text" id="depreciation_term_1"
                                                               class="form-control kb-pad" value=""
                                                               placeholder="<?= lang('term (month)') ?>"/>
                                                        <input type="hidden" id="current_date" class="current_date" class="current_date[]" value="<?php echo date('m/d/Y'); ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <select name="depreciation_type" id="depreciation_type_1"
                                                                class="form-control depreciation_type"
                                                                placeholder="<?= lang('payment type') ?>">
                                                            <option value=""> &nbsp; </option>
                                                            <option value="1"><?= lang("Normal"); ?></option>
                                                            <option value="2"><?= lang("Custom"); ?></option>
                                                            <option value="3"><?= lang("Fixed"); ?></option>
                                                            <option value="4"><?= lang("Normal(Fixed)"); ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group" id="print_" style="display:none">
                                                        <button type="button" class="btn btn-primary col-md-12 print_depre" id="print_depre" style="margin-bottom:5px;"><i class="fa fa-print"> &nbsp; </i>
                                                            <?= lang('Print') ?>
                                                        </button>
                                                        <button type="button" class="btn btn-primary col-md-12 export_depre" id="export_depre" style="margin-bottom:5px;"><i class="fa fa-file-excel-o"> &nbsp; </i>
                                                                <?= lang('export') ?>
                                                            </button>
                                                        <div style="clear:both; height:15px;"></div>
                                                    </div>
                                                 </div>
                                            </div>
                                            <div class="form-group">
                                                
                                                <div class="dep_tbl" style="display:none;">
                                                    <table border="1" width="100%" class="table table-bordered table-condensed tbl_dep" id="tbl_dep">
                                                        <tbody>
                                                    
                                                        </tbody>
                                                    </table>
                                                    <table id="export_tbl" width="70%" style="display:none;">
                                                        
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="pcheque_1" style="display:none;">
                                            <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                                <input name="cheque_no" type="text" id="cheque_no_1"
                                                       class="form-control cheque_no"/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?= lang('payment_note', 'payment_note_1'); ?>
                                            <textarea name="payment_note" id="payment_note_1"
                                                      class="pa form-control kb-text payment_note"></textarea>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                
                            </div>

                            <div class="clearfix"></div>
                            <div class="form-group">
                                <?= lang("note", "ponote"); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>

                        </div>
                        <div class="col-md-12">
                            <div
                                class="from-group"><?php echo form_submit('edit_pruchase', $this->lang->line("submit"), 'id="edit_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>

                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                   <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('suppliers') ?></label>
                            <div class="col-sm-8">
                               <input type="hidden" name="psupplier[]" value="" id="psupplier"class="form-control" style="width:100%;" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
                                <?php
    /*
                                $su[""] = "";
                                foreach ($suppliers as $sup) {
                                    $su[$sup->id] = $sup->name;
                                }
                                echo form_dropdown('psupplier[]', $su, "", 'id="psupplier" class="form-control pos-input-tip" style="width:100%;"');
                                */
                                ?>
                            </div>
                        </div>
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_expiry) { ?>
                        <div class="form-group">
                            <label for="pexpiry" class="col-sm-4 control-label"><?= lang('product_expiry') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="pexpiry">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>

                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pcost">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_cost'); ?></th>
                            <th style="width:25%;"><span id="net_cost"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_cost" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_cost" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_standard_product') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="alert alert-danger" id="mError-con" style="display: none;">
                    <!--<button data-dismiss="alert" class="close" type="button">×</button>-->
                    <span id="mError"></span>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('product_code', 'mcode') ?> *
                            <input type="text" class="form-control" id="mcode">
                        </div>
                        <div class="form-group">
                            <?= lang('product_name', 'mname') ?> *
                            <input type="text" class="form-control" id="mname">
                        </div>
                        <div class="form-group">
                            <?= lang('category', 'mcategory') ?> *
                            <?php
                            $cat[''] = "";
                            foreach ($categories as $category) {
                                $cat[$category->id] = $category->name;
                            }
                            echo form_dropdown('category', $cat, '', 'class="form-control select" id="mcategory" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group">
                            <?= lang('unit', 'munit') ?> *
                            <input type="text" class="form-control" id="munit">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cost', 'mcost') ?> *
                            <input type="text" class="form-control" id="mcost">
                        </div>
                        <div class="form-group">
                            <?= lang('price', 'mprice') ?> *
                            <input type="text" class="form-control" id="mprice">
                        </div>

                        <?php if ($Settings->tax1) { ?>
                            <div class="form-group">
                                <?= lang('product_tax', 'mtax') ?>
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                            <div class="form-group all">
                                <?= lang("tax_method", "mtax_method") ?>
                                <?php
                                $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                                echo form_dropdown('tax_method', $tm, '', 'class="form-control select" id="mtax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
