<?php

namespace JmbgLabs\Jmbg;

use Exception;

class JmbgException extends Exception
{
    /**
     * JmbgException constructor.
     *
     * @param string         $message
     * @param integer        $code
     * @param null|Exception $previous
     */
    public function __construct(
        string $message = 'Invalid Serbian unique master citizen number.',
        int $code = 400,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}