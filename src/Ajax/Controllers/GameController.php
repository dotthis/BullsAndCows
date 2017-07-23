<?php
namespace BullsAndCows\Ajax\Controllers;

use BullsAndCows\Generators\PermutationGenerator;
use BullsAndCows\Generators\ScoreGenerator;
use BullsAndCows\Models\JsonModel;
use BullsAndCows\Models\StartJsonModel;
use BullsAndCows\Solvers\BullsAndCowsSolver;
use BullsAndCows\Views\JsonView;

/**
 * Class GameController
 * @package BullsAndCows\Ajax\Controllers
 * @description This class acts as the main controller for both the
 *              My Guess and Your Guess versions of the Bulls and Cows Game
 *              It returns AJAX responses only.
 */
class GameController
{
    /**
     * @var array
     * @description A measure against attempting to invoke methods in the proper manner.
     *              This array is checked before any reuest handling code is run.
     */
    private $allowedActions = [
        'start',
        'guess',
        'newGuessingGame',
        'getGuess',
    ];

    /**
     * GameController constructor.
     * @description on Instantiation we check to see whether the requested action is allowed
     *              we do not instantiate a session or do any work until this is verified.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        // Check the validity of the request
        if (!(isset($_GET['action']) && in_array($_GET['action'], $this->allowedActions))) {
            throw new \Exception('Action not recognised.');
        }

        // Start a session.
        session_start();

        /*
         * This sets the name of the function to be handled during this request.
         * ... this further allows us to ensure only actions we know about, get called.
         */
        $this->action = ucfirst($_GET['action']);
    }

    /**
     * @description Call the requested actions handler.
     */
    public function handleRequest() {
        $this->{'handle'.$this->action}();
    }

    /**
     * @description This handler is responsible for initialising a new Your Guess
     *              game where the User tries to guess a generated code.
     */
    private function handleStart() {
        // Think of a 4 digit non repeating permutation using digits 1 - 9.
        $generator = new PermutationGenerator(range(1,9), 4);
        $generator->calculatePermutations();

        // Store the generated randomly selected code in the $_SESSION
        // ... so we can access it between Ajax requests.
        $_SESSION['BullsAndCowsSelection'] = $generator->selectAtRandom();

        // Output a simple JSON response with a message saying we're ready to go.
        echo new JsonView(new StartJsonModel());
    }

    /**
     * @description This handler is responsible for checking and scoring
     *              the User's Guess
     *
     * @throws \Exception
     */
    private function handleGuess() {
        // Validate the input, we want 4 numbers, in an array.
        if (
            isset($_GET['guess']) &&
            is_array($_GET['guess']) &&
            array_filter($_GET['guess'], 'is_numeric') === $_GET['guess']
        ) {
            /*
             * The string value of our code is stored like this: 1-2-3-4, therefore
             * ... we must turn it into an array before scoring, using explode
             */
            $scoreGenerator = new ScoreGenerator(explode('-', $_SESSION['BullsAndCowsSelection']), $_GET['guess']);

            // Output a simple JSON response containing the users cows and bulls score.
            echo new JsonView($scoreGenerator->getScore());
            return;
        }

        throw new \Exception('An invalid Guess was supplied.');
    }

    /**
     * @description Because the Game where we guess the User's number
     *              uses a few $_SESSION variables for storing Object's
     *              and other useful data, we need this handler to clear
     *              the $_SESSION data between games.
     */
    private function handleNewGuessingGame() {
        session_unset();
        session_destroy();

        // Output a simple JSON response.
        echo new JsonView(new JsonModel());
    }

    /**
     * @description This handler takes care of a User's request for another
     *              one of our guesses to find their code.
     *
     *              It is used to initialise the $_SESSION variables when asked
     *              for a first guess and to access the instance of the BullsAndCowsSolver
     *              needed to track guesses for a single code.
     *
     * @throws \Exception
     */
    private function handleGetGuess() {
        /*
         * Validate the input, we want 4 numbers, in an array, we need the User's chosen code to determine the score
         * ... but the Solver never sees this value. Obviously, Because, THAT'S CHEATING! :)
         *
         * ... return $_GET['code']; // Just kidding ;)
         */
        if (
            isset($_GET['code']) &&
            is_array($_GET['code']) &&
            array_filter($_GET['code'], 'is_numeric') === $_GET['code']
        ) {
            /*
             * If we don't already have a Guess stored, then this is a new game, or we somehow dropped the session.
             * ... We should reinitialise everything so the game won't error due to attempted guesses on missing data.
             */
            if (!isset($_SESSION['BullsAndCowsMyGuess'])) {
                // First we should see the permutation generator with the 3024 possible codes the User could enter.
                $permutationGenerator = (new PermutationGenerator(range(1,9), 4))->calculatePermutations();

                /*
                 * By passing the $permutationGenerator to the BullsAndCowsSolver object we are able to maintain a
                 * ... single instance of the Generator and Solver objects for the length of the game
                 */
                $_SESSION['BullsAndCowsSolver'] = new BullsAndCowsSolver($permutationGenerator);

                // This gets the first guess, which is generated inside the Solver object upon instantiation
                $_SESSION['BullsAndCowsMyGuess'] = $_SESSION['BullsAndCowsSolver']->getGuess();
            } else {
                /*
                 * If we already have our game in play, we need to set the guess to the Solvers next attempt.
                 * ... We should pass in the score from the last guess as the Solver needs this to reduce its
                 * ... list of potential guesses and return a new guess with a good probability of being correct.
                 */
                $_SESSION['BullsAndCowsMyGuess'] = $_SESSION['BullsAndCowsSolver']->getNextGuess(
                    $_SESSION['BullsAndCowsMyScore']
                );
            }

            /*
             * We work out the score for the latest guess and return it so that the User can see how we're doing.
             * ... and can decide whether to keep letting the Solver make guesses, I preferred this approach to
             * ... having the user manually enter the number of cows and bulls each guess yielded, this could be
             * ... added as an improvement.
             */
            $_SESSION['BullsAndCowsMyScore'] = (new ScoreGenerator(
                $_GET['code'], explode('-', $_SESSION['BullsAndCowsMyGuess'])
            ))->getScore();

            // Return a simple JSON response containing the Score.
            echo new JsonView($_SESSION['BullsAndCowsMyScore']);
            return;
        }

        throw new \Exception('An invalid Guess was supplied.');
    }
}