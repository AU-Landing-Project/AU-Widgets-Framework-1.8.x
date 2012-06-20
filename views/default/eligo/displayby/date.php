<?php

/**
 * Range of dates to display
 */

$widget = $vars['entity'];

//
//    START DATE    //
//
echo '<div class="eligo_field">';

echo elgg_echo('eligo:displayby:date:from') . ":";


$options = array(
  'name' => 'params[eligo_date_from]',
  'value' => $widget->eligo_date_from,
  'id' => "eligo_date_from_" . $widget->guid
);

echo elgg_view('input/text', $options);

echo "</div>"; // eligo_field



//
//    END DATE    //
//
echo '<div class="eligo_field">';
echo elgg_echo('eligo:displayby:date:to') . ":";

$options = array(
  'name' => 'params[eligo_date_to]',
  'value' => $widget->eligo_date_to,
  'id' => "eligo_date_to_" . $widget->guid
);

echo elgg_view('input/text', $options);

echo "</div>"; // eligo_field
?>

<script>
$(document).ready( function(){
	// Datepicker
	$("#eligo_date_from_<?php echo $widget->guid; ?>, #eligo_date_to_<?php echo $widget->guid?>").datepicker({
		inline: true
	});	
});
</script>		