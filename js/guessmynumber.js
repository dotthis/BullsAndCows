// Isolate the scope.
(function ($) {
    // Create a new jQuery plugin to house the Game.
    $.fn.bullsAndCows = function() {

        // Store the 4 digit guess.
        var code = [];

        // These are all the possible messages Mr Bull could say.
        var messages = {
            'working': [
                "I'm just going to check that for M'yooooooo",
                "Why do cows wear bells? Because their horns don't work!! Be right back",
                "Wow, nice guess, but is it right? I'll go and see...",
                "I know it's a fun game, but there's no need to milk it. Brb",
                "What do you call a sleeping bull? A bulldozer! Haha, erm oh yeah, your guess, one mooment...",
                "Just gunna check on that, no, no I'm still here, I'm just ca-mooo-flauged."
            ],
            'nomatch': [
                "So close, but no. I'm really sorry, you'll have to try again.",
                "I'm not making it difficult on purpose, there's only 3024 choices, it's not THAT hard.",
                "Moovin on up, Mooooovin on out. Hi, I'm back - That guess was wrong i'm afraid.",
                "Computer says no, I'd say yes if i could, better luck next time!",
                "That's not right i'm afraid, but if it were, you'd have won by now!"
            ],
            'correct': [
                "FOUR BULLS!!!! YOU ONLY GOT IT RIGHT!! NICE WORK!!",
                "Well played, worthy adversary, but how are you at Chess?",
                "You did it, you diiiid it, oh yeah yeah yeah...",
                "Congratmooolations, and celmoooobrations, That coulda have been cowtastrofic, but it wasn't."
            ],
            'error': [
                'Well this is embarrassing, I seem to have lost the server - Erm, try again?'
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

        // A flag to say whether we are displaying a score, or not.
        var scoreDisplayed = false;

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
        // ... Resets the input if required after previous
        // ... Entry has been scored, so we can enter a new code.
        // ... Enables Guess button on 4th digit entry.
        var inputNumber = function (number) {
            if (scoreDisplayed) {
                reset();
            }

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
                $guess.prop('disabled', false);
            }

            $digits.removeClass('digit-error');
            digits[currentPosition++].html('<p>' + number + '</p>');
        };

        // Handle deletion of digits to rectify mistakes.
        // ... Disables the Guess button.
        // ... Responds to Backspace key and back arrow button click.
        var deleteLatestNumber = function () {
            if (scoreDisplayed) {
                reset();
            }

            if (currentPosition > 5) {
                currentPosition = 5;
            }

            if (currentPosition <= 1) {
                currentPosition = 1;
                return;
            }

            code.pop();
            $guess.prop('disabled', true);

            $digits.removeClass('digit-error');
            digits[--currentPosition].html('<p>0</p>').removeClass('digit-error');
        };

        // Call the server and generate a new number.
        // Reset all the game variables.
        // ... Start the new game.
        var start = function () {
            $.ajax({
                url: "ajax.php",
                data: {
                    action: "start"
                },
                success: function (data) {
                    if (data.status == 'OK') {
                        setMessage(data.message);
                        setUp();
                        reset();
                        $guesses.html('');
                    }
                }
            });
        };

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
            $guess.prop('disabled', true);
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

                scoreDisplayed = false;
            } else {
                scoreDisplayed = true;
            }

            scoreBoard.cows.attr('class', 'cows ' + scores[score.cows]);
            scoreBoard.bulls.attr('class', 'bulls ' + scores[score.bulls]);
        };

        // Sends a guess to the server to be checked against the generated
        // ... secret code, if the request ws successful we update the
        // ... scoreboard and add the guess to the guess list.
        // ... if the score is 4 Bulls the game is won, so we end the game.
        var guess = function () {
            // Do ajax, get message back like this -
            $.ajax({
                url: "ajax.php",
                data: {
                    action: "guess",
                    guess : code
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

                        $guess.prop('disabled', true);
                        $guesses.append(
                            '<span class="label label-primary">' +
                                '[Code: ' + code.join('-') + ', Cows: ' + data.cows + ', Bulls: ' + data.bulls + ']' +
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