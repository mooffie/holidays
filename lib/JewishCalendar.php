<?php
// $Id$

/*
 * @file
 * A PHP class implementing the Jewish calendar.
 *
 * Copyright (C) Mooffie <mooffie@gmail.com>
 *
 * It is released to the public under the GNU General Public License (GPL).
 */

require_once dirname(__FILE__) .'/NativeCalendar.php';

define('TISHREI', 1);
define('HESHVAN', 2);
define('KISLEV',  3);
define('TEVET',   4);
define('SHEVAT',  5);
define('ADAR',    6);
define('ADAR_A',  6);
define('ADAR_B',  7);
define('NISAN',   8);
define('IYAR',    9);
define('SIVAN',  10);
define('TAMUZ',  11);
define('AV',     12);
define('ELUL',   13);

/**
 * This class represents the Jewish calendar.
 *
 * For documentation for the methods this class provides, see the source for
 * its base calss: NativeCalendar.
 */
class JewishCalendar extends NativeCalendar {

  /**
   * Constructor for the calendar object.
   *
   * Please instantiate this class via <code>NativeCalendar::factory('Jewish')</code>.
   */
  function JewishCalendar() {
    parent::NativeCalendar();
    // Initialize defaults:
    $this->settings += array(
      'diaspora' => FALSE,
      'eves' => TRUE,
      'isru' => FALSE,
      'sefirat_omer' => FALSE,
    );
  }

  // Implements NativeCalendar::title()
  function title() {
    if ($this->settings['language'] == CAL_LANG_NATIVE) {
      return 'לוח שנה עברי';
    } else {
      return t('Jewish calendar');
    }
  }

  // Implements NativeCalendar::settings_form()
  function settings_form() {
    $form = parent::settings_form();
    $form['diaspora'] = array(
      '#type' => 'radios',
      '#title' => t('Method'),
      '#description' => t('The method used to calculate the holidays. Some holidays are observed two days when outside of Israel.'),
      '#options' => array(
        1 => t('Diaspora'),
        0 => t('Land of Israel'),
      ),
      '#default_value' => (int)$this->settings['diaspora'],
    );
    $form['eves'] = array(
      '#type'  => 'checkbox',
      '#title' => t('Show holiday eves'),
      '#default_value' => $this->settings['eves'],
    );
    $form['isru'] = array(
      '#type'  => 'checkbox',
      '#title' => t('Show Isru hags'),
      '#default_value' => $this->settings['isru'],
    );
    $form['sefirat_omer'] = array(
      '#type'  => 'checkbox',
      '#title' => t('Show Sefirat HaOmer'),
      '#default_value' => $this->settings['sefirat_omer'],
    );
    return $form;
  }

  // Implements NativeCalendar::is_rtl()
  function is_rtl() {
    return TRUE;
  }

  // Implements NativeCalendar::native_language()
  function native_language() {
    return array('he' => t('Hebrew'));
  }

  // Implements NativeCalendar::getNumber()
  function getNumber($i)
  {
    if ($this->settings['language'] == CAL_LANG_NATIVE) {
      return $this->int2gim($i);
    }
    return $i;
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
      if (isset($date['calendar']) && $date['calendar'] == CAL_JEWISH) {
        return $date; // it's already jewish
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

      $s = jdtojewish($jdc);
      preg_match(',(\d+)/(\d+)/(\d+),', $s, $m);
      return array(
        'year'     => $m[3],
        'mon'      => $m[1],
        'mday'     => $m[2],
        'calendar' => CAL_JEWISH,
      ) + $date; // Merge in the optional hours:minutes:seconds fields.
    }
  }

  // Implements NativeCalendar::getLongDate()
  //
  // Formats a jewish date as a human-readable string.
  function getLongDate($date)
  {    
    $j_date = $this->convertToNative($date);
    if ($this->settings['language'] == CAL_LANG_NATIVE) {
      return sprintf('%s %s %s', 
                $this->int2gim($j_date['mday'], TRUE),
                $this->getMonthName($j_date['year'], $j_date['mon']),
                $this->int2gim($j_date['year'], TRUE));
    }
    else {
      return sprintf('%d %s, %d', 
                $j_date['mday'],
                $this->getMonthName($j_date['year'], $j_date['mon']),
                $j_date['year']);
    }
  }

  // Implements NativeCalendar::getMonthName()
  function getMonthName($j_year, $j_month)
  {
    static $hebrew = array(
      TISHREI => 'תשרי',
      HESHVAN => 'חשוון',
      KISLEV  => 'כסלו',
      TEVET   => 'טבת',
      SHEVAT  => 'שבט',
      ADAR    => 'אדר',
      ADAR_B  => 'אדר-ב\'',
      NISAN   => 'ניסן',
      IYAR    => 'אייר',
      SIVAN   => 'סיוון',
      TAMUZ   => 'תמוז',
      AV      => 'אב',
      ELUL    => 'אלול'
    );

    static $foreign;

    if (!isset($foreign)) {
      $foreign = array(
        TISHREI => t('Tishrei'),
        HESHVAN => t('Heshvan'),
        KISLEV  => t('Kislev'),
        TEVET   => t('Tevet'),
        SHEVAT  => t('Shevat'),
        ADAR    => t('Adar'),
        ADAR_B  => t('Adar II'),
        NISAN   => t('Nisan'),
        IYAR    => t('Iyar'),
        SIVAN   => t('Sivan'),
        TAMUZ   => t('Tamuz'),
        AV      => t('Av'),
        ELUL    => t('Elul')
      );
    }

    if ($j_month == ADAR && $this->isLeapYear($j_year)) {
      return $this->settings['language'] == CAL_LANG_NATIVE ? 'אדר-א\'' : t('Adar I');
    }
    return $this->settings['language'] == CAL_LANG_NATIVE ? $hebrew[$j_month] : $foreign[$j_month];
  }

  // Implements NativeCalendar::getDaysOfWeek()
  function getDaysOfWeek() {
    return array('ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת');
  }
  
  // Implements NativeCalendar::getHolidays()
  function getHolidays($date) {
    static $cache;
    $holiday_details = $this->_getHolidayDetails();

    $j_date = $this->convertToNative($date);
    // shorthand, to save typing:
    $j_year  = $j_date['year'];
    $j_month = $j_date['mon'];
    $j_day   = $j_date['mday'];

    if (!isset($cache)) {
      $cache = array();
    }
    if (!isset($cache[$j_year][$j_month])) {
      $cache[$j_year][$j_month] = $this->_buildHolidays($j_year, $j_month);
    }

    if (isset($cache[$j_year][$j_month][$j_day])) {
      $holidays = array();
      foreach ($cache[$j_year][$j_month][$j_day] as $id) {
        $holidays[$id] = $holiday_details[$id];
        $holidays[$id]['id'] = $id;
        if ($this->settings['language'] == CAL_LANG_NATIVE) {
          $holidays[$id]['name'] = $holidays[$id]['native'];
        } else {
          $holidays[$id]['name'] = $holidays[$id]['foreign'];
        }
      }
      return $holidays;
    } else {
      return array();
    }
  }
  
  // _buildHolidays() is the 'brain' of this object. It builds a table of holidays
  // for either a complete year or a month.
  function _buildHolidays($j_year, $j_month = 0) {
    $list = array();
    $d = new _JewishDateObj();

    // shorthands
    $sefirat_omer = $this->settings['sefirat_omer'];
    $diaspora     = $this->settings['diaspora'];
    $isru         = $this->settings['isru'];
    $eves         = $this->settings['eves'];

    //
    // Holidays of Tishrei
    //

    if ($j_month == TISHREI || !$j_month) {

      $list[TISHREI][1][] = 'roshHaShana1';
      $list[TISHREI][2][] = 'roshHaShana2';

      $d->set($j_year, TISHREI, 3);
      if ($d->dow() == 6) {
        $d->increment();
      }
      $list[TISHREI][$d->day][] = 'tsomGedalya';

      if ($eves) {
        $list[TISHREI][9][] = 'yomKippurErevKhag';
      }
      $list[TISHREI][10][] = 'yomKippur';

      if ($eves) {
        $list[TISHREI][14][] = 'sukkotErevKhag';
      }
      $list[TISHREI][15][] = 'sukkot';
      if ($diaspora) {
        $list[TISHREI][16][] = 'sukkot2';
      } else {
        $list[TISHREI][16][] = 'sukkotKholHaMoed';
      }
      
      $list[TISHREI][17][] = 'sukkotKholHaMoed';
      $list[TISHREI][18][] = 'sukkotKholHaMoed';
      $list[TISHREI][19][] = 'sukkotKholHaMoed';
      $list[TISHREI][20][] = 'sukkotKholHaMoed';

      $list[TISHREI][21][] = 'hoshanaRabba';
      $list[TISHREI][22][] = 'sheminiAtseret';

      if ($diaspora) {
        $list[TISHREI][23][] = 'simkhatTora';
        if ($isru) {
          $list[TISHREI][24][] = 'sukkotIsruKhag';
        }
      } else {
        $list[TISHREI][22][] = 'simkhatTora';
        if ($isru) {
          $list[TISHREI][23][] = 'sukkotIsruKhag';
        }
      }

    }

    //
    // Holidays of Kislev or Tevet
    //

    if ($j_month == KISLEV || $j_month == TEVET || !$j_month) {

      $d->set($j_year, KISLEV, 25);
      for ($i = 1; $i <= 8; $i++) {
        $list[$d->month][$d->day][] = 'khanukka'.$i;
        $d->increment();
      }

    }

    //
    // Holidays of Tevet
    //
    
    if ($j_month == TEVET || !$j_month) {

      $d->set($j_year, TEVET, 10);
      if ($d->dow() == 6) {
        $d->increment();
      }
      $list[TEVET][$d->day][] = 'tsomTevet';

    }

    //
    // Holidays of Shevat
    //

    if ($j_month == SHEVAT || !$j_month) {

      $list[SHEVAT][15][] = 'tuBiShevat';

    }

    //
    // Holidays of Adar
    //

    if ($j_month == ADAR || $j_month == ADAR_A || $j_month == ADAR_B || !$j_month) {

      if ($this->isLeapYear($j_year)) {
        $adar = ADAR_B;
      } else {
        $adar = ADAR;
      }

      $list[$adar][14][] = 'purim';
      $list[$adar][15][] = 'shushanPurim';
      $d->set($j_year, $adar, 13);
      if ($d->dow() == 6) { // if falls on saturday...
        // ...then decrement twice, to thursday.
        // TODO: what if it falls on friday in the first place? is it possible?
        $d->decrement();
        $d->decrement();
      }
      $list[$adar][$d->day][] = 'taanitEsther';

    }

    //
    // Holidays of Nisan
    //

    if ($j_month == NISAN || !$j_month) {

      if ($eves) {
        $list[NISAN][14][] = 'pesakhErevKhag';
      }
      $list[NISAN][15][] = 'pesakh1';
      if ($diaspora) {
        $list[NISAN][16][] = 'pesakh2';
      } else {
        $list[NISAN][16][] = 'pesakhKholHaMoed';
      }

      $list[NISAN][17][] = 'pesakhKholHaMoed';
      $list[NISAN][18][] = 'pesakhKholHaMoed';
      $list[NISAN][19][] = 'pesakhKholHaMoed';
      $list[NISAN][20][] = 'pesakhKholHaMoed';

      $list[NISAN][21][] = 'pesakh7';
      if ($diaspora) {
        $list[NISAN][22][] = 'pesakh8';
        if ($isru) {
          $list[NISAN][23][] = 'pesakhIsruKhag';
        }
      } else {
        if ($isru) {
          $list[NISAN][22][] = 'pesakhIsruKhag';
        }
      }

      // Yom HaShoaa:
 
      $d->set($j_year, NISAN, 27);
      // Rule #1: fri,sat -> thu
      while ($d->dow() == 5 || $d->dow() == 6) {
        $d->decrement();
      }
      // Rule #2: sun -> mon
      while ($d->dow() == 0) {
        $d->increment();
      }
      $list[$d->month][$d->day][] = 'yomHaShoa';
      
      if ($sefirat_omer) {
        for ($i = $diaspora ? 23 : 22; $i <= 30; $i++) {
          $list[NISAN][$i][] = 'omer';
        }
      }     

    }

    //
    // Holidays of Iyar
    //

    if ($j_month == IYAR || !$j_month) {

      $d->set($j_year, IYAR, 4);
      // Rule #1: thu,fri -> wed
      while ($d->dow() == 4 || $d->dow() == 5) {
        $d->decrement();
      }
      // Rule #2: sun -> mon
      while ($d->dow() == 0) {
        $d->increment();
      }
      $list[$d->month][$d->day][] = 'yomHaZikaron';

      $d->set($j_year, IYAR, 5);
      // Rule #1: fri,sat -> thu
      while ($d->dow() == 5 || $d->dow() == 6) {
        $d->decrement();
      }
      // Reul #2: mon -> tue
      while ($d->dow() == 1) {
        $d->increment();
      }
      $list[$d->month][$d->day][] = 'yomHaAzmaut';

      $list[IYAR][18][] = 'lagBaOmer';

      $list[IYAR][28][] = 'yomYerishalayim';
      
      if ($sefirat_omer) {
        for ($i = 1; $i <= 29; $i++) {
          $list[IYAR][$i][] = 'omer';
        }
      }     
    }

    //
    // Holidays of Sivan
    //

    if ($j_month == SIVAN || !$j_month) {

      if ($eves) {
        $list[SIVAN][5][] = 'shavuotErevKhag';
      }
      $list[SIVAN][6][] = 'shavuot';
      if ($diaspora) {
        $list[SIVAN][7][] = 'shavuot2';
        if ($isru) {
          $list[SIVAN][8][] = 'shavuotIsruKhag';
        }
      } else {
        if ($isru) {
          $list[SIVAN][7][] = 'shavuotIsruKhag';
        }
      }
      
      if ($sefirat_omer) {
        for ($i = 1; $i < 6; $i++) {
          $list[SIVAN][$i][] = 'omer';
        }
      }     

    }
    
    //
    // Holidays of Tamuz
    //

    if ($j_month == TAMUZ || !$j_month) {
    
      $d->set($j_year, IYAR, 17);
      if ($d->dow() == 6) {
        $d->increment();
      }
      $list[TAMUZ][$d->day][] = 'tsomTamuz';

    }

    //
    // Holidays of Av
    //

    if ($j_month == AV || !$j_month) {
      
      $d->set($j_year, AV, 9);
      if ($d->dow() == 6) {
        $d->increment();
      }
      $list[AV][$d->day][] = 'tishaBeAv';

    }
    
    //
    // Holidays of Elul
    //

    if ($j_month == ELUL || !$j_month) {

      if ($eves) {
        $list[ELUL][29][] = 'roshHaShanaErevKhag';
      }

    }

    if ($j_month) {
      if (isset($list[$j_month])) {
        return $list[$j_month];
      } else {
        return array();
      }
    } else {    
      return $list;
    }
  }

  function _getHolidayDetails() {
    static $details;
    if (!isset($details)) {
      $details = array(

      // The English spelling for the holidays I took from "Jewish Calendar for Linux" by Refoyl Finkl.

      // Tishrei
      'roshHaShanaErevKhag' =>array('native' => 'ערב ראש השנה',     'foreign' => t('Erev Rosh HaShana'),  'class' => 'khol'),
      'roshHaShana1' =>       array('native' => 'א\' ראש השנה',     'foreign' => t('Rosh HaShana I'),     'class' => 'spec'),
      'roshHaShana2' =>       array('native' => 'ב\' ראש השנה',     'foreign' => t('Rosh HaShana II'),    'class' => 'spec'),
      'tsomGedalya' =>        array('native' => 'צום גדליה',        'foreign' => t('Tsom Gedalya'),       'class' => 'taanit'),
      'yomKippurErevKhag' =>  array('native' => 'ערב יום הכיפורים', 'foreign' => t('Erev Yom Kippur'),    'class' => 'khol'),
      'yomKippur' =>          array('native' => 'יום הכיפורים',     'foreign' => t('Yom Kippur'),         'class' => 'spec'),
      'sukkotErevKhag' =>     array('native' => 'ערב סוכות',        'foreign' => t('Erev Sukkot'),        'class' => 'khol'),
      'sukkot' =>             array('native' => 'סוכות',            'foreign' => t('Sukkot'),             'class' => 'shabat'),
      'sukkot2' =>            array('native' => 'סוכות ב\' (גולה)', 'foreign' => t('Sukkot II (Diaspora)'), 'class' => 'shabat'),
      'sukkotKholHaMoed' =>   array('native' => 'חול המועד סוכות',  'foreign' => t('Khol HaMoed Sukkot'),   'class' => 'khol'),
      'hoshanaRabba' =>       array('native' => 'הושענא רבה',       'foreign' => t('Hoshana Rabba'),      'class' => 'khol'),
      'sheminiAtseret' =>     array('native' => 'שמיני עצרת',       'foreign' => t('Shemini Atseret'),    'class' => 'shabat'),
      'simkhatTora' =>        array('native' => 'שמחת תורה',        'foreign' => t('Simkhat Tora'),       'class' => 'shabat'),
      'sukkotIsruKhag' =>     array('native' => 'אסרו חג',          'foreign' => t('Isru Khag Sukkot'),   'class' => 'khol'),

      // Kislev / Tevet
      'khanukka1' =>          array('native' => 'א\' חנוכה',        'foreign' => t('Khanukka I'),         'class' => 'khol'),
      'khanukka2' =>          array('native' => 'ב\' חנוכה',        'foreign' => t('Khanukka II'),        'class' => 'khol'),
      'khanukka3' =>          array('native' => 'ג\' חנוכה',        'foreign' => t('Khanukka III'),       'class' => 'khol'),
      'khanukka4' =>          array('native' => 'ד\' חנוכה',        'foreign' => t('Khanukka IV'),        'class' => 'khol'),
      'khanukka5' =>          array('native' => 'ה\' חנוכה',        'foreign' => t('Khanukka V'),         'class' => 'khol'),
      'khanukka6' =>          array('native' => 'ו\' חנוכה',        'foreign' => t('Khanukka VI'),        'class' => 'khol'),
      'khanukka7' =>          array('native' => 'ז\' חנוכה',        'foreign' => t('Khanukka VII'),       'class' => 'khol'),
      'khanukka8' =>          array('native' => 'ח\' חנוכה',        'foreign' => t('Khanukka VIII'),      'class' => 'khol'),

      // Tevet
      'tsomTevet' =>          array('native' => 'צום טבת',          'foreign' => t('Tsom Tevet'),         'class' => 'taanit'),

      // Shevat
      'tuBiShevat' =>         array('native' => 'ט\'ו בשבט',        'foreign' => t('Tu BiShevat'),        'class' => 'khol'),

      // Adar
      'taanitEsther' =>       array('native' => 'תענית אסתר',       'foreign' => t('Taanit Esther'),      'class' => 'taanit'),
      'purim' =>              array('native' => 'פורים',            'foreign' => t('Purim'),              'class' => 'khol'),
      'shushanPurim' =>       array('native' => 'שושן פורים',       'foreign' => t('Shushan Purim'),      'class' => 'khol'),

      // Nisan
      'pesakhErevKhag' =>     array('native' => 'ערב פסח',          'foreign' => t('Erev Pesakh'),        'class' => 'khol'),
      'pesakh1' =>            array('native' => 'פסח',              'foreign' => t('Pesakh'),             'class' => 'shabat'),
      'pesakh2' =>            array('native' => 'שני של פסח (גולה)', 'foreign' =>  t('Pesakh II (Diaspora)'), 'class' => 'shabat'),
      'pesakhKholHaMoed' =>   array('native' => 'חול המועד פסח',    'foreign' => t('Khol HaMoed Pesakh'), 'class' => 'khol'), 
      'pesakh7' =>            array('native' => 'שביעי של פסח',     'foreign' =>  t('Pesakh VII'),        'class' => 'shabat'),
      'pesakh8' =>            array('native' => 'שמיני של פסח (גולה)', 'foreign' => t('Pesakh VIII (Diaspora)'), 'class' => 'shabat'),
      'pesakhIsruKhag' =>     array('native' => 'אסרו חג',          'foreign' => t('Isru Khag Pesakh'),   'class' => 'khol'),
      'omer' =>               array('native' => 'ספירת העומר',      'foreign' => t('Sefirat HaOmer'),     'class' => 'omer'),
      'yomHaShoa' =>          array('native' => 'יום השואה',        'foreign' => t('Yom HaShoa'),         'class' => 'taanit'),

      // Iyar
      'yomHaZikaron' =>       array('native' => 'יום הזכרון',       'foreign' => t('Yom HaZikaron'),      'class' => 'taanit'),
      'yomHaAzmaut' =>        array('native' => 'יום העצמאות',      'foreign' => t('Yom HaAtsmaut'),      'class' => 'khol'),
      'lagBaOmer' =>          array('native' => 'ל"ג לעומר',        'foreign' => t('Lag BaOmer'),         'class' => 'omer khol'),
      'yomYerishalayim' =>    array('native' => 'יום ירושלים',      'foreign' => t('Yom Yerushalayim'),   'class' => 'khol'),

      // Sivan
      'shavuotErevKhag' =>    array('native' => 'ערב שבועות',       'foreign' => t('Erev Shavuot'),       'class' => 'khol'),
      'shavuot'  =>           array('native' => 'שבועות',           'foreign' => t('Shavuot'),            'class' => 'shabat'),
      'shavuot2'  =>          array('native' => 'שבועות ב\' (גולה)', 'foreign' => t('Shavuot II (Diaspora)'), 'class' => 'shabat'),
      'shavuotIsruKhag' =>    array('native' => 'אסרו חג',          'foreign' => t('Isru Khag Shavuot'),  'class' => 'khol'),

      // Tamuz
      'tsomTamuz' =>          array('native' => 'צום תמוז',         'foreign' => t('Tsom Tamuz'),         'class' => 'taanit'),

      // Av
      'tishaBeAv' =>          array('native' => 'תשעה באב',         'foreign' => t('Tisha BeAv'),         'class' => 'taanit'),
      );
    }
    return $details;
  }

  // isLeapYear() returns TRUE if there are two Adar months in the
  // given year (aka "Shana Me'uberet").
  function isLeapYear($j_year)
  {
    switch ($j_year % 19) {
      case 0: case 3: case 6: case 8: case 11: case 14: case 17:
        return TRUE;
      default:
        return FALSE;
    }
  }

  // int2gim() returns the gimatria representation of a number.
  //
  // This function was translated into PHP almost verbatim from the C source
  // code of Hspell: http://www.ivrix.org.il/projects/spell-checker/
  // Copyright (C) Nadav Har'El and Dan Kenigsberg
  function int2gim($n, $add_geresh = FALSE)
  {
    $utf8 = (strlen('א') == 2); // is this file UTF-8 encoded?
    $digits = array(
      array('א','ב','ג','ד','ה','ו','ז','ח','ט'),       // ones
      array('י','כ','ל','מ','נ','ס','ע','פ','צ'),       // tens
      array('ק','ר','ש','ת','קת','רת','שת','תת','קתת')  // hundreds
    );
    $special = array('וט','זט');

    $b = '';
    $i = 0;
    while ($n > 0) {
      if ($i == 3) {
        $i = 0;
        $b .= "'";
      }
      if (!$i && ($n % 100 == 15 || $n % 100 == 16)) {
        $b .= $special[$n % 100 - 15];
        $n = (int)($n / 100);
        $i = 2;
      } else {
        if ($n % 10)
          $b .= $digits[$i][$n % 10 - 1];
        $n = (int)($n / 10);
        $i++;
      }
    }

    // Reverse the string, locale independent.
    if ($utf8) {
      $b = join('', array_reverse(preg_split('/(.)/u', $b, -1, PREG_SPLIT_DELIM_CAPTURE)));
    } else {
      $b = strrev($b);
    }

    if ($add_geresh) {
      $character_size = ($utf8 ? 2 : 1);
      if (strlen($b) == $character_size) { // a single letter?
        // Yep, append a geresh.
        $b .= "'";
      }
      else {
        // No, it's a longer number. Insert gershayim.
        $b = preg_replace($utf8 ? '/(.)(.)$/u' : '/(.)(.)$/', '\\1"\\2', $b);
      }
    }
    return $b;
  }

}

/*
 * This class is an alternative representation of a Jewish date and is used only
 * inside the JewishCalendar::_buildHolidays() function. It makes incrementing and
 * decrementing a date easier.
 *
 * You should not need to use this class yourself.
 */
class _JewishDateObj {
  
  function set($j_year, $j_month, $j_day) {
    $this->year = $j_year;
    $this->month = $j_month;
    $this->day = $j_day;
    $this->jdc = 0;
  }
  
  function setByJdc($jdc) {
    $s = jdtojewish($jdc);
    preg_match(',(\d+)/(\d+)/(\d+),', $s, $m);
    $this->year  = $m[3];
    $this->month = $m[1];
    $this->day   = $m[2];
    $this->jdc = $jdc;
  }

  function _ensureJdc() {
    if (!$this->jdc) {
      $this->jdc = jewishtojd($this->month, $this->day, $this->year);
    }
  }

  function dow() {
    $this->_ensureJdc();
    return jddayofweek($this->jdc);
  }
  
  function increment() {
    if ($this->day <= 27) {
      // the simple case
      $this->day++;
      $this->jdc = 0;
    }
    else {
      $this->_ensureJdc();
      $this->setByJdc($this->jdc + 1);
    }
  }
  
  function decrement() {
    if ($this->day > 1) {
      // the simple case
      $this->day--;
      $this->jdc = 0;
    }
    else {
      $this->_ensureJdc();
      $this->setByJdc($this->jdc - 1);
    }
  }
}


