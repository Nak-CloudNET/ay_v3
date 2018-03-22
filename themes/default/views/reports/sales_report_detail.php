<style>
#PrRData {
		overflow-x: scroll;
		max-width: 100%;
		min-height: 300px;
		display: block;
		cursor: pointer;
		white-space: nowrap;
	}
</style>
<?php
	$v = "";
	if ($this->input->post('category_name')) {
		$v .= "&category_name=" . $this->input->post('category_name');
	}
	if (isset($sale_id)) {
		$v .= "&sale_id=" . $sale_id;
	}
	if (isset($biller)) {
		$v .= "&biller=" . $biller;
	}
	/* if($this->input->post('dates')){
		$getDate = $this->input->post('dates');
		$getDate_ = explode(" - ",$getDate[0]);
		$start_date   = $this->erp->fld($getDate_[0]);
		$end_date     = $this->erp->fld($getDate_[1]);
		
	}*/
?>
<script>
	$(document).ready(function(){
		$('#form').hide();
		$('.toggle_down').click(function (){
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		var stockInhand = 0;
		$(".stockInHand").each(function() {
			stockInhand += parseFloat($(this).html());
		});
		$(".tdStockInhand").html(formatQuantity(stockInhand));
		
		
		var saleQuantity = 0;
		$(".saleQuantity").each(function() {
			saleQuantity += parseFloat($(this).html());
		});
		$(".tdsaleQuantity").html(formatQuantity(saleQuantity));
		
		var saleReturnQuantity = 0;
		$(".saleReturnQuantity").each(function() {
			saleReturnQuantity += parseFloat($(this).html());
		});
		$(".tdsaleReturnQuantity").html(formatQuantity(saleReturnQuantity));
		
		var unitCost = 0;
		$(".unitCost").each(function() {
			var getunitCost = $(this).html();
			unitCost += parseFloat(getunitCost.replace(',', ''));
		});
		$(".tdunitCost").html(formatMoney(unitCost));
		
		var unitPrice = 0;
		$(".unitPrice").each(function() {
			var getUnitPrice = $(this).html();
			unitPrice += parseFloat(getUnitPrice.replace(',', ''));
		});
		$(".tdunitPrice").html(formatMoney(unitPrice));
		
		var item_dis = 0;
		$(".item_dis").each(function() {
			var getItem_dis = $(this).html();
			item_dis += parseFloat(getItem_dis.replace(',', ''));
		});
		$(".tditem_dis").html(formatMoney(item_dis));
		
		var order_dis = 0;
		$(".order_dis").each(function() {
			var getOrder_dis = $(this).html();
			order_dis += parseFloat(getOrder_dis.replace(',', ''));
		});
		$(".tdorder_dis").html(formatMoney(order_dis));
		
		var revenue = 0;
		$(".revenue").each(function() {
			var getRevenue = $(this).html();
			revenue += parseFloat(getRevenue.replace(',', ''));
		});
		$(".tdrevenue").html(formatMoney(revenue));
		
		var coms = 0;
		$(".coms").each(function() {
			var getComs = $(this).html();
			coms += parseFloat(getComs.replace(',', ''));
		});
		$(".tdcoms").html(formatMoney(coms));
		
		var refund = 0;
		$(".refund").each(function() {
			var getRefund = $(this).html();
			refund += parseFloat(getRefund.replace(',', ''));
		});
		$(".tdrefund").html(formatMoney(refund));
		
		var profit = 0;
		$(".profit").each(function() {
			var getProfit = $(this).html();
			profit += parseFloat(getProfit.replace(',', ''));
		});		
		$(".tdprofit").html(formatMoney(profit));
	});
</script>
<?php
	echo form_open('reports/saleReportDetail_actions', 'id="action-form"');
?>
<div class="box">
    <div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('sales_report_detail'); ?></h2>   
		<div class="box-icon" style="">
            
			<div class="box-icon">
				<ul class="btn-tasks">
					<li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i
								class="icon fa fa-toggle-up"></i></a></li>
					<li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i
								class="icon fa fa-toggle-down"></i></a></li>
				</ul>
			</div> 
			<div class="box-icon">
				<ul class="btn-tasks">
					<li class="dropdown"><a href="#" id="pdf" data-action="export_pdf" class="tip" title="<?= lang('download_pdf') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
					<li class="dropdown"><a href="#" id="excel" data-action="export_excel" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>				
				</ul>
			</div>
			<input type="hidden" id="datetime"  name="dates">			
		</div>
    </div>	
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?php echo form_close(); ?>
<?php } ?>
	<div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('customize_report'); ?></p>
                <div id="form">
                    <?php echo form_open("reports/getSalesReportDetail"); ?>
					
                    <div class="row">
						<div class="col-sm-4">
							<div class="form-group choose-date hidden-xs" style="width:100%;">
								<?= lang("date", "date") ?>
								<div class="controls">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
										<input type="text" value="<?= ($start_date > 0 && $end_date > 0 ? $start_date .' - '. $end_date : date('Y-m-d 00:00') . ' - ' . date('Y-m-d 23:59')) ?>" id="daterange" name ="daterange[]" class="form-control">
									</div>
								</div>
							</div>
						</div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category_name", "category_name") ?>
                                <?php
                                $cat['0'] = lang("all");
                                foreach ($cate as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category_name', $cat, (isset($_POST['category_name']) ? $_POST['category_name'] : ''), 'class="form-control select" id="category_name" placeholder="' . lang("select") . " " . lang("category_name") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product_name", "product_name") ?>
                                <?php
                                $pro['0'] = lang("all");
                                foreach ($products as $product) {
                                    $pro[$product->id] = $product->name;
                                }
                                echo form_dropdown('product_name', $pro, (isset($_POST['product_name']) ? $_POST['product_name'] : ''), 'class="form-control select" id="category_name" placeholder="' . lang("select") . " " . lang("product_name") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>  
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl["0"] = lang("all");
                                foreach ($billers as $biller) {
                                    $bl[$biller->cf5] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"),'class="btn btn-primary" id="btn_submit"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>

            </div>
        </div>
    </div>
	<div class="box-content" id="PrRData">
        <div class="row">
		    <div class="col-lg-12" style="margin-top: -46px; width : 1800px !important;">
			<?php 
				foreach($categories as $category){					
				?>					
					<div style="width:297px;border:1px solid #a9c8d6; height: 34px;padding-top:7px;background-color:#D9EDEF;padding-left:5px;margin-top:33px !important;"><input type="checkbox" name="check[]" value="<?= $category->id; ?>"/> <span style="color:blue;padding-left:10px;"><b><?= strtoupper($category->name);?></b></span></div>   
					<table class="table table-bordered table-striped">
						<thead>
							<tr>						
								<th style="min-width:296px;"><?php echo $this->lang->line("product_name"); ?></th>
								<th style="max-width:50px;"><?php echo $this->lang->line("stock_in_hand"); ?></th>
								<th><?php echo $this->lang->line("qty_sale"); ?></th>
								<th><?php echo $this->lang->line("qty_return"); ?></th>
								<th><?php echo $this->lang->line("unit_cost"); ?></th>
								<th><?php echo $this->lang->line("unit_price"); ?></th>
								<th><?php echo $this->lang->line('item_dis');?></th>
								<th><?php echo $this->lang->line('order_dis');?></th>
								<th style="min-width:140px;"><?php echo $this->lang->line("revenue"); ?></th>
								<th style="min-width:140px;"><?php echo $this->lang->line("coms"); ?></th>
								<th style="min-width:140px;"><?php echo $this->lang->line("refund"); ?></th>
								<th style="min-width:140px;"><?php echo $this->lang->line("profit"); ?></th>                       
							</tr>
						</thead>
						<tbody>	
							<?= $this->reports_model->getDataReportDetail($category->id, $start, $end, $biller_id)?>
						</tbody>
					</table>
				  <?php				
				}	
			?>
            </div>
        </div>
		<br/>
		<br/>
            <table class="table table-bordered table-hover table-striped table-condensed" style="min-width: 1770px;">
				<thead>
					<tr>						
						<th style="min-width:290px;"></th>
						<th style="max-width:50px;"><?php echo $this->lang->line("stock_in_hand"); ?></th>
						<th><?php echo $this->lang->line("qty_sale"); ?></th>
						<th><?php echo $this->lang->line("qty_return"); ?></th>
						<th><?php echo $this->lang->line("unit_cost"); ?></th>
						<th><?php echo $this->lang->line("unit_price"); ?></th>
						<th><?php echo $this->lang->line('item_dis');?></th>
						<th><?php echo $this->lang->line('order_dis');?></th>
						<th style="min-width:140px;"><?php echo $this->lang->line("revenue"); ?></th>
						<th style="min-width:140px;"><?php echo $this->lang->line("coms"); ?></th>
						<th style="min-width:140px;"><?php echo $this->lang->line("refund"); ?></th>
						<th style="min-width:140px;"><?php echo $this->lang->line("profit"); ?></th>                      
					</tr>
				</thead>
				<tbody>	
					<tr style="background-color:#F2F5E9;text-align:center;font-weight:bold;">
						<td style="width:225px;">Total</td>
						<td class="tdStockInhand" style="text-align:center !important;"></td>
						<td class="tdsaleQuantity" style="text-align:center !important;"></td>
						<td class="tdsaleReturnQuantity" style="text-align:center !important;"></td>
						<td class="tdunitCost" style="text-align:center !important;"></td>
						<td class="tdunitPrice" style="text-align:center !important;"></td>
						<td class="tditem_dis" style="text-align:center !important;"></td>
						<td class="tdorder_dis" style="text-align:center !important;"></td>
						<td class="tdrevenue" style="text-align:center !important;"></td>
						<td class="tdcoms" style="text-align:center !important;"></td>
						<td class="tdrefund" style="text-align:center !important;"></td>
						<td class="tdprofit" style="text-align:center !important;"></td>
					</tr>
				</tbody>
			</table>
    </div>
</div>
<script type="text/javascript">
         
         $('#excel').click( function(){
			var date = $('#daterange').val();
			  $('#datetime').val(date); 
		 });
		  $('#pdf').click( function(){
			var date = $('#daterange').val();
			  $('#datetime').val(date); 
		 }); 
		 
</script>