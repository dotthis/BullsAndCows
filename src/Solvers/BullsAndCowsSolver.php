<?php
namespace BullsAndCows\Solvers;

use BullsAndCows\Generators\PermutationGenerator;
use BullsAndCows\Generators\ScoreGenerator;
use BullsAndCows\Models\ScoreJsonModel;

/**
 * Class BullsAndCowsSolver
 * @package BullsAndCows\Solvers
 */
class BullsAndCowsSolver
{
    /**
     * @var array
     */
    protected $permutationsRemaining = [];

    /**
     * @var PermutationGenerator
     */
    protected $permutationGenerator;

    /**
     * @var array
     */
    protected $guesses = [];

    /**
     * @var string
     */
    protected $guess;

    /**
     * BullsAndCowsSolver constructor.
     * @description Upon instantiation, we store all available permutations in an array
     *              so that we can reduce its content during our search, without affecting
     *              the full set in the generator.
     *
     *              We also generate our first guess at random here.
     *
     * @param PermutationGenerator $permutationGenerator
     */
    public function __construct(PermutationGenerator $permutationGenerator) {
        $this->permutationGenerator = $permutationGenerator;
        $this->permutationsRemaining = $this->permutationGenerator->getPermutations();
        $this->setGuess($this->permutationGenerator->selectAtRandom()); // Random seed for first guess.
    }

    /**
     * @description A standard setter, with an additional line that adds each guess to an array
     *              of guesses uses to determine if a ptential guess has already been tried.
     *
     * @param $guess
     */
    public function setGuess($guess) {
        $this->guess = $guess;
        $this->guesses[] = $this->guess;
    }

    /**
     * @return string
     */
    public function getGuess() {
        return $this->guess;
    }

    /**
     * @description Using a score object this function first reduces the array to the remaining possible permutations
     *              and then selects the next guess from the result, before returning it.
     *
     * @param ScoreJsonModel $score
     * @return string
     */
    public function getNextGuess(ScoreJsonModel $score) {
        $this->reducePermutationsRemainingByScore($score);
        $this->setGuess(array_shift($this->permutationsRemaining));
        return $this->getGuess();
    }

    /**
     * @see https://en.wikipedia.org/wiki/Mastermind_%28board_game%29#Five-guess_algorithm
     * @see http://www.delphiforfun.org/Programs/Download/Mastermind%20Algorithm.doc
     *
     * @description Using the above site and document as references, I wrote the following function
     *              to accept the last guess's score and use it to find all other remaining permutations
     *              that return the same score when scored against the last guess.
     *
     *              This method of reduction ensures that we retain all permutations containing
     *              matching numbers from the real code and discard the rest.
     *
     * @param ScoreJsonModel $score
     */
    private function reducePermutationsRemainingByScore(ScoreJsonModel $score) {
        foreach ($this->permutationsRemaining as $key => $remaining) {
            // Generate a new score for the current permutation against the last guess we attempted.
            $newScore = (new ScoreGenerator(explode('-', $this->guess), explode('-', $remaining)))->getScore();
            if ($newScore->getBulls() == $score->getBulls() && $newScore->getCows() == $score->getCows() && !in_array($remaining, $this->guesses)) {
                // We can keep this one.
                continue;
            } else {
                // Remove if the score is not equal to our latest attempt.
                unset ($this->permutationsRemaining[$key]);
            }
        }
    }
}