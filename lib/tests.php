<?php
require 'tests.inc';

class TestJewishCalendar extends JewishCalendar {
  function canonize($date) {
    return $this->_canonizeInputDate($date);
  }
}
$cal = new TestJewishCalendar();

date_default_timezone_set('Asia/Tel_Aviv');

$ts_2007_12_30__12_45_10 = gmmktime(12, 45, 10, 12, 30, 2007);
assert_array_contains($cal->canonize($ts_2007_12_30__12_45_10), 'hours:14 minutes:45', 'canonized timestamp is timezone offsetted');
