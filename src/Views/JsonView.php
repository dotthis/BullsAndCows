<?php
namespace BullsAndCows\Views;

use BullsAndCows\Models\JsonModel;

/**
 * Class JsonView
 * @package BullsAndCows\Views
 * @description A very simple View class that sets the correct JSON header
 *              before creating a valid JSON string from a JsonModel
 *              JsonModels implement JsonSerializable.
 */
class JsonView
{
    /**
     * @var JsonModel
     */
    protected $model;

    /**
     * JsonView constructor.
     * @param JsonModel $model
     */
    public function __construct(JsonModel $model)
    {
        $this->model = $model;
    }

    /**
     * @description Output the correct headers before returning the proposed content.
     * @return string
     */
    public function __toString()
    {
        header('Content-Type: application/json');
        return json_encode($this->model);
    }
}