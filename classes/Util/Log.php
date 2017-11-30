<?php
/* Logging
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2012
 */


class Log {
    private static $BeginString = '##################### Start ######################';
    private static $EndString = '#####################  End  ######################';

	public static function GetLogFileName($time=null){
		$date = $time ? date("Y_m_d", $time) : date("Y_m_d");

		return self::GetLogFilePath() . $date . "_" . LOG_FILE;
	}

	public static function GetLogFilePath(){
		return GetProjectDirectory().DIR_DYNAMIC . '/';
	}

	public static function Exception($exception, $file = '', $stack=null){
		self::Write("EXCEPTION " . $exception->getCode() . ": " . $exception->getMessage(), $file, $stack);
	}

    public static function Write($msg, $file = '', $stack=null){
        if (DEBUG_ENABLED){
            if (!$file){
	            $file = self::GetLogFileName();
	            $path = self::GetLogFilePath();

	            if (!file_exists($path)){
		            FileSystem::CreateDirectory($path);
	            }

	            //clearstatcache();

	            if (!file_exists($path)){
		            $path = str_ireplace('\\', '/', __FILE__);
		            $path = str_ireplace('classes/Util/Log.php', '', $path);

		            if (!file_exists($path.self::GetLogFilePath())){
			            throw new Exception("Couldn't find Log path: ".$path.self::GetLogFilePath());
		            }

		            $file = $path.$file;
	            }
            }else{
	            if (!file_exists($file)){
		            $filePath = explode('/', $file);
		            if (count($filePath) > 1){
			            array_splice($filePath, count($filePath)-1, 1);
			            $filePath = implode('/', $filePath);
			            if (!file_exists($filePath)){
				            FileSystem::CreateDirectory($filePath);
			            }
		            }
	            }
            }

            $file = fopen($file, file_exists($file) ? 'a' : 'w');
            fwrite($file, self::$BeginString."\n");
            fwrite($file, time()."\n");
            fwrite($file, date("d/m/Y H:i:s")."\n");
            fwrite($file, $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']."\n");

            if (DEBUGBT_ENABLED && $stack!==false){
                $bt = debug_backtrace();
                foreach($bt as $caller){
	                if (isset($caller['file']))
                        @fwrite($file, "> " . $caller['file'].' - Line '.$caller['line']."\n");
                }
            }

            fwrite($file, print_r($msg,true) . "\n");
            fwrite($file, self::$EndString."\n\n");
            fclose($file);
        }
    }

    /**
     * @param $file
     * @return LogMessage[]
     * @throws Exception
     */
    public static function Parse($file){
        $messages = [];
        if (file_exists($file)){
            $file = fopen($file, 'r');

            function fgets_custom($f){
                $L =  str_ireplace("\n", '', fgets($f));
                //echo "Reading Line: $L\n";
                return $L;
            }


            while(!feof($file)){
                $line = fgets_custom($file);
                if ($line == self::$BeginString){
                    $msg = new LogMessage();

                    $line = fgets_custom($file);
                    $msg->time = intval($line);

                    $line = fgets_custom($file);

                    $line = fgets_custom($file);
                    $msg->url = $line;

                    $line = fgets_custom($file);
                    while($line[0] == '>'){
                        $msg->stack[] = $line;
                        $line = fgets_custom($file);
                    }

                    while($line != self::$EndString){
                        $msg->message .= $line."\n";
                        $line = fgets_custom($file);
                    }

                    $messages[] = $msg;
                }
            }
        }else{
            throw new Exception("Attempt to parse non existent log file.");
        }
        return $messages;
    }
}



class LogMessage {
    public $time = 0;
    public $url = '';
    public $stack = [];
    public $message = '';
}