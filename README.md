# JSON Schema for PHP

## Usage

```php
<?php

$validator = new JsonSchema\Validator();
$result = $validator->validate(json_decode($json), json_decode($schema));

if ($result->valid) {
    echo "The supplied JSON validates against the schema.\n";
}
else {
    echo "JSON does not validate. Violations:\n";
    foreach ($result->errors as $error) {
        echo "[{$error['property']}] {$error['message']}\n";
    }
}
```

## Running the tests

    $ git submodule update --init
    $ phpunit
