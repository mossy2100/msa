<?php
namespace AstroMultimedia\Drupal;

use \stdClass;

/**
 * Relation class.
 */
class Relation extends Entity {

  /**
   * The entity type.
   *
   * @var string
   */
  const ENTITY_TYPE = 'relation';

  /**
   * The database table name.
   *
   * @var string
   */
  const DB_TABLE = 'relation';

  /**
   * The primary key
   *
   * @var string
   */
  const PRIMARY_KEY = 'rid';

  /**
   * Constructor.
   */
  protected function __construct() {
    parent::__construct();

    // All relations in MM are owned by the superuser:
    $this->entity->uid = 1;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Create/delete

  /**
   * Create a new Relation object.
   *
   * @param null|int|stdClass $relation_param
   * @return Relation
   */
  public static function create($relation_param = NULL) {
    // Get the class of the object we want to create:
    $class = get_called_class();

    if (is_null($relation_param)) {
      // Create new relation:
      $relation_obj = new $class;

      // The relation is valid without a rid:
      $relation_obj->valid = TRUE;
    }
    elseif (is_uint($relation_param)) {
      // rid provided:
      $rid = $relation_param;

      // Only create the new relation if not already in the cache:
      if (self::inCache($rid)) {
        return self::getFromCache($rid);
      }
      else {
        // Create new relation:
        $relation_obj = new $class;

        // Set the rid:
        $relation_obj->entity->rid = $rid;
      }
    }
    elseif ($relation_param instanceof stdClass) {
      // Drupal relation object provided:
      $relation = $relation_param;

      // Get the object from the cache if possible:
      if (isset($relation->rid) && $relation->rid && self::inCache($relation->rid)) {
        $relation_obj = self::getFromCache($relation->rid);
      }
      else {
        $relation_obj = new $class;
      }

      // Reference the provided entity object:
      $relation_obj->entity = $relation;

      // Make sure we mark the relation as loaded and valid. It may not have been saved yet, and if we load it, any
      // changes to the relation entity would be overwritten.
      $relation_obj->loaded = TRUE;
      $relation_obj->valid = TRUE;
    }

    // If we have a relation object, add to cache and return:
    if (isset($relation_obj)) {
      $relation_obj->addToCache();
      return $relation_obj;
    }

    trigger_error("Relation::create() - Invalid parameter.", E_USER_WARNING);
  }

  /**
   * Delete a relation.
   */
  public function delete() {
    relation_delete($this->rid());
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load/save

  /**
   * Load the relation object.
   *
   * @return Relation
   */
  public function load() {
    // Avoid reloading:
    if ($this->loaded) {
      return $this;
    }

    // Default result:
    $relation = FALSE;

    // If we have a rid, try to load the relation:
    if (isset($this->entity->rid) && $this->entity->rid) {
      // Load by rid. Drupal caching will prevent reloading of the same relation.
      $relation = relation_load($this->entity->rid);
    }

    // Set the valid flag:
    $this->valid = (bool) $relation;

    // If the relation was successfully loaded, update properties:
    if ($relation) {
      $this->entity = $relation;
      $this->loaded = TRUE;
    }

    return $this;
  }

  /**
   * Save the relation object.
   *
   * @return Relation
   */
  public function save() {
    // Ensure the relation has been loaded:
    $this->load();

    // Save the relation:
    $result = relation_save($this->entity);
    if (!$result) {
      trigger_error('AstroMultimedia\Drupal\Relation', "Relation could not be saved", E_USER_WARNING);
      return FALSE;
    }

    // In case the relation is new, add it to the cache:
    $this->addToCache();

    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set

  /**
   * Get/set the rid.
   *
   * @param int $rid
   * @return int|Relation
   */
  public function rid($rid = NULL) {
    if ($rid === NULL) {
      // Get the rid:
      return isset($this->entity->rid) ? $this->entity->rid : NULL;
    }
    else {
      // Set the rid:
      $this->entity->rid = $rid;

      // Add the relation object to the cache if not already:
      $this->addToCache();

      return $this;
    }
  }

  /**
   * Get the relation object.
   *
   * @return stdClass
   */
  public function relation() {
    return $this->entity();
  }

  /**
   * Get/set the uid of the user who created the relation.
   *
   * @param null|int
   * @return int|Relation
   */
  public function uid($uid = NULL) {
    return $this->prop('uid', $uid);
  }

  /**
   * Get the relation's creator.
   *
   * @return User
   */
  public function creator() {
    return User::create($this->uid());
  }

  /**
   * Get the relation type.
   *
   * @return string
   */
  public function relationType() {
    return $this->prop('relation_type');
  }

  /**
   * Get an endpoint.
   *
   * @param string $lang
   * @param int $delta
   * @return object|null
   */
  public function endpoint($delta, $lang = LANGUAGE_NONE) {
    $this->load();
    return isset($this->entity->endpoints[$lang][$delta]) ? ((object) $this->entity->endpoints[$lang][$delta]) : NULL;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Status flags.

  /**
   * Get the relation status.
   *
   * @return Relation
   */
  public function published() {
    return $this->prop('status');
  }

  /**
   * Publish the relation, i.e. set the status flag to 1.
   *
   * @return Relation
   */
  public function publish() {
    return $this->prop('status', 1);
  }

  /**
   * Unpublish the relation, i.e. set the status flag to 0.
   *
   * @return Relation
   */
  public function unpublish() {
    return $this->prop('status', 0);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods for working with binary relationships.

  /**
   * Create a new binary relation.
   *
   * @static
   * @param string $relationship_type
   * @param Entity $entity0
   * @param Entity $entity1
   * @param bool $save
   *   Whether or not to save the relationship. Defaults to TRUE.
   * @return Relation
   */
  public static function createBinary($relationship_type, $entity0, $entity1, $save = TRUE) {
    $entity_type0 = $entity0->entityType();
    $entity_id0 = $entity0->id();
    $entity_type1 = $entity1->entityType();
    $entity_id1 = $entity1->id();

    // Get the called class:
    $class = get_called_class();

    $endpoints = array(
      array(
        'entity_type' => $entity_type0,
        'entity_id'   => $entity_id0,
      ),
      array(
        'entity_type' => $entity_type1,
        'entity_id'   => $entity_id1,
      ),
    );

    // Create the relation entity.
    $rel_entity = relation_create($relationship_type, $endpoints);

    // Create the Relation object:
    $relation = $class::create($rel_entity);

    // Save if requested:
    if ($save) {
      $relation->save();
    }

    return $relation;
  }

  /**
   * Search for relationships matching the provided parameters.
   *
   * @todo This method currently relies on the database view 'view_relationship', which makes it somewhat unportable.
   *
   * @param string $relationship_type
   * @param null|Entity $entity0
   *   Use NULL to match all.
   * @param null|Entity $entity1
   *   Use NULL to match all.
   * @param null|int $offset
   * @param null|int $limit
   * @return array
   */
  public static function searchBinary($relationship_type, $entity0 = NULL, $entity1 = NULL, $offset = NULL, $limit = NULL, $orderByField = NULL, $orderByDirection = NULL) {
    // Look for a relationship record:
    $q = db_select('view_relationship', 'vr')
      ->fields('vr', array('rid'));

    // Add conditions:
    $q->condition('relation_type', $relationship_type);
    if ($entity0 !== NULL) {
      $q->condition('entity_type0', $entity0->entityType());
      $q->condition('entity_id0', $entity0->id());
    }
    if ($entity1 !== NULL) {
      $q->condition('entity_type1', $entity1->entityType());
      $q->condition('entity_id1', $entity1->id());
    }

    // Add LIMIT clause:
    if ($offset !== NULL && $limit !== NULL) {
      $q->range($offset, $limit);
    }

    // Add ORDER BY clause:
    if ($orderByField === NULL) {
      $orderByField = 'changed';
    }
    if ($orderByDirection === NULL) {
      $orderByDirection = 'DESC';
    }
    $q->orderBy($orderByField, $orderByDirection);

    // Get the called class:
    $class = get_called_class();

    // Get the relationships:
    $rs = $q->execute();
    $results = array();
    foreach ($rs as $rec) {
      $results[] = $class::create($rec->rid);
    }
    return $results;
  }

  /**
   * Update or create a relationship.
   *
   * @param string $relationship_type
   * @param Entity $entity0
   * @param Entity $entity1
   * @param bool $save
   *   Whether or not to save the relationship. Defaults to TRUE.
   * @return Relation
   */
  public static function updateBinary($relationship_type, $entity0, $entity1, $save = TRUE) {
    // Get the called class:
    $class = get_called_class();

    // See if the relationship already exists:
    $rels = $class::searchBinary($relationship_type, $entity0, $entity1);

    if ($rels) {
      // Update the relationship. We really just want to update the changed timestamp, so let's just load and save it.
      $rel = $rels[0];
      $rel->load();

      if ($save) {
        $rel->save();
      }
    }
    else {
      // Create a new relationship:
      $rel = $class::createBinary($relationship_type, $entity0, $entity1, $save);
    }

    return $rel;
  }

  /**
   * Delete relationships.
   *
   * @param string $relationship_type
   * @param Entity $entity0
   * @param Entity $entity1
   * @return bool
   *   TRUE on success, FALSE on failure
   */
  public static function deleteBinary($relationship_type, $entity0 = NULL, $entity1 = NULL) {
    // Get the called class:
    $class = get_called_class();

    // Get the relationships:
    $rels = $class::searchBinary($relationship_type, $entity0, $entity1);

    // If none were found, return FALSE:
    if (empty($rels)) {
      return FALSE;
    }

    // Delete the relationships:
    foreach ($rels as $rel) {
      $rel->delete();
    }

    return TRUE;
  }

}
