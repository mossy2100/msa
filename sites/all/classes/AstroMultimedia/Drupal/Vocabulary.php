<?php
namespace AstroMultimedia\Drupal;

use \stdClass;

/**
 * Encapsulates a Drupal 7 taxonomy vocabulary.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2012-10-10 16:38
 */
class Vocabulary {

  /**
   * Static cache of entity objects.
   *
   * @var array
   */
  protected static $cache;

  /**
   * The Drupal vocabulary object.
   *
   * @var stdClass
   */
  protected $entity;

  /**
   * If the vocabulary has been loaded yet.
   *
   * @var bool
   */
  protected $loaded;

  /**
   * If the vid is valid, i.e. refers to an actual vocabulary in the database.
   *
   * @var bool
   */
  protected $valid;

  /**
   * The taxonomy terms in this vocabulary.
   *
   * @var array
   */
  protected $terms;

  /**
   * Private constructor.
   */
  private function __construct() {}

  /**
   * Create a new Vocabulary object.
   *
   * @todo improve this so it uses lazy loading and caching, like entities.
   *
   * @param null|int|string $vocabulary_key
   */
  public static function create($vocabulary_key = NULL) {
    if (is_null($vocabulary_key)) {
      $vocab = NULL;
      $vocab_obj = new Vocabulary();
      $vocab_obj->loaded = TRUE;
      $vocab_obj->valid = TRUE;
    }
    elseif (is_uint($vocabulary_key)) {
      $vocab = taxonomy_vocabulary_load($vocabulary_key);
    }
    else {
      $vocab = taxonomy_vocabulary_machine_name_load($vocabulary_key);
    }
    if ($vocab) {
      $vocab_obj = new Vocabulary();
      $vocab_obj->entity = $vocab;
      $vocab_obj->loaded = TRUE;
      $vocab_obj->valid = TRUE;
    }
    return $vocab_obj;
  }

  /**
   * Get all the terms in the vocabulary.
   *
   * @return array
   */
  public function terms() {
    if (!isset($this->terms)) {
      if (!$this->entity) {
        trigger_error("Vocabulary::terms() - Invalid vocabulary.", E_USER_WARNING);
        return FALSE;
      }
      $tree = taxonomy_get_tree($this->entity->vid);
      $this->terms = [];
      foreach ($tree as $term) {
        $this->terms[$term->tid] = Term::create($term, $this);
      }
    }
    return $this->terms;
  }

  /**
   * Get the vid.
   *
   * @return int|null
   */
  public function vid() {
    return isset($this->entity->vid) ? $this->entity->vid : NULL;
  }
}
