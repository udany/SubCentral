<?php
function UTFConvert($str) {
    return iconv( "Windows-1252", "UTF-8", $str );
}


class CSVParser {
    private $file;
    private $handle;
    private $rows;
    private $separator;
    private $header;

    public function __construct($filename, $separator){
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;

        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            $this->file = $filename;
            $this->handle = $handle;
            $this->separator = $separator;

            $this->header = $this->GetLine();
            $this->rows = [];
        }
    }

    public function Parse($function, $timeoutPerLine=0, $start=0, $total=0, $keepRows = 0){
        if($this->handle){
            for($i = 0; $i < $start; $i++){
                $this->GetLine();
            }

            $i = 0;
            $row = $this->GetLine();
            while ($row && (!$total || $i<$total)){

                $data = array_combine($this->header, $row);
                if ($keepRows) array_push($this->rows, $data);

                if ($function) $function($data);

                if ($timeoutPerLine) set_time_limit($timeoutPerLine);

                $i++;
                $row = $this->GetLine();
            }

            fclose($this->handle);
            $this->handle = null;
        }

        return $this->rows;
    }

    private function GetLine(){
        if (($row = fgetcsv($this->handle, null, $this->separator))){
            //$row = array_map("UTFConvert", $row);
            return $row;
        }
        return null;
    }

}