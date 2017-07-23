// Isolate the scope.
(function ($) {
    // Create a new jQuery plugin to house the Game.
    $.fn.bullsAndCows = function() {

        // Store the 4 digit code.
        var code = [];

        // These are all the possible messages Mr Bull could say.
        var messages = {
            'start' : [
                "OK, I'm ready to start guessing, I've warmed up the server and awaiting your number ..... we're off!",
                "Time to think of a difficult number. Then just click on that big get my guess button and see if I can do it.",
                "You'd think I'd use a computer algorithm for this, I'm actually using a Hamster that's well versed in mathematics."
            ],
            'working': [
                "I'm just going to check that for M'yooooooo",
                "Why do cows wear bells? Because their horns don't work!! Be right back",
                "I know it's a fun game, but there's no need to milk it. Brb",
                "What do you call a sleeping bull? A bulldozer! Haha, erm oh yeah, my guess, one mooment...",
                "Just gunna check on that, no, no I'm still here, I'm just ca-mooo-flauged."
            ],
            'nomatch': [
                "Alright, alright, I've not been sleeping very well lately, that's why I couldn't find your number!",
                "I've looked everywhere, it's just not there...",
                "Permutate damn you ... perrrrrrmooooooootaaate!",
                "It's not my fault I didn't get it, I {Insert suitably lame excuse here}. "
            ],
            'correct': [
                "Hey, looks like I guessed your number! Mooooarvellous",
                "Wow, beginners luck? Bet i couldn't do that again",
                "To be honest, I'm just as shocked that I got it right as you are."
            ],
            'error': [
                'Well this is embarrassing, I seem to have lost the server....'
            ]
        };

        // Helper methods for getting and setting messages in Mr Bull's speech bubble
        var getMessage = function (type) {
            return messages[type][Math.floor(Math.random() * messages[type].length)];
        };

        var setMessage = function (message) {
            $message.text(message);
        };

        // Store the current cursor position, this aids typing and deleting the 4 digit code.
        var currentPosition = 1;

        // Pre wrapped for jQuery goodness.
        var $window = $(window);
        var $document = $(document);

        // Wrap commonly used elements in jQuery for convenience.
        var $codeView         = this.find('#code-view');
        var $digits           = $codeView.find('.digit');
        var $buttons          = this.find('button[id!=start]');
        var $playagainwrapper = this.find('#playagainwrapper');
        var $restart          = this.find('#restart');
        var $guess            = this.find('#guess');
        var $lock             = this.find('#lock');
        var $guesses          = this.find('#guesses');
        var $message          = this.find('#message');
        var $mrbull           = this.find('#mrbull');


        // Score classes, these determine how many Cows or Bulls
        // ... are shown when added to the score board.
        var scores = ['', 'one', 'two', 'three', 'four'];

        // Store jQuery references to both Cows and Bulls
        // ... portions of the Scoreboard in an object to
        // ... group them together
        var scoreBoard = {
            cows: this.find(".score .cows"),
            bulls: this.find(".score .bulls")
        };

        // Store jQuery wrapped references to the Digit display
        // ... elements in an object for easy access later.
        var digits = {};
        $digits.each(function (i, el) {
            var $el = $(el);
            digits[$el.data('position')] = $el;
        });

        // Handle inputting a number into the Digit display.
        // ... Responds to key down events on the number pad
        // ... if num lock is on, the normal number keys, and
        // ... click events on the number buttons.
        // ... Prevents and warns of duplicate number entry.
        // ... Enables lock button on 4th digit entry.
        var inputNumber = function (number) {
            if (currentPosition > 4) {
                return;
            }

            // Check if the number is valid.
            if (code.indexOf(number) != -1) {
                digits[currentPosition].addClass('digit-error');
                return
            }

            code.push(number);
            if (code.length == 4) {
                $lock.prop('disabled', false);
            }

            $digits.removeClass('digit-error');
            digits[currentPosition++].html('<p>' + number + '</p>');
        };

        // Handle deletion of digits to rectify mistakes.
        // ... Disables the Lock button.
        // ... Responds to Backspace key and back arrow button click.
        var deleteLatestNumber = function () {
            if (currentPosition > 5) {
                currentPosition = 5;
            }

            if (currentPosition <= 1) {
                currentPosition = 1;
                return;
            }

            code.pop();
            $lock.prop('disabled', true);
            $digits.removeClass('digit-error');
            digits[--currentPosition].html('<p>0</p>').removeClass('digit-error');
        };

        // Call the server and set up a new Guess Your Number session.
        // ... Reset all the game variables.
        // ... Start the new game.
        var start = function () {
            setUp();
            reset();
            $.ajax({
                url: "ajax.php",
                data: {
                    action: "newGuessingGame",
                },
                success: function (data) {
                    if (data.status == 'OK') {
                        setMessage(getMessage('start'));
                    }
                }
            });
        };

        // Remove all events.
        // ... disable all other buttons.
        // ... enable the Get Guess button.
        var lock = (function() {
            this.off("click");
            this.on("click", "#guess", guess);

            $window.off("keyup");
            $buttons.prop('disabled', true);
            $guess.prop('disabled', false);
        }).bind(this);

        // Remove all Events.
        // ... Enable the restart button
        // ... Add restart click handler.
        // ... Show the end of game screen.
        var endGame = (function() {
            this.off("click");
            this.on("click", "#restart", restart);

            $window.off("keyup");
            $buttons.prop('disabled', true);
            setTimeout(function () {
                $playagainwrapper.fadeIn();
                $restart.prop('disabled', false);
            }, 3000);
        }).bind(this);

        // Exactly what it says on the tin.
        var restart = function() {
            $playagainwrapper.fadeOut(500, start);
        };

        // Reset game vars and Element properties to defaults.
        var reset = function () {
            code = [];
            currentPosition = 1;
            $digits.html('<p>0</p>').removeClass('digit-error');
            $buttons.prop('disabled', false);
            $lock.prop('disabled', true);
            $guess.prop('disabled', true).text('GET MY FIRST GUESS');
            $guesses.html('');
            updateScoreBoard();
        };

        // Update the score board to display the number of
        // ... Bulls and Cows the latest guess yielded.
        // ... if score is not sent in, the board is cleared.
        var updateScoreBoard = function (score) {
            if (typeof score == 'undefined') {
                score = {
                    cows: 0,
                    bulls: 0
                };
            }

            scoreBoard.cows.attr('class', 'cows ' + scores[score.cows]);
            scoreBoard.bulls.attr('class', 'bulls ' + scores[score.bulls]);
        };

        // Sends a request for us to ake a new guess from the server
        // ... to be checked against the User's selected secret code,
        // ... if the request was successful we update the
        // ... scoreboard with our latest score and add our guess to
        // ... the guess list. If the score is 4 Bulls the game is won,
        // ... so we end the game.
        var guess = function () {
            $guess.text('GET MY NEXT GUESS');

            $.ajax({
                url: "ajax.php",
                data: {
                    action: "getGuess",
                    // This is ONLY sent to predetermine a score to simplify the UI
                    // It also removes the element of human error
                    // it's not used when solving the code, that would be cheating!!
                    code : code
                },
                success: function (data) {
                    if (data.status == 'OK') {

                        updateScoreBoard({
                            cows : data.cows,
                            bulls : data.bulls
                        });

                        var isWinner = (data.bulls == 4);
                        setMessage(getMessage(isWinner ? 'correct' : 'nomatch'));
                        if (isWinner) {
                            endGame();
                        }

                        $guesses.append(
                            '<span class="label label-primary">' +
                                '[Code: ' + data.guess.join('-') + ', Cows: ' + data.cows + ', Bulls: ' + data.bulls + ']' +
                            '</span> &nbsp;'
                        );
                    } else {
                        setMessage(data.message);
                    }
                }
            });
        };

        // Disable all buttons initially.
        $buttons.prop('disabled', true);

        // Add default Ajax event handlers.
        $document.ajaxSend(function () {
            setMessage(getMessage('working'));
            $mrbull.fadeOut();
        }).ajaxComplete(function () {
            $mrbull.fadeIn();
        }).ajaxError(function () {
            setMessage(getMessage('error'));
            $mrbull.fadeIn();
        });

        // We can't add this event in setUp because it calls setUp.
        this.on("click", "#start", start);

        // The setup function handles the assignment and delegation of all required events.
        var setUp = (function() {
            this.on("click", "#backspace", function (e) {
                e.stopPropagation();
                e.preventDefault();

                deleteLatestNumber();
            });

            this.on("click", "#lock", lock);
            this.on("click", "#guess", guess);
            this.on("click", "#code-input .number-key", function (e) {
                e.stopPropagation();
                e.preventDefault();

                inputNumber(parseInt($(this).data('number')));
            });

            $window.on("keyup", function (e) {
                e.stopPropagation();
                e.preventDefault();

                switch (e.keyCode) {
                    // 1
                    case 49:
                    case 97:
                        inputNumber(1);
                        break;

                    // 2
                    case 50:
                    case 98:
                        inputNumber(2);
                        break;

                    // 3
                    case 51:
                    case 99:
                        inputNumber(3);
                        break;

                    // 4
                    case 52:
                    case 100:
                        inputNumber(4);
                        break;

                    // 5
                    case 53:
                    case 101:
                        inputNumber(5);
                        break;

                    // 6
                    case 54:
                    case 102:
                        inputNumber(6);
                        break;

                    // 7
                    case 55:
                    case 103:
                        inputNumber(7);
                        break;

                    // 8
                    case 56:
                    case 104:
                        inputNumber(8);
                        break;

                    // 9
                    case 57:
                    case 105:
                        inputNumber(9);
                        break;

                    // backspace.
                    case 8:
                        deleteLatestNumber();
                        break;
                }
            });
        }).bind(this); // Bind 'this' so we don't have to create a tunnel variable, like me or self.
    };

    // Start the show!
    $(document).ready(function() {
        $('#content').bullsAndCows();
    });
})(jQuery);