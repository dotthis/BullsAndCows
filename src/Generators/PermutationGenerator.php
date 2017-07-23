<?php
namespace BullsAndCows\Generators;

/**
 * Class PermutationGenerator
 * @description This class currenty only creates permutations of sequential numbers,
 *              It an potentially be generalised to permutate any set of things.
 *
 * @package BullsAndCows\Generators
 */
class PermutationGenerator
{
    /**
     * @var array
     */
    protected $numberChoices = [];

    /**
     * @var int
     */
    protected $length = 0;

    /**
     * @var int
     */
    protected $min = 0;

    /**
     * @var int
     */
    protected $max = 0;

    /**
     * @description The array representation of the current permutation e.g. [1, 2, 3, 4].
     * @var array
     */
    protected $columns = [];

    /**
     * @var int
     */
    protected $sequenceColumnKey = 0;

    /**
     * @var int
     */
    protected $maxPermutations = 0;

    /**
     * @var array
     */
    protected $permutations = [];

    /**
     * PermutationGenerator constructor.
     * @param array $numberChoices
     * @param int $length
     * @throws \Exception
     */
    public function __construct(array $numberChoices = [], $length = 4)
    {
        // This uses recursion extensively, tell xdebug, not to shut it down.
        ini_set('xdebug.max_nesting_level', 0);

        // Make sure that we have a valid set of numbers and the length is something we can use.
        if (!$this->validate($numberChoices, $length)) {
            throw new \Exception(
                'Unable to create permutations, please ensure your number choice array contains valid '.
                'integers and that your required length is an integer greater than 0 and less than or equal to the number of choices.'
            );
        }

        // Intialise necessary variables.
        $this->setNumberChoices($numberChoices)
             ->setLength($length)
             ->setMinMax()
             ->setMaxPermutations()
             ->setSequenceColumnKey()
             ->initialisePermutations();
    }

    /**
     * @return int
     */
    public function getMaxPermutations() {
        return $this->maxPermutations;
    }

    /**
     * @return array
     */
    public function getPermutations() {
        return $this->permutations;
    }

    /**
     * @return int
     */
    public function getLength() {
        return $this->length;
    }

    /**
     * @description This is the main point of the Permutation generator. It loads a sequence
     *              of numbers not present in previous columns and then loops through the
     *              sequence to populate the final column of the columns array and then saves
     *              those columns to the permutations array.
     *
     *              e.g     columns = [1, 2, 3];
     *                     sequence = [4, 5, 6, 7, 8, 9];
     *
     *              The loop then generates - [1, 2, 3, 4]
     *                                        [1, 2, 3, 5]
     *                                        [1, 2, 3, 6]
     *                                        [1, 2, 3, 7]
     *                                        [1, 2, 3, 8]
     *                                        [1, 2, 3, 9]
     *
     *              Once it's at the end of the sequence it unsets the sequence
     *              column in the current permutation before attempting to increment
     *              permutation columns that preceded it, if there are no more increments
     *              to be made e.g. the permutation is [9, 8, 7, 6] then the loop is terminated.
     *
     *              If there are more permutations to be made, the prior columns are updated
     *              accordingly, and the calculatePermutations function is recursively called.
     *
     * @return $this
     */
    public function calculatePermutations() {
        $sequenceItems = $this->getSequence();

        foreach ($sequenceItems as $sequenceItem) {
            $this->columns[$this->sequenceColumnKey] = $sequenceItem;
            $this->addCurrentPermutation();
        }

        // Increment column reset start position and start again.
        unset($this->columns[$this->sequenceColumnKey]);
        if ($this->updateColumns()) {
            $this->calculatePermutations();
        }

        return $this;
    }

    /**
     * @description returns a random permutation without disordering the permutations array.
     * @return string
     */
    public function selectAtRandom() {
        $permutations = $this->permutations;

        // Shuffle a copy, don't ruin the original ordering.
        shuffle($permutations);

        // Return a random item.
        return $permutations[array_rand($permutations)];
    }

    /**
     * @param array $numberChoices
     * @return $this
     */
    protected function setNumberChoices(array $numberChoices) {
        $this->numberChoices = $numberChoices;
        return $this;
    }

    /**
     * @description Stores the lowest and the highest number in the choices array,
     *              min is used when working out the starting permutation,
     *              and I added max incase I needed it; I didn't.
     *
     * @return $this
     */
    protected function setMinMax() {
        if (!empty($this->numberChoices)) {
            $this->min = min($this->numberChoices);
            $this->max = max($this->numberChoices);
        }

        return $this;
    }

    /**
     * @param $length
     * @return $this
     */
    protected function setLength($length) {
        $this->length = $length;
        return $this;
    }

    /**
     * @description This function sets the factorial result of the
     *              number of choices given to the Permutation Generator
     *              so that we can know how many permutations there are
     *              without first creating and counting them.
     *
     * @return $this
     */
    protected function setMaxPermutations() {
        if (!empty($this->numberChoices) && !empty($this->length)) {
            $maxNumberOfChoices = count($this->numberChoices);
            $numberOfPermutations = $maxNumberOfChoices;

            for ($i = 1; $i < $this->length; $i++) {
                $numberOfPermutations *= ($maxNumberOfChoices - $i);
            }

            $this->maxPermutations = $numberOfPermutations;
        }

        return $this;
    }

    /**
     * @description The 'sequence' is the last column of the permutation array,
     *              as it increments most frequently. This function saves it's
     *              array index for quick access later.
     *
     * @return $this
     */
    protected function setSequenceColumnKey() {
        $this->sequenceColumnKey = $this->length-1;
        return $this;
    }

    /**
     * @description Adds a string representation of the current columns array
     *              to the array of permutations.
     */
    protected function addCurrentPermutation() {
        $this->permutations[] = implode('-', $this->columns);
    }

    /**
     * @return $this
     */
    protected function initialisePermutations() {
        // We don't need the sequence column at this point. Hence the -2.
        $this->columns = range($this->min, $this->numberChoices[$this->length-2]);
        return $this;
    }

    /**
     * @return array
     */
    protected function getSequence() {
        return array_diff($this->numberChoices, $this->columns);
    }

    /**
     * @param $numberChoices
     * @param $length
     * @return bool
     */
    protected function validate(&$numberChoices, $length) {
        // We expect $length to be an integer greater than 0.
        $valid = (is_int($length) && $length > 0);

        // We expect The $numberChoices to be an array of integers.
        $valid = $valid && (is_array($numberChoices) && array_filter($numberChoices, 'is_int') === $numberChoices);

        // Make sure there are enough numberChoices to ceate the desired length of permutations.
        $valid = $valid && count($numberChoices) >= $length;

        // If the type and checking is fine, check the numberChoice values for a valid incremental sequence.
        if ($valid) {
            // Strip out duplicated and sort the numbers in ascending numerical order.
            $numberChoices = array_unique($numberChoices);
            sort($numberChoices);

            // we need this to ensure we don't compare the ast value to a non existent array element
            end($numberChoices);
            $lastIndex = key($numberChoices);

            // reset pointer, were going to iterate.
            reset($numberChoices);

            foreach ($numberChoices as $key => $currentValue) {
                // Are we at the end?
                if ($key != $lastIndex) {
                    // No, $valid is currently 'true' no need to update unless we find an invalid number.
                    $nextValue = $numberChoices[$key + 1];
                    if ($nextValue !== $currentValue + 1) {
                        // If we get here we encountered a non sequential number,
                        // ... no point carrying on, validation failed.
                        $valid = false;
                        break;
                    }
                }
            }
        }

        return $valid;
    }

    /**
     * @description This function updates columns that have not reached their last available
     *              value.
     *
     *              First we loop through each column; backwards to maintain an ascending order.
     *
     *              We then work out if there are any values to move to by finding all the numbers
     *              after the current one and removing all values from it that appear in preceeding columns.
     *
     *              e.g. if we have [8,9,5] as our current columns array we check each value from 5 backwards.
     *                   i.e we have 5, so we look in the numberChoices for items beyond 5.
     *
     *                   number choices = [1, 2, 3, 4, 5, 6, 7, 8, 9]
     *                        available = [6, 7, 8, 9]
     *
     *                   we then remove the numbers appearing in columns preceeding the 5, from those available.
     *
     *                       preceeding = [8, 9]
     *                        available = [6, 7]
     *
     *                   If available is not empty, we set the column we're updating (5) to the first value in
     *                   available [6].
     *
     *              After we've updated the column we need to reset the rest of the columns AFTER the one
     *              we just updated, UNLESS the one we just updated was the last column before the sequence column.
     *
     *              e.g. If we had found that our columns were [8, 9, 7] We'd have to update the '8' to '9' using the
     *                   above process and then reset 9 and 7, to the first two available values i.e. [1, 2] leaving
     *                   [9, 1, 2] and would return to calculatePermutations, which would then generate the sequence
     *                   [3, 4, 5, 6, 7, 8] to carry on it's job.
     *
     *              Once this function reaches the highest value on the lowest column, it returns false, and the job
     *              is done!
     *
     *
     * @return bool
     */
    protected function updateColumns() {
        // Flag to stop at the highest number of the 0th column.
        $continue = false;

        // Find the column we are updating, work backwards.
        $lastColumnBeforeSequenceIndex = $this->sequenceColumnKey-1;
        for ($i = $lastColumnBeforeSequenceIndex; $i > -1; $i--) {
            // Work out if there is a next value.
            $remainingIncrements = array_diff(
                array_slice(
                    $this->numberChoices,
                    array_search($this->columns[$i],
                    $this->numberChoices)+1
                ),
                array_slice($this->columns, 0, $i+1)
            );

            // Update the value.
            if (!empty($remainingIncrements)) {
                $this->columns[$i] = current($remainingIncrements);
                $this->columns = array_slice($this->columns, 0, $i + 1);
                $continue = true;
                break;
            }
        }

        if ($continue) {
            if ($i !== $lastColumnBeforeSequenceIndex) {
                // Set Columns from $i onwards, to their next available values.
                $defaults = $this->getSequence();

                for ($j = $i+1; $j <= $lastColumnBeforeSequenceIndex; $j++) {
                    $this->columns[$j] = current($defaults);
                    next($defaults);
                }
            }

            // Carry on calculating.
            return true;
        }

        // We're done.
        return false;
    }
}