<?php

use Exception;

class IllegalStateException extends Exception
{
    public function __construct(string $message = "L'ID d'une classe ne peut pas être modifié une fois défini.", int $code = 400, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}