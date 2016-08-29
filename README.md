[![Build Status](https://travis-ci.org/KEIII/PdoWrapper.svg?branch=master)](https://travis-ci.org/KEIII/PdoWrapper)

Provides wrapper of PHP PDO class.

## Installation

```bash
composer require keiii/pdo-wrapper
```

## Example

```php
<?php
$db = new \KEIII\PdoWrapper\PdoWrapper('sqlite::memory:');
$db->write('INSERT INTO people (name) VALUES (\'John Smith\');', [':name' => 'John']);
$john = $db->readOne('SELECT * FROM people WHERE name = :name;', [':name' => 'John']);
```
