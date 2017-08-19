<?php

namespace Rasulian\ZarinPal\Exceptions;

use InvalidArgumentException;
use Throwable;

class ArgumentsAreNull extends \Exception
{
    private $arrayMessage;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (is_array($message)) {
            $this->arrayMessage = $message;
            $message = "";
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array|string
     */
    public function getCustomMessage()
    {
        return $this->arrayMessage ? $this->arrayMessage : $this->getMessage();
    }
}