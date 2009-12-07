<?php
require 'tests.inc';

class TestJewishCalendar extends JewishCalendar {
  function canonize($date) {
    return $this->_canonizeInputDate($date);
  }
  function tweakHolidaysCache() {
    $this->cache['getHolidays'][5767][1][22][] = 'sukkot';
    $this->cache['getHolidays'][5767][1][22][] = 'lagBaOmer';
  }
}
$cal = new TestJewishCalendar();

date_default_timezone_set('Asia/Tel_Aviv');

$ts_2007_12_30__12_45_10 = gmmktime(12, 45, 10, 12, 30, 2007);
assert_array_contains($cal->canonize($ts_2007_12_30__12_45_10), 'hours:14 minutes:45', 'canonized timestamp is timezone offsetted');

$obj_2007_12_30__12_45_10_utc = date_create('2007-12-30 12:45:10', timezone_open('UTC'));
assert_array_contains($cal->canonize($obj_2007_12_30__12_45_10_utc), 'hours:14 minutes:45', 'canonized date object is timezone offsetted #1');

$obj_2007_12_30__12_45_10_new_york = date_create('2007-12-30 12:45:10', timezone_open('America/New_York'));
assert_array_contains($cal->canonize($obj_2007_12_30__12_45_10_new_york), 'hours:19 minutes:45', 'canonized date object is timezone offsetted #2');

$str_2007_12_30__12_45_10 = '2007-12-30 12:45:10';
assert_array_contains($cal->canonize($str_2007_12_30__12_45_10), 'hours:12 minutes:45 seconds:10', 'canonized iso string is NOT timezone offsetted');

$str_2007_12_30__12_45 = '2007-12-30 12:45';
assert_array_contains($cal->canonize($str_2007_12_30__12_45), 'hours:12 minutes:45 seconds:0', 'canonized iso string w/o seconds');

assert_array_contains($cal->canonize(array('whatever' => 'nevermind')), 'whatever:nevermind', 'canonized unrecognized input returned as-is');

assert_array_contains($cal->convertToNative('2009-12-03'), 'year:5770 mon:3 mday:16', 'jewish #1');

assert_array_contains($cal->convertToNative('2009-12-03 12:47:33'), 'seconds:33', 'time parts are not lost');

$cal->settings(array('diaspora' => TRUE));
assert_that(count($cal->getHolidays("2006-10-14")) == 1, 'sanity check');
$cal->tweakHolidaysCache();
assert_that(count($cal->getHolidays("2006-10-14")) == 3, 'holidays cache');
$cal->settings(array('diaspora' => FALSE));
assert_that(count($cal->getHolidays("2006-10-14")) == 2, 'calling settings() should reset cache');
