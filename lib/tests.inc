<?php
error_reporting(E_ALL);
(php_sapi_name() == 'cli') or die('Run this script form the command-line only.');

require 'JewishCalendar.php';
require 'HijriCalendar.php';

/**
 * Asserts that array $array contains all elements appearing in $contains.
 */
function assert_array_contains($array, $contains, $test_name) {
  if (is_string($contains)) {
    // Allow for string input. Prevent Carpal Tunnel Syndrome. PHP's array syntax is bulky.
    $parsed = array();
    foreach (preg_split('/ +/', trim($contains)) as $element) {
      list($key, $val) = split(':', $element, 2);
      $parsed[$key] = $val;
    }
    $contains = $parsed;
  }

  foreach ($contains as $key => $value) {
    if (!isset($array[$key])) {
      $die = "The array doesn't contain the key '$key'";
    }
    elseif ($array[$key] != $contains[$key]) {
      $die = "Key '$key' doesn't have the same value in both arrays.";
    }
    if (isset($die)) {
      print "Test '$test_name' failed; assert_array_contains() failed\n\n";
      print "$die\n\n";
      print "Subject array is:\n\n";
      print_r($array);
      print "\nAnd the '\$contains' array is:\n\n";
      print_r($contains);
      exit(1);
    }
  }

  print "Test '$test_name' succeded\n";
}

function assert_that($condition, $test_name) {
  if ($condition) {
    print "Test '$test_name' succeded\n";
  }
  else {
    print "Test '$test_name' failed\n";
    exit(1);
  }
}
