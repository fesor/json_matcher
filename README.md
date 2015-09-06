Json Matcher
====================

[![Build Status](https://travis-ci.org/fesor/json_matcher.svg?branch=master)](https://travis-ci.org/fesor/json_matcher) 
[![Latest Stable Version](https://poser.pugx.org/fesor/json_matcher/v/stable.svg)](https://packagist.org/packages/fesor/json_matcher) 
[![Latest Unstable Version](https://poser.pugx.org/fesor/json_matcher/v/unstable.svg)](https://packagist.org/packages/fesor/json_matcher) 
[![License](https://poser.pugx.org/fesor/json_matcher/license.svg)](https://packagist.org/packages/fesor/json_matcher) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fesor/json_matcher/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fesor/json_matcher/?branch=master) 
[![Total Downloads](https://poser.pugx.org/fesor/json_matcher/downloads.svg)](https://packagist.org/packages/fesor/json_matcher)

Assertion library for simplifying JSON data and structure verification in your tests. It's framework-agnostic, so you can use it with PhpUnit, PhpSpec, Peridot or whatever framework you use.

## Why another JSON assertion library?

If you tried to test your JSON based REST APIs, then you probably faced a several issues:

- You can't simply check is a response is equal to given string as there are things like server-generated IDs and timestamps.
- Key ordering should be the same both for your API and for expected JSON.
- Matching the whole responses breaks DRY for the tests

All these issues can be solved with two simple things: JSON normalization and key exclusion on matching. This is what this library does. It provides you a way to verify data in given JSON in multiple steps instead of one big assertion.

For example we are developing an friend list feature for our API for. What we want to check is only is given user presents in response, we don't want to check whole response, it could be done via json schema validation or on another test cases.

```php

$alice = new User('Alice', 'Miller');
$john = new User('John', 'Smith');
$alice->addFriend($john);

$json = JsonMatcher::create(
    json_encode($alice->toArrayIncludingFriends()), ['id', 'created_at']
);

```

In above example we just created an `JsonMatcher` instance and specified excluded-by-default keys (`id` and `created_at`). Excluded keys will be removed from JSON and it's values will not interfere in equality assertion. You can also override this list of keys via matching `excluding` and `including` options.

Then we can check is John presents in Alice's friend list at some specific position via json paths:
```php
$json->equal(json_encode($john->toArray()), ['at' => 'friends/0']);
```

Or if we don't know specific position, we can just check is John just presents in our friendlist.
```php
$json->includes(json_encode($john->toArray()), ['at' => 'friends']);
```

Or we can just verify is any John presents in Alice's friend list:
```php
$json->includes('{"first_name": "John"}'), ['at' => 'friends']);
```

## Getting started

You can install this library via composer:
```
composer require fesor/json_matcher
```

Then you will need an `JsonMatcher` instance to be created. To do this, you can:

 - manually create instance with all dependencies and set subject
 - use named constructor `JsonMatcher::create` as static factory-method. It will handle all dependencies for you.
 - use JsonMatcherFactory. This is useful when you have some IoC container in your test framework (Behat for example). In this case you'll need to register this class as a service.

Subject on which assertion will be preformed is setted up in matcher consturctor. If you want to reuse the same instance of matcher for every assertions, you can just change subject via `setSubject` method.

Example:
```php
$jsonResponse = JsonMatcher::create($response->getContent());

// or you can use factory instead
$jsonResponse = $matcherFactory->create($response->getContent());

// and there you go, for example you may use something like this 
// for your gherkin steps implementations
$jsonResponse
    ->hasSize(1, ['at' => 'friends']) // checks that list of friends was incremented
    ->includes($friend, ['at' => 'friends']) // checks that correct record contained in collection
;
```

You can provide list of excluded-by-default keys as second argument in constructors:
```php
$matcher = JsonMatcher::create($subject, ['id', 'created_at']);
```

Please note, that `id` key will be ignored by default.

## Matchers

All matchers are supports fluent interface, negative matching and some options. See detailed description for more information about what options each matcher has.

### equal
This is most commonly used matcher. You take two json strings and compare them. Except that before compassion this matcher will normalize structure of both JSON strings, reorder keys, exclude some of them (this is configurable) and then will simply assert that both strings are equal. You can specify list of excluded keys with `excluding` options:
```php
$actualJson = '["id": 1, "json": "spec"]';
$expectedJson = '["json": "spec"]';
$matcher
    ->setSubject($actualJson)
    ->equal($expectedJson, ['excluding' => ['id']])
;
```

If you have some keys, which contains some time dependent value of some server-generated IDs it is more convenient to specify list of excluded-by-default keys when you construct matcher object:
```php
$matcher = JsonMatcher::create($subject, ['id', 'created_at', 'updated_at']);
```

If you want the values for these keys to be taken into account during the matching, you can specify list of included keys with `including` options
```php
$matcher = JsonMatcher::create($response->getContent(), ['id', 'created_at', 'updated_at']);
$jsonResponseSubject->equal($expectedJson, ['including' => ['id']]);
```

Also you can specify json path on which matching should be done via `at` options. We will back to this later since all matchers supports this option.

### includes
This matcher works a little different from `equal` matcher. What it does is recursively scan subject JSON and tries to find any inclusions of JSON subset. This is useful for cases when you checking that some record exists in collection and you do not know or don't want to know specific path to it.

```php
$json = <<<JSON
{
    "id": 1,
    "name": "Foo",
    "collection": [
        {"id": 1, "name": "Foo"},
        {"id": 2, "name": "Bar"},
    ]
}
JSON;

$matcher
    ->setSubject($json)
    // check for value inclusion
    ->includes('"Foo"')
    // checks is json subset presents in any item of collection
    ->includes('{"name": "Bar"}', ['at' => 'collection'])
    // checks is json presents in collection
    ->includes('{"name": "Bar", "value": "FooBar"}', ['at' => 'collection'])
;
```

Since this matcher works the same way as `equal` matcher, it accepts same options.

### hasPath
This matcher checks if given JSON have specific path ot not.

```php
$json = <<<JSON
{
    "collection": [
        "json",
        "matcher"
    ]
}
JSON;

$matcher
    ->setSubject($json)
    ->hasPath('collection/1')
;
```

### hasSize
This matcher checks is collection in given JSON contains specific amount of entities.

```php
$json = <<<JSON
{
    "collection": [
        "json",
        "matcher"
    ]
}
JSON;

$matcher
    ->setSubject($json)
    ->hasSize(2, ['at' => 'collection'])
;
```

### hasType
```php
$json = <<<JSON
{
    "collection": [
        {},
        "json",
        42,
        13.45
    ]
}
JSON;

$matcher
    ->setSubject($json)
    ->hasType('array', ['at' => 'collection'])
    ->hasType('object', ['at' => 'collection/0'])
    ->hasType('string', ['at' => 'collection/1'])
    ->hasType('integer', ['at' => 'collection/2'])
    ->hasType('float', ['at' => 'collection/3'])
;
```

### matchesSchema

`hasPath` and `hasType` matchers provide you a simple way to verify JSON structure. But if you want to verify large part of JSON or event hole structure with this matchers, it can be quite tedious. What can be used instead is [Json Schema](http://json-schema.org/) and this matcher allows you to verify JSON or it part on schema matching.

```php
$schema = <<<JSONSCHEMA
{
	"title": "User json representation",
	"type": "object",
	"properties": {
		"firstName": {
			"type": "string"
		},
		"last_name": {
			"type": "string"
		},
		"created_at": {
			"description": "Date in ISO-8601 format",
			"type": "string"
		}
	},
	"required": ["first_name", "last_name"]
}
JSONSCHEMA;

$json = <<<JSON
{
    "first_name": "Alice",
    "last_name": "Miller",
    "created_at": "2015-09-02T00:00:00+00:00"
}
JSON;

$matcher
  ->setSubject($json)
  ->matchesSchema($schema);
```

Json schema is quite verbose, and you probably want to store it in separate files. Also you can use things like [RAML](http://raml.org/) or [api-blueprint](https://apiblueprint.org/) to generate schemas for responses in your functional tests from API documentation. But currently `JsonMatcher` doesn't provide you an API to load files since you can do that via very different ways. What is suggested is to create separate function to load schema file and return it's contents:

```
function fromFile($fileName) {
    return file_get_contents(sprintf('file://%s/%s', __DIR__ . '/../support/schema', $fileName));
}

$matcher
  ->matchesSchema(fromFile('users_schema.json'));
```

Supported options:

 - `at` - verify JSON Schema by given path.
 - `excluding` - exclude specific keys before matching. Please not that excluded-by-default are ignored here.

### Negative matching
To invert expectations just call matcher methods with `not` prefix:
```php
$matcher
    ->setSubject($json)
    ->notEqual($expected)
    ->notIncludes($part)
;
```

### Json Path
Also all methods have option, which specifies path which should be performed matching. For example:

```php
$actual = <<<JSON
{
    "collection": [
        "item"
    ]
}
JSON;
$expected = '"item"';
JsonMatcher::create($actual)
    ->equal($expected, ['at' => 'collection/0'])
;
```
## Contribution
Please welcome to contribute! 

