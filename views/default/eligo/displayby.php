<?php
/**
 * 
 * 	Generates the dropdown menus for displaying content
 */

$widget = $vars['entity'];
if($widget->eligo_displayby == ''){
  $widget->eligo_displayby = 'number';
}

echo '<div class="eligo_field">';

echo elgg_echo('eligo:displayby') . ":";

$options = array(
  'name' => 'params[eligo_displayby]',
  'value' => $widget->eligo_displayby,
  'id' => 'eligo_displayby_select_' . $widget->guid,
  'options_values' => array(
      'recent' => elgg_echo('eligo:displayby:recent'),
      'date' => elgg_echo('eligo:displayby:date'),
      'selected' => elgg_echo('eligo:displayby:selected'),
    ),
);

echo elgg_view('input/dropdown', $options);

echo "<div id=\"eligo_displayby_{$widget->guid}\" class=\"eligo_subfield\">";
  echo elgg_view('eligo/displayby/options', $vars);
echo "</div>"; // eligo_displayby_{$widget->guid}

echo "</div>"; // eligo_field

?>

<script>
$(document).ready( function(){

	//
	// always change for select dropdown
	$("#eligo_displayby_select_<?php echo $widget->guid; ?>").change(function() {
		eligo_update_selected(<?php echo $widget->guid; ?>);
	});

	// 
	// all other selects only change if eligo_display == selected
	$("#eligo_owners_<?php echo $widget->guid; ?>, #eligo_tagfilter_andor<?php echo $widget->guid; ?>").change(function() {
		if($("#eligo_displayby_select_<?php echo $widget->guid; ?> option:selected").val() == 'selected'){
			eligo_update_selected(<?php echo $widget->guid; ?>);
		}
	});

	// radio buttons
	// all other options only change if eligo_display == selected
	$(".eligo_selectsort_<?php echo $widget->guid; ?> input[type=radio]").live('click', function() {
		if($("#eligo_displayby_select_<?php echo $widget->guid; ?> option:selected").val() == 'selected'){
			eligo_update_selected(<?php echo $widget->guid; ?>);
		}
	});


	// text fields
	// only change if eligo_display == selected
	$("#eligo_tagfilter<?php echo $widget->guid; ?>").keyup(function() {
		if($("#eligo_displayby_select_<?php echo $widget->guid; ?> option:selected").val() == 'selected'){
			eligo_update_selected(<?php echo $widget->guid; ?>);
		}
	});
});
</script>
