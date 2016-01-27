# php-ioprofiler

Gather and report timing information

## Example:

```php
require_once('IOProfiler.php');

IOProfiler::enable();  // Toggle global on/off switch

$profile = new IOProfiler();  // Timers are instances

usleep(500000);  // 0.500 sec
 
// Time something...
$start = IOProfiler::now();
usleep(750000);  // 0.750 sec
$profile->log('test', '123456789', $start);

// Results can be data or an HTML table
print_r($profile->reportData());
print($profile->reportHTML());
```

The basic syntax of storing a start timestamp from now(), invoking your I/O
code, and then telling it to log() is to allow nesting: maybe your lower-level
module is also using the same instance of IOProfiler.  This edge case would
probably cause negative script total time but would reveal interesting details
about at which depth in your stack time is spent.

See inline documentation in IOProfiler.php itself for all the details.
