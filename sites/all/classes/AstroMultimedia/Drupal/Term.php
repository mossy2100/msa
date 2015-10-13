<?php
namespace AstroMultimedia\Drupal;

use \stdClass;

/**
 * Class to encapsulate D7 taxonomy terms.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2012-10-10 16:38
 */
class Term extends Entity {

  /**
   * The entity type.
   *
   * @var string
   */
  const ENTITY_TYPE = 'taxonomy_term';

  /**
   * The database table name.
   *
   * @var string
   */
  const DB_TABLE = 'taxonomy_term_data';

  /**
   * The primary key.
   *
   * @var string
   */
  const PRIMARY_KEY = 'tid';

  /**
   * The vocabulary machine name, a.k.a. 'term type'. To be overridden by child classes.
   *
   * @var string
   */
  const VOCABULARY_MACHINE_NAME = NULL;

  /**
   * The vocabulary that this term belongs to.
   *
   * @var Vocabulary
   */
  protected $vocabulary;

  /**
   * The term's parents, if any.
   *
   * @var array
   */
  protected $parents;

  /**
   * Constructor.
   */
  protected function __construct() {
    // Create the object:
    parent::__construct();

    // Set the vocabulary:
    $class = get_called_class();
    $this->vocabulary = Vocabulary::create($class::VOCABULARY_MACHINE_NAME);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Create/delete

  /**
   * Create a new Term object.
   *
   * @param null|int|stdClass $term_param
   * @return Term
   */
  public static function create($term_param = NULL, $vocab_obj = NULL) {
    // Get the class of the object we want to create:
    $class = get_called_class();

    if (is_null($term_param)) {
      // Create new term:
      $term_obj = new $class;

      // Set the vocabulary:
      $term_obj->vocabulary = Vocabulary::create($class::VOCABULARY_MACHINE_NAME);

      // The new term is valid without a tid:
      $term_obj->valid = TRUE;
    }
    elseif (is_uint($term_param)) {
      // tid provided:
      $tid = $term_param;

      // Only create the new term if not already in the cache:
      if (self::inCache($tid)) {
        return self::getFromCache($tid);
      }
      else {
        // Create new term:
        $term_obj = new $class;

        // Set the vocabulary:
        $term_obj->vocabulary = Vocabulary::create($class::VOCABULARY_MACHINE_NAME);

        // Set the tid:
        $term_obj->entity->tid = $tid;
      }
    }
    elseif ($term_param instanceof stdClass) {
      // Drupal term object provided:
      $term = $term_param;

      // Get the object from the cache if possible:
      if (isset($term->tid) && $term->tid && self::inCache($term->tid)) {
        $term_obj = self::getFromCache($term->tid);
      }
      else {
        $term_obj = new $class;
      }

      // Reference the provided entity object:
      $term_obj->entity = $term;

      // Make sure we mark the term as loaded and valid. It may not have been saved yet, and if we load it, any
      // changes to the term entity would be overwritten.
      $term_obj->loaded = TRUE;
      $term_obj->valid = TRUE;
    }

    // If we have a term object, add to cache and return:
    if (isset($term_obj)) {
      $term_obj->addToCache();

      // If we have a Vocabulary object, attach it. But check the vid first.
      if ($vocab_obj) {
        if ($term_obj->entity->vid) {
          if ($vocab_obj->vid() == $term_obj->entity->vid) {
            $term_obj->vocabulary = $vocab_obj;
          }
          else {
            trigger_error("Term::create() - Mistmatched vocabulary id's.", E_USER_WARNING);
          }
        }
        else {
          $vid = $vocab_obj->vid();
          if ($vid) {
            $term_obj->entity->vid = $vid;
          }
          $term_obj->vocabulary = $vocab_obj;
        }
      }

      return $term_obj;
    }

    trigger_error("Term::create() - Invalid parameter.", E_USER_WARNING);
  }

  /**
   * Delete a term.
   */
  public function delete() {
    taxonomy_term_delete($this->tid());
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load/save

  /**
   * Load the term object.
   *
   * @return Term
   */
  public function load() {
    // Avoid reloading:
    if ($this->loaded) {
      return $this;
    }

    // Default result:
    $term = FALSE;

    // If we have a tid, try to load the term:
    if (isset($this->entity->tid)) {
      // Load by tid. Drupal caching will prevent reloading of the same term.
      $term = taxonomy_term_load($this->entity->tid);
    }

    // Set the valid flag:
    $this->valid = (bool) $term;

    // If the term was successfully loaded, update properties:
    if ($term) {
      $this->entity = $term;
      $this->loaded = TRUE;
    }

    return $this;
  }

  /**
   * Save the term object.
   *
   * @return Term
   */
  public function save() {
    // Ensure the term is loaded if not already:
    $this->load();

    // Save the term:
    taxonomy_term_save($this->entity);

    // If the term is new, the tid has now been set, so add it to the cache:
    $this->addToCache();

    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set

  /**
   * Get/set the tid.
   *
   * @param int $tid
   * @return int|Term
   */
  public function tid($tid = NULL) {
    if ($tid === NULL) {
      // Get the tid:
      return isset($this->entity->tid) ? $this->entity->tid : NULL;
    }
    else {
      // Set the tid:
      $this->entity->tid = $tid;

      // Add the term object to the cache if not already:
      $this->addToCache();

      return $this;
    }
  }

  /**
   * Get the term object.
   *
   * @return stdClass
   */
  public function term() {
    return $this->entity();
  }

  /**
   * Get/set the term's name.
   *
   * @param null|string $name
   * @return string|Term
   */
  public function name($name = NULL) {
    return $this->prop('name', $name);
  }

  /**
   * Get/set the term's description.
   *
   * @param null|string $description
   * @return string|Term
   */
  public function description($description = NULL) {
    return $this->prop('description', $description);
  }

  /**
   * Get/set the term's vocabulary id.
   *
   * @param null|int $vid
   * @return string|Term
   */
  public function vid($vid = NULL) {
    return $this->prop('vid', $vid);
  }

  /**
   * Get the term's vocabulary.
   * We don't use the VOCABULARY_MACHINE_NAME of the derived class, we use the vid which is more reliable.
   *
   * @return Vocabulary
   */
  public function vocabulary() {
    if (!isset($this->vocabulary)) {
      $this->vocabulary = Vocabulary::create($this->vid());
    }
    return $this->vocabulary;
  }

  /**
   * Get the term's parents.
   *
   * @return array
   */
  public function parents() {
    if (!isset($this->parents)) {
      $this->load();
      $this->parents = array();
      foreach ($this->entity->parents as $parent_tid) {
        if ($parent_tid) {
          $this->parents[$parent_tid] = Term::create($parent_tid);
        }
      }
    }
    return $this->parents;
  }

  /**
   * Get the first parent of the term. There will usually only be one anyway.
   *
   * @return Term|null
   */
  public function parent() {
    $parents = $this->parents();
    if ($parents) {
      $parent = array_shift($parents);
    }
    return isset($parent) ? $parent : NULL;
  }

}
