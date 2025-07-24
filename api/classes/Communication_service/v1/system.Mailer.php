<?php

class systemMailer{

	var $cn; //connection
	var $intl; //internationalization
	var $ph; //placeholder

	var $username;
	var $password;
	var $smtpAuth;
	var $smtpSecue;
	var $host;
	var $port;
	var $from;
	var $fromName;
	var $replyTo;
	var $replyToName;

	public function __construct( $cn, $intl = false ){
		$this->cn = $cn;
		$this->intl = $intl;

		$this->localize();

	}




	/*
		send mail message
	*/
	public function send($toEmail, $obj, $messageBody, $name=null, $replyTo=null, $replyToName=null, $attachement=null,$mailParams = null){

			if(empty($mailParams)) require dirname(__FILE__) . ('/system.mail.parameters.php');
			// echo json_encode($mailParams[0]);
			$mail = new PHPMailer;
			$mail->IsSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $mailParams[0]['smtp_server'];		  // Specify main and backup server
			$mail->SMTPAuth = !empty($mailParams[0]['smtp_auth']);      // Enable SMTP authentication
			// $mail->SMTPDebug = 1;
			$mail->Username = $mailParams[0]['username'];       // SMTP username
			$mail->Password = $mailParams[0]['password'];       // SMTP password
			if($mailParams[0]['smtp_secure']!='')
			$mail->SMTPSecure = $mailParams[0]['smtp_secure'];  // Enable encryption, 'ssl' also accepted
			$mail->Port = $mailParams[0]['smtp_port'];

			$from_email = strpos($mailParams[0]['username'], '@')!==false ? $mailParams[0]['username'] : $mailParams[0]['reply_to'];
			$mail->setFrom($from_email,!empty($name)?$name:$mailParams[0]['name']);

			$to_emails = str_replace(' ', '', $toEmail);
			$to_emails = explode(',', $to_emails);

			foreach($to_emails as $to_email)
			{
				$mail->addAddress($to_email);						  // Add a recipient
			}

			$mail->addReplyTo((isset($replyTo)?$replyTo:$mailParams[0]['reply_to']), (isset($replyToName)?$replyToName:$mailParams[0]['name']));
			$mail->isHTML(true);                                  // Set email format to HTML
			// if ($logo!=NULL)	$mail->addEmbeddedImage($logo, 'logoimg', 'logomail.png');
			$mail->CharSet = 'UTF-8';
			$mail->Subject = $obj;

			//Read an HTML message body from an external file, convert referenced images to embedded,
			//convert HTML into a basic plain-text alternative body
			$html_body = '<html> <body> '.urldecode($messageBody).' </body> </html>';

			$this->embed_images($html_body, $mail );

			$mail->MsgHTML($html_body);
			$mail->AltBody = '';

			if(isset($attachement)) $this->attachPDF($attachement['url'], $attachement['filename'], $mail);

			$success=false;
			//send the message, check for errors
			if (!$mail->send()) {
				$message=$mail->ErrorInfo;
				return (array('message'=>MAIL_SENDING_ERROR.' '.$mail->ErrorInfo,'success'=>false));
				//echo "Mailer Error: " . $mail->ErrorInfo;
			} else {
				return (array('message'=>MAIL_SENDING_SUCCESS,'success'=>true));
			}


	}

	/*
		returns message template with specified information
	*/
	public function getMessageTemplate($template_label, $vars, $lang='en') {

		require dirname(__FILE__) . ('/system.mail.templates.php');

		if(isset($templates[$template_label]))
		{
			$template = $templates[$template_label];
			if(!empty($template['translations'])){
				foreach($template['translations'] as $key => $value){
					$translation = !empty($value[$lang])?$value[$lang]:$key;
					$template['message']= str_replace($key,urlencode($translation), $template['message']);
				}
			}
			foreach($vars as $key=>$var)
			{
				$template['message']		= 	str_replace("%7B_".$key."_%7D",urlencode($var), $template['message']);
				$template['message']		= 	str_replace("{_".$key."_}",urlencode($var), $template['message']);
				$template['object']		=	str_replace("{_".$key."_}",($var), $template['object']);
				$template['object']		=	str_replace("%7B_".$key."_%7D",($var), $template['object']);
			}
		}

		if (!isset($template['message'])) {
			return (array('message'=>'NO TEMPLATE FOUND... ', 'success'=>false));
		} else {
			return (array('message'=>'','success'=>true, "data"=>$template));
		}

	}


	/*
		default internal localization
	*/
	private function localize(){
		if (!defined('MAIL_SENDING_ERROR')) 				define("MAIL_SENDING_ERROR", "error sending email");
		if (!defined('MAIL_SENDING_SUCCESS')) 			define("MAIL_SENDING_SUCCESS", "mail sent success");
		if (!defined('MAIL_INVALID_CODE')) 					define("MAIL_INVALID_CODE", "the required code was not found");
		if (!defined('MAIL_INVALID_PHSETCODE')) 			define("MAIL_INVALID_PHSETCODE", "the required placeholder set code was not found");
		if (!defined('MAIL_INVALID_LANGEXIST')) 			define("MAIL_INVALID_LANGEXIST", "the template already has the required language");
		if (!defined('MAIL_INVALID_NAMETOOSHORT')) 	define("MAIL_INVALID_NAMETOOSHORT", "template name is too short (mim 3 chars)");
		if (!defined('MAIL_INVALID_OBJTOOSHORT')) 		define("MAIL_INVALID_OBJTOOSHORT", "object is too short (mim 3 chars)");
		if (!defined('MAIL_INVALID_MSGTOOSHORT')) 	define("MAIL_INVALID_MSGTOOSHORT", "message is too short (mim 3 chars)");
		if (!defined('MAIL_INVALID_NAMEEXIST')) 			define("MAIL_INVALID_NAMEEXIST", "template name is already exist");
		if (!defined('MAIL_INVALID_LANGNOTEXIST')) 	define("MAIL_INVALID_LANGNOTEXIST", "language don't exist");
		if (!defined('MAIL_INVALID_USERID')) 				define("MAIL_INVALID_USERID", "user id not valid (expect integer)");
	}



	private function embed_images(&$html_body, &$mail)
	{
		// get all img tags
		preg_match_all('/<img.*?>/', $html_body, $matches);
		if (!isset($matches[0])) return;
		// foreach tag, create the cid and embed image
		$i = 1;
		foreach ($matches[0] as $img)
		{
			// make cid
			// $id = 'img_'.( getCode() ).'_'.;
			$id = 'img'.($i++);
			// replace image web path with local path
			// print_r($img);
			preg_match('/src="(.*?)"/', $img, $m);
			// print_r($m);
			if (!isset($m[1])) continue;
			$arr = parse_url($m[1]);
			if (!isset($arr['host']) || !isset($arr['path']))continue;
			// echo '<script>console.log("';
			// echo json_encode($arr);
			// echo '"); </script>';
			// echo('/var/www/html'.$arr['path']);

			// add
			if($mail->AddEmbeddedImage('/var/www/vhosts/smooney.it/httpdocs'.$arr['path'], $id, 'attachment', 'base64', 'image/jpeg'))
			{
				$html_body = str_replace($img, str_replace($m[0], 'src="cid:'.$id.'"', $img), $html_body);
				// $html_body = str_replace($img, '<img alt="" src="cid:'.$id.'" />', $html_body);
				// echo '<img alt="" src="cid:'.$id.'" style="border: none;" />';
			}
		}
	}


	public function validateMX($email){
		// Check the formatting is correct
		if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
		return FALSE;
		}
		// Next check the domain is real.
		$domain = explode("@", $email, 2);
		return checkdnsrr($domain[1]); // returns TRUE/FALSE;
	}


	public function attachPDF($url, $filename, &$mail)
	{
		$mail->addStringAttachment(file_get_contents($url), $filename, 'base64', 'application/pdf');
	}

	public function execInBackground($cmd) {
		if (substr(php_uname(), 0, 7) == "Windows"){
			pclose(popen("start /B ". $cmd, "r"));
		}
		else {
			exec($cmd . " > /dev/null &");
		}
	}

}



?>
