<?php

require __DIR__ . '/vendor/autoload.php';

use function Fesor\JsonMatcher\{json};

$response = '{}';
$id = 1;

json($response, ['id', 'created_at'])

    // response equals json within()->keys('test')->path('/')
    ->equals(json('{
        "id": 1,
        "title": "some_title"
    }'), t()->path('/foo')->keys('id', 'created_at')->except(''))
    ->equals([
        'id' => $id,
        'title' => 'some_title'
    ])
    ->includes('{}')
    ->hasSize(2)
    ->hasType([
        'id' => 'int',
        'title' => 'string'
    ])
;
