<?php

namespace PurplePass\Exceptions;

class VenueFormValidationException extends \Exception
{
    private $errors;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $errors = array())
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
