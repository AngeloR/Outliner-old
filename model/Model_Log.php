<?php

class Model_Log extends RedBean_SimpleModel {
	
	public static function log($type,$details) {
		$log = R::dispense('log');
		$log->timestamp = time();
		$log->type = strtolower($type);
		$log->details = $details;
		R::store($log);
	}
}