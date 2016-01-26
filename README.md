# php-ioprofiler

Gather and report timing information

## Example:

```php
IOProfiler::enable();
$profile = new IOProfiler();
usleep(500000);  # 0.500 sec
$start = $profile::now();
usleep(750000);  # 0.750 sec
$profile->log('test', '123456789', $start);
print_r($profile->report_data());
```

See inline documentation in IOProfiler.php itself for all the details.
