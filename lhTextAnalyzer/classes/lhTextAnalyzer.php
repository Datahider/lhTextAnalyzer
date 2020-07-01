<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhSentenceSplitter
 *
 * @author user
 */
class lhTextAnalyzer extends lhSelfTestingClass {
    private $mind;
    public function __construct(lhAIML $mind) {
        $this->mind = $mind;
    }

    private function genPart($stack, $type=null, $match_level=null) {
        $text = implode(' ', $stack);
        if (true||($type === null)||($match_level === null)) {
            $matches = $this->mind->bestMatches($text, [], 80);
            $match = array_shift($matches);
            $type = isset($match['category']['type']) ? (string)$match['category']['type'] : 'unknown';
            $match_level = isset($match['match_level']) ? (float)$match['match_level'] : 0;
            $supposed = isset($match['best_match']) ? $match['best_match'] : 'NONE';
            $name = isset($match['category']['name']) ? (string)$match['category']['name'] : '';
        }
        return [
            'text' => $text,
            'type' => $type,
            'match_level' => $match_level,
            'supposed' => $supposed,
            'class' => $name
        ];
    }

    public function analyzeSentence($text) {
        $this->log(__CLASS__.'->'.__FUNCTION__);
        $lexems = lhTextConv::split($text);
        $this->log($lexems, 20);
        $parts = [];
        $stack = [];
        for ($index = 0; $index < count($lexems); $index++) {
            if (preg_match("/[^a-zA-ZА-Яа-я0-9]/u", $lexems[$index])) {
                if (count($stack)) {
                    $parts[] = $this->genPart($stack);
                    $this->log("stack was: ".implode(" ", $stack), 25);
                    $stack = [];
                }
                continue;
            }
            $match_stack = count($stack) ? array_shift($this->mind->bestMatches(implode(" ", $stack))) : ['match_level' => 0];
            $match_lexem = array_shift($this->mind->bestMatches($lexems[$index]));
            $stack[] = $lexems[$index]; 
            $match_current = array_shift($this->mind->bestMatches(implode(" ", $stack)));
            if (($match_current['match_level'] > 90) && ($match_current['category']['final'] == "yes")) {
                // Текущее состояние стека подходит хорошо и к файнал
                $parts[] = $this->genPart($stack, (string)$match_current['category']['type'], (float)$match_current['match_level']);
                $this->log("FINAL stack was: ".implode(" ", $stack), 25);
                $stack = [];
            } elseif ($match_stack['match_level'] > $match_current['match_level']) {
                // Прошлое состояние стека было лучше
                array_pop($stack);
                $parts[] = $this->genPart($stack);
                $this->log("LAST STACK BETTER stack was: ".implode(" ", $stack), 25);
                $stack = [];
                $index--;
            } elseif ($match_lexem['match_level'] > $match_current['match_level']) {
                array_pop($stack);
                $parts[] = $this->genPart($stack);
                $this->log("LEXEM is BETTER stack was: ".implode(" ", $stack), 25);
                $stack = [];
                $index--;
            }
        }
        if (count($stack)) {
            $this->log("stack was: ".implode(" ", $stack), 20);
            $parts[] = $this->genPart($stack);
        }
        
        return $parts;
    }
    
    public function analyze($text) {
        $this->log(__CLASS__.'->'.__FUNCTION__);
        $this->log('$text='. $text, 15);
        $sentences = lhTextConv::splitSentences($text);
        $analyzed = [];
        foreach ($sentences as $sentence) {
            $analyzed[] = $this->analyzeSentence($sentence);
        }
        return $analyzed;
    }

    protected function testAnalyzeSentence($text) {
        $text_parts = [];
        $analysis = $this->analyzeSentence($text);
        foreach ($analysis as $part) {
            $text_parts[] = $part['text'];
        }
        return $text_parts;
    }
    
    protected function _test_data() {
        $this->log(__CLASS__.'->'.__FUNCTION__);
        
        return [
            'analyze' => '_test_skip_',
//            'analyzeSentence' => [
//                ["Ты можешь удаленно принтер подключить к компу?", [
//                    "Ты", "можешь", "удаленно", "принтер", "подключить", "к компу"
//                ]],
//            ],
            'analyzeSentence' => '_test_skip_',
            'testAnalyzeSentence' => [
                ["Добрый день, у нас не работает принтер", [ 
                    "Добрый день", "у нас", "не работает", "принтер"
                ]],
                ["Привет, как дела?", ["Привет", "как дела"]],
                ["Ты можешь удаленно принтер подключить к компу?", [
                    "Ты", "можешь", "удаленно", "принтер", "подключить", "к компу"
                ]],
                ["Не могу начать работу, вот что пишети", [
                    "Не могу", "начать", "работу", "вот", "что", "пишети"
                ]],
                ["Со вчерашнего вечера не работает удаленка", [
                    "Со", "вчерашнего", "вечера", "не работает", "удаленка"
                ]]
            ]
        ];
    }    
}


