<?php

$widget = $vars['entity'];

// some logic to set a default displayby if one isn't passed in ajax
if(empty($vars['eligo_displayby'])){
  $vars['eligo_displayby'] = $widget->eligo_displayby ? $widget->eligo_displayby : 'recent';
}


switch ($vars['eligo_displayby']) {
  case 'date':
    echo elgg_view('eligo/displayby/date', $vars);
  break;
  
  case 'selected':
    echo elgg_view('eligo/displayby/selected', $vars);
  break;
  
  case 'recent':  
  default:
    echo elgg_echo('eligo:displayby:recent:label');
  break;
}
