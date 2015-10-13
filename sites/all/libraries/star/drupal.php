<?php
/**
 * Some handy functions that Drupal should have but doesn't.
 */


////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful functions related to URL arguments.

function alias_args() {
  $request_uri = trim(urldecode($_SERVER['REQUEST_URI']), '/');
  return array_filter(explode('/', $request_uri));
}

function alias_arg($i) {
  $alias_args = alias_args();
  return $alias_args[$i];
}

function alias_arg_count() {
  return count(alias_args());
}

/**
 * Return the number of arguments in the path.
 * @return int
 */
function arg_count() {
  $n = 0;
  while (arg($n) !== NULL) {
    $n++;
  }
  return $n;
}

/**
 * Return the last argument.
 * @return string
 */
function last_arg() {
  return arg(arg_count() - 1);
}


////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful database functions.

/**
 * Same as db_query, but if debug mode is on that it will show the actual query being executed.
 * I copied db_query to avoid hacking core.
 */
function debug_query($query) {
  $args = func_get_args();
  array_shift($args);
  $query = db_prefix_tables($query);
  if (isset($args[0]) and is_array($args[0])) { // 'All arguments in one array' syntax
    $args = $args[0];
  }
  _db_query_callback($args, TRUE);
  $query = preg_replace_callback(DB_QUERY_REGEXP, '_db_query_callback', $query);
  // determine whether to display the query:
  $debug = function_exists('debugMode') ? debugMode() : TRUE;
  if ($debug) {
    $x = debug_backtrace();
    $n = $x[0]['file'] ? 0 : 1;
    if ($x[$n + 1]['function'] == 'do_query') {
      $n++;
    }
    $msg = "<p>Query called from {$x[$n]['file']}, line {$x[$n]['line']}";
    if (isset($x[$n + 1])) {
      $msg .= ", function {$x[$n + 1]['function']}()";
    }
    $msg .= "</p>";
    echo $msg;
  }
  return _db_query($query, $debug);
}

/**
 * Helper function for db_query().
 */
function show_query($query) {
  global $active_db, $queries, $user;

  $args = func_get_args();
  array_shift($args);
  $query = db_prefix_tables($query);
  if (isset($args[0]) and is_array($args[0])) { // 'All arguments in one array' syntax
    $args = $args[0];
  }
  _db_query_callback($args, TRUE);
  $query = preg_replace_callback(DB_QUERY_REGEXP, '_db_query_callback', $query);

  if (variable_get('dev_query', 0)) {
    list($usec, $sec) = explode(' ', microtime());
    $timer = (float)$usec + (float)$sec;
    // If devel.module query logging is enabled, prepend a comment with the username and calling function
    // to the SQL string. This is useful when running mysql's SHOW PROCESSLIST to learn what exact
    // code is issueing the slow query.
    $bt = debug_backtrace();
    // t() may not be available yet so we don't wrap 'Anonymous'
    $name = $user->uid ? $user->name : variable_get('anonymous', 'Anonymous');
    // str_replace() to prevent SQL injection via username or anonymous name.
    $name = str_replace(array('*', '/'), '', $name);
    $query = '/* '. $name .' : '. $bt[2]['function'] .' */ '. $query;
  }
  print '<p>query: ' . $query . '</p>';
}

function do_query($really_do_it, $query) {
  $fn = $really_do_it ? 'debug_query' : 'show_query';
  $args = func_get_args();
  array_shift($args);
  return call_user_func_array($fn, $args);
}


////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful node-related functions.

/**
 * Just get the HTML for a node.
 * @param object $node
 * @return string
 */
function theme_node($node) {
  $node = node_build_content($node, FALSE, FALSE);
  return drupal_render($node->content);
}

/**
 * Create a new node of a certain type, with default settings.
 * The node is not saved to the DB by this function.
 * Remember that the uid is set to 1, so you may want to override this.
 * @param string $type
 * @return object
 */
function node_create($type, $published = TRUE) {
  $node = new stdClass();
  $node->type = $type;
  $node->status = (int)$published;
  $node->uid = 1;
  $node->language = 'en';
  $node->created = $node->changed = time();
  return $node;
}

/**
 * Load a node from the database, bypassing the static node cache in node_load()
 *
 * @param int $nid
 * @return object
 */
function node_load_bypass_cache($nid) {
  $vid = (int) db_result(db_query("SELECT vid FROM {node} WHERE nid = %d", $nid));
  // Remove cached CCK data:
  if (function_exists('content_cache_tablename')) {
    db_query("DELETE FROM " . content_cache_tablename() . " WHERE cid = 'content:$nid:$vid'");
  }
  // load the node:
  return node_load($nid, $vid);
}

/**
 * Delete all nodes of a given type.
 * @param string $type
 */
function node_delete_by_type($type) {
  // Delete all functions:
  $sql = "SELECT * FROM node WHERE type='$type'";
  $rs = db_query($sql);
  while ($rec = db_fetch_array($rs)) {
//    debug($rec);
    node_delete($rec['nid']);
  }
}

/**
 * Get a node's title, given its nid.
 * @param int $nid
 * @return string
 */
function node_get_title($nid) {
  if (!$nid) {
    return '';
  }
  return db_result(db_query('SELECT title FROM {node} WHERE nid = %d', $nid));
}

/**
 * Return a link to a node, given a nid.
 * @param int $nid
 * @return string
 */
function node_get_link($nid) {
  return $nid ? l(node_get_title($nid), "node/$nid") : '';
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful user functions.

/**
 * Make a username from a first and last name.
 *
 * @param string $first_name
 * @param string $last_name
 * @return string
 */
function user_get_base_username($first_name, $last_name = NULL) {
  // Remove any punctuation and whitespace characters from the names:
  $first_name = stripNonAlnum($first_name);
  $last_name = stripNonAlnum($last_name);

  if (!$first_name && !$last_name) {
    // No names provided!  Should never happen.
    return FALSE;
  }
  elseif (!$first_name) {
    $username = $last_name;
  }
  elseif (!$last_name) {
    $username = $first_name;
  }
  else {
    // Build a base name from the first and last name, containing only letters and digits:
    $username = $first_name . $last_name[0];
    // Check if the username is too short:
    if (strlen($username) < 6) {
      // See if we can make a longer one by using the first initial and last name:
      $alt_name = $first_name[0] . $last_name;
      if (strlen($alt_name) > strlen($username)) {
        $username = $alt_name;
      }
      // Check if the username is still too short:
      if (strlen($username) < 6) {
        // See if we can make a longer one by using both first and last names:
        $alt_name = $first_name . $last_name;
        if (strlen($alt_name) > strlen($username)) {
          $username = $alt_name;
        }
      }
    }
  }
  return $username;
}

/**
 * Generate a new, unique username for a new user.
 * @param $first_name
 * @param $last_name
 * @return string
 */
function user_get_unique_name($first_name, $last_name = NULL) {
  // Get the base username:
  $base_name = user_get_base_username($first_name, $last_name);

  // Try out the base_name with appended digits until we find an unused username:
  $i = 0;
  do {
    $name = $base_name . ($i ? $i : '');
    $sql = "SELECT uid FROM {users} WHERE name = '%s'";
    $rec = db_fetch_array(db_query($sql, $name));
    $i++;
  } while ($rec);
  return $name;
}

/**
 * Create a new user with unique name generated from provided names.
 * @param string $first_name
 * @param string $last_name
 * @param int $status = 0
 * @return object
 */
function user_create($first_name, $last_name = NULL, $status = 0) {
  $name = user_get_unique_name($first_name, $last_name);
  return user_save(NULL, array('name' => $name, 'status' => $status));
}

/**
 * Get a user's name - include the first and last name from the profile if found.
 * @param int $uid
 * @return string
 * @author shaunm
 */
function user_get_name($uid) {
  if (!$uid) {
    return '';
  }
  $sql = "
    SELECT u.name, p.field_profile_first_name_value, p.field_profile_last_name_value
    FROM users u
      JOIN node n ON u.uid = n.uid AND n.type = 'profile'
      JOIN content_type_profile p USING (vid)
    WHERE u.uid = %d";
  $rec = db_fetch_array(db_query($sql, $uid));
  if (!$rec) {
    return '';
  }
  $name = trim("{$rec['field_profile_first_name_value']} {$rec['field_profile_last_name_value']}");
  return $name ? "$name ({$rec['name']})" : $rec['name'];
}

/**
 * Return a link to a node, given a nid.
 * @param int $nid
 * @return string
 * @author shaunm
 */
function user_get_link($uid) {
  return $uid ? l(user_get_name($uid), "user/$uid") : '';
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful role-related functions.

/**
 * Given a role name or a rid, find the role.
 *
 * @param mixed $role
 * @return array
 */
function role_load($role) {
  if (is_numeric($role)) {
    // Assume $role == rid
    $sql = "SELECT * FROM {role} WHERE rid = %d";
  }
  else {
    // Assume $role is role name:
    $sql = "SELECT * FROM {role} WHERE name = '%s'";
  }
  return db_fetch_array(db_query($sql, $role));
}

/**
 * Add a given role to a user.
 * @param mixed $user
 * @param mixed $role
 */
function user_add_role(&$user, $role) {
  // get the uid:
  if (is_object($user)) {
    $uid = $user->uid;
  }
  else {
    // assume the first param is a $uid:
    $uid = (int)$user;
  }
  // get the role:
  $rec = role_load($role);
  if ($rec) {
    // see if this user already has this role:
    $sql2 = "SELECT * FROM {users_roles} WHERE uid = %d AND rid = %d";
    $rec2 = db_fetch_array(db_query($sql2, $uid, $rec['rid']));
    if (!$rec2) {
      // add the role:
      $sql3 = "INSERT INTO {users_roles} (uid, rid) VALUES (%d, %d)";
      db_query($sql3, $uid, $rec['rid']);
      // update the user object:
      if (is_object($user)) {
        $user->roles[$rec['rid']] = $rec['name'];
      }
    }
    return $rec;
  }
  return FALSE;
}

/**
 * Remove a given role from a user.
 * This function does not use Drupal functions but direct db access for speed.
 * @param mixed $user
 * @param mixed $role
 */
function user_remove_role(&$user, $role) {
  // Get the uid:
  if (is_object($user)) {
    $uid = $user->uid;
  }
  else {
    // Assume the first param is a $uid:
    $uid = (int) $user;
  }
  // Get the role:
  $rec = role_load($role);
  if ($rec) {
    // Remove the role for this user:
    $sql2 = "DELETE FROM {users_roles} WHERE uid = %d AND rid = %d";
    db_query($sql2, $uid, $rec['rid']);
  }
}

/**
 * Returns TRUE if the user has the given role.
 * If $user == NULL, defaults to the logged-in user.
 * If the user is not logged in, returns FALSE.
 * @param mixed $role
 * @param object $user
 * @param array $edit Edit fields as would be passed to hook_user() or user_save()
 * @return bool
 */
function user_has_role($role, $user = NULL, $edit = array()) {
  // default to global user:
  if (!$user) {
    $user = $GLOBALS['user'];
  }
  // allow for $user being the uid:
  if (is_numeric($user)) {
    $uid = $user;
    $user = user_load($uid);
  }
  if (!$user || !$user->uid) {
    return FALSE;
  }
  $role_info = role_load($role);
  $rid = $role_info['rid'];
  $result = $user->roles[$rid] || $edit['roles'][$rid];
//  debug($result ? "user is $role" : "user is not $role");
  return $result;
}

/**
 * Returns TRUE if the user has the given role.
 * If $user == NULL, defaults to the logged-in user.  If the user is not logged in, returns FALSE.
 * Same as above function but optimised for speed.
 * This function does not load or save the user, but access the DB directly for speed.
 *
 * @param mixed $role Can be rid or role name.
 * @param int $uid
 * @return bool
 */
function user_has_role_by_uid($role, $uid) {
  if (!$uid) {
    return FALSE;
  }
  // Get the role info:
  $role = role_load($role);
  // See if the user has this role:
  $sql = "SELECT * FROM {users_roles} WHERE uid = %d AND rid = %d";
  $rec = db_fetch_array(db_query($sql, $uid, $role['rid']));
  return (bool) $rec;
}

/**
 * Get all roles for a user as an array of rid => role_name.
 * Does not load user object, optimised for speed.
 * @param int $uid
 * @return array
 */
function user_get_roles($uid) {
  if (!$uid) {
    return FALSE;
  }
  $sql = "
    SELECT *
    FROM {users_roles} ur LEFT JOIN {role} USING (rid)
    WHERE uid = %d";
  $rs = db_query($sql, $uid);
  $roles = array();
  while ($rec = db_fetch_array($rs)) {
    $roles[(int) $rec['rid']] = $rec['name'];
  }
  return $roles;
}


////////////////////////////////////////////////////////////////////////////////////////////////////
// Useful form-related functions.

/**
 * Remove any elements from an array with a key equal to $key, regardlesss of depth.
 * @param string $key
 * @param array $array
 * @param string $parent
 */
function remove_key_from_array($key, &$array, $parent = '') {
  if (is_array($array) && !empty($array)) {
    foreach ($array as $key2 => $value) {
      if ($key2 === $key) {
        // debug("Removing \$array{$parent}[$key2].");
        unset($array[$key2]);
      }
      else if (is_array($value) && !empty($value)) {
        remove_key_from_array($key, $array[$key2], $parent . "[$key2]");
      }
    }
  }
}

/**
 * Remove a field or a group from a form.
 * @param string $field
 * @param array $form
 * @param array $form_state
 */
function remove_field_from_form($field, &$form, &$form_state = NULL) {
  remove_key_from_array($field, $form);
  if ($form_state) {
    remove_key_from_array($field, $form_state);
  }
}

/**
 * Remove a group from a form including all fields inside it.
 * @param string $group
 * @param array $form
 * @param array $form_state
 */
function remove_group_from_form($group, &$form, &$form_state = NULL) {
  // remove the fields in the group:
  if (is_array($form[$group])) {
    foreach ($form[$group] as $key => $value) {
      // don't remove attributes, which start with '#':
      if ($key[0] != '#') {
        // debug("Removing $key");
        remove_key_from_array($key, $form);
        if ($form_state) {
          remove_key_from_array($key, $form_state);
        }
      }
    }
  }
  // remove the group:
  remove_key_from_array($group, $form);
  if ($form_state) {
    remove_key_from_array($group, $form_state);
  }
}

/**
 * Get the true path to the root of the Drupal site.
 * Better than using DOCUMENT_ROOT and base_path().
 *
 * NOTE - this code is replicated in classes_base_dir()
 * Copied here because this is a more logical place for it.
 * Kept in the classes module so that this file is not a dependency.
 * @todo Real solution is to make the Star library a module.
 */
function absolute_path_to_drupal() {
  static $absolute_path_to_drupal = NULL;

  if ($absolute_path_to_drupal === NULL) {
    // Get the absolute path to this file:
    $dir = rtrim(str_replace('\\', '/', dirname(__FILE__)), '/');
    $parts = explode('/', $dir);

    // Iterate up the directory hierarchy until we find the website root:
    $done = FALSE;
    do {
      // Check a couple of obvious things:
      $done = is_dir("$dir/sites") && is_dir("$dir/includes") && is_file("$dir/index.php");
      if (!$done) {
        // If there's no more path to examine, we didn't find the site root:
        if (empty($parts)) {
          $absolute_path_to_drupal = FALSE;
          break;
        }
        // Go up one level and look again:
        array_pop($parts);
        $dir = implode('/', $parts);
      }
    } while (!$done);
    
    $absolute_path_to_drupal = $dir;
  }
  
  return $absolute_path_to_drupal;
}
