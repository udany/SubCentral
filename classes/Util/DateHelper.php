<?php
class DateHelper {
	public static function UnixToHtml($timestamp){
		return date("Y-m-d", $timestamp);
	}
	public static function UnixToHtmlTime($timestamp){
		return date("H:i", $timestamp);
	}

	public static function HtmlToUnix($str, $strTime=null){
		$parts = explode('-', $str);
		if (count($parts)==3){
			if ($strTime){
				$strTime = explode(':', $strTime);
				$time = [intval($strTime[0]), intval($strTime[1]), 0];
			}else{
				$time = [0, 0, 0];
			}

			return gmmktime($time[0],$time[1],$time[2], $parts[1], $parts[2], $parts[0]);
		}else{
			return 0;
		}
	}

	public static function BrToUnix($str, $strTime=null){
		$parts = explode('/', $str);
		if (count($parts)==3){
			if ($strTime){
				$strTime = explode(':', $strTime);
				$time = [intval($strTime[0]), intval($strTime[1]), 0];
			}else{
				$time = [0, 0, 0];
			}

			return gmmktime($time[0],$time[1],$time[2], $parts[1], $parts[0], $parts[2]);
		}else{
			return 0;
		}
	}
}