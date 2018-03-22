<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="transfer" content="width=device-width, initial-scale=1.0">
		<link href="<?= $assets ?>styles/helpers/bootstrap.min.css" rel="stylesheet"/>
		<style type="text/css">
			body {
				height: 100%;
				margin : 0 auto;
				background: #FFF;
				font-size:15px;
			}
			@media print
			{
				#no_print
				{
					display: none !important;
				}
				.no_print
				{
					display: none !important;
				}
			}
		</style>
	</head>
	<body>
		<div class="invoice" id="wrap" style="width: 90%; margin: 0 auto;">
			<div class="row">
				<div style="text-align:center">
					<?php if ($logo) { ?>
						<div class="text-center" style="margin-bottom:20px;">
							<img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
								 alt="<?= $Settings->site_name; ?>">
						</div>
					<?php } ?>
				</div>
				<div class="well well-sm">
					<div class="row bold">
						<div class="col-xs-4"><?= lang("date"); ?>: <?= $this->erp->hrld($transfer->date); ?>
							<br><?= lang("ref"); ?>: <?= $transfer->transfer_no; ?></div>
						<div class="col-xs-6 pull-right text-right">
							<?php $br = $this->erp->save_barcode($transfer->transfer_no, 'code39', 35, false); ?>
							<img src="<?= base_url() ?>assets/uploads/barcode<?= $this->session->userdata('user_id') ?>.png"
								 alt="<?= $transfer->transfer_no ?>"/>
							<?php $this->erp->qrcode('link', urlencode(site_url('transfers/view/' . $transfer->id)), 1); ?>
							<img src="<?= base_url() ?>assets/uploads/qrcode<?= $this->session->userdata('user_id') ?>.png"
								 alt="<?= $transfer->transfer_no ?>"/>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="clearfix"></div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<?= lang("from"); ?>:
						<h3 style="margin-top:10px;"><?= $from_warehouse->name . " ( " . $from_warehouse->code . " )"; ?></h3>
						<?= "<p>" . $from_warehouse->address . "</p><p>" . $from_warehouse->phone . "<br>" . $from_warehouse->email . "</p>";
						?>
					</div>
					<div class="col-xs-6">
						<?= lang("to"); ?>:<br/>

						<h3 style="margin-top:10px;"><?= $to_warehouse->name . " ( " . $to_warehouse->code . " )"; ?></h3>
						<?= "<p>" . $to_warehouse->address . "</p><p>" . $to_warehouse->phone . "<br>" . $to_warehouse->email . "</p>";
						?>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table table-bordered table-hover table-striped order-table">
						<thead>
						<tr>
							<th style="text-align:center; vertical-align:middle;"><?= lang("no"); ?></th>
							<th style="vertical-align:middle;"><?= lang("description"); ?></th>
							<th style="text-align:center; vertical-align:middle;"><?= lang("quantity"); ?></th>
							<?php if ($Owner || $Admin || $GP['transfers-net_unit_cost']) { ?>
								<th style="text-align:center; vertical-align:middle;"><?= lang("unit_cost"); ?></th>
							<?php } ?>
							<?php if ($this->Settings->tax1) {
								echo '<th style="text-align:center; vertical-align:middle;">' . lang("tax") . '</th>';
							} ?>
							<?php if ($Owner || $Admin || $GP['transfers-subtotal']) { ?>
							<th style="text-align:center; vertical-align:middle;"><?= lang("subtotal"); ?></th>
							<?php } ?>
						</tr>
						</thead>

						<tbody>
						<?php $r = 1;
						foreach ($rows as $row): 
							$qty += $row->quantity;
							?>
							<tr>
								<td style="text-align:center; width:25px;"><?= $r; ?></td>
								<td style="text-align:left;"><?= $row->product_name . " (" . $row->product_code . ")" . ($row->variant ? ' (' . $row->variant . ')' : ''); ?></td>
								<td style="text-align:center; width:80px; "><?= $this->erp->formatQuantity($row->quantity); ?></td>
								<?php if ($Owner || $Admin || $GP['transfers-net_unit_cost']) { ?>
									<td style="width: 100px; text-align:right; padding-right:10px; vertical-align:middle;"><?= $this->erp->formatMoney($row->net_unit_cost); ?></td>
								<?php } ?>
								<?php if ($this->Settings->tax1) {
									echo '<td style="width: 80px; text-align:right; vertical-align:middle;"><!--<small>(' . $row->tax . ')</small>--> ' . $this->erp->formatMoney($row->item_tax) . '</td>';
								} ?>
								<?php if ($Owner || $Admin || $GP['transfers-subtotal']) { ?>
								<td style="width: 100px; text-align:right; padding-right:10px; vertical-align:middle;"><?= $this->erp->formatMoney($row->subtotal); ?></td>
								<?php } ?>
							</tr>
							<?php $r++;
						endforeach; ?>
						</tbody>
						<tfoot>
						<?php 
						$col = 1;
						if ($Owner || $Admin || $GP['transfers-subtotal']) {
							$col = $col + 1;
						}
						if($Owner || $Admin || $GP['transfers-net_unit_cost']){
							$col = $col + 1;
						}

						if ($this->Settings->tax1) {
							$col += 1;
						} ?>

							<tr>
								<td colspan="<?= $col; ?>"
									style="text-align:right; padding-right:10px;"><?= lang("total"); ?>
									<?php if ($Owner || $Admin || $GP['transfers-subtotal']) { ?>
									(<?= $default_currency->code; ?>)
									<?php } ?>
								</td>
								<?php if ($Owner || $Admin) { }else{
									echo '<td style="text-align:right; padding-right:10px;">'.$this->erp->formatQuantity($qty).'</td>';
									} ?>
								<td style="text-align:right; padding-right:10px;"><?= $this->erp->formatMoney($transfer->total_tax); ?>
								<?php if ($Owner || $Admin || $GP['transfers-subtotal']) { ?>
								<td style="text-align:right; padding-right:10px;"><?= $this->erp->formatMoney($transfer->total+$transfer->total_tax); ?></td>
								<?php } ?>
							</tr>
							<?php if($transfer->shipping >0){ ?>
								<tr>
									<td colspan="<?= $col+1;?>" style="text-align:right;padding-right:10px;"><?= lang("shipping");?>
										(<?= $default_currency->code; ?>)
									</td>
									<td style="text-align:right; padding-right:10px;">
										<?= $this->erp->formatMoney($transfer->shipping);?>
									</td>
								</tr>
							<?php } ?>
							<?php if ($Owner || $Admin || $GP['transfers-subtotal']) { ?>
								<tr>
									<td colspan="<?= $col+1; ?>"
										style="text-align:right; padding-right:10px; font-weight:bold;"><?= lang("total_amount"); ?>
										(<?= $default_currency->code; ?>)
									</td>
									<td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->erp->formatMoney($transfer->grand_total); ?></td>
								</tr>
							<?php } ?>
						</tfoot>
					</table>
				</div>

				<div class="row">
					<div class="col-xs-12">
						<?php if ($transfer->note || $transfer->note != "") { ?>
							<div class="well well-sm">
								<p class="bold"><?= lang("note"); ?>:</p>

								<div><?= $this->erp->decode_html($transfer->note); ?></div>
							</div>
						<?php } ?>
					</div>
					<div class="col-xs-4 pull-left">
						<p><?= lang("created_by"); ?>: <?= $created_by->first_name.' '.$created_by->last_name; ?> </p>

						<p>&nbsp;</p>

						<p>&nbsp;</p>
						<hr>
						<p><?= lang("stamp_sign"); ?></p>
					</div>
					<div class="col-xs-4 col-xs-offset-1 pull-right">
						<p><?= lang("received_by"); ?>: </p>

						<p>&nbsp;</p>

						<p>&nbsp;</p>
						<hr>
						<p><?= lang("stamp_sign"); ?></p>
					</div>
				</div>  
				<div id="wrap" style="width: 90%; margin:0px auto;" class="no_print">
					<div class="col-xs-10" style="margin-bottom:20px;">
						<button type="button" class="btn btn-primary btn-default no-print pull-left" onclick="window.print();">
							<i class="fa fa-print"></i> <?= lang('print'); ?>
						</button>&nbsp;&nbsp;
						<a href="<?= site_url('transfers'); ?>"><button class="btn btn-warning no-print" ><i class="fa fa-heart"></i>&nbsp;<?= lang("back_to_transfers"); ?></button></a>
					</div>
				</div>
				<script type="text/javascript">
					window.onload = function() { window.print(); }
				</script>
			</div>
		</div>
	</body>
</html>