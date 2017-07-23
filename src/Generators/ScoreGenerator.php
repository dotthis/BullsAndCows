<?php
namespace BullsAndCows\Generators;

use BullsAndCows\Models\ScoreJsonModel;

/**
 * Class ScoreGenerator
 * @package BullsAndCows\Generators
 */
class ScoreGenerator
{
    /**
     * @var array
     */
    private $secret;

    /**
     * @var array
     */
    private $guess;

    /**
     * @var int
     */
    private $cows = 0;

    /**
     * @var int
     */
    private $bulls = 0;

    /**
     * ScoreGenerator constructor.
     * @param array $secret
     * @param array $guess
     */
    public function __construct(array $secret, array $guess) {
        // Convert numeric strings into real integers.
        $this->secret = array_map('intval', $secret);
        $this->guess = array_map('intval', $guess);

        // Work out the score.
        $this->calculateBullsAndCows();
    }

    /**
     * @return ScoreJsonModel
     */
    public function getScore() {
        return (new ScoreJsonModel())
            ->setCows($this->cows)
            ->setBulls($this->bulls)
            ->setGuess($this->guess);
    }

    /**
     * @description This function loops through all values in the supplied guess
     *              and searches the secret code for matching values, if a match
     *              is found we know we have either a Bull or a Cow, so we check
     *              the array keys for matches,  if the keys match then we score
     *              against Bulls if not we increment the score for Cows instead.
     */
    protected function calculateBullsAndCows() {
        foreach($this->guess as $guessKey => $digit) {
            // Do the search, store the key to check for Bulls.
            $secretKey = array_search($digit, $this->secret);
            if ($secretKey !== false) {
                // Well, it's definitely cattle. Bull or Cow? - Ternary.
                $this->{($secretKey == $guessKey) ? 'bulls' : 'cows'}++;
            }
        }
    }
}