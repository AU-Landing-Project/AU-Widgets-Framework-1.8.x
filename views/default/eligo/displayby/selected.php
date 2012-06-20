<?php

/**
 * Select items to display
 */

$widget = $vars['entity'];

// get our current list, serialized as widget settings don't allow arrays
// this is serialized during the action plugin hook
$currently_selected = unserialize($widget->eligo_selected_entities);

// set up options for our listing
$options = eligo_get_selected_entities_options($vars);

// get a list of our objects
$objects = elgg_get_entities($options);

echo "<div class=\"eligo_field\">";

echo elgg_echo('eligo:displayby:selected:label') . ":<br>";


// unfortunately no elgg view for multiple select
// have to do it oldschool
echo '<select multiple="true" name="eligo_selected_entities[]">';
foreach($objects as $object){
  //the option label is the entity title, could be different attributes
  $name = $widget->eligo_select_option_title ? $widget->eligo_select_option_title : 'title';
  $title = $object->$name;
  if(strlen($title) > 28){
    $title = substr($title, 0, 25) . "...";
  }
  echo "<option value=\"{$object->guid}\"";
  
  echo "title=\"" . str_replace('"', '\"', $object->title) . "\"";
  if(in_array($object->guid, $currently_selected)){
    echo " selected=\"selected\"";
  }
  echo ">{$title}</option>";
}
echo '</select>';
// debug select options
//echo "<pre>" . print_r($options, 1) . "</pre>";

// add in our sorting controls for the select
// ajax variables take precedence, then saved settings, then default
$value = $vars['eligo_select_sort'] ? $vars['eligo_select_sort'] : '';
if(empty($value)){
  $value = $widget->eligo_select_sort ? $widget->eligo_select_sort : 'date';
}

$options = array(
  'name' => 'params[eligo_select_sort]',
  'value' => $value,
  'class' => 'eligo_selectsort_' . $widget->guid,
  'options' => array(
      elgg_echo('eligo:selectsortby:date') => 'date',
      elgg_echo('eligo:selectsortby:name') => 'name',
      elgg_echo('eligo:selectsortby:access') => 'access',
   ),
);

  echo "<div class=\"eligo_field\">";
    echo elgg_view('input/radio', $options);
  echo "</div>"; // eligo_field
echo "</div>"; // eligo_field
