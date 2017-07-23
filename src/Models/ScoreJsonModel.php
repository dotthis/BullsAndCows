<?php
namespace BullsAndCows\Models;

/**
 * Class ScoreJsonModel
 * @package BullsAndCows\Models
 */
class ScoreJsonModel extends JsonModel
{
    /**
     * @var int
     */
    protected $cows = 0;

    /**
     * @var int
     */
    protected $bulls = 0;

    /**
     * @var string
     */
    protected $guess = '';

    /**
     * @return int
     */
    public function getCows()
    {
        return $this->cows;
    }

    /**
     * @param int $cows
     * @return ScoreJsonModel
     */
    public function setCows($cows)
    {
        $this->cows = $cows;
        return $this;
    }

    /**
     * @return int
     */
    public function getBulls()
    {
        return $this->bulls;
    }

    /**
     * @param int $bulls
     * @return ScoreJsonModel
     */
    public function setBulls($bulls)
    {
        $this->bulls = $bulls;
        return $this;
    }

    /**
     * @return string
     */
    public function getGuess()
    {
        return $this->guess;
    }

    /**
     * @param $guess
     * @return $this
     */
    public function setGuess($guess)
    {
        $this->guess = $guess;
        return $this;
    }
}