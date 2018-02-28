<?php

class TimeMark{
	public $mark;
	public $mark_point;
	public $time;
	public function __construct(){
		$this->mark = array();
		$this->mark_point = array();
		$this->time = array();
	}

	public function mark(){
		$backtrace = debug_backtrace();
		$backtrace = $backtrace[0];
		$this->mark_point[] = $backtrace['file'].':'.$backtrace['line'];
		$this->mark_time[] = microtime(true);
	}

	public function show(){
		$this->count_time();
		print_r($this->time);
	}
	public function result(){
		$this->count_time();
		
	}
	public function setName($name){
		$this->name = $name;
	}
	public function log($log_file = './time_mark.log'){
		$this->count_time();
		$str = "--------start--------{$this->name}----------\n";
		foreach($this->time as $mark_point => $time){
			$str .= "[$mark_point]:$time\n";
		}

		file_put_contents($log_file, $str, FILE_APPEND);
	}

	public function count_time(){
		$count = count($this->mark_point);
		for($i = 1; $i < $count; $i++){
			$this->time[$this->mark_point[$i-1].'--'.$this->mark_point[$i]] = $this->mark_time[$i] - $this->mark_time[$i-1];
		}
		$this->time['total_time'] = $this->mark_time[$count - 1] - $this->mark_time[0];
	}
}

