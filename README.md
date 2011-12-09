# JSON Schema for PHP

## Usage

```php
<?php

$json = json_decode($input_json);
$schema = json_decode($input_schema);
$result = JsonSchema::validate($json, $schema);

if ($result->valid) {
    die('success!');
}
else {
    die('fail...');
}
```