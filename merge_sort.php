<?php

/**
 * Perform a merge sort on individual lines within a text file.
 */
class FileMergeSort {
    /**
     * A handle to the input file.
     *
     * @var resource
     */
    protected $inputFile;

    /**
     * Keep our timing log, ready to be printed at the end of execution.
     *
     * @var string
     */
    protected $log = '';

    /**
     * The number of elements that should be merged together before reporting in the log.
     *
     * Note that the merge recursion means that the number returned by merge() will be going up and down, but the FIRST
     * time the number in $reportIntervals is reached, it will be reported upon.
     *
     * @var int[]
     */
    protected $reportIntervals = [10, 30, 100, 300, 1000, 3000, 10000, 30000, 100000, 300000, 1000000];

    /**
     * Used to point at the next interval in $reportIntervals.
     *
     * If $reportPointer is 0, it will wait until merge() returns an array of at least 10 elements, and if
     * $reportPointer is 1, it will wait until merge() contains an array of at least 30 elements before reporting.
     *
     * @var int
     */
    protected $reportPointer = 0;

    /**
     * A safety switch in case we run out of elements in $reportIntervals and we need to turn off reporting.
     *
     * @var bool
     */
    protected $reporting = true;

    /**
     * The time, in microseconds, that the execution started.
     *
     * @var float
     */
    protected $start;

    /**
     * FileMergeSort constructor.
     *
     * @param string $filename
     *   The relative path to the file to be opened.
     */
    public function __construct($filename) {
        // Record the start of execution.
        $this->start = microtime(true);

        // Attempt to open the input file. Suppress errors; we'll deal with this ourselves.
        $this->inputFile = @fopen($filename, "r");

        // End execution if we could not read the file for some reason.
        if ($this->inputFile === false) {
            die ('Could not open file: ' . $filename);
        }

        // Turn the entire file into an array to start with.
        $array = $this->getArrayFromFile();

        print_r($array);

        print "\n\n";

        // Print the log entries.
        print $this->log;

        // Close the file handle now we are done with it.
        fclose($this->inputFile);

    }

    /**
     * Get the lines from the file as an array.
     *
     * @return string[]
     *   Array of strings, one for each line in the file.
     */
    protected function getArrayFromFile() {
        $array = [];

        // Loop through each line in the file.
        while (!feof($this->inputFile)) {
            // Get the next entire line from the file.
            $word = fgets($this->inputFile);

            // Trim any whitespace from the start and end before adding to the array.
            $trimmed = trim($word);

            // Ensure that we cannot get an empty string (blank line) in the array.
            if (!empty($trimmed)) {
                $array[] = $trimmed;
            }
        }

        // Log the time it took to open and read the file.
        $message = 'Finished reading file: ' . (microtime(true) - $this->start) . "\n";
        $this->log .= $message;
        print $message;

        return $this->mergeSort($array);
    }

    /**
     * Recursive splitter.
     *
     * @param array $input
     *   The array to be split and then have the merge sort performed on it.
     *
     * @return array
     *   The array after having been merged with merge().
     *
     * @see merge()
     */
    protected function mergeSort(array $input) {
        // Find the number of elements in the input array.
        $length = count($input);

        if ($length <= 1) {
            return $input;
        }
        else {
            // Find the nearest full integer to the halfway point.
            $halfway = ceil($length / 2);

            $left = array_slice($input, 0, $halfway);

            // Note we don't specify the last element; it automatically runs to the end of the array.
            $right = array_slice($input, $halfway);

            // Recursion: keep splitting until we end up with a 1-element array and then  use merge().
            return $this->merge($this->mergeSort($left), $this->mergeSort($right));
        }
    }

    /**
     * Perform a merge on left and right array pieces.
     *
     * @param string[] $left
     *   The left array to be merged.
     * @param string[] $right
     *   The right array to be merged.
     *
     * @return string[]
     *   The result of merging the left and right arrays, while ordering them.
     */
    protected function merge(array $left, array $right) {
        $leftSize = count($left);
        $rightSize = count($right);
        $fullSize = $leftSize + $rightSize;

        // Assign some pointers so that we know where we are up to in each array.
        $leftPointer = 0;
        $rightPointer = 0;

        // Create a new array for our merged output.
        $output = [];

        for ($i = 0; $i < $fullSize; $i++) {
            // If we have gone past the end of the left array, take an element from the right array and increment the
            // right pointer.
            if ($leftPointer == $leftSize) {
                $output[] = $right[$rightPointer++];
            }
            // If we have gone past the end of the right array, take an element from the left array and increment the
            // left pointer.
            else if ($rightPointer == $rightSize) {
                $output[] = $left[$leftPointer++];
            }
            // Compare the elements in the left and right arrays at the pointer. Ignore case. Take the one from the left
            // array if it's "before" the right one, and increment the left pointer.
            else if (strcasecmp($left[$leftPointer], $right[$rightPointer]) < 0) {
                $output[] = $left[$leftPointer++];
            }
            // The only remaining option is that the element in the right arary is "before" the left, so use the one in
            // the right array, and increment the right pointer.
            else {
                $output[] = $right[$rightPointer++];
            }
        }

        // If we need to report to the log, do it now. We run the conditional here instead of in its own method, to save
        // on the method call overhead, since this will be executed every time merge() runs.
        if ($this->reporting && count($output) > $this->reportIntervals[$this->reportPointer]) {
            $this->report(count($output));
        }

        return $output;
    }

    /**
     * Determine if we need to
     *
     * @param $size
     */
    protected function report($size) {
        // Add to the log, with the timing information. Also print to the screen so we know what's going on.
        $message = $size . ' elements: ' . (microtime(true) - $this->start) . "\n";
        $this->log .= $message;
        print $message;

        // Increment the reporting pointer, and make sure we don't go beyond the end of the intervals array.
        $this->reportPointer++;

        if (!isset($this->reportIntervals[$this->reportPointer])) {
            // We don't have another interval to use for reporting, so turn off reporting.
            $this->reporting = false;
        }
    }
}

$main = new FileMergeSort('words_random.txt');
