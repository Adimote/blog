<?php
	//Writes all log messages to file
	Class logger {
		public static function log($text) {
			error_log(print_r(debug_backtrace(),true));
		}
	}
?>