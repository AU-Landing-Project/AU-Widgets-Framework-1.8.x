<?php
/**
 * 
 * 	Generates the dropdown menus for sorting
 */

$widget = $vars['entity'];

$sortby_options = array(
	'date' => elgg_echo('eligo:sortby:date'),
    'title' => elgg_echo('eligo:sortby:title'),
    'owner' => elgg_echo('eligo:sortby:owner'),
);

if ($vars['sortby_options']) {
  $sortby_options = array_filter(array_merge($sortby_options, $vars['sortby_options']));
}

echo '<div class="eligo_field">';

echo elgg_echo('eligo:sortby') . ":";

$options = array(
  'name' => 'params[eligo_sortby]',
  'value' => $widget->eligo_sortby ? $widget->eligo_sortby : 'date',
  'options_values' => $sortby_options
);

echo elgg_view('input/dropdown', $options);


$sortby_dir_options = array(
	'asc' => elgg_echo('eligo:sortbydir:asc'),
    'desc' => elgg_echo('eligo:sortbydir:desc'),
);

if ($vars['sortby_dir_options']) {
  $sortby_dir_options = array_filter(array_merge($sortby_dir_options, $vars['sortby_dir_options']));
}

$options = array(
  'name' => 'params[eligo_sortby_dir]',
  'value' => $widget->eligo_sortby_dir ? $widget->eligo_sortby_dir : 'desc',
  'options_values' => $sortby_dir_options
);

echo elgg_view('input/dropdown', $options);
echo "</div>"; // eligo_field
