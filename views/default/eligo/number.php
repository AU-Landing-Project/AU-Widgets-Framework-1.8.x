<?php

/**
 * Number of recent items to display
 */

$widget = $vars['entity'];

echo '<div class="eligo_field">';

echo elgg_echo('eligo:number:label') . ":";

$options = array(
  'name' => 'params[num_display]',
  'value' => $widget->num_display ? $widget->num_display : 10,
  'options' => array(1,2,3,4,5,6,7,8,9,10,15,20,30),
);

echo elgg_view('input/dropdown', $options);

echo "</div>"; // eligo_field