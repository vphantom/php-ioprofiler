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

The basic syntax of storing a start timestamp from now(), invoking your I/O code, and then telling it to log() is to allow nesting: maybe your lower-level module is also using the same instance of IOProfiler.  This edge case would probably cause negative script total time but would reveal interesting details about at which depth in your stack time is spent.


## Class methods

### IOProfiler::enable()

Enable profiling

Sets an internal flag telling IOProfiler that it should work normally.

### IOProfiler::disable()

Disable profiling

(Default.)  Sets an internal flag telling IOProfiler to return immediately without performing any actions for all methods except instance construction.

This implementation makes it possible to enable and disable profiling several times throughout the life of a program and still count time from the moment of construction.

### IOProfiler::now()

Get a fresh timestamp, in milliseconds

Note that this timestamp is not "milliseconds since EPOCH" because we want to support 32-bit systems.  Seconds are therefore truncated to their 21 least significant bits to make room for microseconds.


## Instance methods

### log($class, $unique, $start)

Log the end of an operation

A unique counter will be created for each $class/$unique combination, and multiple calls for the same will increment its occurence and time counters.

Class "sql" (case-insensitive) has its whitespace collapsed to help catch duplicates.  Further, if their unique identifier's first word is one of DELETE, INSERT, REPLACE, UPDATE, it is truncated after the following table name.  Typical keywords found between the two are safely kept.  (i.e. "INTO", "FROM", etc.)

Parameters:

```
string $class  Category of operation (i.e. sql, sphinx, file)
string $unique Identity of the operation (i.e. a full SQL query)
int    $start  Microsecond timestamp from start of operation
```

Returns no value.

### reportData()

Return current session report data

The returned array is structured as:

```
'class_name' => Array(
    'unique' => Array(
        'count' => int,
        'time' => int
    ), ...
), ...,
'__TOTALS' => Array(
    '__SCRIPT' => Array(
        'count' => 1,
        'time' => int,
        'total_time' => int
    ),
    'class_name' => Array(
        'count' => int,
        'time' => int
    ), ...
)
```

The counter represents the number of times a unique identifier was logged within a specific class.  The time integer is the total number of milliseconds spent on this unique entry.

Special class '__TOTALS' holds handy totals per entire classes (i.e. how many microseconds were spent on 'sql' overall) and its special entry '__SCRIPT' accounts for any time not already accounted for in class timers.  (This is handy for quickly building percentages of time spent within PHP vs waiting for I/O.)

A handy shortcut to the grand total of all time elapsed between the class instantiation and when this report is generated is also added as ['__TOTALS']['__SCRIPT']['total_time'].

### reportHTML($report_data)

Produce HTML table from report data

A quick-and-dirty TABLE is returned, with CSS class "ioprofiler" containing one row per unique identifier detailing its counter and cumulative timer.  A footer is appended with global counters and timers.

Parameters:

```
array $report_data (Optional.)
```

Return value:

String of HTML.

## MIT License

Copyright (c) 2016 Stephane Lavergne <https://github.com/vphantom>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
