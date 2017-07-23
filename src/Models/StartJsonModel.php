<?php
namespace BullsAndCows\Models;

/**
 * Class StartJsonModel
 * @package BullsAndCows\Models
 */
class StartJsonModel extends JsonModel
{
    /**
     * @description Potential response messages.
     * @var string[]
     */
    protected $messages = [
        "OK, I'm ready when you are boss",
        "Wow, that number sure took some generating, but I've got one, you'll never guess it.",
        "Right then, the number is ready - you have up to 3024 unique attempts to get through.",
        "I like to moove it, mooove it, youuu like to moove it moooove, it - get guessing, I'm all ready.",
        "Steven's generator can get the non repeatable permutations of any range of numbers, WOW!"
    ];

    /**
     * StartJsonModel constructor.
     */
    public function __construct() {
        // Let's randomise the response message, to add a little fun.
        $this->setMessage($this->messages[array_rand($this->messages)]);
    }
}