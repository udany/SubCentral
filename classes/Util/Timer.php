<?php

class Timer {
    private $t1 = 0;
    private $t2 = 0;

    public function Start(){
        $this->t1 = microtime(true);
    }
    public function Stop(){
        $this->t2 = microtime(true);
    }
    public function Reset(){
        $this->t1 = 0;
        $this->t2 = 0;
    }
    public function GetTime(){
        return ($this->t2-$this->t1);
    }
}