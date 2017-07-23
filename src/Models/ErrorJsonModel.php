<?php
namespace BullsAndCows\Models;

/**
 * Class ErrorJsonModel
 * @package BullsAndCows\Models
 */
class ErrorJsonModel extends JsonModel
{

    /**
     * ErrorJsonModel constructor.
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception)
    {
        // Set the JSON message property to the exception message and status to FAIL.
        $this->setMessage($exception->getMessage())
             ->setStatus('FAIL');
    }
}