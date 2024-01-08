<?php

namespace NIIT\ESign\Exceptions;

use Exception;

class DocumentExceptions extends Exception
{
    public static function make(string $msg): self
    {
        return new self($msg);
    }
}
