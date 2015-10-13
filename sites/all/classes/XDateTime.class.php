<?php

/**
 * This class is designed to be an improvement over PHP's built-in class DateTime.
 */
class XDateTime extends DateTime {

  //////////////////////////////////////////////////////
  // Static methods

  /**
   * The current datetime as an XDateTime object.
   * 
   * @return XDateTime
   */
  public static function now() {
    return new XDateTime();
  }
  
  /**
   * Today's date as an XDateTime object.
   * 
   * @return XDateTime
   */
  public static function today() {
    $now = self::now();
    return $now->getDate();
  }

  //////////////////////////////////////////////////////
  // Getters
  
  /**
   * Gets the date part of the datetime as an XDateTime object.
   * 
   * @return XDateTime
   */
  public function getDate() {
    return new XDateTime($this->format('Y-m-d'));
  }

  /**
   * Gets the time part of the datetime as an DateInterval object.
   * 
   * @return DateInterval
   */
  public function getTime() {
    return new DateInterval('PT' . $this->format('His'));
  }

  //////////////////////////////////////////////////////
  // Getters for standard datetime parts

  /**
   * Get the year as an integer.
   * 
   * @return int
   */
  public function getYear() {
    return (int) $this->format('Y');
  }
  
  /**
   * Get the month as an integer.
   * 
   * @return int
   */
  public function getMonth() {
    return (int) $this->format('n');
  }
  
  /**
   * Get the day of the month as an integer (1.. 31).
   * 
   * @return int
   */
  public function getDay() {
    return (int) $this->format('j');
  }

  /**
   * Get the hour as an integer.
   *
   * @return int
   */
  public function getHour() {
    return (int) $this->format('G');
  }
  
  /**
   * Get the minute as an integer.
   *
   * @return int
   */
  public function getMinute() {
    return (int) $this->format('i');
  }
  
  /**
   * Get the second as an integer.
   *
   * @return int
   */
  public function getSecond() {
    return (int) $this->format('s');
  }
  
  //////////////////////////////////////////////////////
  // Additional getters

  /**
   * Get the week of the year as an integer (1.. 52).
   *
   * @return int
   */
  public function getWeek() {
    return (int) $this->format('W');
  }

  /**
   * Get the day of the year as an integer (1..366).
   *
   * @return int
   */
  public function getDayOfYear() {
    return ((int) $this->format('z')) + 1;
  }

  /**
   * Get the day of the week as an integer (1..7).
   * 1 = Monday .. 7 = Sunday
   *
   * @return int
   */
  public function getDayOfWeek() {
    return (int) $this->format('N');
  }

  //////////////////////////////////////////////////////
  // Setters

  /**
   * Set the year.
   * 
   * @param int $year
   * @return XDateTime
   */
  public function setYear($year) {
    return $this->setDate($year, $this->getMonth(), $this->getDay());
  }
  
  /**
   * Set the month.
   * 
   * @param int $month
   * @return XDateTime
   */
  public function setMonth($month) {
    return $this->setDate($this->getYear(), $month, $this->getDay());
  }
  
  /**
   * Set the day of the month.
   * 
   * @param int $day
   * @return XDateTime
   */
  public function setDay($day) {
    return $this->setDate($this->getYear(), $this->getMonth(), $day);
  }

  /**
   * Set the hour.
   * 
   * @param int $hour
   * @return XDateTime
   */
  public function setHour($hour) {
    return $this->setTime($hour, $this->getMinute(), $this->getSecond());
  }
  
  /**
   * Set the minute.
   * 
   * @param int $minute
   * @return XDateTime
   */
  public function setMinute($minute) {
    return $this->setTime($this->getHour(), $minute, $this->getSecond());
  }
  
  /**
   * Set the second.
   * 
   * @param int $second
   * @return XDateTime
   */
  public function setSecond($second) {
    return $this->setTime($this->getHour(), $this->getMinute(), $second);
  }

  //////////////////////////////////////////////////////
  // Add periods

  /**
   * Add years.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function addYears($n) {
    return $this->setYear($this->getYear() + $n);
  }
  
  /**
   * Add months.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function addMonths($n) {
    return $this->setMonth($this->getMonth() + $n);
  }
  
  /**
   * Add weeks.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function addWeeks($n) {
    return $this->addDays($n * 7);
  }
  
  /**
   * Add days.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function addDays($n) {
    return $this->setDay($this->getDay() + $n);
  }
  
  /**
   * Add hours.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function addHours($n) {
    return $this->setHour($this->getHour() + $n);
  }

  /**
   * Add minutes.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function addMinutes($n) {
    return $this->setMinute($this->getMinute() + $n);
  }
  
  /**
   * Add seconds.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function addSeconds($n) {
    return $this->setSecond($this->getSecond() + $n);
  }
  
  //////////////////////////////////////////////////////
  // Subtract periods

  /**
   * Subtract years.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function subYears($n) {
    return $this->addYears(-$n);
  }
  
  /**
   * Subtract months.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function subMonths($n) {
    return $this->addMonths(-$n);
  }
  
  /**
   * Subtract weeks.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function subWeeks($n) {
    return $this->addWeeks(-$n);
  }

  /**
   * Subtract days.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function subDays($n) {
    return $this->addDays(-$n);
  }
  
  /**
   * Subtract hours.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function subHours($n) {
    return $this->addHours(-$n);
  }
  
  /**
   * Subtract minutes.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function subMinutes($n) {
    return $this->addMinutes(-$n);
  }
  
  /**
   * Subtract seconds.
   * 
   * @param int $n
   * @return XDateTime
   */
  public function subSeconds($n) {
    return $this->addSeconds(-$n);
  }

}
