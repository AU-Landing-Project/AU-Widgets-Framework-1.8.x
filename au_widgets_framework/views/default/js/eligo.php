<?php 
/*
 * 
 * This function replaces the select box with a throbber while it gets the new view
 * Then sticks the new view in its place
 * Long live ajax
 */
?>

function eligo_update_selected(guid){
	var throbber = '<img src="<?php echo elgg_get_site_url() . "_graphics/ajax_loader.gif"; ?>">';
	
	var eligo_owners = $("#eligo_owners_"+guid+" option:selected").val();
	var eligo_displayby = $("#eligo_displayby_select_"+guid+" option:selected").val();
	var eligo_selectsort = $("#eligo_displayby_"+guid+" input[type=radio]:checked").val();
	
	$("#eligo_displayby_"+guid).html(throbber);

	elgg.get("<?php echo elgg_get_site_url() . 'ajax/view/eligo/displayby/options' ?>", {
		guid: guid,
		eligo_owners: eligo_owners,
		eligo_displayby: eligo_displayby,
		eligo_select_sort: eligo_selectsort
	}).done( function(data){
		$("#eligo_displayby_"+guid).html(data);
	});
}