<?php declare(encoding = 'utf-8');
/**
 * IOProfiler
 *
 * Gather and report timing information.
 *
 * Makes use of gettimeofday() which your system must support (most seem to).
 *
 * Example:
 *
 * IOProfiler::enable();
 * $profile = new IOProfiler();
 * usleep(500000);  # 0.500 sec
 * $start = $profile::now();
 * usleep(750000);  # 0.750 sec
 * $profile->log('test', '123456789', $start);
 * print_r($profile->report_data());
 *
 * PHP version 5
 *
 * @category  Library
 * @package   IOProfiler
 * @author    Stéphane Lavergne <lis@imars.com>
 * @copyright 2010-2016 Stéphane Lavergne
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt  GNU GPL version 3
 * @link      http://www.imars.com/
 */

/**
 * IOProfiler class
 *
 * @category  Library
 * @package   IOProfiler
 * @author    Stéphane Lavergne <lis@imars.com>
 * @copyright 2010-2016 Stéphane Lavergne
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt  GNU GPL version 3
 * @link      http://www.imars.com/
 */
class IOProfiler
{
	private static $_enabled = false;
	private $_start_time;
	private $_timers;

	/**
	 * Enable profiling
	 *
	 * Sets an internal flag telling IOProfiler that it should work normally.
	 *
	 * @return null
	 */
	public static function enable() {
		self::$_enabled = true;
	}

	/**
	 * Disable profiling
	 *
	 * (Default.)  Sets an internal flag telling IOProfiler to return
	 * immediately without performing any actions for all methods except
	 * instance construction.
	 *
	 * This implementation makes it possible to enable and disable profiling
	 * several times throughout the life of a program and still count time from
	 * the moment of construction.
	 *
	 * @return null
	 */
	public static function disable() {
		self::$_enabled = false;
	}

	/**
	 * Get a fresh timestamp, in milliseconds
	 *
	 * Note that this timestamp is not "milliseconds since EPOCH" because we
	 * want to support 32-bit systems.  Seconds are therefore truncated to
	 * their 21 least significant bits to make room for microseconds.
	 *
	 * @return int
	 */
	public static function now()
	{
		// microtime() depends on gettimeofday() anyway and we want the best
		// precision possible on a millisecond level.
		$now = gettimeofday();
		return (($now['sec'] & 0x1fffff) << 10) + (int)($now['usec'] / 1000);
	}

	/**
	 * Constructor
	 *
	 * @return IOProfiler
	 */
	public function __construct()
	{
		$this->_start_time = self::now();
		$this->_timers = Array(
			'__TOTALS' => Array(
				'__SCRIPT' => Array(
					'count' => 1
				)
			)
		);
	}

	/**
	 * Log the end of an operation
	 *
	 * A unique counter will be created for each $class/$unique combination,
	 * and multiple calls for the same will increment its occurence and time
	 * counters.
	 *
	 * Class "sql" is processed a little bit: if their unique identifier's
	 * first non-whitespace word is one of DELETE, INSERT, REPLACE, UPDATE it
	 * is truncated to 32 characters.  Also, all whitespace is collapsed to a
	 * single space to help catch duplicates.
	 *
	 * TODO: Actually truncate SQL queries after the name of the table instead.
	 *
	 * @param string $class  Category of operation (i.e. sql, sphinx, file)
	 * @param string $unique Identity of the operation (i.e. a full SQL query)
	 * @param int    $start  Microsecond timestamp from start of operation
	 *
	 * @return null
	 */
	public function log($class, $unique, $start)
	{
		if (self::$_enabled === false) {
			return;
		}

		$duration = $this->now() - $start;

		// Pre-process certain cases
		if ($class === 'sql') {
			// TODO: Collapse all whitespace into a single space
			// TODO: Remove initial and trailing whitespace
			// TODO: Actually fetch the first non-whitespace word
			$firstword = strtoupper('');
			switch ($firstword) {
				case 'DELETE':
				case 'INSERT':
				case 'REPLACE':
				case 'UPDATE':
					$unique = substr($unique, 0, 32);
					break;
				default:
			}
		}

		if (!array_key_exists($class, $this->_timers)) {
			$this->_timers[$class] = Array();
		}
		if (array_key_exists($unique, $this->_timers[$class])) {
			$this->_timers[$class][$unique]['count']++;
			$this->_timers[$class][$unique]['time'] += $duration;
		} else {
			$this->_timers[$class][$unique] = Array(
				'count' => 1,
				'time' => $duration,
			);
		}

		if (!array_key_exists($class, $this->_timers['__TOTALS'])) {
			$this->_timers['__TOTALS'][$class] = Array( 'count' => 0, 'time' => 0 );
		}
		$this->_timers['__TOTALS'][$class]['count']++;
		$this->_timers['__TOTALS'][$class]['time'] += $duration;
	}

	/**
	 * Return current session report data
	 *
	 * The returned array is structured as:
	 *
	 * 'class_name' => Array(
	 *     'unique' => Array(
	 *         'count' => int,
	 *         'time' => int
	 *     ), ...
	 * ), ...,
	 * '__TOTALS' => Array(
	 *     '__SCRIPT' => Array(
	 *         'count' => 1,
	 *         'time' => int,
	 *         'total_time' => int
	 *     ),
	 *     'class_name' => Array(
	 *         'count' => int,
	 *         'time' => int
	 *     ), ...
	 * )
	 *
	 * The counter represents the number of times a unique identifier was
	 * logged within a specific class.  The time integer is the total number of
	 * microseconds spent on this unique entry.
	 *
	 * Special class '__TOTALS' holds handy totals per entire classes (i.e. how
	 * many microseconds were spent on 'sql' overall) and its special entry
	 * '__SCRIPT' accounts for any time not already accounted for in class
	 * timers.  (This is handy for quickly building percentages of time spent
	 * within PHP vs waiting for I/O.)
	 *
	 * A handy shortcut to the grand total of all time elapsed between the
	 * class instantiation and when this report is generated is also added as
	 * ['__TOTALS']['__SCRIPT']['total_time'].
	 *
	 * @return array
	 */
	public function report_data()
	{
		if (self::$_enabled === false) {
			return Array();
		}

		$duration = $this->now() - $this->_start_time;
		$iotime = 0;

		foreach ($this->_timers['__TOTALS'] as $class => $counters) {
			if ($class !== '__SCRIPT') {
				$iotime += $counters['time'];
			}
		}

		$this->_timers['__TOTALS']['__SCRIPT']['time'] = ($duration - $iotime);
		$this->_timers['__TOTALS']['__SCRIPT']['total_time'] = $duration;
		return $this->_timers;
	}

	/**
	 * Produce HTML table from report data
	 *
	 * A quick-and-dirty TABLE is returned, with one row per unique identifier
	 * detailing its counter and cumulative timer.  A footer is appended with
	 * global counters and timers.
	 *
	 * @param array $report_data (Optional.)
	 *
	 * @return string
	 */
	public function report_html($report_data = null)
	{
		if (self::$_enabled === false) {
			return;
		}

		$r = '';
		if ($report_data === null) {
			$report_data = $this->report_data();
		}

		// TODO: Iterate per the description

		return $r;
	}

}

?>
