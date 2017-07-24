# BullsAndCows
My interpretation of the Bulls and Cows game written in PHP.

You can view a live demo at <http://bullsandcows.dotthis.co.uk> or run an automatic demo of 15 random codes being cracked <http://bullsandcows.dotthis.co.uk/guesser.php>

## Installation Notes.
This project users PSR and composers Autoloading, so you'll need to run `composer install` if installing this locally.

### Licence
Feel free to modify, edit or use pieces of this project in your own projects. I have heavily commented each file so there should be n confusion as to what's going on.

#### Points of interest
- My [PermutationGenerator](https://github.com/dotthis/BullsAndCows/blob/master/src/Generators/PermutationGenerator.php) class generates all unique permutations or a range of numbers.
- My [BullsAndCowsSolver](https://github.com/dotthis/BullsAndCows/blob/master/src/Solvers/BullsAndCowsSolver.php), used in combination with my [ScoreGenerator](https://github.com/dotthis/BullsAndCows/blob/master/src/Generators/ScoreGenerator.php) and [ScoreJsonModel](https://github.com/dotthis/BullsAndCows/blob/master/src/Models/ScoreJsonModel.php) works well.

## I used the following references when developing this Project.

- https://en.wikipedia.org/wiki/Mastermind_%28board_game%29#Five-guess_algorithm
- http://www.delphiforfun.org/Programs/Download/Mastermind%20Algorithm.doc - Word Doc Download, **__NOT A SITE__**
#### Updates.
I don't plan on maintaining this Project but if you have any questions, or want to suggest improvements. I will do my best to respond.

Thanks!