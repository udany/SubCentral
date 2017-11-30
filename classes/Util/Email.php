<?php
/* Email class
 * This code may not be reused without proper permission from its creator.
 *
 * Coded by Daniel Andrade - All rights reserved © 2014
 */
class Email {
    public $Sender = "";
	public $SenderName = "";
	public $ReplyTo = "";
	public $Destination = "";
	public $DestinationName = "";
	public $Subject = "";
	public $Message = "";
	public $html = false;
	public $inlineCss = false;
    
    public function __construct($from="", $to="", $subject="", $message="", $html = false, $fromName="", $toName=""){
        $this->Sender = $from;
        $this->Destination = $to;
        $this->Subject = $subject;
        $this->Message = $message;
        $this->html = $html;
	    $this->SenderName = $fromName;
	    $this->DestinationName = $toName;
	    $this->CarbonCopy = [];
	    $this->BlindCarbonCopy = [];
    }
	
	public function addCC ($email, $name = ''){
		array_push($this->CarbonCopy, [
			'name'=>$name,
			'email'=>$email,
			'type' => 'cc'
		]);
	}
	
	public function addBCC ($email, $name = ''){
		array_push($this->BlindCarbonCopy, [
			'name'=>$name,
			'email'=>$email,
			'type' => 'bcc'
		]);
	}
    
    public function Send(){
        $result = array();

        $header = 'From: ' . $this->Sender . "\r\n";
        if ($this->html){
            $header .= 'MIME-Version: 1.0' . "\r\n";
            $header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        }

	    if ($this->ReplyTo){
		    $header .= 'Reply-To: ' . ($this->ReplyTo) . "\r\n";
	    }
        
        $result['status'] = mail($this->Destination,
             $this->Subject,
             $this->Message,
             $header,
	         '-f '.$this->Sender);
             
        return $result;
    }

	public function Mandrill($tag){
		$settings = ServerSettings::$current;

		$from = $this->Sender ? $this->Sender : $settings->Get("Email");

		$message = array(
			'html' => $this->Message,
			'subject' => $this->Subject,
			'from_email' => $from,
			'from_name' => $this->SenderName ? $this->SenderName : $settings->Get("EmailName"),
			'to' => array(
				array(
					'email' => $this->Destination,
					'name' => $this->DestinationName,
					'type' => 'to'
				)
			),
			'headers' => array('Reply-To' => $this->ReplyTo ? $this->ReplyTo : $from),
			'auto_text' => true,

			'tags' => array($tag)
		);

		if ($this->inlineCss){
			$message['inline_css'] = true;
		}

		if (count($this->CarbonCopy)){
			foreach ($this->CarbonCopy as $cc){
				array_push($message['to'], $cc);
			}
		}
		if (count($this->BlindCarbonCopy)){
			foreach ($this->BlindCarbonCopy as $cc){
				array_push($message['to'], $cc);
			}
		}

		try {
			$mandrill = new Mandrill($settings->Get("MandrillKey"));

			return $mandrill->messages->send($message, false);
		} catch(Exception $e) {
			LogMessage('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage()."\n\n\n".print_r($message, true));
			return false;
		}
	}
    
}
?>