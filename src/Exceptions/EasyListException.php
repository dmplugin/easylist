<?php

namespace EasyList\Exceptions;

use Exception;

/**
 * EasyList_Exception
 */
class EasyListException extends Exception
{
    /**
     * Error handler callbacknamespace EasyList2;
     * @param mixed $file
     * @param mixed $line
     * @param mixed $context
     */
    public static function errorHandlerCallback($code, $string, $file, $line, $context)
    {
        $e = new self($string, $code);
        $e->line = $line;
        $e->file = $file;
        throw $e;
    }
}
