<?php
// $Id$

/*
 * @file
 * A PHP class implementing a Hijri calendar.
 *
 * I got the JDToHijri/HijriToJD algorithm from a user comment on PHP's manual[1] page.
 * Code on another[2] site suggested this about the author:
 *
 * "[...] programmed by a kind person who does not ask for any recognition. In his
 * own words; 'There’s no need to give me credit for anything. The coding really
 * is minimal and I’d prefer the anonymity.'"
 *
 * So I take it to be Public Domain.
 *
 * [1] http://php.net/calendar
 * [2] http://www.usayd.com/pluginshacks/hijri-date/
 */

require_once dirname(__FILE__) .'/NativeCalendar.php';

/**
 * This class represents the Hijri calendar.
 *
 * For documentation for the methods this class provides, see the source for
 * its base class: NativeCalendar.
 */
class HijriCalendar extends NativeCalendar {

  /**
   * Constructor for the calendar object.
   *
   * Please instantiate this class via <code>NativeCalendar::factory('Hijri')</code>.
   */
  function HijriCalendar() {
    parent::NativeCalendar();
  }

  function title() {
    if ($this->settings['language'] == CAL_LANG_NATIVE) {
      return 'التقويم الهجري';
    } else {
      return t('Hijri calendar');
    }
  }

  function is_rtl() {
    return TRUE;
  }

  // Implements NativeCalendar::native_language()
  function native_language() {
    return array('ar' => t('Arabic'));
  }

  // Implements NativeCalendar::getNumber()
  function getNumber($i)
  {
    if ($this->settings['language'] == CAL_LANG_NATIVE) {
      return strtr("$i", array('1'=>'١','2'=>'٢','3'=>'٣','4'=>'٤','5'=>'٥','6'=>'٦','7'=>'٧','8'=>'٨','9'=>'٩','0'=>'٠'));
    } else {
      return $i;
    }
  }

  // Implements NativeCalendar::convertToNative()
  //
  // For your convenience, this function accepts various input types:
  //
  // Inputs on which conversion to local date is performed:
  //
  //   A unix timestamp:
  //     time()
  //   PHP 5's DateTime object:
  //     date_create('2007-12-30 12:45:10', timezone_open('America/New_York'));
  //
  // Inputs which are considered to be local already:
  //
  //   The result of getdate():
  //     array('year' => ..., 'mon' => ..., 'mday' => ...)
  //   An ISO date string:
  //     '2006-12-08'
  //     '2006-12-08T14:57:12'
  //
  // Other inputs:
  //
  //   A Julian Date Count:
  //     array('jdc' => ...)
  function convertToNative($date) {
    if (is_array($date)) {
      if (isset($date['calendar']) && $date['calendar'] == 'HIJRI') {
        return $date; // it's already hijri
      }
    }
    else {
      $date = $this->_canonizeInputDate($date);
    }

    // At this point, $date is an array, unless it's invalid.

    if (is_array($date)) {
      if (!empty($date['jdc'])) {
        $jdc = $date['jdc'];
      } else {
        $jdc = gregoriantojd($date['mon'], $date['mday'], $date['year']);
      }

      $h = $this->JDToHijri($jdc);
      return array(
        'year'     => $h[2],
        'mon'      => $h[0],
        'mday'     => $h[1],
        'calendar' => 'HIJRI',
      ) + $date; // Merge in the optional hours:minutes:seconds fields.
    }
  }

  // Implements NativeCalendar::getLongDate()
  //
  // Formats a hijri date as a human-readable string.
  function getLongDate($date)
  {
    $h_date = $this->convertToNative($date);
    return sprintf('%s %s %s', 
              $this->getNumber($h_date['mday']),
              $this->getMonthName($h_date['year'], $h_date['mon']),
              $this->getNumber($h_date['year']));
  }

  // Implements NativeCalendar::getMonthName()
  function getMonthName($h_year, $h_month)
  {
    static $arabic = array(
      1  => 'محرّم',
      2  => 'صفر',
      3  => 'ربيع الأول',
      4  => 'ربيع الآخر',
      5  => 'جمادى الأول',
      6  => 'جمادى الآخر',
      7  => 'رجب',
      8  => 'شعبان',
      9  => 'رمضان',
      10 => 'شوّال',
      11 => 'ذو القعدة',
      12 => 'ذو الحجة'
    );

    static $foreign;

    if (!isset($foreign)) {
      $foreign = array(
        1  => t('Muharram'),
        2  => t('Safar'),
        3  => t('Rabi\' al-awwal'),
        4  => t('Rabi\' al-thani'),
        5  => t('Jumada al-awwal'),
        6  => t('Jumada al-thani'),
        7  => t('Rajab'),
        8  => t('Sha\'aban'),
        9  => t('Ramadan'),
        10 => t('Shawwal'),
        11 => t('Dhu al-Qi\'dah'),
        12 => t('Dhu al-Hijjah')
      );
    }

    return ($this->settings['language'] == CAL_LANG_NATIVE) ? $arabic[$h_month] : $foreign[$h_month];
  }

  // Implements NativeCalendar::getDaysOfWeek()
  function getDaysOfWeek() {
    return array('الأحد', 'الإثنين', 'الثُّلَاثاء', 'الأَرْبِعاء', 'الخَمِيس', 'الجُمُعَة', 'السَّبْت');
  }
  
  // Implements NativeCalendar::getHolidays()
  function getHolidays($date) {
    return array();
  }

/*function greg2jd($d, $m, $y) {
	$jd = (1461 * ($y + 4800 + ($m - 14) / 12)) / 4 +
	(367 * ($m - 2 - 12 * (($m - 14) / 12))) / 12 -
	(3 * (($y + 4900 + ($m - 14) / 12) / 100 )) / 4 +
	$d - 32075;
	return $jd;
}*/

  // Julian Day Count To Hijri
  function JDToHijri($jd)
  {
      $jd = $jd - 1948440 + 10632;
      $n  = (int)(($jd - 1) / 10631);
      $jd = $jd - 10631 * $n + 354;
      $j  = ((int)((10985 - $jd) / 5316)) *
          ((int)(50 * $jd / 17719)) +
          ((int)($jd / 5670)) *
          ((int)(43 * $jd / 15238));
      $jd = $jd - ((int)((30 - $j) / 15)) *
          ((int)((17719 * $j) / 50)) -
          ((int)($j / 16)) *
          ((int)((15238 * $j) / 43)) + 29;
      $m  = (int)(24 * $jd / 709);
      $d  = $jd - (int)(709 * $m / 24);
      $y  = 30*$n + $j - 30;

      return array($m, $d, $y);
  }

  // Hijri To Julian Day Count
  function HijriToJD($m, $d, $y)
  {
      return (int)((11 * $y + 3) / 30) +
          354 * $y + 30 * $m -
          (int)(($m - 1) / 2) + $d + 1948440 - 385;
  }

}

