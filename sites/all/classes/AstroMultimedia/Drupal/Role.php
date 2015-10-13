<?php
namespace AstroMultimedia\Drupal;

/**
 * Encapsulates a user role.
 */
class Role {

  /**
   * Role object cache.
   *
   * @var array
   */
  protected static $cache;

  /**
   * The role id.
   *
   * @var int
   */
  protected $rid;

  /**
   * The role name.
   *
   * @var string
   */
  protected $name;

  /**
   * If the role is loaded.
   *
   * @var bool
   */
  protected $loaded;

  /**
   * If the role is valid.
   *
   * @var bool
   */
  protected $valid;

  /**
   * Constructor.
   *
   * @param null|int|string $role_param
   * @param null|string $role_name
   */
  protected function __construct($role_param = NULL, $role_name = NULL) {
    if (is_uint($role_param)) {
      $this->rid($role_param);
      if (is_string($role_name)) {
        $this->name($role_name);
      }
    }
    elseif (is_string($role_param)) {
      $this->name($role_param);
    }
  }

  /**
   * Create a new Role object.
   *
   * @param null|int|string $role_param
   * @param null|string $role_name
   * @return Role
   */
  public static function create($role_param = NULL, $role_name = NULL) {
    // If provided with a rid, check the object cache:
    if (is_uint($role_param) && isset(self::$cache[$role_param])) {
      return self::$cache[$role_param];
    }
    // Create a new object:
    return new self($role_param, $role_name);
  }

  /**
   * Load the role.
   *
   * @return Role
   */
  public function load() {
    // Avoid reloading:
    if ($this->loaded) {
      return $this;
    }

    // Default result:
    $role = FALSE;

    // If we have a rid, try to load the role:
    if ($this->rid) {
      $role = role_load($this->rid);
    }
    elseif ($this->name) {
      $role = role_load($this->name);
    }

    if ($role) {
      // Copy the fields:
      foreach ($role as $key => $value) {
        $this->$key = $value;
      }
    }

    return $this;
  }

  /**
   * Get/set the role id.
   *
   * @param null|int $rid
   * @return int|Role
   */
  public function rid($rid = NULL) {
    if ($rid === NULL) {
      // Get the rid:
      return $this->load()->rid;
    }
    else {
      $this->rid = $rid;
      return $this;
    }
  }

  /**
   * Get/set the role name.
   *
   * @param null|string $name
   * @return string|Role
   */
  public function name($name = NULL) {
    if ($name === NULL) {
      // Get the name:
      return $this->load()->name;
    }
    else {
      $this->name = $name;
      return $this;
    }
  }

  /**
   * Check if the role is loaded.
   *
   * @return bool
   */
  public function loaded() {
    return $this->loaded;
  }

  /**
   * Check if the rid is valid.
   *
   * @return bool
   */
  public function valid() {
    $this->load();
    return $this->valid;
  }

}
