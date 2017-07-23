<?php
require_once 'vendor/autoload.php';
/*
 * Simulate a slower server, should be removed in reality,
 * ... added to allow time to read the various entertaining (??)
 * ... messages Mr Bull says between AJAX requests
 */
sleep(2);
try {
    // Lets see if we can handle this request.
    $controller = new \BullsAndCows\Ajax\Controllers\GameController();
    $controller->handleRequest();
} catch (\Exception $e) {
    /*
     *  Any Exceptions thrown by the controller are caught and
     * ... sent as an AJAX request with a FAIL status.
     */
    echo new BullsAndCows\Views\JsonView(new \BullsAndCows\Models\ErrorJsonModel($e));
}
