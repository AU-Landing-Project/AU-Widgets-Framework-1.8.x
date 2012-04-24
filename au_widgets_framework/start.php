<?php

/**
 * 
 * Provides reuseable code/views for eligo aware widgets
 */

include 'lib/functions.php';

function eligo_framework_init(){
  // add in our own css
  elgg_extend_view('css/elgg', 'eligo/css');
  
  // add in our own js
  elgg_extend_view('js/elgg', 'js/eligo');
  
  // make some views accessible by ajax
  elgg_register_ajax_view('eligo/displayby/options');
  
  // hook into the widget save action
  // so we can manually save a serialized array
  elgg_register_plugin_hook_handler('action', 'widgets/save', 'eligo_widget_save_selected');
}

elgg_register_event_handler('init', 'system', 'eligo_framework_init');