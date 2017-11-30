<?PHP
/* Parser
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2015
 */
class DNAParser extends Singleton {
    private $fileName = "parser.json";
	private $data;
	/** @var DNAParserTag[] */
	private $tags=[];

    protected function __construct() {
        $this->json = new JSONData(GetDynamicDirectory().$this->fileName);
        $this->data = $this->json->GetData();
	    if (is_array($this->data)){
		    foreach($this->data['tags'] as $k=>$v){
			    $this->RegisterTag($k, $v);
		    }
	    }
    }

    private function GetFieldString($field, $options=''){
        switch($field['type']){
            case 'Float':
                if(isset($field['value'])){
                    $val = $field['value'];
                }else{
                    $val = 0;
                }
                if ($options=='abs') $val = abs($val);

                return number_format($val, $field['precision']);
                break;
            case 'Integer':
                if(isset($field['value'])){
                    $val = $field['value'];
                }else{
                    $val = 0;
                }
                if ($options=='abs') $val = abs($val);

                return number_format($val);
                break;
        }
        return $field['value'];
    }

    public function GetFieldValue($field,$options=''){
        if (!isset($field['type'])) $field['type'] = '';

        switch($field['type']){
            case 'Integer':
            case 'Float':
                $val = $field['value'];
                if ($options=='abs') $val = abs($val);
                return round($val, isset($field['precision']) ? $field['precision'] : 0);
                break;
        }
        return $field['value'];
    }

	public function RegisterTag($tag, $val){
		$obj = new DNAParserTag($tag, $val);

		$this->tags[$tag] = $obj;
	}

    private function ParseTag($tag, $options, $contents, $fields){
        $args = explode('|', $options);

	    if (isset($this->tags[$tag])){
		    return $this->tags[$tag]->Parse($args,$contents,$fields);
	    }
        return '';
    }

    public function Parse($text, $fields){
        $matches = [];

        // Match Field references
        preg_match_all($this->data['regex']['field'], $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $val) {
            if (isset($fields[$val[1]])){
                $text = str_replace($val[0], $this->GetFieldString($fields[$val[1]], $val[2]), $text);
            }
        }

        $matches = [0];
        while(count($matches)){
            // Match tags
            preg_match_all($this->data['regex']['tag'], $text, $matches, PREG_SET_ORDER);

            //print_r($matches);
            foreach ($matches as $val) {
                $replacement = $this->ParseTag($val[1], $val[2], $val[3], $fields);
                $text = str_replace($val[0], $replacement, $text);
            }
        }

        return $text;
    }

    public function Format($text, $values, $pre="{", $su="}"){
        foreach($values as $k => $v){
            $text = str_replace($pre.$k.$su, $v,$text);
        }
        return $text;
    }

    /**
     * @static
     * @return DNAParser
     */
    public static function getInstance() {
        return parent::getInstance();
    }
}

class DNAParserTag {
	public $tag;
	public $val;

	public function __construct($tag, $val){
		$this->tag = $tag;
		$this->val = $val;
	}

	public function Parse($args, $contents, $fields){
		$p = DNAParser::getInstance();

		if (is_callable($this->val)){
			return $this->val($args, $contents, $fields);
		}else{
			return $p->Format($this->val, [$contents]);
		}
	}
}

$p = DNAParser::getInstance();
$p->RegisterTag('if', function($args, $contents, $fields){
	//[if|eq|0|shortfallOrSurplus]
	if (isset($fields[$args[2]])){
		$p = DNAParser::getInstance();
		$field = $fields[$args[2]];
		$val = $p->GetFieldValue($field);
		$test = false;
		if($args[0] == 'eq') $test = $val == $args[1];
		if($args[0] == 'ne') $test = $val != $args[1];
		if($args[0] == 'gt') $test = $val > $args[1];
		if($args[0] == 'lt') $test = $val < $args[1];
		if($args[0] == 'ge') $test = $val >= $args[1];
		if($args[0] == 'le') $test = $val <= $args[1];

		if ($test){
			return $contents;
		}
	}

	return '';
});
$p->RegisterTag('a', function($args, $contents, $fields){
	$href = isset($args[0]) ? $args[0] : '#';
	$title = isset($args[1]) ? $args[1] : '';
	$target = isset($args[2]) ? $args[2] : '_blank';

	return "<a href='$href' target='$target' title='$title'>$contents</a>";
});
