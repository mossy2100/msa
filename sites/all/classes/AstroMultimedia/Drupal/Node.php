<?php
namespace AstroMultimedia\Drupal;

use \stdClass;

/**
 * Node class.
 */
class Node extends Entity {

  /**
   * The entity type.
   *
   * @var string
   */
  const ENTITY_TYPE = 'node';

  /**
   * The database table name.
   *
   * @var string
   */
  const DB_TABLE = 'node';

  /**
   * The primary key.
   *
   * @var string
   */
  const PRIMARY_KEY = 'nid';

  /**
   * The node type.
   */
  const NODE_TYPE = NULL;

  /**
   * The node's comments.
   *
   * @var array
   */
  protected $comments;

  /**
   * Users who commented on the node.
   *
   * @var array
   */
  protected $commenters;

  /**
   * Constructor.
   */
  protected function __construct() {
    // Create the object:
    parent::__construct();

    // Set the node type:
    $class = get_called_class();
    $this->entity->type = $class::NODE_TYPE;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Create and delete.

  /**
   * Create a new Node object.
   *
   * @param null|int|stdClass $param
   * @return Node
   */
  public static function create($param = NULL) {
    // Get the class of the object we want to create:
    $class = get_called_class();

    if (is_null($param)) {
      // Create new node:
      $node_obj = new $class;

      // Set the type:
      $node_obj->entity->type = $class::NODE_TYPE;

      // Default status to published:
      $node_obj->entity->status = 1;

      // Default language to none:
      $node_obj->entity->language = LANGUAGE_NONE;

      // Default user to current user:
      $node_obj->entity->uid = user_is_logged_in() ? $GLOBALS['user']->uid : NULL;

      // The node is valid without a nid:
      $node_obj->valid = TRUE;
    }
    elseif (is_uint($param)) {
      // nid provided:
      $nid = $param;

      // Only create the new node if not already in the cache:
      if (self::inCache($nid)) {
        return self::getFromCache($nid);
      }
      else {
        // Create new node:
        $node_obj = new $class;

        // Set the nid:
        $node_obj->entity->nid = $nid;
      }
    }
    elseif ($param instanceof stdClass) {
      // Drupal node object provided:
      $node = $param;

      // Get the object from the cache if possible:
      if (isset($node->nid) && $node->nid && self::inCache($node->nid)) {
        $node_obj = self::getFromCache($node->nid);
      }
      else {
        $node_obj = new $class;
      }

      // Reference the provided entity object:
      $node_obj->entity = $node;

      // Make sure we mark the node as loaded and valid. It may not have been saved yet, and if we load it, any
      // changes to the node entity would be overwritten.
      $node_obj->loaded = TRUE;
      $node_obj->valid = TRUE;
    }

    // If we have a node object, add to cache and return:
    if (isset($node_obj)) {
      $node_obj->addToCache();
      return $node_obj;
    }

    trigger_error("Node::create() - Invalid parameter.", E_USER_WARNING);
  }

  /**
   * Delete a node.
   */
  public function delete() {
    node_delete($this->nid());
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load and save.

  /**
   * Load the node object.
   *
   * @return Node
   */
  public function load() {

    // Avoid reloading:
    if ($this->loaded) {
      return $this;
    }

    // Default result:
    $node = FALSE;

    // If we have a nid, try to load the node:
    if (isset($this->entity->nid) && $this->entity->nid) {
      // Load by nid. Drupal caching will prevent reloading of the same node.
      $node = node_load($this->entity->nid);
    }

    // Set the valid flag:
    $this->valid = (bool) $node;

    // If the node was successfully loaded, update properties:
    if ($node) {
      $this->entity = $node;
      $this->loaded = TRUE;
    }

    return $this;
  }

  /**
   * Save the node object.
   *
   * @return Node
   */
  public function save() {
    // Ensure the node is loaded:
    $this->load();

    // We must set the pathauto flag so any custom alias doesn't get clobbered.
    $this->setPathauto();

    // Save the node:
    node_save($this->entity);

    // In case the node is new, add it to the cache:
    $this->addToCache();

    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get and set.

  /**
   * Get the quick-load properties.
   *
   * @static
   * @return array
   */
  protected static function quickLoadProperties() {
    return array('title', 'type', 'uid');
  }

  /**
   * Get/set the nid.
   *
   * @param int $nid
   * @return int|Node
   */
  public function nid($nid = NULL) {
    if ($nid === NULL) {
      // Get the nid:
      return isset($this->entity->nid) ? $this->entity->nid : NULL;
    }
    else {
      // Set the nid:
      $this->entity->nid = $nid;

      // Add the node object to the cache if not already:
      $this->addToCache();

      return $this;
    }
  }

  /**
   * Get the node object.
   *
   * @return stdClass
   */
  public function node() {
    return $this->entity();
  }

  /**
   * Get/set the node's title.
   *
   * @param null|string
   * @return string|Node
   */
  public function title($title = NULL) {
    return $this->prop('title', $title);
  }

  /**
   * Get/set the node's type.
   *
   * Note as a rule we never say just 'type' because it's far too easy to get node type, entity type, field type,
   * relation type, actor type, etc., mixed up, which is a source of bugs.
   *
   * @param null|string
   * @return string|Node
   */
  public function nodeType($type = NULL) {
    // If getting, we could theoretically just return self::NODE_TYPE here. However, by checking the property
    // this function can be used to check if the node referenced by the $entity property is the correct type.
    return $this->prop('type', $type);
  }

  /**
   * Get/set the node's uid.
   *
   * @param null|int
   * @return int|Node
   */
  public function uid($uid = NULL) {
    return $this->prop('uid', $uid);
  }

  /**
   * Get the node's creator.
   *
   * @return User
   */
  public function creator() {
    return User::create($this->uid());
  }

  /**
   * Get a link to the node's page.
   *
   * @param null|string $label
   * @param bool $absolute
   * @return string
   */
  public function link($label = NULL, $absolute = FALSE) {
    $label = ($label === NULL) ? $this->title() : $label;
    return parent::link($label, $absolute);
  }

  /**
   * Get the node's comments, order by created time.
   *
   * @param bool|null $published
   *   NULL for all comments
   *   TRUE for published comments (default)
   *   FALSE for unpublished comments
   * @param string $comment_class
   *   The class to use for the comment objects.
   * @return array
   */
  public function comments($published = TRUE, $comment_class = 'Comment') {
    if (!isset($this->comments)) {
      // Get the comments:
      $q = db_select('comment', 'c')
        ->fields('c', array('cid'))
        ->condition('nid', $this->nid())
        ->orderBy('created');

      $rs = $q->execute();
      $this->comments = array();
      foreach ($rs as $rec) {
        $this->comments[] = $comment_class::create($rec->cid);
      }
    }

    // If we want both published and unpublished comments, return them all:
    if ($published === NULL) {
      return $this->comments;
    }

    // Return just the published or unpublished comments:
    $comments = array();
    foreach ($this->comments as $comment) {
      if ($comment->published() == $published) {
        $comments[] = $comment;
      }
    }
    return $comments;
  }

  /**
   * Find out how many comments the node has.
   *
   * @param bool|null $published
   *   NULL for all comments
   *   TRUE for published comments (default)
   *   FALSE for unpublished comments
   */
  public function commentCount($published = TRUE) {
    $q = db_select('comment', 'c')
      ->fields('c', array('cid'))
      ->condition('nid', $this->nid());

    // Set the published condition if specified:
    if (is_bool($published)) {
      $q->condition('status', (int) $published);
    }

    return $q->execute()->rowCount();
  }

  /**
   * Get the users who commented on this item.
   *
   * @return array
   */
  public function commenters($user_class = 'User') {
    if (!isset($this->commenters)) {
      $q = db_select('comment', 'c')
        ->fields('c', array('uid'))
        ->distinct()
        ->condition('nid', $this->nid())
        ->orderBy('created');
      $rs = $q->execute();
      $this->commenters = array();
      foreach ($rs as $rec) {
        $this->commenters[$rec->uid] = $user_class::create($rec->uid);
      }
    }
    return $this->commenters;
  }

  /**
   * Get the most recently created comment.
   *
   * @return ItemComment
   */
  public function lastCommentCreated($published = TRUE, $comment_class = 'Comment') {
    // Always get the answer in the fastest possible way.

    // If we already have the comments, find which was created most recently:
    if (isset($this->comments)) {
      $comments = $this->comments($published, $comment_class);
      if (!$comments) {
        return NULL;
      }
      // $comments are ordered by created time, so just grab the latest:
      return $this->comments[count($this->comments) - 1];
    }

    // Look up the most recently created comment:
    $q = db_select('comment', 'c')
      ->fields('c', array('cid'))
      ->condition('nid', $this->nid());
    if ($published !== NULL) {
      $q->condition('status', $published);
    }
    $cid = $q->orderBy('created', 'DESC')->range(0, 1)->execute()->fetchField();
    return $cid ? $comment_class::create($cid) : NULL;
  }

  /**
   * Get the most recently changed comment.
   *
   * @return ItemComment
   */
  public function lastCommentChanged($published = TRUE, $comment_class = 'Comment') {
    // Always get the answer in the fastest possible way.

    // If we already have the comments, find which was changed most recently:
    if (isset($this->comments)) {
      $comments = $this->comments($published, $comment_class);
      if (!$comments) {
        return NULL;
      }
      $latest_comment_changed_time = NULL;
      $latest_comment = NULL;
      foreach ($comments as $comment) {
        $comment_changed_time = $comment->changed();
        if (!$latest_comment_changed_time || $comment_changed_time > $latest_comment_changed_time) {
          $latest_comment_changed_time = $comment_changed_time;
          $latest_comment = $comment;
        }
      }
      return $latest_comment;
    }

    // Look up the most recently changed comment:
    $q = db_select('comment', 'c')
      ->fields('c', array('cid'))
      ->condition('nid', $this->nid());
      if ($published !== NULL) {
        $q->condition('status', $published);
      }
    $cid = $q->orderBy('changed', 'DESC')->range(0, 1)->execute()->fetchField();
    return $cid ? $comment_class::create($cid) : NULL;
  }

  /**
   * Get the datetime of the latest comment changed.
   *
   * @return DateTime
   */
  public function lastCommentChangedTime($published = TRUE) {
    // Always get the answer in the fastest possible way.

    // If we already have the comments, grab the time from the array:
    if (isset($this->comments)) {
      $latest_comment = $this->lastCommentChanged();
      return $latest_comment ? $latest_comment->changed() : NULL;
    }

    // If we don't, look it up directly:
    $q = db_select('comment', 'c')
      ->fields('c', array('changed'))
      ->condition('nid', $this->nid());
    if ($published !== NULL) {
      $q->condition('status', $published);
    }
    $changed = $q->orderBy('changed', 'DESC')->range(0, 1)->execute()->fetchField();
    $datetime_class = $this->_dateTimeClass();
    return $changed ? (new $datetime_class($changed)) : NULL;
  }

  /**
   * Get the modified datetime.
   * This is the latest of the changed timestamp of the item, and of the latest comment.
   *
   * @return DateTime
   */
  public function modified() {
    $changed = $this->changed();
    $latest_comment_time = $this->lastCommentChangedTime();
    return ($latest_comment_time && $latest_comment_time > $changed) ? $latest_comment_time : $changed;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Publish and unpublish.

  /**
   * Get the node status.
   *
   * @return Node
   */
  public function published() {
    return $this->prop('status');
  }

  /**
   * Publish the node, i.e. set the status flag to 1.
   *
   * @return Node
   */
  public function publish() {
    return $this->prop('status', 1);
  }

  /**
   * Unpublish the node, i.e. set the status flag to 0.
   *
   * @return Node
   */
  public function unpublish() {
    return $this->prop('status', 0);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Render method.

  /**
   * Get the HTML for a node.
   *
   * @param bool $include_comments
   * @param string $view_mode
   * @return string
   */
  public function render($include_comments = FALSE, $view_mode = 'full') {
    $node = $this->node();
    $node_view = node_view($node, $view_mode);
    if ($include_comments) {
      $node_view['comments'] = comment_node_page_additions($node);
    }
    return render($node_view);
  }

}
