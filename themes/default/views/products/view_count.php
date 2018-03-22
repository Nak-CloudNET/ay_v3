<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
	@media print{
		.modal-content, modal-dialog{
			border: none !important;
		}
	}
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('stock_count'); ?></h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-xs-12">
                <table class="table table-bordered table-condensed">
                        <tbody>
                            <tr>
                                <td><?= lang('warehouse'); ?></td>
                                <td><?= $warehouse->name.' ( '.$warehouse->code.' )'; ?></td>
                            </tr>
							<tr>
                                <td><?= lang('category_name'); ?></td>
                                <td><?= $category_name; ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('start_date'); ?></td>
                                <td><?= $this->erp->hrld($stock_count->date); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('end_date'); ?></td>
                                <td><?= $this->erp->hrld($stock_count->updated_at); ?></td>
                            </tr>
                            <tr>
                                <td><?= lang('reference'); ?></td>
                                <td><?= $stock_count->reference_no; ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div>
                        <table class="table table-bordered table-hover table-striped order-table">
                            <thead>
                            <tr>
                                <th style="text-align:center; vertical-align:middle;"><?= lang("no"); ?></th>
                                <th style="vertical-align:middle;"><?= lang("description"); ?></th>
                                <th style="text-align:center; vertical-align:middle;"><?= lang("expected"); ?></th>
                                <th style="text-align:center; vertical-align:middle;"><?= lang("counted"); ?></th>
                                <th style="text-align:center; vertical-align:middle;"><?= lang("difference"); ?></th>
                                <th style="text-align:center; vertical-align:middle;"><?= lang("cost"); ?></th>
                            </tr>
                            </thead>

                            <tbody>
								<?php 
									$r = 1; $total = 0; $items = 0;
									$products = array();
									foreach ($stock_count_items as $item){ 
										$products[$item->product_code] = array(
											'code' 		=> $item->product_code,
											'counted' 	=> $item->counted
										);
									}									
									
									foreach($warehouse_product as $row){
										$counted = 0;
										if($products[$row->code]['counted']){
											$counted = $products[$row->code]['counted'];
										} 
									?>
										<tr>
											<td style="text-align:center; width:25px;"><?= $r; ?></td>
											<td style="text-align:left;">
												<?= $row->code .' - '.$row->label; ?>
											</td>
											<td style="text-align:center; width:80px;">
												<?= $this->erp->formatQuantity($row->quantity); ?>
											</td>
											<td style="text-align:center; width:80px;">
												<?= $this->erp->formatQuantity($counted); ?>
											</td>
											<td style="text-align:right; width:80px;">
												<?= $this->erp->formatQuantity($counted - $row->quantity); ?>
											</td>
											<td style="text-align:right; width:100px;">
												<?= $this->erp->formatMoney($row->cost * ($counted - $row->quantity)); ?>
											</td>
										</tr>
										<?php $r++;
										$items += $counted - $row->expected;
										$total += $row->cost * ($counted - $row->expected);
									} ?>
								<tr style="font-weight : bold !important;">
                                    <td colspan="4"><?= lang('total'); ?></td>
                                    <td style="text-align:right; width:80px;">
                                    <?= $this->erp->formatQuantity($items); ?>
                                    </td>
                                    <td style="text-align:right; width:100px;">
                                        <?= $this->erp->formatMoney($total); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
