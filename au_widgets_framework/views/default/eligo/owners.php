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
      'mine' => elgg_echo('eligo:owners:mine'),
      'friends' => elgg_echo('eligo:owners:friends'),
      'groups' => elgg_echo('eligo:owners:groups'),
    ),
);

echo elgg_view('input/dropdown', $options);

echo "</div>"; // eligo_field
