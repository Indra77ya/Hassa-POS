<tr>
	<td>{{$product->name}} ({{$product->sku}})</td>
	<input type="hidden" name="products[]" value="{{$product->id}}">
	<td><button type="button" class="btn btn-xs btn-danger remove_modifier_product"><i class="fa fa-times"></i></button></td>
</tr>