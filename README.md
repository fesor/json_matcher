Json Match
====================

If you tried to test your JSON based REST APIs, then you probably faced a several issues:

- You can't simply check is a response is equal to given string as there are things like server-generated IDs and timestamps.
- Key ordering should be the same both for your API and for expected JSON.
- Matching the whole responses breaks DRY for the spec

All this issues can be solved with two simple things: JSON normalization and key exclusion on matching.

## Getting started

You can install this library via composer:
```
composer require fesor/json_match
```

## Matchers

### equal
This is most common matcher of all. You take two json strings and compares it. Except that before comparsion this matcher will normalize structure of both JSON strings, will reorder keys, exclude some of them (this is configurable) and then will simply assert that both strings are equal. You can specify list of excluded keys with `excluding` options:
```
$matcher->equal($actula, $expected, ['excluding' => ['id']]);
```

If you have some keys, which contains some time dependent value of some server-generated IDs it is more convenient to specify list of excluded-by-default keys when you construct matcher object:
```
$matcher = JsonMatcher::create(['id', 'created_at', 'updated_at']);
```

If you want the values for these keys were taken into account during the matching, you can specify list of included keys with `including` options
```
$matcher = JsonMatcher::create(['id', 'created_at', 'updated_at']);
$matcher->equal($expected, $actual, ['including' => ['id']]);
```

Also you can specify json path on which matching should be done via `at` options. We will back to this later since all matchers supports this option.

### includes
This matcher is pretty match the same as `equal` matcher except that is recursively scan given JSON and tries to find expected JSON in any values. This is useful for cases when you checking that some record exists in collection and you do not know or don't whant to know specific path to it.

```php
$actual = <<<EOD
{
    "collection": [
        "json",
        "matcher"
    ]
}
EOD;

$expected = '"matcher"';
$matcher->includes($actual, $expected, ['at' => 'collection']);
```

This matcher works the same way as `equal` matcher, so it accepts the same options.

### havePath
This matcher checks if given JSON have specific path ot not.

```php
$json = <<<EOD
{
    "collection": [
        "json",
        "matcher"
    ]
}
EOD;

$matcher->havePath($json, 'collection/1');
```

### haveSize
This matcher checks is collection in given JSON contains specific amount of entities.

```php
$json = <<<EOD
{
    "collection": [
        "json",
        "matcher"
    ]
}
EOD;

$matcher->haveSize($json, 2, ['at' => 'collection']);
```

### haveType
```php
$json = <<<EOD
{
    "collection": [
        {},
        "json",
        42,
        13.45
    ]
}
EOD;

$matcher->haveType($json, 'array', ['at' => 'collection']);
$matcher->haveType($json, 'object', ['at' => 'collection/0']);
$matcher->haveType($json, 'string', ['at' => 'collection/1']);
$matcher->haveType($json, 'integer', ['at' => 'collection/2']);
$matcher->haveType($json, 'float', ['at' => 'collection/3']);
```

### Negative matching
To invert expectations just call matcher methods with `not` prefix:
```php
$matcher->notEqual($actulat, $expected);
$matcher->notIncludes($json, $part);
```

### Json Path
Also all methods have option, which specifies path which should be performed matching. For example:

```php
$json = <<<EOD
{
    "collection": [
        "item"
    ]
}
EOD;
$expected = '"item"';
$matcher->equal($actual, $expected, ['at' => 'collection/0']);
```


