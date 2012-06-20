<?php
/**
 * 
 * 	Generates the dropdown menus for sorting
 */

$widget = $vars['entity'];

echo '<div class="eligo_field">';

echo elgg_echo('eligo:sortby') . ":";

$options = array(
  'name' => 'params[eligo_sortby]',
  'value' => $widget->eligo_sortby ? $widget->eligo_sortby : 'date',
  'options_values' => array(
      'date' => elgg_echo('eligo:sortby:date'),
      'title' => elgg_echo('eligo:sortby:title'),
      'owner' => elgg_echo('eligo:sortby:owner'),
    ),
);

echo elgg_view('input/dropdown', $options);


$options = array(
  'name' => 'params[eligo_sortby_dir]',
  'value' => $widget->eligo_sortby_dir ? $widget->eligo_sortby_dir : 'desc',
  'options_values' => array(
      'asc' => elgg_echo('eligo:sortbydir:asc'),
      'desc' => elgg_echo('eligo:sortbydir:desc'),
    ),
);

echo elgg_view('input/dropdown', $options);
echo "</div>"; // eligo_field
