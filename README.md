[![Build Status](https://travis-ci.org/KEIII/PdoWrapper.svg?branch=master)](https://travis-ci.org/KEIII/PdoWrapper)

Provides wrapper of PHP PDO class to solve verbosity problem.

## Installation

```bash
composer require keiii/pdo-wrapper
```

## Example

```php
<?php

use KEIII\PdoWrapper\PdoWrapper;
use KEIII\PdoWrapper\PdoQuery;

$db = new PdoWrapper('sqlite::memory:');

// write
$sql = 'INSERT INTO people (name) VALUES (:name);';
$parameters = [':name' => 'John'];
$db->write(new PdoQuery($sql, $parameters));

// read one
$sql = 'SELECT * FROM people WHERE name = :name;';
$parameters = [':name' => 'John'];
$john = $db->read(new PdoQuery($sql, $parameters))->getFirst();

// as generator
$people = $db->read(new PdoQuery('SELECT * FROM people;'))->asGenerator();
foreach ($people as $human) {
    // ...
}
```
