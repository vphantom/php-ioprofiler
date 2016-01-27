<?php declare(encoding = 'utf-8');

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Example for IOProfiler
 *
 * PHP version 5
 *
 * @category  Library
 * @package   IOProfiler
 * @author    Stéphane Lavergne <lis@imars.com>
 * @copyright 2010-2016 Stéphane Lavergne
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt  GNU GPL version 3
 * @link      https://github.com/VPhantom/php-ioprofiler
 */

require_once 'IOProfiler.php';

IOProfiler::enable();

$profile = new IOProfiler();  // Timer starts

usleep(500000);  // 0.500 sec

$start = IOProfiler::now();
usleep(750000);  // 0.750 sec
$profile->log('test', '123456789', $start);

$start = IOProfiler::now();
usleep(200000);  // 0.200 sec
$profile->log('test', '123456789', $start);

$start = IOProfiler::now();
usleep(125000);  // 0.125 sec
$profile->log('test', 'different', $start);

$start = IOProfiler::now();
usleep(80000);  // 0.080 sec
$profile->log('Sql', ' Delete   From roger extra chars', $start);

usleep(20000);  // 0.020 sec

$report_data = $profile->reportData();

print_r($report_data);

print $profile->reportHTML($report_data);

?>
