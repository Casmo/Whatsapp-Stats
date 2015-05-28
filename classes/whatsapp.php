<?php

/**
 * File whatsapp.php
 * @author Mathieu de Ruiter <www.fellicht.nl>
 */
class Whatsapp
{
    private $_words = array(); // word => count
    private $_names = array(); // name => count
    private $_times = array('00' => 0, '01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0, '07' => 0, '08' => 0, '09' => 0, '10' => 0,
        '11' => 0, '12' => 0, '13' => 0, '14' => 0, '15' => 0, '16' => 0, '17' => 0, '18' => 0, '19' => 0, '20' => 0, '21' => 0, '22' => 0, '23' => 0);
    private $_messages = array(); // list with messages

    private $_settings = array(
        'wordLimit' => 500,
        'wordLengthLimit' => 0,
        'multipliFactor' => 2.5, // Each word-size percent will be multiplied by this number. Play around with it to get best effect in combination with wordLimit and wordLengthLimit. 500 / 0 / 5 works good.
        'minimumPercent' => 4,
        'bannedWords' => array('someword', 'anotherword', 'media', 'weggelaten', 'de', 'het', 'een', 'die', 'dat', 'en'),
        'sortBySize' => false // set to true to have big words in center and small words in outer circle
    );

    public function __construct($settings = array())
    {
        $this->_settings = array_merge($this->_settings, $settings);
    }

    private $_matchLine = '/([^-]+)( - )([^:]+)(: )(.+)/i'; // <date> - <name> : <message>

    public function readFile($filename)
    {
        if (is_file($filename)) {

            $lines = file($filename);
            $count = 0;
            foreach ($lines as $i => $line) {
                $count++;
                if (preg_match($this->_matchLine, $line, $parts)) {

                    $name = $parts[3];
                    $message = $parts[5];
                    $date = $parts[1];
                    preg_match('/([0-9]{2}):([0-9]{2})/', $date, $timeParts);
                    $hour = $timeParts[1];
                    $this->_times[$hour]++;
                    $cleanMessage = preg_replace('/[^[:space:]A-Z]+/i', '', strtolower($message));
                    $words = preg_split('/[[:space:]]+/', $cleanMessage);
                    foreach ($words as $word) {
                        if (empty($word) || strlen($word) <= $this->_settings['wordLengthLimit'] || in_array($word, $this->_settings['bannedWords'])) {
                            continue;
                        }
                        if (!isset($this->_words[$word])) {
                            $this->_words[$word] = 0;
                        }
                        $this->_words[$word]++;
                    }
                    if (!isset($this->_names[$name])) {
                        $this->_names[$name] = 0;
                    }
                    $this->_names[$name]++;
                    $this->_messages[] = $parts[5];
                }

            }
            return true;
        }
        return false;
    }

    /**
     * Returns list with all words and counter of each word
     * @return array
     */
    public function getWordList()
    {
        if ($this->_settings['wordLimit'] > 0) {
            $words = $this->_words;
            arsort($words);
            $newWords = array();
            $total = 0;
            foreach ($words as $word => $count) {
                $newWords[$word] = $count;
                $total++;
                if ($total >= $this->_settings['wordLimit']) {
                    break;
                }
            }
            $newWords = $this->_fixBigCloud($newWords);
            return $newWords;
        }
        return $this->_words;
    }

    private function _fixBigCloud($wordCloud) {
        $hundredPercent = $wordCloud[key($wordCloud)];
        foreach ($wordCloud as $word => $count) {
            $percent = round(100 / $hundredPercent * $count, 3);
            while ($percent < $this->_settings['minimumPercent']) {
                $percent *= 10;
            }
            $percent *= $this->_settings['multipliFactor'];
            $wordCloud[$word] = $percent;
        }
        if ($this->_settings['sortBySize'] == false) {
            $this->ashuffle($wordCloud);
        }
        return $wordCloud;

    }

    /**
     * Returns list with all names and counter of that person has spoken
     * @return array
     */
    public function getNameList()
    {
        return $this->_fixBigCloud($this->_names);
    }

    public function getTimeList()
    {
        return $this->_times;
    }

    function ashuffle(&$arr)
    {
        uasort($arr, function ($a, $b) {
            return rand(-1, 1);
        });
    }

    /**
     * Returns a list with most used words
     *
     * @param int $limit the limit of words to return
     * @param int $wordLimit the minimum size of a word
     */
    public function commonWords($limit = 0, $wordLimit = 0)
    {


    }


}