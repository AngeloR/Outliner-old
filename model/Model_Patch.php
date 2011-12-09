<?php

class Model_Patch extends RedBean_SimpleModel {
	
	public static function new_patch($version) {
		$patch = R::dispense('patch');
		$patch->version = $version;
		$patch->patch_date = date('U');
		return $patch;
	}
}