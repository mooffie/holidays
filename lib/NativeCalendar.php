<?php
// $Id$

/*
 * @file
 * PHP classes providing calendars.
 *
 * Copyright (C) 2007 Mooffie <mooffie@typo.co.il>
 *
 * It is released to the public under the GNU General Public License (GPL).
 */

define('CAL_LANG_FOREIGN', 0);
define('CAL_LANG_NATIVE',  1);

/**
 * This is the base class of every native calendar.
 */
class NativeCalendar {

  /**
   * You may use this function to instantiate calendar objects.
   *
   * Instead of <code>$cal = new JewishCalendar</code>, do
   * <code>$cal = NativeCalendar::factory('Jewish')</code>.
   *
   * @static
   * @param string $id
   * @return object
   */
  function factory($id, $settings = NULL) {
    $filename = dirname(__FILE__) .'/'. $id .'Calendar.php';
    if (!file_exists($filename)) {
      return NULL;
    }
    require_once $filename;
    $classname = $id .'Calendar';
    $obj = new $classname;
    // We keep the ID around in case some 3rd party code may wish to use it:
    $obj->name = $id;
    if (isset($settings)) {
      $obj->settings($settings);
    }
    return $obj;
  }

  /**
   * Get installed calendars.
   *
   * @static
   * @return array
   */
  function factory_list() {
    static $list = array();
    if ($list) {
      return $list;
    }
    $dir = opendir(dirname(__FILE__));
    while (($file = readdir($dir)) !== FALSE) {
      if (preg_match('/(.*)Calendar\.php$/', $file, $m) && ($file != 'NativeCalendar.php')) {
        $list[$m[1]] = $m[1];
      }
    }
    closedir($dir);
    // TODO: sort alphabetically?
    return $list;
  }

  function NativeCalendar() {
    $this->settings = array();
    // All talk defaults to English.
    $this->settings['language'] = CAL_LANG_FOREIGN;
    // We provide for a possible localization function, t().
    // If it's not already defined by the host system (e.g. your CMS), we 
    // implement a dummy one.
    if (!function_exists('t')) {
      function t($s) {
        return $s;
      }
    }
  }

  /**
   * Get the title of this calendar.
   */
  function title() {
    die('Error: pure virtual function NativeCalendar::title() called');
  }

  /**
   * Overridable; Return TRUE if the calendar's native language is right to left.
   */
  function is_rtl() {
    return FALSE;
  }

  /**
   * Set one of more settings.
   *
   * Various calendars may have various settings. Instead of
   * defining a separate setXYZ() function for each setting, we elect for
   * a central settings() method.
   *
   * A setting which all calendars are required to support is the 'language' setting. 
   * If may either be CAL_LANG_NATIVE or CAL_LANG_FOREIGN and it determines
   * the language in which the calendar 'talks.'
   *
   * @param array $settings
   */
  function settings($settings) {
    $this->settings = array_merge($this->settings, $settings);
  }

  // @todo: do we need this?
  //function settings_get() {
  //  return $this->settings;
  //}

  // @todo: do we need this?
  function settings_keys() {
    return array_keys($this->settings);
  }

  /**
   * Get a form used to interactively edit the settings. Calendars should 
   * override this method.
   *
   * This is the only code in this library that's Drupal-dependant. We'd better
   * move it to the Drupal module itself.
   */
  function settings_form() {
    $form['language'] = array(
      '#type' => 'radios',
      '#title' => t('The language in which to print dates and holiday names'),
      '#options' => array(
        CAL_LANG_FOREIGN => t("The website's language"),
        CAL_LANG_NATIVE  => t("The calendar's native language"),
      ),
      '#default_value' => $this->settings['language'],
    );
    return $form;
  }

  /**
   * Get the holidays falling on a certain date.
   *
   * If no holidays occur on this date, the array returned is empty. Else, the 
   * array contains one element for each holiday. Usually a maximun of one 
   * holiday occurs on a date, but since some religions may have two or more 
   * events occuring on the same date, you should loop over the array.
   *
   * Each holiday is represented thus:
   *
   * <pre>
   *
   * array(
   *
   *   'native'  => '...',          // The native name of the holiday
   *
   *   'foreign' => 'Rosh HaShana', // The foreign, usually English, name of the holiday
   *
   *   'name'    => 'Rosh HaShana', // The name you should pick for printing; it's
   *                                // either of the above, depending on
   *                                // the 'language' setting.
   *
   *   'class'  => 'taanit'         // A string that may be used in an HTML 'class'
   *                                // attibute (CSS). This string usually tells us
   *                                // something about the nature of this holiday.
   *
   *   'id'     =>  'roshHaShana1'  // The ID of this holiday. Each holiday has a
   *                                // unique ID string. You may use it in your CSS.
   * );
   *
   * </pre>
   *
   * @param  date
   * @return array Array of holidays. 
   */
  function getHolidays($date) {
    die('Error: pure virtual function NativeCalendar::getHoliday() called');
  }

  /**
   * Convert a date, given in various formats, to the native format.
   *
   * All the methods of a calendar object are using this function to convert the
   * $date parameter they receive to the native format. This is why you can feed them a
   * gregorian date, or a unix timestamp, and know nothing about the native date system.
   *
   * You seldom need to call this function directly.
   *
   * @param mixed $date
   * @return internal
   */
  function convertToNative($date) {
    die('Error: pure virtual function NativeCalendar::convertToNative() called');
  }

  /*
   * Return the native representation of a number.
   *
   * Some calendars represent numbers using a counting system different than the common one. 
   * Whenever you need to print a number, use this function to get its 
   * representation.
   *
   * @param int $i
   * @return string
   */ 
  function getNumber($i) {
    die('Error: pure virtual function NativeCalendar::getNumber() called');
  }

  /**
   * Get the name of the n'th native month.
   *
   * The $year parameter is required because leap years may have different sets of
   * months.
   *
   * @param int $year
   * @param int $month
   */
  function getMonthName($year, $month) {
    die('Error: pure virtual function NativeCalendar::getMonthName() called');
  }
  
  /*
   * Return the native names of the days of the week.
   *
   * @return array An array with seven string elements.
   */
  function getDaysOfWeek() {
    die('Error: pure virtual function NativeCalendar::getDaysOfWeek() called');
  }

  /**
   * Get the native name of a date.
   *
   * Gregorian dates look like "25 Apr, 1984", but dates in other calendar 
   * systems may look quite differently.
   *
   * @param date
   * @return string
   */ 
  function getLongDate($date) {
    die('Error: pure virtual function NativeCalendar::getLongDate() called');
  }
  

  /**
   * Returns a nice HTML table showing one month in the calendar.
   *
   * This function is used for debugging. But you may use it for your 'end
   * product' if you're lazy.
   *
   * Note that the month shown is Gregorian (that is, January, February, ...),
   * therefore the parameters this function receives are the Gregorian year
   * and month.
   *
   * You should include the 'demo-core.css' style sheet in your page for a
   * pretty display.
   * 
   * @param int $year 
   * @param int $month
   */
  function printCal($year, $month)
  {
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $today_jdc     = unixtojd(time());
    $prev_dow      = 100; // anything above 7

    if ($this->settings['language'] == CAL_LANG_FOREIGN) {
      $days_of_week = array(t('Sun'), t('Mon'), t('Tue'), t('Wed'), t('Thu'), t('Fri'), t('Sat'));
    } else {
      $days_of_week = $this->getDaysOfWeek();
    }

    $output  = "<table class='calendar'>";
    $output .= "<tr>";
    foreach ($days_of_week as $day) {
      $output .= "<td class='day-header'>$day</td>";
    }
    $output .= "</tr>";

    for ($day = 1; $day <= $days_in_month; $day++)
    {
      $jdc = gregoriantojd($month, $day, $year);
      $dow = jddayofweek($jdc, 0) + 1;

      if ($dow < $prev_dow) {
        // Starting a new week, so start a new row in table.
        if ($day != 1) {
          $output .= "</tr>";
          $output .= "<tr>";
        } else {
          $output .= "<tr>";
          for ($i = 1; $i < $dow; $i++) {
            $output .= "<td class='empty-day'></td>";
          }
        }
      }

      $j_date = $this->convertToNative(array('jdc' => $jdc));
      $holidays = $this->getHolidays($j_date);
      $holiday_names = '';
      $holiday_classes = array();

      if ($holidays) {
        foreach ($holidays as $hday) {
          $holiday_classes[$hday['id']] = 1;
          $holiday_classes[$hday['class']] = 1;
          $holiday_names .= "<div class='holiday-name'>$hday[name]</div>\n";
        }
      }
      if ($jdc == $today_jdc) {
        $holiday_classes['today'] = 1;
      }
      $holiday_classes = implode(' ', array_keys($holiday_classes));

      $output .= "<td class='day $holiday_classes'>\n";
      $output .= "<span class='gregorian-number'>$day</span>\n";
      $output .= "<span class='native-number'>".$this->getNumber($j_date['mday']);
      if ($j_date['mday'] == 1)
        $output .= " <span class='month-name'>(".
              $this->getMonthName($j_date['year'], $j_date['mon']).")</span>";
      $output .= "</span>\n";
      $output .= $holiday_names;
      $output .= "</td>";

      $prev_dow = $dow;
    }
    for ($i = $dow + 1; $i <= 7; $i++) {
      $output .= "<td class='empty'></td>";
    }
    $output .= "</tr>";
    $output .= "</table>";

    return $output;
  }

}

