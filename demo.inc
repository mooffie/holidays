<?php
// $Id$

/*
 * @file
 * Some utility functions used by the main demonstration script.
 *
 * I won't bother documenting these functions, they should be obvious.
 */

function get_param($name, $default)
{
  if (isset($_REQUEST[$name])) {
    return $_REQUEST[$name];
  } else {
    return $default;
  }
}

function back_arrow() {
  global $language;
  return ($language == 'he' ? 'prev.gif' : 'next.gif');
}

function forward_arrow() {
  global $language;
  return ($language == 'he' ? 'next.gif' : 'prev.gif');
}

function print_select_element($id, $options) {
  print "<select onchange='change_date()' id='$id'>\n";
  foreach ($options as $value => $text) {
    $selected = ($GLOBALS[$id] == $value ? 'selected' : '');
    print "<option value='$value' $selected>$text</option>\n";
  }
  print "</select>\n";
}

function print_link($text, $url, $img = NULL) {
  if ($img) {
    $text = "<img src='demo-style/$img' alt='$text' title='$text' border='0'>";
  }
  print "<a href='$url'>$text</a>";
}

function create_url($year, $month, $feed = FALSE)
{
  global $language, $method, $eves, $sefirat_omer, $isru;
  if ($month < 1) {
    $month = 12;
    --$year;
  }
  if ($month > 12) {
    $month = 1;
    ++$year;
  }
  $url = $_SERVER['PHP_SELF'] ."?year=$year&amp;month=$month&amp;language=$language&amp;method=$method&amp;eves=$eves&amp;sefirat_omer=$sefirat_omer&amp;isru=$isru";
  if ($feed) {
    $url .= '&amp;feed=1';
  }
  return $url;
}

function trans($eng, $heb) {
  global $language;
  return ($language == 'he' ? $heb : $eng);
}

// @todo: give this poor function a bit more love!
function get_ical_feed($cal, $year, $month, $years_count) {
  $output  = "BEGIN:VCALENDAR\nVERSION:2.0\n";
  $output .= 'X-WR-CALNAME:' . $cal->ical_title() . "\n";
  $jdc = gregoriantojd($month, 1, $year);
  for ($n = $years_count * 365; $n--; ) {
    $holidays = $cal->getHolidays(array('jdc' => $jdc));
    if ($holidays) {
      $gregorian = cal_from_jd($jdc, CAL_GREGORIAN);
      $gregorian_str = sprintf('%04d%02d%02d',
          $gregorian['year'], $gregorian['month'], $gregorian['day']);
      foreach ($holidays as $holiday) {
        $output .= "BEGIN:VEVENT\n";
        $output .= "DTSTART;VALUE=DATE:$gregorian_str\n";
        $output .= "SUMMARY:$holiday[name]\n";
        $output .= "END:VEVENT\n";
      }
    }
    $jdc++;
  }
  $output .= "END:VCALENDAR\n";
  return $output;
}

function get_demo_javascript() {
  $js=<<<EOS
<script type="text/javascript"> 
 
function E(id) 
{ 
  if (document.getElementById) 
    return document.getElementById(id); 
  else 
    return document.all[id]; 
} 

function get_selected(id)
{
  var sel_obj = E(id);
  if (!sel_obj) {
    return '';
  }
  var val = '';
  for (var i = 0; i < sel_obj.options.length; i++) {
    if (sel_obj.options[i].selected)
      val = sel_obj.options[i].value;
  }
  return val;
}

function change_date()
{
  var params = [ 'year', 'month', 'language', 'method', 'eves', 'sefirat_omer', 'isru' ];
  var href = "{$_SERVER['PHP_SELF']}?";
  for (var i = 0; i < params.length; i++) {
    href += params[i] + '=' + get_selected(params[i]) + '&';
  }
  window.location = href;
}

</script>
EOS;
  return $js;
}

