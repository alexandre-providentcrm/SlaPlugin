<?php
$manifest = array (
  0 =>
  array (
    'acceptable_sugar_versions' =>
    array (
		'exact_matches' => array(
        '7.8.0.0'
    ),
  ),
  1 =>
  array (
    'acceptable_sugar_flavors' =>
    array (
		
		),
    ),
  ),
  'readme' => '',
  'key' => 'PR',
  'author' => 'Provident',
  'description' => 'SLA Plugin - 0.0.1',
  'icon' => '',
  'is_uninstallable' => true,
  'name' => 'SLA Plugin - 0.0.1',
  'published_date' => '2017-01-16 11:34:00',
  'type' => 'module',
  'version' => '0.0.1',
  );
$installdefs = array (
  'id' => 'SLA Plugin - 0.0.1',
  'copy' =>
  array (
   	0 =>
    array (
      'from' => '<basepath>/custom/include/helpers/SLACalculation.php',
      'to' => 'custom/include/helpers/SLACalculation.php',
    ),
    array (
      'from' => '<basepath>/custom/include/helpers/lib/UKBankHoliday.php',
      'to' => 'custom/include/helpers/bin/UKBankHoliday.php',
    ),	
  ),
);
