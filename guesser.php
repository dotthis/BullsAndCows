<?php
/**
 * A simple demo script to test the BullsAndCowsSolver guesser over 15 randomly selected codes in order to find the answer.
 */
require_once 'vendor/autoload.php';
$permutationGenerator = (new \BullsAndCows\Generators\PermutationGenerator(range(1,9), 4))->calculatePermutations();

// Generate 15 random secrets to find!
$secrets = array_map([$permutationGenerator, 'selectAtRandom'], array_fill(0, 15, []));
header('Content-type: text/plan');
foreach ($secrets as $secret) {
    echo "======================================================\n";
    echo "              TRYING TO FIND [ $secret ]\n";
    echo "======================================================\n\n";

    $solver = new \BullsAndCows\Solvers\BullsAndCowsSolver($permutationGenerator);
    $guess = $solver->getGuess();
    $secret = explode('-', $secret);
    $score = (new \BullsAndCows\Generators\ScoreGenerator($secret, explode('-', $guess)))->getScore();
    $answer = '';

    // First Guess.
    $i = 1;
    echo " ---- Trying: [ $guess ] [ Bulls : {$score->getBulls()}, Cows: {$score->getCows()} ] ---- \n";

    while ($guess = $solver->getNextGuess($score)) {
        $score = (new \BullsAndCows\Generators\ScoreGenerator($secret, explode('-', $guess)))->getScore();
        $answer = $guess;
        echo " ---- Trying: [ $guess ] [ Bulls : {$score->getBulls()}, Cows: {$score->getCows()} ] ---- \n";
        $i++;
    }

    echo "\n======================================================\n";
    if ($answer === implode('-', $secret)) {
        echo "  Final answer is [ $answer ] found after $i attempts\n";
    } else {
        echo "  It appears as though we could crack the secret code\n";
    }
    echo "======================================================\n\n\n\n";
}