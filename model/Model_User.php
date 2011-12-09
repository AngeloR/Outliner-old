<?php

class Model_User extends RedBean_SimpleModel {
	
	public static function hash($pw) {
		return sha1(sha1('2i8vn39!#1'.md5($pw.'38v')).'813v1H%*@$(V');
	}
	
	public function new_user_setup($username,$password) {
		$this->username = $username;
		$this->password = $this->hash($password);
		$now = time();
		$this->signup_date = $now;
		$this->last_login = $now;
	}
}