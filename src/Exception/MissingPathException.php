<?php

namespace Fesor\JsonMatcher\Exception;

class MissingPathException extends JsonMatcherException
{

    public function __construct($path)
    {
        $this->message = sprintf('Path `%s` is not exists for given JSON', $path);
    }

}
