<?PHP
class JSONData {
    public $file;
    public $data;
    public function __construct($file) {
        $this->file = $file;
        $this->Read();
    }

    private  function Read(){
        if (file_exists($this->file)) $this->data = json_decode(file_get_contents($this->file), true);
    }
    private function Write(){
	    FileSystem::Write($this->file, json_encode($this->data));
    }
    public function GetData(){
        $this->Read();
        return $this->data;
    }
    public function SetData($data){
        $this->data = $data;
        $this->Write();
        return true;
    }
}
?>