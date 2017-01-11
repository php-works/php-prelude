# php-prelude

A PHP library that makes daily programming much easier by providing concise APIs for some important aspects in application development

Features:

- A very nice way to handle lazy sequences
- Most APIs are provided as fluent interfaces
- Errors will result in exceptions, no need to check return values
  whether they are false
- IO operations can be run without taking care about opening
  and closing resources
- Database operations and queries are performed without handling things
  like database connections or preparing statements explicitly
- Database transaction commits and rollbacks will be executed automatically
  depending on the success/result of an operation
- Simple DSL for generating CSV exports

# Contents
* [Introduction](#introduction)
  * [Motivation](#motivation)
  * [Installation](#installation)

* [API](#api)
  * [Lazy sequences](#lazy-sequences)
  * [Dynamic objects](#dynamic-objects)
  * [Database access](#database-access)
  * [Scanning directories](#scanning-directories)
  * [File input and output](#file-input-and-output)
  * [CSV exports](#csv-exports)
  
* [Miscellaneous](#miscellaneous)
  * [Project status](#project-status)

# Introduction

## Motivation

PHP provides out-of-the-box a lot of great APIs for a very large amount of concerns, which makes application devolopment much faster.<br>
Nevertheless the usage of this APIs does often not result in very concise code.<br>
The reason is that the APIs are often quite low level and therefore the programmer have to deal with quite a lot of secondary aspects that could theoretically have also been handled by the library itself.
For example, for a lot of I/O operations (file access, network, database etc.) you have to open the resource and close again it at the end and have to examine a lot of return values from I/O method calls whether they represent an error.<br>
For database queries you have to open the database, create a prepared statement, bind values to that statement, execute the query, fetch the result by looping over the statement and release the database connection afterwards.<br>
With "php-prelude" in contrast, you normally do not have to deal with opening and closing resources.<br>
You do not have to care about database connections and prepared statements etc.<br>
The library will handle that for you and you can concentrate on the really important aspects of your application.

A large part of "php-prelude" is based on the concept of lazy sequences.<br>
This concept in well-known in other languages:

- "Stream" in Java 8, Scala, Scheme
- "Seq" in Clojure/ClojureScript
- "Lazy list" in Haskell
- "IEnumerable" in C#
- etc.

In "php-prelude" a lazy sequence is called "Seq" and implements the PHP standard interface IteratorAggregate.<br>
It is (in contrast to for example streams in Java 8) completely immutable and stateless, that means that you can traverse the sequence as often you want and pass the Seq around any way you like.<br>

Database result sets, the lines in a text files, file entries in a directory, CSV records, everything can be handled as lazy sequences, which makes things much easier.<br>
And that is exactly what "php-prelude" does.

Sound good? Then check the [API](#api) for details....

## Installation

Just add the "php-prelude" folder somewhere to your project
("composer" support is currently not available).<br>
Then include the PHP file "include.php" which is to be found in the root
folder of  "php-prelude" (all class and interface files will be included
automatically as "php-prelude" uses PHP's autoload functionality).

# API

## Lazy sequences

Creating a sequence from an array
```php
Seq::from(['a', 'b', 'c', 'd'])
// Result: <'a', 'b', 'c', 'd'>
```

Creating a lazy range of numbers 
```php
Seq::range(1, 5);
// Result: <1, 2, 3, 4> (right value is excluded)
```

Sequences are lazy: The enumeration will only be performed as far as really needed
```php
Seq::range(1, 1000000000)
    ->take(10)
    ->max()
// Result: 10 (only 10 values have been enumerated)
```
Building an array of first 10 fibonacci numbers
```php
Seq::iterate([1, 1], function ($a, $b) {
        return $a + $b;
    })
    ->take(10)
    ->toArray()
// Result: [0, 1, 1, 2, 3, 5, 8, 13, 21, 34]
```
Creating a lazy sequence based on a generator function
```php
Seq::from(function () {
    for ($i = 0; $i < 1000000000; +$i) {
        yield $i;
    }
});
```

Empty sequence
```php
Seq::nil()
// Result: Empty sequence
```

Single element sequence
```php
Seq::of(42)
// Result: <42>
```

Filtering sequences
```php
Seq::from([1, 2, 7, 9, 12, 24, 33, 45])
    ->filter(function ($n) {
        return $n % 3 === 0;
    })
// Result: <9, 12, 33, 45>
```

Mapping sequences
```php
Seq::from([1, 2, 3, 4, 5])
    ->map(function ($n) {
        return $n * $n;
    })
// Result: <1, 4, 9, 16, 25>
```

Limiting sequences
```php
Seq::range(1, 100)
    ->skip(5)
    ->take(10)
// Result: <6, 7, 8, 9, 10, 11, 12, 13, 14, 15>
```

Concatenating sequences
```php
$seq1 = Seq::of(42)
$seq2= Seq::from([43, 44, 45])
$seq3 = Seq::range(46, 50)

Seq::concat($seq1, $seq2)
// Result: <42, 43, 44, 45>

Seq::concatMany([$seq1, $seq2, $seq3])
// Result: <42, 43, 44, 45, 46, 47, 48, 49>
```

Flattening sequences
```php
$seq = Seq::from([Seq::from([1, 2, 3]), Seq::from([4, 5, 6])])
$seq->flatten()
// Result: <1, 2, 3, 4, 5, 6>
```

Traversing sequences
```php
$seq = Seq::from([1, 2, 3, 4, 5]);

foreach ($seq as $n) {
    print $n;
}
// Prints out 12345

// Same as
$seq->each(function ($n) {
    print $n;
});
```

And many other sequence operations (see API documentation for details) .....

## Dynamic objects

Instead of handling records in associative arrays, it's possible to use dynamic objects where each property can be accessed using "->" arrow.
The advantage is that this is syntactically much nicer then with associative arrays and a it will throw a RuntimeException in case that someone will try to read a property that does not exist.
The disadvantage is that dynamic objects use PHP's magic functions internally which is slower than accessing values in an associative array.
Dynamic objects are actually quite handy to be used in database query results.
As a rule of thumb, it's always fine to use dynamic objects in areas where you do not necessarily need the highest performance possible (e.g. in cronjobs), for high performance multi-client environment they may not be the best choice.

```php
$user = new DynObject([
    'firstName' => 'John',
    'lastName' => 'Doe';
]);

$user->city = 'Seattle';
$user->country = 'USA';

print "$user->firstName $user->lastName, $user->city $user->country";
// Prints out: John Doe, Seattle USA
```

## Database access

Executing query:

```php
$database
    ->query('delete from user')
    ->execute();
// Will clear table 'user'
```
Executing query with bindings:

```php
$userId = 12345;

$database
    ->query('delete from user where id=?')
    ->bind($userId)
    ->execute();
// Will delete the record of user 12345
```

```php
$database
    ->query('delete from user where city=:city and country=:country')
    ->bind(['city' => 'Seattle', 'country' => 'USA'])
    ->execute();
// Will delete all users from Seattle
```

Inserting many records  with the same query
(internally, a single prepared statement will be used)
```php
$users = [
    [1, 'John', 'Doe', 'Boston', 'USA'],
    [2, 'Jane', 'Whoever', 'Portland', 'USA']];

$database
    ->query('insert into user values (?, ?, ?, ?, ?)')
    ->bindMany($users) // also lazy sequences would be allowed here
    ->execute();
// will insert two new user records to table 'user'
```

Fetching a single value:
```php
$database
    ->query('select count(*) from user where country=:0 and city=:1')
    ->bind([$country, $city])
    ->fetchSingle()
// Result: Number of matching records
```

Fetching an array of single values:
```php
$database
    ->query('select id from user where country=:0 and city=:1')
    ->bind([$country, $city])
    ->fetchSingles()
// Result: [111, 222, ...]
```

Fetching an array of numeric arrays:
```php
$database
    ->query('select id, firstName, lastName from user where country=?')
    ->bind($country)
    ->fetchRows()
// Result: [[111, 'John', 'Doe'], [222, 'Jane', 'Whoever'], ...]
```

Fetching an array of associative arrays:
```php    
$databse
    ->query('select id, firstName, lastName from user where country=?'),
    ->bind($country)
    ->fetchRecs()
// Result:
// [['id' => 111, 'firstName' => 'John', 'lastName' => 'Doe'],
//  ['id' => 222, 'firstName' => 'Jane', 'lastName' => 'Whoever'], ...]
```

Fetching a lazy sequence of numeric arrays:
```php
$database
    ->query('select * from user where country=:0 and city=:1')
    ->bind([$country, $city])
    ->fetchSeqOfRows()
// Result:
//    <[111, 'John', 'Doe'],
//     [222, 'Jane', 'Whoever'], ...>
```

Fetching a lazy sequence of associative arrays:
```php        
$database
    ->query('select id, firstName, lastName from user where country=?')
    ->bind($country)
    ->fetchSeqOfRecs()
// Result:
//    <['id' => 111, 'firstName' => 'John', 'lastName' => 'Doe'],
//     ['id' => 222, 'firstName' => 'Jane', 'lastName' => 'Whoever'], ...>
```

Fetching a lazy sequence of dynamic objects:

```php
$users = 
    $database
        ->query('select id, firstName, lastName from user where country=?',
        ->bind($country)
        ->limit(100)
        ->fetchSeqOfDynObjects();
    
foreach ($user as $user) {
    print "$user->id: $user->firstName $user->lastName\n";
}
// Prints out the first 100 users from the selected country
```

Fetching a key-value map:

```php
$database
    ->query('select id, lastName from user')
    ->fetchMap()
// Result: [111 => 'Doe', '222' => 'Whoever', ...]
```

For simple SELECT, INSERT, UPDATE and DELETE queries there is also an
alternative DSL available.<br>
Depending on the concrete case that may be shorter compared to the
more powerful query method mentioned above. 

Alternative DSL for simple SELECT queries:

```php
$users =
    $database
        ->from('user')
        ->select('firstName, lastName, city, country')
        ->where('city=? and country=?', ['Seattle', 'USA'])
        ->orderBy('lastName, firstName')
        ->limit(100)
        ->fetchRecs();
```

Alternative DSL for simple INSERT queries:

```php
$newUser = {
    'firstName' => 'James',
    'lastName' => 'Jools',
    'city' => 'Sidney',
    'country' => 'Australia'
};

$database
    ->insertInto('user')
    ->values($newUser)
    ->execute();
```

Alternative DSL for simple UPDATE queries:

```php
$database
    ->update('user')
    ->set(['city' => 'Sacramento'])
    ->where('id=?', 12345)
    ->execute();
```

Alternative DSL for simple DELETE queries:

```php
$database
    ->deleteFrom('user')
    ->where('id=?', 12345)
    ->execute();
```

Transactions:

```php
$users = ...; // either an array or a lazy sequence of data from several users

$database->runTransaction(function ($database) use ($users) {
     $database
         ->query('delete from user')
         ->execute();
         
     $database
         ->query('
             insert  into user values
             (:id, :firstName, :lastName, :city, :country, :type)
         ')
         ->bindMany($users)
         ->execute();
});
// A rollback will be performed in case that
// the closure throws an exception or returns false.
// Otherwise a commit will be performed once the closure
// has been run completely.
```

Using dedicated database connections:
Normally "php-prelude" will keep database connections open until the end of the script or until connection times out.
An once opened database connection will be reused for each following database query.
If a dedicated database connection shall be used for a particular part of the program, having the database connection been opened at its start and closed at its end, then the method 'runIsolated' has to be used.

```php
$database->runIsolated(function ($database) {
     $database
         ->query('delete from user')
         ->execute();
});

```

## Scanning directories

Scanning a directory for certain files or subdirectories

```php
PathScanner::create()
    ->recursive()
    ->includeFiles(['*.php', '*.json'])
    ->excludeFiles(['*tmp*', '*temp*'])
    ->excludeLinks()
    ->forceAbsolute() // list absolute paths
    ->sort(FileComparators::byFileSize())
    ->listPaths() // list paths as strings otherwise File objects would be returned
    ->scan('.') // scan current directory, will return a lazy sequence
    ->toArray();
// Result:
// An array of all PHP and JSON file paths as strings in the
// current directory (including files in the subdirectories),
// excluding temporary files and symbolic links,
// sorted by file size (ascending)
```

## File input and output

File operations without the need of handling file pointers explicitly:
No need to open or close resources.
Each IO operation will throw an IOException on error, that means that it is not necessary to check the result of each IO operation for being false, like with the underlying original PHP API.

Reading a file line by line (lazily)
```php
$lines =
    FileReader::from('input.txt')
        ->readSeq();

foreach ($lines as $Line) {
    print $line . "\n";
}
// Reads and prints out the content of the input file line by line
```
Reading a file completely into a string
```php
$content =
    FileReader::from('input.txt')
        ->readFully();
// The whole content will be read and returned.
// Similar to function file_get_contents, but will throw
// an IOException on error.
```

Determine the number of "error" lines in a certain log file.
```php
$errorLineCount =
    FileReader::fromFilename('path/to/logs/app.log')
        ->readLines()
        ->filter(function ($line) {
            return stripos($line, 'error') !== false);
        })
        ->count();
```

Writing to files

```php
$lines =
    Seq::range(1, 100)
        ->map(function ($n) {
            return 'Line ' . $n;
        });

FileWriter::fromFile('output.txt')
    ->writeSeq($lines);
// Write 99 lines to the file:
// "Line 1", "Line2", "Line3" etc.
```
Appendng a concrete text to the file

```php
FileWriter::fromFile('output.txt')
    ->append()
    ->writeFully('This text will be appended to the existing file');
```

Counting lines of code in a whole project:

```php
// Determine the number of-non blank PHP lines in the "src" folder:
// "scan" returns a lazy sequence of files, "flatMap" takes
// that file sequence, maps each file entry to a sequence of text
// lines and flattens the seqence of text line sequences afterwards
// into a single senquence of text lines.
// All of that happens completely lazily.
$lineCount =
	PathScanner::create()
		->recursive()
		->includeFiles('*.php')
		->forceAbsolute()
		->scan('./src')
		->flatMap(function ($file) {
			return
				FileReader::fromFile($file)
					->readSeq();
	})
	->filter(function ($line) {
		return trim($line) !== '';
	})
	->count();

print "Number of non-blank PHP lines in directory 'src': $lineCount\n";
```

## CSV exports

Also for CSV exports a nice fluent API is provided

```php
// Please be aware that the following recordsets vary
// structurally
$recs = [
    ['LAST_NAME' => 'Iverson',
     'FIRST_NAME' => 'Allen',
     'CITY' => 'Hampton',
     'COUNTRY' => 'USA'],
     
    ['FIRST_NAME' => 'Dirk',
     'LAST_NAME' => 'Nowitzki',
     'CITY' => 'Wuerzburg',
     'COUNTRY' => 'Germany'],
    
    ['Michael "Air"', 'Jordan', 'New York',
     'USA', 'This field will not be exported']
];

$format =
    CSVFormat::create()
        ->columns(['FIRST_NAME', 'LAST_NAME', 'CITY', 'COUNTRY'])
        ->delimiter(';')
        ->quoteChar('"');

CSVExporter::create()
    ->format($format)
    ->mapper(function ($rec) {
        // Add some clones in Vienna - just because we can  ;-)
        $rec2 = $rec;
        $rec2['LAST_NAME'] = 'Doppelganger';
        $rec2['CITY'] = 'Vienna';
        $rec2['COUNTRY'] = 'Austria';
        
        return Seq::from([$rec, $rec2]);
    })
    ->charsetRecoder(
        CharsetRecoder::create('UTF-8', 'ISO-8859-1')
    ->export(
        Seq::from($recs),
        FileWriter::fromFile('php://stdout'));
            
// Will print out the following CSV formatted records to stdout:

// FIRST_NAME;LAST_NAME;CITY;COUNTRY
// Allen;Iverson;Hampton;USA
// Allen;Doppelganger;Vienna;Austria
// Dirk;Nowitzki;Wuerzburg;Germany
// Dirk;Doppelganger;Vienna;Austria
// "Michael ""Air""";Jordan;"New York";USA
// "Michael ""Air""";Doppelganger;Vienna;Austria
```

# Miscellaneous

## Project status

"php-prelude" is quite a new project which is still in alpha state.<br>
While the API is almost final and the implementation is already working,
there's still a lot to do regarding finalizing code, unit testing and inline
documentation.
