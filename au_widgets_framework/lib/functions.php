<?php
/**
 * 
 * Formats options for content query
 * @param ElggWidget object $widget
 */
function eligo_get_display_entities_options($widget){
  // defaults for most widgets
  $options = array(
    'full_view' => FALSE,
    'pagination' => FALSE,
    'limit' => $widget->num_display ? $widget->num_display : 10,
  );
  
  
  // eligo_type set for individual widgets = object subtype
  // this will limit the search to just our type
  if(!empty($widget->eligo_type)){
    $options['types'] = array($widget->eligo_type);
  }
  
  if(!empty($widget->eligo_subtype)){
    $options['subtypes'] = array($widget->eligo_subtype);
  }
  
  
  // set options dealing with display
  // selected, date range, recent (default)
  switch ($widget->eligo_displayby) {
    case 'selected':
      $options['guids'] = unserialize($widget->eligo_selected_entities);
      
      // override limit, only want to show the ones we have selected, with no truncation
      $options['limit'] = 0;
    break;
    
    case 'date':
      $from_parts = explode('/', $widget->eligo_date_from);
      $to_parts = explode('/', $widget->eligo_date_to);
      
      $from = mktime(0,0,0,$from_parts[0],$from_parts[1],$from_parts[2]);
      $to = mktime(0,0,0,$to_parts[0],$to_parts[1],$to_parts[2]);
      $options['created_time_lower'] = $from;
      $options['created_time_upper'] = $to;
    break;
    
    case 'recent':
    default:
    break;
  }
  
  
  $owner_options = eligo_get_owner_options($widget, $widget->eligo_owners);
  
  $options = array_merge($options, $owner_options);
  
  
  // set up options for sorting
  switch ($widget->eligo_sortby) {
    case 'title':
      //  join to objects_entity table to sort by title in sql
      $table = 'objects_entity';
      $title = 'title';
      if($widget->eligo_type == 'group'){
        $table = 'groups_entity';
        $title = 'name';
      }
      $join = "JOIN " . elgg_get_config('dbprefix') . $table . " o ON o.guid = e.guid";
      $options['joins'] = array($join);
      
      $options['order_by'] = "o.{$title} ASC";
      if($widget->eligo_sortby_dir == 'desc'){
        $options['order_by'] = "o.{$title} DESC";
      }
    break;
    
    case 'owner':
      // join user table to sort by owner name
      $join = "JOIN " . elgg_get_config('dbprefix') . "users_entity u ON u.guid = e.owner_guid";
      $options['joins'] = array($join);
      
      $options['order_by'] = 'u.name ASC';
      if($widget->eligo_sortby_dir == 'desc'){
        $options['order_by'] = 'u.name DESC';
      }
    break;
    
    case 'date':
    default:
      // time_created desc is default in elgg if nothing else is set
      if($widget->eligo_sortby_dir == 'asc'){
        $options['order_by'] = "e.time_created asc";
      }
    break;
  }
  
  return $options;
}


/**
 * 
 * Formats the options to populate the widget multiselect
 * @param $vars = array of view vars
 * @return array $options for use in elgg_get_entities
 */
function eligo_get_selected_entities_options($vars){
  // defaults - always want to show all results
  $options = array(
    'limit' => 0
  );
  
  $widget = $vars['entity'];
  
  // eligo_type set for individual widgets = object subtype
  // this will limit the search to just our type
  if(!empty($widget->eligo_type)){
    $options['types'] = array($widget->eligo_type);
  }
  
  if(!empty($widget->eligo_subtype)){
    $options['subtypes'] = array($widget->eligo_subtype);
  }

  
  // get based on type of owners
  // priority goes to $vars as it's ajax populated, then saved $widget
  $owners = $vars['eligo_owners'] ? $vars['eligo_owners'] : FALSE;
  if(!$owners){
    $owners = $widget->eligo_owners ? $widget->eligo_owners : 'mine';
  }

  $owner_options = eligo_get_owner_options($widget, $owners);
  
  $options = array_merge($options, $owner_options);
  
  // determine sort-by
  $sort = $vars['eligo_select_sort'] ? $vars['eligo_select_sort'] : FALSE;
  if(!$sort){
    $sort = $widget->eligo_select_sort ? $widget->eligo_select_sort : 'date';
  }
  switch ($sort){
    case "name":
      // join to objects_entity table to sort by title in sql      
      $join = "JOIN " . elgg_get_config('dbprefix') . "objects_entity o ON o.guid = e.guid";
      $options['joins'] = array($join);
      $options['order_by'] = 'o.title ASC';
      break;
      
    case "access":
      $options['order_by'] = "e.access_id ASC";
      break;
    
    case "date":
    default:
      // elgg does this automagically.  Thanks elgg.
      break;
  }
  
  // let widgets override defaults in a custom callback function
  // called at the end in case only one small part needs to change
  if($widget->eligo_custom_select_options && is_callable($widget->eligo_custom_select_options)){
  	$selected_options = call_user_func($widget->eligo_custom_select_options, $widget, $vars);
  	
  	if(is_array($selected_options)){
  	  $options = array_merge($options, $selected_options);
  	}
  }
  
  return $options;
}

/**
 * 
 * Compiles options based on the owners of items to display
 * Differentiates between user/group widgets
 * @param ElggWidget $widget
 * @param String $owners
 * @return array $options
 */
function eligo_get_owner_options($widget, $owners){
  
	// if a widget has requirements beyond the default
	// they can define their own owner options
	if($widget->eligo_custom_owners_options && is_callable($widget->eligo_custom_owners_options)){
		return call_user_func($widget->eligo_custom_owners_options, $widget, $owners);
	}
	
  $options = array();
  
  // set defaults based on context
  // yes I know they do the same thing at the moment
  if(empty($owners)){
    $owners = 'mine';
    if($widget->getContext() == 'groups'){
      $owners = 'thisgroup';
    }
  }
  
  switch ($owners) {
    case 'groups':
        $user = get_user($widget->owner_guid);
        $groups = $user->getGroups('',0,0);
        
        $group_guids = array();
        foreach($groups as $group){
          $group_guids[] = $group->guid;
        }
        $options['container_guids'] = $group_guids;
        
        // if there's no groups we don't want it to return everything
        // so invalidate the query
        if(count($group_guids) == 0){
          $options['subtypes'] = array('eligo_invalidate_query');
        }
      break;
    
    case 'friends':
        // get a list of friend guids
        $user = get_user($widget->owner_guid);
        $friends = $user->getFriends('',0,0);
        
        $friend_guids = array();
        foreach($friends as $friend){
          $friend_guids[] = $friend->guid;
        }
        $options['container_guids'] = $friend_guids;
        
        // if there's no friends we don't want it to return everything
        // so invalidate the query
        if(count($friend_guids) == 0){
          $options['subtypes'] = array('eligo_invalidate_query');
        }
      break;
      
    case 'members':
        $group = get_entity($widget->owner_guid);
        $members = $group->getMembers(0,0,FALSE);
        
        $member_guids = array();
        foreach($members as $member){
          $member_guids[] = $member->guid;
        }
        $options['container_guids'] = $member_guids;
        
        // if there's no members (is this possible?) we don't want
        // to return all items, so invalidate query
        if(count($member_guids) == 0){
          $options['subtypes'] = array('eligo_invalidate_query');
        }
    break;
    
    case 'all':
      // any owner - used for group membership as we don't care who
      // the group owner is generally
    break;
    
    case 'thisgroup':
    case 'mine':
    default:
      $options['container_guids'] = array($widget->owner_guid);
    break;
  }
  
  return $options;
}


/**
 * 
 * This function upgrades legacy widgets from widgets_eligo in 1.7
 * That plugin was poorly written, and used huge blocks of javascript
 * and was quite slow to load.  This rewrite fixes all that, but some
 * settings storage has changed to make sense
 * 
 * This simply takes existing settings and translates it to new settings
 * 
 * @param $widget
 */
function eligo_upgrade_old_widget($widget){
  // determine if it  needs to be upgraded first
  if(empty($widget->CS_sortby)){
    // not an old widget, we're done here
    return $widget;
  }
  
  // now we have to map all of the old settings to proper ones
  // set the sortby and direction
  switch ($widget->CS_sortby) {
    case "CS_byDate_desc":
      $widget->eligo_sortby = 'date';
      $widget->eligo_sortby_dir = "desc";
    break;
    
    case "CS_byName":
      $widget->eligo_sortby = 'title';
      $widget->eligo_sortby_dir = 'asc';
    break;
    
    case "CS_byName_desc":
      $widget->eligo_sortby = 'title';
      $widget->eligo_sortby_dir = 'desc';
    break;
    
    case "CS_byOwner":
      $widget->eligo_sortby = 'owner';
      $widget->eligo_sortby_dir = 'asc';
    break;
    
    case "CS_byOwner_desc":
      $widget->eligo_sortby = 'owner';
      $widget->eligo_sortby_dir = 'desc';
    break;
    
    case "CS_byDate":
    default:
      $widget->eligo_sortby = 'date';
      $widget->eligo_sortby_dir = 'asc';
    break;
  }
  
  
  // set the displayby
  switch ($widget->CS_display_according_to) {
    case "range_display".$widget->guid:
      $widget->eligo_displayby = 'date';
      $widget->eligo_date_from = $widget->CS_select_by_date_range_from;
      $widget->eligo_date_to = $widget->CS_select_by_date_range_to;
    break;
    
    case "arbit_display".$widget->guid:
      $widget->eligo_displayby = 'selected';
      
      if($widget->BS_display_according_to == "arbit_display_my".$widget->guid){
        $objectlist = "CS_arbit_display";
      }
      elseif($widget->BS_display_according_to == "arbit_display_friends".$widget->guid){
        $objectlist = "CS_arbit_display_friends";
      }
      else{
        $objectlist = "CS_arbit_display_all";
      }
      
      $widget->eligo_selected_entities = serialize(explode(",", $widget->$objectlist));
    break;
      
    case "num_display".$widget_guid:
    default:
      $widget->eligo_displayby = 'recent';
    break;
  }
  
  // set the owner filter
  switch ($widget->BS_display_according_to) {
    case "arbit_display_friends".$widget->guid:
      if($widget->getContext() == 'groups'){
        $widget->eligo_owners = 'members';
      } 
      else{
        $widget->eligo_owners = 'friends';
      }
    break;
    
    case "arbit_display_all".$widget->guid:
        $widget->eligo_owners = 'groups';
    break;
    
    default:
      $widget->eligo_owners = 'mine';
    break;
  }
  
  return $widget;
}

//
// plugin hook handler called on action, widgets/save
// serializes the array of selected entities
function eligo_widget_save_selected($hook, $type, $returnvalue, $params){
  $guid = get_input('guid');
  
  $widget = get_entity($guid);
  
  if(is_object($widget)){
    
    $entitylist = get_input('eligo_selected_entities');

    if(is_array($entitylist)){
      $widget->eligo_selected_entities = serialize($entitylist);
    }
  }
}