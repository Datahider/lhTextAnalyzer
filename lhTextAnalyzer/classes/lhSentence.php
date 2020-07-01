<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhSentence
 *
 * @author user
 */
class lhSentence extends lhSelfTestingClass {
    private $subject;
    private $predicate;
    private $object;
    
    private $has_greeting;
    private $has_addressing;
    
    private function getset($name, $arg) {
        switch (count($arg)) {
            case 0:
                return $this->$name;
            case 1;
                $last = $this->$name;
                $this->$name = $arg[0];
                return $last;
            default:
                throw new Exception("Function $name() takes no or one argument");
        }
    }
    public function subject(...$arg) {
        $name = __FUNCTION__;
        return $this->getset($name, $arg);
    }
    public function predicate(...$arg) {
        $name = __FUNCTION__;
        return $this->getset($name, $arg);
    }
    public function object(...$arg) {
        $name = __FUNCTION__;
        return $this->getset($name, $arg);
    }
    public function has_greeting(...$arg) {
        $name = __FUNCTION__;
        return $this->getset($name, $arg);
    }
    public function has_addressing(...$arg) {
        $name = __FUNCTION__;
        return $this->getset($name, $arg);
    }
    
    protected function _test_data() {
        return [
            'subject' => [
                [null],
                ["Я", null],
                ["Ты", "Я"],
            ],
            'predicate' => [
                [null],
                ["сижу", null],
                ["стою", "сижу"],
            ],
            'object' => [
                [null],
                ["стол", null],
                ["стул", "стол"],
            ],
            'has_greeting' => [
                [null],
                [true, null],
                [false, true],
            ],
            'has_addressing' => [
                [null],
                [true, null],
                [false, true],
            ],
        ];
    }
}
