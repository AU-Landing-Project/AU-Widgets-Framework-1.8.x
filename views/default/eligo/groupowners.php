<?php
/**
 * 
 * 	Generates the dropdown menus for entity containers
 */

$widget = $vars['entity'];

echo '<div class="eligo_field">';

echo elgg_echo('eligo:owners', array(elgg_echo($vars['eligo_type']))) . ":";

$options = array(
  'name' => 'params[eligo_owners]',
  'value' => $widget->eligo_owners ? $widget->eligo_owners : 'mine',
  'id' => 'eligo_owners_' . $widget->guid,
  'options_values' => array(
      'thisgroup' => elgg_echo('eligo:owners:thisgroup'),
      'members' => elgg_echo('eligo:owners:members'),
      'all' => elgg_echo('eligo:owners:all')
    ),
);

echo elgg_view('input/dropdown', $options);

echo "</div>"; // eligo_field
