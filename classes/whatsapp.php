<?php

/**
 * File whatsapp.php
 * @author Mathieu de Ruiter <www.fellicht.nl>
 */
class Whatsapp
{
    private $_words = array(); // word => count
    private $_names = array(); // name => count
    private $_relations = array(); // name => [friends_name => count]. @see settings activityTime
    private $_minutesNames = array(); // Timestamp => name
    private $_times = array('00' => 0, '01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0, '07' => 0, '08' => 0, '09' => 0, '10' => 0,
        '11' => 0, '12' => 0, '13' => 0, '14' => 0, '15' => 0, '16' => 0, '17' => 0, '18' => 0, '19' => 0, '20' => 0, '21' => 0, '22' => 0, '23' => 0);
    private $_messages = array(); // list with messages

    private $_settings = array(
        'iPhone' => false, // Set to true to fix some iPhone issues.
        'activityTime' => 5, // Time in minutes for relations
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

    private $_matchLineIphone = '/([^A-Z]+)(: )([^:]+)(: )(.+)/i'; //  01-09-15 10:57:30: Dr Joe: Lorum ipsum
    private $_matchLine = '/([^-]+)( - )([^:]+)(: )(.+)/i'; // <date> - <name> : <message>
    private $_matchDate = '/([0-9]{2})\/([0-9]{2})\/([0-9]{4}), ([0-9]{2}):([0-9]{2})/';
    private $_matchDateIphone = '/([0-9]{2})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/';

    public function readFile($filename)
    {
        if (is_file($filename)) {
            $lines = file($filename);
            $count = 0;
            $match = $this->_matchLine;
            $dateMatch = $this->_matchDate;
            if ($this->_settings['iPhone']) {
                $match = $this->_matchLineIphone;
                $dateMatch = $this->_matchDateIphone;
            }
            foreach ($lines as $i => $line) {
                $count++;
                if (preg_match($match, $line, $parts)) {

                    $name = $parts[3];
                    $message = $parts[5];
                    $date = $parts[1]; // "26/11/2015, 18:17"
                    // 01-09-15 09:25:51
                    preg_match($dateMatch, $date, $timeParts); // { [0]=> string(17) "26/11/2015, 18:17" [1]=> string(2) "26" [2]=> string(2) "11" [3]=> string(4) "2015" [4]=> string(2) "18" [5]=> string(2) "17" }
                    if ($this->_settings['iPhone']) {
                        $timeParts[3] = '20' . $timeParts[3];
                    }
                    $oDate = new DateTime($timeParts[3] . '-' . $timeParts[2] . '-' . $timeParts[1] . ' ' . $timeParts[4] . ':' . $timeParts[5]);
                    $timeInMinutes = floor($oDate->getTimestamp() / 60);
                    if (!isset($this->_minutesNames[$timeInMinutes])) {
                        $this->_minutesNames[$timeInMinutes] = [];
                    }
                    $this->_minutesNames[$timeInMinutes][] = $name;
                    $hour = $timeParts[4];
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

    private function _fixBigCloud($wordCloud)
    {
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
     * Get relations from everyone. Depending on the activity time
     */
    public function getRelations()
    {

        //var_dump($this->_minutesNames);
        foreach ($this->_minutesNames as $timestamp => $names) {
            foreach ($names as $name) {
                for ($i = ($timestamp - $this->_settings['activityTime']); $i <= $timestamp; $i++) {
                    if (!isset($this->_minutesNames[$i])) {
                        continue;
                    }
                    foreach ($this->_minutesNames[$i] as $friendName) {
                        if ($name == $friendName) {
                            continue;
                        }
                        if (!isset($this->_relations[$name][$friendName])) {
                            $this->_relations[$name][$friendName] = 0;
                        }
                        $this->_relations[$name][$friendName]++;
                    }
                }
            }
        }
        return $this->_relations;
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