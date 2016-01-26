<?php

require_once('IOProfiler.php');

IOProfiler::enable();

$profile = new IOProfiler();

usleep(500000);  # 0.500 sec

$start = IOProfiler::now();
usleep(750000);  # 0.750 sec
$profile->log('test', '123456789', $start);

$start = IOProfiler::now();
usleep(200000);  # 0.200 sec
$profile->log('test', '123456789', $start);

$start = IOProfiler::now();
usleep(125000);  # 0.125 sec
$profile->log('test', 'different', $start);

$start = IOProfiler::now();
usleep(80000);  # 0.080 sec
$profile->log('test2', '123456789', $start);

usleep(20000);  # 0.020 sec

$report_data = $profile->report_data();

print_r($report_data);

print $profile->report_html($report_data);

?>
