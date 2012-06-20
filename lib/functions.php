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
  if($widget->eligo_type !== NULL){
    $options['types'] = array($widget->eligo_type);
  }
  
  if($widget->eligo_subtype !== NULL){
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
  
  // add in owner options
  $options = eligo_get_owner_options($widget, $widget->eligo_owners, $options);
  
  // add in tag filtering options
  $options = eligo_get_tag_filter_options($widget, array('eligo_tagfilter' => $widget->eligo_tagfilter, 'eligo_tagfilter_andor' => $widget->eligo_tagfilter_andor), $options);
  
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
      $options['joins'] = eligo_options_array_merge($options['joins'], array($join));
      
      $options['order_by'] = "o.{$title} ASC";
      if($widget->eligo_sortby_dir == 'desc'){
        $options['order_by'] = "o.{$title} DESC";
      }
    break;
    
    case 'owner':
      // join user table to sort by owner name
      $join = "JOIN " . elgg_get_config('dbprefix') . "users_entity u ON u.guid = e.owner_guid";
      $options['joins'] = eligo_options_array_merge($options['joins'], array($join));
      
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
  
  // get owner options
  // pass $options by reference
  $options = eligo_get_owner_options($widget, $owners, $options);
  
  
  // get based on tags
  // priority goes to $vars as it's ajax populated, then saved $widget
  $tags = $vars['eligo_tagfilter'] ? $vars['eligo_tagfilter'] : FALSE;
  if(!$tags){
    $tags = $widget->eligo_tagfilter ? $widget->eligo_tagfilter : '';
  }
  
  $andor = $vars['eligo_tagfilter_andor'] ? $vars['eligo_tagfilter_andor'] : FALSE;
  if(!$andor){
    $andor = $widget->eligo_tagfilter_andor ? $widget->eligo_tagfilter_andor : 'and';
  }
  
  $options = eligo_get_tag_filter_options($widget, array('eligo_tagfilter' => $tags, 'eligo_tagfilter_andor' => $andor), $options);
   
  
  // determine sort-by
  $sort = $vars['eligo_select_sort'] ? $vars['eligo_select_sort'] : FALSE;
  if(!$sort){
    $sort = $widget->eligo_select_sort ? $widget->eligo_select_sort : 'date';
  }
  switch ($sort){
    case "name":
      // join to objects_entity table to sort by title in sql      
      $join = "JOIN " . elgg_get_config('dbprefix') . "objects_entity o ON o.guid = e.guid";
      
      $options['joins'] = eligo_options_array_merge($options['joins'], array($join));
      
      if(!empty($options['order_by'])){
        $options['order_by'] .= ", ";
      }
      $options['order_by'] .= 'o.title ASC';
      break;
      
    case "access":
      if(!empty($options['order_by'])){
        $options['order_by'] .= ", ";
      }
      $options['order_by'] .= "e.access_id ASC";
      break;
    
    case "date":
    default:
      // elgg does this automagically.  Thanks elgg.
      break;
  }
  
  // let widgets override defaults in a custom callback function
  // called at the end in case only one small part needs to change
  if($widget->eligo_custom_select_options && is_callable($widget->eligo_custom_select_options)){
  	$options = call_user_func($widget->eligo_custom_select_options, $widget, $vars, $options);
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
function eligo_get_owner_options($widget, $owners, $options){
    if(!is_array($options)){
      $options = array();
    }
  
	// if a widget has requirements beyond the default
	// they can define their own owner options
	if($widget->eligo_custom_owners_options && is_callable($widget->eligo_custom_owners_options)){
		return call_user_func($widget->eligo_custom_owners_options, $widget, $owners, $options);
	}
  
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
    break;
    
    case 'thisgroup':
    case 'mine':
    default:
      $options['container_guids'] = array($widget->owner_guid);
    break;
  }
  
  return $options;
}

//
//  set up options to filter by tag
//  $options passed by reference
function eligo_get_tag_filter_options($widget, $vars, $options){
  global $CONFIG;
  
  $tags = $vars['eligo_tagfilter'];
  $andor = $vars['eligo_tagfilter_andor'];
  
  if(empty($tags)){
    return $options;
  }
  
  
    $wheres = array();
    $joins = array();
    // will always want to join these tables if pulling metastrings.
	$joins[] = "JOIN {$CONFIG->dbprefix}metadata tagmd on e.guid = tagmd.entity_guid";

	// get names wheres and joins
	$names_where = '';
	$values_where = '';
	
	$names = array("tags", "universal_categories");
	$values = string_to_tag_array($tags);
	
	if(!empty($values)){
		$sanitised_names = array();
		foreach ($names as $name) {
			// normalise to 0.
			if (!$name) {
				$name = '0';
			}
			$sanitised_names[] = '\'' . sanitise_string($name) . '\'';
		}
	
		if ($names_str = implode(',', $sanitised_names)) {
			$joins[] = "JOIN {$CONFIG->dbprefix}metastrings tagmsn on tagmd.name_id = tagmsn.id";
			$names_where = "(tagmsn.string IN ($names_str))";
		}
		
		$sanitised_values = array();
		foreach ($values as $value) {
			// normalize to 0
			if (!$value) {
				$value = 0;
			}
			$sanitised_values[] = '\'' . sanitise_string($value) . '\'';
		}	
		
		$joins[] = "JOIN {$CONFIG->dbprefix}metastrings tagmsv on tagmd.value_id = tagmsv.id";
		
		$values_where .= "(";
		foreach($sanitised_values as $i => $value){
			if($i !== 0){
				if($andor == "and"){
					// AND
					
					$joins[] = "JOIN {$CONFIG->dbprefix}metadata tagmd{$i} on e.guid = tagmd{$i}.entity_guid";
					$joins[] = "JOIN {$CONFIG->dbprefix}metastrings tagmsn{$i} on tagmd{$i}.name_id = tagmsn{$i}.id";
					$joins[] = "JOIN {$CONFIG->dbprefix}metastrings tagmsv{$i} on tagmd{$i}.value_id = tagmsv{$i}.id";
	 
					$values_where .= " AND (tagmsn{$i}.string IN ($names_str) AND tagmsv{$i}.string = $value)";
				} else {
					$values_where .= " OR (tagmsv.string = $value)";
				}
			} else {
				$values_where .= "(tagmsv.string = $value)";
			}				
		}
		$values_where .= ")";
	}
	
	$access = get_access_sql_suffix('tagmd');
	
	if ($names_where && $values_where) {
		$wheres[] = "($names_where AND $values_where AND $access)";
	} elseif ($names_where) {
		$wheres[] = "($names_where AND $access)";
	} elseif ($values_where) {
		$wheres[] = "($values_where AND $access)";
	}

  $options['joins'] = eligo_options_array_merge($options['joins'], $joins);
  $options['wheres'] = eligo_options_array_merge($options['wheres'], $wheres);
  
  return $options;
}



//
//  Takes an original array, adds a new one to it
//  returns the merged arrays, retaining all options
//  useful when you don't know what has been set previously if anything
function eligo_options_array_merge($original = array(), $new = array()){
  // if original isn't an array, it's either null, or a string that needs converting to array
  if(empty($original)){
    return $new;
  }
  
  // treat it as a string
  if(!is_array($original)){
    $original = array($original);
  }
  
  // normal scenario
  if(is_array($original) && is_array($new)){
    foreach($new as $item){
      $original[] = $item;
    }
    return $original;
  }
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
  
  // normalize the number to display
  // old eligo had 'All' keyword
  if($widget->num_display == 'All'){
    $widget->num_display = 30;
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