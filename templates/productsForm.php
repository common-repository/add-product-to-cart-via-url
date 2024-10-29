<div class="productFormWrapper">
	<h2><?php echo $template->get('productType'); ?> Products</h2>
	<form autocomplete="off" id="wcad_<?php echo $template->get('productType'); ?>_product_url_form" class="<?php echo $template->get('productType'); ?>">
		<p class="wcad_error_message"></p>
		<div class="product_url_wrapper">
			<div class="setRow prod_qty_set">
				<select data-search="true" id="wcad_<?php echo $template->get('productType'); ?>prod_url_select_1" class="prod_url_select" placeholder="Search for product of choice...">
						<option value="0">Search for product of choice...</option>
						<?php echo $template->get('dropDownOptions'); ?>
				</select>
				<input  placeholder="Quantity" type="number" class="prod_url_qty" value="1">
			</div>
			<div class="variationAttributes"></div>
		</div>
		<div class="setRow">
			<a href="#" class="<?php echo $template->get('productType'); ?>" id="wcad_<?php echo $template->get('productType'); ?>_new_product">Add New Product Row</a>

			<input type="submit" value="Get URL" id="wcad_<?php echo $template->get('productType'); ?>_submit_product_url_list">
		</div>
	</form>
	 
	<div class="copybox setRow">
		<input type="text" id="<?php echo $template->get('productType'); ?>_urlHolder" value="No value yet"></input>
		<a class="wcadHide button" href="#" id="wcad_<?php echo $template->get('productType'); ?>_copy_url">Copy this URL</a>
	</div>
</div>
 