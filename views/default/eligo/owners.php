<?php
/**
 * 
 * 	Generates the dropdown menus for entity containers
 */

$widget = $vars['entity'];
$user = elgg_get_logged_in_user_entity();

$options_values = array(
  'mine' => elgg_echo('eligo:owners:mine'),
  'friends' => elgg_echo('eligo:owners:friends'),
  'groups' => elgg_echo('eligo:owners:groups'),
  'all' => elgg_echo('eligo:owners:all')
);

// get groups and add their acls to the options
if($user && elgg_instanceof($user, 'user')){
  $groups = $user->getGroups('', 0, 0);
    
  if($groups){
    foreach($groups as $group){
        
      // only show top level groups if we're using subgroups
      if (elgg_is_active_plugin('au_subgroups')) {
        $parent = au_subgroups_get_parent_group($group);
        if ($parent) {
          continue;
        }
          
        $options_values[$group->guid] = elgg_echo('groups:group') . ": " . $group->name;
        $options_values = eligo_get_subgroups_as_owners($group, $user, 5, $options_values);
      }
      else {
        $options_values[$group->guid] = elgg_echo('groups:group') . ": " . $group->name;
      }
    }
  }
}

echo '<div class="eligo_field">';

echo elgg_echo('eligo:owners', array(elgg_echo($vars['eligo_type']))) . ":";

$options = array(
  'name' => 'params[eligo_owners]',
  'value' => $widget->eligo_owners ? $widget->eligo_owners : 'mine',
  'id' => 'eligo_owners_' . $widget->guid,
  'options_values' => $options_values,
);

echo elgg_view('input/dropdown', $options);

echo "</div>"; // eligo_field
