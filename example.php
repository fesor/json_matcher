<?php

require __DIR__ . '/vendor/autoload.php';

use Fesor\JsonMatcher\Matcher\{Contain, HaveSize, BeEqual};

$json = new \Fesor\JsonMatcher\JsonMatcher('{
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com"
}');

$json
    ->should(Contain::subset('"Doe"')->at('/name'))
    ->shouldNot(Contain::subset("Jane")->at('/name'))
    ->should(HaveSize::of(4)->at("/name"))
    ->should(BeEqual::to('{"name": "John Doe"}')->ignoring('id', 'email'))
;

