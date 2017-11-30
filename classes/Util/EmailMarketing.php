<?php
class EmailMarketing {
	public static function GetMessage($page, $data){
		ob_clean();
		include(GetProjectDirectory(ServerSettings::GetCurrent('CustomEmail') ? null : 'ClientArea')."pages/Email.php");

		$html = ob_get_contents();
		ob_clean();
		
		return $html;
	}

	public static function Mandrill($page, $data, $to, $subject, $name=null, $from=null){		
		$html = self::GetMessage($page, $data);

		if (ServerSettings::GetCurrent('MockEmail')){
			FileSystem::Write(GetDynamicDirectory('EliteSales')."modck_mail".time().'.html', $html);
			return true;
		}

		$email = new Email();
		$email->html = true;
		
		$email->Destination = $to;
		$email->DestinationName = $name;

		$email->Sender = $from ? $from : ServerSettings::GetCurrent("Email");
		$email->SenderName = $from ? null : ServerSettings::GetCurrent("EmailName");

		$email->Subject = $subject;
		$email->Message = $html;

		return $email->Mandrill($page);
	}

    public static function Send($page, $data, $to, $subject, $from=null){
	    $settings = ServerSettings::$current;
	    $html = self::GetMessage($page, $data);

        $mail = new Email($settings->Get("Email"), $to, $subject, $html, true);
        return $mail->Send();
    }
}