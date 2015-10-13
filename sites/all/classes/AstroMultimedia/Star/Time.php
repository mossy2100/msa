<?php
namespace AstroMultimedia\Star;

/**
 * User: shaun
 * Date: 2012-09-17
 * Time: 8:09 PM
 *
 * Class to encapsulate a time of day or duration.
 */
class Time {

  /**
   * The number of seconds.
   *
   * @var int
   */
  protected $seconds = 0;

  /**
   * Constructor.
   *
   * @param int $hour
   * @param int $minute
   * @param int $second
   */
  public function __construct($hour = 0, $minute = 0, $second = 0) {
    $this->time($hour, $minute, $second);
  }

  /**
   * Convert the time to an array, with hours, minutes and seconds.
   *
   * @return array
   */
  public function toArray() {
    $seconds = $this->seconds;
    $hours = floor($seconds / DateTime::SECONDS_PER_HOUR);
    $seconds -= $hours * DateTime::SECONDS_PER_HOUR;
    $minutes = floor($seconds / DateTime::SECONDS_PER_MINUTE);
    $seconds -= $minutes * DateTime::SECONDS_PER_MINUTE;
    return array(
      'hour'   => $hours,
      'minute' => $minutes,
      'second' => $seconds,
    );
  }

  /**
   * Gets or sets the time.
   *
   * @param int $hour
   * @param int $minute
   * @param int $second
   * @return Time
   */
  public function time($hour = 0, $minute = 0, $second = 0) {
    if (func_num_args() == 0) {
      // Get the time:
      return $this;
    }
    else {
      // Set the time:
      $this->seconds = ($hour * DateTime::SECONDS_PER_HOUR) + ($minute * DateTime::SECONDS_PER_MINUTE) + $second;
      return $this;
    }
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set time parts

  /**
   * Get or set the hour.
   *
   * @param null|int $hour
   * @return int|Time
   */
  public function hour($hour = NULL) {
    $a = $this->toArray();
    if ($hour === NULL) {
      // Get the hour:
      return $a['hour'];
    }
    else {
      // Set the hour:
      return $this->time($hour, $a['minute'], $a['second']);
    }
  }

  /**
   * Get or set the minute.
   *
   * @param null|int $minute
   * @return int|Time
   */
  public function minute($minute = NULL) {
    $a = $this->toArray();
    if ($minute === NULL) {
      // Get the minute:
      return $a['minute'];
    }
    else {
      // Set the minute:
      return $this->time($a['hour'], $minute, $a['second']);
    }
  }

  /**
   * Get or set the second.
   *
   * @param null|int $second
   * @return int|Time
   */
  public function second($second = NULL) {
    $a = $this->toArray();
    if ($second === NULL) {
      // Get the second:
      return $a['second'];
    }
    else {
      // Set the second:
      return $this->time($a['hour'], $a['minute'], $second);
    }
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // The time in various units

  /**
   * Get or set the total seconds.
   *
   * @param null|int $seconds
   * @return int|Time
   */
  public function seconds($seconds = NULL) {
    if ($seconds === NULL) {
      // Get the second:
      return $this->seconds;
    }
    else {
      // Set the second:
      $this->seconds = (int) $seconds;
      return $this;
    }
  }

  /**
   * Get the time in minutes.
   *
   * @return float
   */
  public function minutes() {
    return $this->seconds / DateTime::SECONDS_PER_MINUTE;
  }

  /**
   * Get the time in hours.
   *
   * @return float
   */
  public function hours() {
    return $this->seconds / DateTime::SECONDS_PER_HOUR;
  }

  /**
   * Get the time in days.
   *
   * @return float
   */
  public function days() {
    return $this->seconds / DateTime::SECONDS_PER_DAY;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Format

  /**
   * Format a Time.
   *
   * @param string $format
   * @return string
   */
  public function format($format) {
    // Easy way is to use DateTime::format().
    $dt = new DateTime();
    $dt->time($this);
    return $dt->format($format);
  }

}
