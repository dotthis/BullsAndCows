<?php
namespace BullsAndCows\Models;

/**
 * Class JsonModel
 * @description Implementing \JsonSerializable here so that we can pass the model
 *              directly to json_encode for parsing as JSON.
 *
 * @package BullsAndCows\Models
 */
class JsonModel implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $status = 'OK';

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @description All JsonModels are very simple, I have used protected
     *              access on all the properties that should be returned
     *              in any JSON response we send.
     *
     * @return array
     */
    public function jsonSerialize() {
        return get_object_vars($this);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return JsonModel
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return JsonModel
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
}