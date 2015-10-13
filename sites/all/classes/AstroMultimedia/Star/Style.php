<?php
namespace AstroMultimedia\Star;

/**
 * Class to encapsulate CSS style settings.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2012-10-14 18:58
 */
class Style {

  /**
   * Array of CSS properties.
   *
   * @var array
   */
  protected $properties;

  /**
   * Constructor.
   *
   * @param array $properties
   */
  public function __construct(array $properties = NULL) {
    $this->properties = $properties ?: array();
  }

  /**
   * Convert an array of style settings into an inline style attribute.
   *
   * @param array $styles
   * @return string
   */
  function inline() {
    $pairs = array();
    foreach ($this->properties as $property => $value) {
      $pairs[] = "$property: $value;";
    }
    return implode(' ', $pairs);
  }

  /**
   * Get/set the value of a property.
   *
   * @param $property
   * @param $value
   * @return mixed
   */
  public function property($property, $value = NULL) {
    if ($value === NULL) {
      return $this->properties[$property];
    }
    else {
      $this->properties[$property] = $value;
    }
  }

  /**
   * Get the array of properties.
   *
   * @return array
   */
  public function properties() {
    return $this->properties;
  }

  /**
   * Merge some new properties with existing.
   *
   * @param mixed $properties
   *   Can be an array or another Style object.
   */
  public function merge($properties) {
    if ($properties instanceof Style) {
      $properties = $properties->properties();
    }
    $this->properties = array_merge($this->properties, $properties);
    return $this;
  }

  /**
   * Add a cross-browser border-radius style.
   *
   * @param $radius
   * @return array
   */
  public function borderRadius($radius) {
    $this->merge(array(
      '-moz-border-radius' => $radius,
      '-webkit-border-radius' => $radius,
      '-o-border-radius' => $radius,
      '-ms-border-radius' => $radius,
      '-khtml-border-radius' => $radius,
      'border-radius' => $radius,
    ));
  }

}
