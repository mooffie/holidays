<?php
// $Id$

/*
 * @file
 * The demonstration script for the PHP Jewish calendar class.
 */

error_reporting(E_ALL);

require_once dirname(__FILE__) .'/NativeCalendar.php'; // Provides the calendar object. The 'engine.'
require_once dirname(__FILE__) .'/demo.inc'; // Utility functions

// $now contains today's date, and will be highlighted on the calendar printed.
// You may wish to add to it the user's timezone offset.
$now = getdate(time());

//
// Step 1:
//
// Load parameters from the URL.
//

// The year to show the callendar for. If it isn't provided in the URL, use current year.
$year  = get_param('year', $now['year']);
// The month to show the calendar for. If it isn't provided in the URL, use current month.
$month = get_param('month', $now['mon']);

// The language in which to show the calendar. Defaults to Hebrew if and only if the browser
// tells us the user reads Hebrew.
$language = get_param('language', strstr(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], 'he') ? 'he' : 'en');

// The method used to calculate the holidays. Can be either 'israel' or 'diaspora'. Defaults
// to 'israel' if the language used is Hebrew.
$method = get_param('method', $language == 'he' ? 'israel' : 'diaspora');

// Show 'Erev Rosh HaShana', etc. Defaults to true.
$eves = get_param('eves', '1');

// Show Sefirat HaOmer (from Passover to Shavuot). Defaults to false.
$sefirat_omer = get_param('sefirat_omer', '0');

// Show 'Isru Khags'. Defaults to false because they have almost no halakhic meaning.
$isru = get_param('isru', '0');

//
// Step 2:
//
// Instantiate the calendar object.
//

$jcal = NativeCalendar::factory('Jewish');
$jcal->settings(array(
  'language' => ($language == 'he' ? CAL_LANG_NATIVE : CAL_LANG_FOREIGN),
  'diaspora' => ($method == 'diaspora'),
  'sefirat_omer' => $sefirat_omer,
  'eves' => $eves,
  'isru' => $isru,
));

if (get_param('feed', 0)) {
  header('Content-Type: text/calendar; charset=utf-8');
  header('Content-Disposition: attachment; filename="calendar.ics";');
  print get_ical_feed($jcal, $year, $month, 2 /* two years */);
  exit();
} else {
  header('Content-type: text/html; charset=utf-8');
}

//
// Step 3:
//
// Print the page.
//

$page_title = trans('Jewish Calendar', 'לוח שנה');
$javascript = get_demo_javascript();
$direction = ($language == 'he' ? 'rtl' : 'ltr');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
  "http://www.w3.org/TR/html4/loose.dtd">
<html dir="<?php echo $direction ?>" class="<?php echo $direction ?>">
<head>
   <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
   <meta name="robots" content="nofollow, noarchive" />
   <title><?php echo $page_title ?></title>
   <link href="demo-style/demo-core.css?1" rel="stylesheet" type="text/css" />
   <link href="demo-style/demo.css?1" rel="stylesheet" type="text/css" />
   <?php echo $javascript ?>
</head>
<body>

<?php
print "<table class='navigator-table' align='center'><tr>";
print "<td>";

print "<div class='title'>$page_title</div>";
print "<div class='navigator-today'>";
print_link(trans('Back to today', 'דלג להיום'), create_url($now['year'], $now['mon']));
print "</div>";

print "</td>";
print "<td>";

print "<div class='navigator'>";

print_link(trans('Previous year', 'השנה הקודמת'), create_url($year - 1, $month), back_arrow());
print " ";
$options = array();
foreach (range($year - 70, $year + 70) as $y) {
  $options[$y] = $y;
}
print_select_element('year', $options);

print_link(trans('Next year', 'השנה הבאה'), create_url($year + 1, $month), forward_arrow());

print "</div>"; // <!-- .navigator -->

print "<div class='navigator'>";

print_link(trans('Previous month', 'החודש הקודם'), create_url($year, $month - 1), back_arrow());
print " ";

$options = array(1 =>
  trans('January',  'ינואר'),
  trans('February', 'פברואר'),
  trans('March',    'מרץ'),
  trans('April',    'אפריל'),
  trans('May',      'מאי'),
  trans('June',     'יוני'),
  trans('July',     'יולי'),
  trans('August',   'אוגוסט'),
  trans('September', 'ספטמבר'),
  trans('October',  'אוקטובר'),
  trans('November', 'נובמבר'),
  trans('December', 'דצמבר')
);
print_select_element('month', $options);

print_link(trans('Next month', 'החודש הבא'), create_url($year, $month + 1), forward_arrow());

print "</div>"; // <!-- .navigator -->

$start_date_str = $jcal->getLongDate(array('year'=>$year, 'mon'=>$month, 'mday'=>1));
$end_date_str   = $jcal->getLongDate(array('year'=>$year, 'mon'=>$month, 'mday'=>cal_days_in_month(CAL_GREGORIAN, $month, $year)));

print "<div class='calendar-range'>";
print "$start_date_str &#x2013; $end_date_str";
print "</div>";

print "</td>";
print "</table>";

print "<fieldset id='preferences'>\n";
print "<legend>". trans('Preferences', 'הגדרות') ."</legend>\n";

print 'Language:<br />';
$options = array(
  'en' => 'English',
  'he' => 'Hebrew',
);
print_select_element('language', $options);
print '<br />';

print trans('Method:', 'שיטה:') .'<br />';
$options = array(
  'diaspora' => trans('Diaspora', 'גולה'),
  'israel'   => trans('Land of Israel', 'ארץ ישראל'),
);
print_select_element('method', $options);
print '<br />';

print trans('Sefirat HaOmer:', 'ספירת העומר:') .'<br />';
$options = array(
  '0' => trans('No',  'לא'),
  '1' => trans('Yes', 'כן'),
);
print_select_element('sefirat_omer', $options);
print '<br />';

print trans('Holiday Eves:', 'ערבי חגים:') .'<br />';
$options = array(
  '0' => trans('No',  'לא'),
  '1' => trans('Yes', 'כן'),
);
print_select_element('eves', $options);
print '<br />';

print trans('Isru Khags:', 'אסרו חג:') .'<br />';
$options = array(
  '0' => trans('No',  'לא'),
  '1' => trans('Yes', 'כן'),
);
print_select_element('isru', $options);
print '<br />';

if ($language == 'he' && $method == 'diaspora') {
?>
  <div id="method-warning">
  תזכורת: מכיוון שאתה צופה בגרסה העברית של לוח השנה, יתכן שאתה מצפה לראות את מועדי החגים כפי שהם
  נהוגים בארץ ישראל. שנה ל"ארץ ישראל" את הבחירה בתפריט "שיטה" כדי שכך יהיה.
  </div>
<?
}
print "</fieldset>\n";

// Hurray! finally the heart of this script:
print $jcal->printCal($year, $month);

print "<br />";
print_link(trans('iCal feed', 'פיד iCal'), create_url($year, $month, TRUE));

require_once dirname(__FILE__) .'/demo.help';

