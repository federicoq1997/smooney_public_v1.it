<?php
	/*
		constant class name
	*/
	ini_set('memory_limit','2048M');
	define("IOINTL", "ioIntl");
	define("IOLOGIN", "ioLogin");
	define("IOPLACEHOLDER", "ioPlaceholder");
	
	function truncate($str, $len=42) {
		if (strlen($str) > $len) {
			$str = substr($str, 0, $len);
			$str .= '...';
		}
		return $str;
	}
	function roundUpToTenOrHundred($number) {
		if ($number > 1000) {
				// Arrotonda a 100
				$remainder = intval($number) % 100;
				if ($remainder > 0) {
						$number += 100 - $remainder;
				}
		} else {
				// Arrotonda a 10
				$remainder = intval($number) % 10;
				if ($remainder > 0) {
						$number += 10 - $remainder;
				}
		}

		return intval($number);
	}
	function oscurareStringa($stringa, $lunghezzaMassima=null) {
    // Lunghezza totale della stringa
    $lunghezza = strlen($stringa);

    // Se la stringa è troppo corta per l'oscuramento, restituisci la stringa originale
    if ($lunghezza <= 6) {
        return $stringa;
    }

    // Prendi le prime 3 lettere
    $inizio = substr($stringa, 0, 3);

    // Prendi le ultime 3 lettere
    $fine = substr($stringa, -3);

    // Calcola la lunghezza della parte oscurata
    $parteOscurataLunghezza = $lunghezza - 6;

    // Se la stringa risultante è più lunga della lunghezza massima
    if (!empty($lunghezzaMassima) && $lunghezza > $lunghezzaMassima) {
        // La parte centrale oscurata deve essere ridotta
        $lunghezzaParteCentrale = $lunghezzaMassima - 6;
        $oscurati = str_repeat('*', max(0, $lunghezzaParteCentrale));
    } else {
        // Mantieni la parte centrale completamente oscurata
        $oscurati = str_repeat('*', $parteOscurataLunghezza);
    }

    // Combina le parti visibili con gli asterischi
    return $inizio . $oscurati . $fine;
	}
	function numberFormatReduceAccuracyThousands($number){
		return $number>1000? number_format($number/1000,2,',','.').' K' :number_format($number,2,',','.');
	}
	function convertToYmd($dateString) {
    // Prova a convertire la stringa in un timestamp
    $timestamp = strtotime($dateString);

    // Verifica che la conversione sia andata a buon fine
    if ($timestamp === false) throw new Exception("Formato della data non riconosciuto");

    // Converte il timestamp nel formato 'Y-m-d'
    return date('Y-m-d', $timestamp);
	}
	function convertToWebP($imageData, $maxWidth = 1920, $maxHeight = 1080, $quality = 80) {
		// Creare una risorsa immagine da dati binari
		$image = imagecreatefromstring($imageData);

		$currentWidth = imagesx($image);
		$currentHeight = imagesy($image);

		// Verificare se è necessario ridimensionare l'immagine
		if ($currentWidth > $maxWidth || $currentHeight > $maxHeight) {
				$aspectRatio = $currentWidth / $currentHeight;

				if ($currentWidth > $maxWidth || $currentHeight > $maxHeight) {
						$newWidth = $maxWidth;
						$newHeight = $maxWidth / $aspectRatio;

						if ($newHeight > $maxHeight) {
								$newHeight = $maxHeight;
								$newWidth = $maxHeight * $aspectRatio;
						}

						$newImage = imagecreatetruecolor($newWidth, $newHeight);
						imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $currentWidth, $currentHeight);

						// Converti l'immagine in formato RGB
						$rgbImage = imagecreatetruecolor($newWidth, $newHeight);
						imagecopy($rgbImage, $newImage, 0, 0, 0, 0, $newWidth, $newHeight);
						imagedestroy($newImage);
				} else {
						// Se l'immagine non ha bisogno di ridimensionamento, convertila direttamente in formato RGB
						$rgbImage = $image;
				}

				ob_start(); // Cattura l'output nel buffer
				imagewebp($rgbImage, null, $quality);
				$webpData = ob_get_clean(); // Recupera l'output dal buffer

				imagedestroy($rgbImage);
		} else {
				// Se l'immagine è già più piccola delle dimensioni desiderate, non è necessario ridimensionarla
				ob_start();
				imagewebp($image, null, $quality);
				$webpData = ob_get_clean();
		}

		imagedestroy($image);

		return $webpData;
	}
	function truncateString($string, $len = 22) {
    // Controlla se la lunghezza della stringa è maggiore o uguale a $len
    if (strlen($string) >= $len) {
        // Trunca la stringa, togliendo 3 caratteri per fare spazio a "..."
        $string = substr($string, 0, $len - 3) . '...';
    }
    // Ritorna la stringa modificata o l'originale se non è stata troncata
    return $string;
	}
	/**
	 * this method provides for identifying changes that exist between two complex objects.
	 * CAUTION, if there are 'classical' arrays it is recommended to always turn them into objects
	 * @param array $array1 new object
	 * @param array $array2 old object
	 * @return array returns a one-dimensional array, where it has as a key the path to get to that change ie key:id:key2:id2:key3
	 * [activations:22:service_id] => Array
	 *	(
	 *			[status] => updated
	 *			[value] => 3432
	 *	)

	*/
	function array_diff_extended($array1, $array2, $path = '') {
    $diff = [];

    foreach ($array1 as $key => $value) {
        $currentPath = ($path !== '') ? $path . ':' . $key : $key;

        if (is_array($value)) {
            if (!isset($array2[$key]) || !is_array($array2[$key])) {
                $diff[$currentPath] = ['status' => 'new', 'value' => $value];
            } else {
                $recursive_diff = array_diff_extended($value, $array2[$key], $currentPath);
                if (!empty($recursive_diff)) {
                    $diff = array_merge($diff, $recursive_diff);
                }
            }
        } elseif (!array_key_exists($key, $array2)) {
            $diff[$currentPath] = ['status' => 'removed', 'value' => null];
        } elseif ($array2[$key] !== $value) {
            $diff[$currentPath] = ['status' => 'updated', 'value' => $value];
        }
    }

    foreach ($array2 as $key => $value) {
        $currentPath = ($path !== '') ? $path . ':' . $key : $key;

        if (!array_key_exists($key, $array1)) {
            $diff[$currentPath] = ['status' => 'removed', 'value' => $value];
        }
    }

    return $diff;
	};
	function calculateCoordinatesDistance($x1, $y1, $x2, $y2) {
    return abs($x1 - $x2) + abs($y1 - $y2);
	}
	function getInfoUser(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
				$ipAddress = $_SERVER['REMOTE_ADDR'];
		}
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		return ['ip'=>$ipAddress,'user-agent'=>$userAgent];
	}

	function array_values_inner(array $array){
		$elements = [];
		foreach($array as $k=>$v)
			$elements = array_merge($elements,array_values($v));
		return $elements;
	}
	function recursive_unsets(&$array, $unwanted_key){
		foreach($unwanted_key as $key) recursive_unset($array,$key);
	}
	function recursive_unset(&$array, $unwanted_key) {
			unset($array[$unwanted_key]);
			foreach ($array as &$value) {
					if (is_array($value)) {
							recursive_unset($value, $unwanted_key);
					}
			}
	}
	function generateStrongPassword($length = 12) {
		// Caratteri consentiti nella password
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_";

		// Mescolamento dei caratteri
		$chars = str_shuffle($chars);

		// Generazione della password
		$password = substr($chars, 0, $length);

		return $password;
	}
	function generateOTP() {
    $otp = "";
    for ($i = 0; $i < 6; $i++) {
        $otp .= rand(0, 9);
    }
    return $otp;
	}
	/**
	 * @param array      $array
	 * @param int|string $position
	 * @param mixed      $insert
	 */
	function array_insert(&$array, $position, $insert)
	{
			if (is_int($position)) {
					array_splice($array, $position, 0, $insert);
			} else {
					$pos   = array_search($position, array_keys($array));
					$array = array_merge(
							array_slice($array, 0, $pos),
							$insert,
							array_slice($array, $pos)
					);
			}
	}
	function getServiceByTime($services, $time) {
		$service_by_time = null;
		if(empty($services)) return [];
		$services = array_column($services,null,'id');
		if(!empty($services)) foreach ($services as $service) {
			foreach ($service['activations'] as $key => $activation) {
				if ($time >= $activation['time_start'] && $time <= $activation['time_end']) {
					$service['activations'][$key]['active'] = true;
					return $service;
				}
				if ($time < $activation['time_start']) {
					if (isset($service['activations'][$key + 1])) {
						$nextActivation = $service['activations'][$key + 1];
						if ($time >= $nextActivation['time_start'] && ( empty($service_by_time) || ( (strtotime($nextActivation['time_start'])-strtotime($time)) <= (strtotime($service_by_time['activation']['time_start'])-strtotime($time)) ) ) ) {
							$service_by_time =[
								'id'=>$service['id'],
								'activation'=>$nextActivation
							];
						}
					} elseif( ( empty($service_by_time) || ( (strtotime($activation['time_start'])-strtotime($time)) <= (strtotime($service_by_time['activation']['time_start'])-strtotime($time)) ) )) {
						$service_by_time = [
							'id'=>$service['id'],
							'activation'=>$activation
						];
					}
				}
			}
		}
		if(empty($service_by_time)) return reset($services);
		$services[$service_by_time['id']]['activations'] = array_column($services[$service_by_time['id']]['activations'],null,'id');
		$services[$service_by_time['id']]['activations'][$service_by_time['activation']['id']]['active']=true;
		return $services[$service_by_time['id']];
	}
	/**
	 * Change values and keys in the given array recursively keeping the array order.
	 *
	 * @param array    $_array    The original array.
	 * @param callable $_callback The callback function takes 2 parameters (key, value)
	 *                            and returns an array [newKey, newValue] or null if nothing has been changed.
	 *
	 * @return void
	 */
	function modifyArrayRecursive(array &$_array, callable $_callback): void
	{
			$keys = \array_keys($_array);
			foreach ($keys as $keyIndex => $key) {
					$value = &$_array[$key];
					if (\is_array($value)) {
							modifyArrayRecursive($value, $_callback);
							continue;
					}

					$newKey = $key;
					$newValue = $value;
					$newPair = $_callback ($key, $value);
					if ($newPair !== null) {
							[$newKey, $newValue] = $newPair;
					}

					$keys[$keyIndex] = $newKey;
					$_array[$key] = $newValue;
			}
			$_array = \array_combine($keys, $_array);
	}

	function get_time_elapsed_advance($start,$ptime,$limit=1,$string='Adesso') { 
    $etime = $start - $ptime; 
    if ($etime < $limit) return $string; 
 
    $a = array( 
   12 * 30 * 24 * 60 * 60 =>  array('anno','anni'), 
   30 * 24 * 60 * 60 =>  array('mese','mesi'), 
   24 * 60 * 60 => array('gg','gg'), 
   60 * 60 => array('ora','ore'), 
   60 => array('min','min'), 
   1 => array('sec','sec') 
    ); 
 
    foreach ($a as $secs => $str) { 
   $d = $etime / $secs; 
   if ($d >= 1) { 
     $r = round($d); 
     return $r . ' ' . ($r > 1 ? $str[1] : $str[0]) . ''; 
   } 
    } 
  } 


	function get_time_elapsed($ptime) { 
    $etime = time() - $ptime; 
    if ($etime < 1) return 'Adesso'; 
 
    $a = array( 
   12 * 30 * 24 * 60 * 60 =>  array('anno','anni'), 
   30 * 24 * 60 * 60 =>  array('mese','mesi'), 
   24 * 60 * 60 => array('giorno','giorni'), 
   60 * 60 => array('ora','ore'), 
   60 => array('minuto','minuti'), 
   1 => array('secondo','secondi') 
    ); 
 
    foreach ($a as $secs => $str) { 
   $d = $etime / $secs; 
   if ($d >= 1) { 
     $r = round($d); 
     return $r . ' ' . ($r > 1 ? $str[1] : $str[0]) . ' fa'; 
   } 
    } 
  } 
 
  function get_time_until($ptime) { 
   $etime =  $ptime - time(); 
   if ($etime < 1) return '0 seconds'; 
 
   $a = array( 
   12 * 30 * 24 * 60 * 60 =>  array('anno','anni'), 
   30 * 24 * 60 * 60 =>  array('mese','mesi'), 
   24 * 60 * 60 => array('giorno','giorni'), 
   60 * 60 => array('ora','ore'), 
   60 => array('minuto','minuti'), 
   1 => array('secondo','secondi') 
   ); 
 
   foreach ($a as $secs => $str) { 
   $d = $etime / $secs; 
   if ($d >= 1) { 
    $r = round($d); 
    return $r . ' ' . ($r > 1 ? $str[1] : $str[0]) . ''; 
   } 
   } 
  }
	function moduleAcvite($curr_module,$list_module=array()){
		if(empty($curr_module)) return false;
		if(empty($list_module)) return false;
		if(!in_array($curr_module,$list_module)) return false;
		return true;
	}
	function cal_date_from_days($date){
    $days=$days_last_moth=0;
		$m = $day = null;
    for($month=1;$month<=12;$month++){ 
        $days = $days + cal_days_in_month(CAL_GREGORIAN,$month,date('Y'));
				if($days_last_moth<$date && $date<=$days){ $m = $month;}
				if($days<$date) $days_last_moth=$days;
				else break;				
     }
		 return date('Y').'-'.$m.'-'.($date-$days_last_moth-1);
	}
	function array_key_merge_deceze($filtered, $changed) {
		foreach($filtered as $k=>$v){
			$filtered[$k] = isset($changed[$k])?strip_tags($changed[$k]):'';
		}
    return $filtered;
	}
	function outputCSV($data, $header_data=[], $footer_data=[]) {
		$outputBuffer = fopen("php://output", 'w');
		fputs( $outputBuffer, "\xEF\xBB\xBF" );
		$header = false;
		$footer = false;
		foreach ($data as $row)
		{
			if(empty($header))
			{
				if(!empty($header_data)){
					$header = array_keys($header_data);
					fputcsv($outputBuffer, $header_data, ';');
					$header = array_flip($header);
				}else{
					$header = array_keys($row);
					fputcsv($outputBuffer, $header, ';');
					$header = array_flip($header);
				}
			}
			fputcsv($outputBuffer, array_key_merge_deceze($header, $row), ';');
		}
	
		if(!empty($footer_data)){
			$footer = array_keys($footer_data);
			fputcsv($outputBuffer, $footer_data, ';');
			$footer = array_flip($footer);
		}
	
		fclose($outputBuffer);
	}
	function findSubStrings($str, $tag_s, $tag_f = null)
	{
			// Utilizzo di un'array predefinito per evitare problemi con i valori di default
			$subStrings = [];

			// Impostazione del tag finale uguale al tag iniziale se non specificato
			$tag_f = $tag_f ?? $tag_s;

			// Escape dei caratteri speciali nei tag
			$tag_s = preg_quote($tag_s, '/');
			$tag_f = preg_quote($tag_f, '/');

			// Pattern per l'espressione regolare che corrisponde alle sottostringhe desiderate
			$pattern = "/$tag_s(.*?)$tag_f/";

			// Utilizzo di preg_match_all per trovare tutte le corrispondenze
			if (preg_match_all($pattern, $str, $matches)) {
					foreach ($matches[1] as $match) {
							$subStrings[] = $match;
					}
			}

			return $subStrings;
	}
	function comparisonCF($params= array()){
		if(empty($params['firstname']) || empty($params['lastname']))
			return false;
		$cf="";
		$firstpart="";
		$lastnameVowels=getVowels($params['lastname']);
		$lastnameConsonants=getConsonant($params['lastname']);
		if(strlen($lastnameConsonants)>=3)
			$firstpart.=substr($lastnameConsonants,0,3);
		else{
			$firstpart.=$lastnameConsonants;
			if(strlen($lastnameVowels) > 0){
                $firstpart.=substr($lastnameVowels,0,strlen($lastnameConsonants)-3);
            }
            if(strlen($lastnameVowels)< 3 && strlen($firstpart)< 3){
                $firstpart.="X";
            }
		}
		$cf.=$firstpart;
		$secondtpart="";
		$firsnameVowels=getVowels($params['firstname']);
		$firsnameConsonants=getConsonant($params['firstname']);
		if(strlen($firsnameConsonants) >= 3){
			$secondtpart.=substr($firsnameConsonants,0,1);
			$secondtpart.=substr($firsnameConsonants,2,2);
        }else{
            $secondtpart.=$firsnameConsonants;
            if(strlen($firsnameVowels) > 0){
                $secondtpart.=substr($firsnameVowels,0,strlen($firsnameConsonants)-3);
            }
            if(strlen($firsnameVowels) < 3 && strlen($secondtpart)< 3){
                $secondtpart.="X";
            }  
        }
		$cf.=$secondtpart;
		if(strtoupper($cf) == strtoupper(substr($params['fiscal_code'],0,6))) return true;
		else return false;
	}
	function random_color_part() {
		return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
	}
	function random_color() {
		return random_color_part() . random_color_part() . random_color_part();
	}

	function simpleXMLtoArray($xml){
		$sxe = new SimpleXMLElement($xml);
		return xmlToArray($sxe);
	}
	function xmlToArray($xml, $options = array()) {
		$defaults = array(
			'namespaceSeparator' => ':',//you may want this to be something other than a colon
			'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
			'alwaysArray' => array(),   //array of xml tag names which should always become arrays
			'autoArray' => true,        //only create arrays for tags which appear more than once
			'textContent' => '$',       //key used for the text content of elements
			'autoText' => true,         //skip textContent key if node has no attributes or child nodes
			'keySearch' => false,       //optional search and replace on tag and attribute names
			'keyReplace' => false       //replace values for above search values (as passed to str_replace())
		);
		$options = array_merge($defaults, $options);
		$namespaces = $xml->getDocNamespaces();
		$namespaces[''] = null; //add base (empty) namespace
	
		//get attributes from all namespaces
		$attributesArray = array();
		foreach ($namespaces as $prefix => $namespace) {
			foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
				//replace characters in attribute name
				if ($options['keySearch']) $attributeName =
						str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
				$attributeKey = $options['attributePrefix']
						. ($prefix ? $prefix . $options['namespaceSeparator'] : '')
						. $attributeName;
				$attributesArray[$attributeKey] = (string)$attribute;
			}
		}
	
		//get child nodes from all namespaces
		$tagsArray = array();
		foreach ($namespaces as $prefix => $namespace) {
			foreach ($xml->children($namespace) as $childXml) {
				//recurse into child nodes
				$childArray = xmlToArray($childXml, $options);
				list($childTagName, $childProperties) = myEach($childArray);
	
				//replace characters in tag name
				if ($options['keySearch']) $childTagName =
						str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
				//add namespace prefix, if any
				if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
	
				if (!isset($tagsArray[$childTagName])) {
					//only entry with this key
					//test if tags of this type should always be arrays, no matter the element count
					$tagsArray[$childTagName] =
							in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
							? array($childProperties) : $childProperties;
				} elseif (
					is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
					=== range(0, count($tagsArray[$childTagName]) - 1)
				) {
					//key already exists and is integer indexed array
					$tagsArray[$childTagName][] = $childProperties;
				} else {
					//key exists so convert to integer indexed array with previous value in position 0
					$tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
				}
			}
		}
	
		//get text content of node
		$textContentArray = array();
		$plainText = trim((string)$xml);
		if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;
	
		//stick it all together
		$propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
				? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;
	
		//return node as array
		return array(
			$xml->getName() => $propertiesArray
		);
	}
	
	/**
	* Introdotta per fixare la funzione each() deprecata a partire dalla versione 7.2 di PHP
	* Viene utilizzata nel parsing dei tracciati XML nel channel manager
	*/
	function myEach(&$arr) {
			$key = key($arr);
			$result = ($key === null) ? false : [$key, current($arr), 'key' => $key, 'value' => current($arr)];
			next($arr);
			return $result;
	}
	function encodeXmlEntity($str='') {
		if (preg_match('/[<>&"\t\n\r]/',$str)) 
			foreach(str_split('/[<>&"\t\n\r]/',1) as $char){
				switch ($char) {
					case '<':$str = str_replace($char,'&lt;',$str);
							break;
					case '>':$str = str_replace($char,'&gt;',$str);
							break;
					case '&':$str = str_replace($char,'&amp;',$str);
							break;
					case "'":$str = str_replace($char,'&apos;',$str);
							break;
					case '"':$str = str_replace($char,'&quot;',$str);
							break;
					case '\t':$str = str_replace($char,'&#9;',$str);
							break;
					case '\n':$str = str_replace($char,'&#10;',$str);
							break;
					case '\r':$str = str_replace($char,'&#13;',$str);
							break;
					default:
							break;
				}
			}
		if (preg_match("/'/",$str)) 
			foreach(str_split("/'/",1) as $char){
				switch ($char) {
					case "'":$str = str_replace($char,'&apos;',$str);
							break;
					default:
							break;
				}
			}
	
		return $str;
	}
	function arrayMinPerMin($datetime1, $datetime2) {
    $arrayMinuti = [];
    $currentDateTime = new DateTime($datetime1);
    $endDateTime = new DateTime($datetime2);

    while ($currentDateTime <= $endDateTime) {
        $orario = $currentDateTime->format('Y-m-d H:i');
        $arrayMinuti[$orario] = 0;
        $currentDateTime->add(new DateInterval('PT1M')); // Aggiunge 1 minuto
    }

    return $arrayMinuti;
	}
	function generateTimeIntervalsPerDay($from, $to, $time_from, $time_to) {
    $arrayMinuti = [];
    $currentDate = new DateTime($from);
    $endDate = new DateTime($to);

    while ($currentDate <= $endDate) {
        $currentTime = new DateTime($currentDate->format('Y-m-d') . ' ' . $time_from);
        $endTime = new DateTime($currentDate->format('Y-m-d') . ' ' . $time_to);

        while ($currentTime <= $endTime) {
            $orario = $currentTime->format('Y-m-d H:i');
            $arrayMinuti[$orario] = 0;
            $currentTime->add(new DateInterval('PT1M')); // Aggiunge 1 minuto
        }

        $currentDate->add(new DateInterval('P1D')); // Aggiunge 1 giorno
    }

    return $arrayMinuti;
	}
	function generateUniqueAlphanumeric($letters, $numbers) {
    $code_value = 0;
    for ($i = 0; $i < strlen($letters); $i++) {
        $code_value += ord($letters[$i]);
    }

    // Unisce il valore numerico del codice con il numero dato
    $result_number = $code_value . $numbers;

    // Calcola l'hash MD5 del risultato numerico
    $md5_hash = md5($result_number);

    // Prendi solo i primi 4 caratteri dell'hash MD5
   	return substr($md5_hash, 0, 4);
	}
	
	function getConsonant($str){
		$consonant = "";
		$vowelsList = array("a","e","i","o","u");
		for ($i = 0; $i < strlen($str); $i++)
			if (!in_array($str[$i],$vowelsList))
				$consonant.=$str[$i];
		return $consonant;
	}
	function getVowels($str){
		$vowels = "";
		$vowelsList = array("a","e","i","o","u");
		for ($i = 0; $i < strlen($str); $i++)
			if (in_array($str[$i],$vowelsList))
				$vowels.=$str[$i];
		return $vowels;
	}
	function validEmail($email){
		$filter = '/^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/';
		if(preg_match($filter,$email)==1) return true;
		else return false;
	}
	function validPhone($phone){
		$filter = '/^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/';
		if(preg_match($filter,$phone)==1) return true;
		else return false;
	}
	function validPIva($piva){
		$filter = '/^\d{11}$/';
		if(preg_match($filter,$piva)==1) return true;
		else return false;
	}
	/*
		in: dd/mm/yyyy
		out: yyyy-mm-dd
	*/
	function mysqlDateFormat($date) {
		$d_temp = array();
		if(!empty($date)) $d_temp = explode("/", $date);
		$d_from = "";
		if(count($d_temp) > 0){ $d_from = $d_temp[2].'-'.$d_temp[1].'-'.$d_temp[0];}
		return $d_from;
	}
	/*
		in: yyyy-mm-dd
		out: dd/mm/yyyy
	*/
	function itDateFormat($date) {
		$d_temp = array();
		if(!empty($date)) $d_temp = explode("-", $date);
		$d_from = "";
		if(count($d_temp) > 0){ $d_from = $d_temp[2].'/'.$d_temp[1].'/'.$d_temp[0];}
		return $d_from;
	}
	/*
		convert a date string YYYY-MM-DD
		in a timestamp around to 1ft or 15 day
	*/
	function dateToLink($date) {
		$ts = strtotime($date);
		if(date("d", $ts) < 15) return mktime(0,0,0,date("m",$ts),1,date("Y",$ts));
		else return mktime(0,0,0,date("m",$ts),15,date("Y",$ts));
	}
	/*
		in: date string YYYY-MM-DD
		out: days between two date
	*/
	function dateStrDiffDays($date1, $date2){
		$date1=date_create($date1);
		$date2=date_create($date2);
		$diff=date_diff($date1,$date2);
		return $diff->format("%R%a");
	}
	function dateDiffSec($date1, $date2){
		$date1=date_create($date1);
		$date2=date_create($date2);
		$diff = $date2->getTimestamp() - $date1->getTimestamp();
		return $diff;
	}
	function hourStrDiff($hour1, $hour2){
		$time1 = date_create($hour1);
		$time2 = date_create($hour2);
		$interval = $time1->diff($time2);
		return $interval->format('%H:%i:%s');
	}
	function convertHourToSec($hour){
		return explode(':',$hour)[0]*3600+explode(':',$hour)[1]*60+explode(':',$hour)[2];
	}
	function convertSecToHour($time){
		$t = round($time);
		return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
	}
	function month_diff($date1,$date2){
		$ts1 = strtotime($date1);
		$ts2 = strtotime($date2);

		$year1 = date('Y', $ts1);
		$year2 = date('Y', $ts2);

		$month1 = date('m', $ts1);
		$month2 = date('m', $ts2);

		return (($year2 - $year1) * 12) + ($month2 - $month1);
	}
	function formatTime($time) {
		if (strpos($time, '.') !== false)  $format = 'H:i:s.u';
		else  $format = 'H:i:s';

		$datetime = DateTime::createFromFormat($format, $time);
    $interval = new DateInterval('PT1H'); // Intervallo di 1 ora

    $formattedTime = $datetime->format(' G \h, i \m');
    $formattedTime = str_replace(' 0 h, ', '', $formattedTime); // Rimuovi "0 h" se non ci sono ore

    return trim($formattedTime);
	}
	function subtractTime($dateTimeString, $timeIntervalString) {
    $dateTime = new DateTime($dateTimeString);
    list($hours, $minutes, $seconds) = explode(':', $timeIntervalString);
    $intervalSpec = "PT" . $hours . "H" . $minutes . "M" . $seconds . "S";
    $interval = new DateInterval($intervalSpec);
    $dateTime->sub($interval);
    return $dateTime->format('Y-m-d H:i:s');
	}

	/*
		in: datetime
		out: days between two date
	*/
	function dateTimeDiffDays($date1, $date2){
		$diff=date_diff($date1,$date2);
		return $diff->format("%R%a");
	}
	function dateFormatLocal($date, $format='Y-m-d', $formatDefault = 'Y-m-d', $local='it_IT', $timeZone='Europe/Rome'){
		if(class_exists('IntlDateFormatter')){
			$d = new IntlDateFormatter($local,IntlDateFormatter::FULL, IntlDateFormatter::FULL,
			$timeZone,IntlDateFormatter::GREGORIAN );
			$d->setPattern($format);
			return $d->format(new DateTime($date));
		}else
			return date($formatDefault,strtotime($date));
	}
	function capitalizeFirstLetter($inputString) {
    // Usa una regex per trovare la prima lettera alfabetica
    return preg_replace_callback('/[a-zA-Z]/', function($matches) {
        return strtoupper($matches[0]);
    }, $inputString, 1);
	}
	function formatLanguage(DateTime $dt,string $format,string $language = 'en') : string {
        if (class_exists('IntlDateFormatter')) {
            $curTz = $dt->getTimezone();
            if ($curTz->getName() === 'Z') {
                //INTL don't know Z
                $curTz = new DateTimeZone('UTC');
            }
    
            $formatPattern = strtr($format, array(
            'D' => '{#1}',
            'l' => '{#2}',
            'M' => '{#3}',
            'F' => '{#4}',
            'H' => '{#5}',
            'i' => '{#6}',
            's' => '{#7}',
          ));
            $strDate = $dt->format($formatPattern);
            $regEx = '~\{#\d\}~';
            while (preg_match($regEx, $strDate, $match)) {
                $IntlFormat = strtr($match[0], array(
              '{#1}' => 'E',
              '{#2}' => 'EEEE',
              '{#3}' => 'MMM',
              '{#4}' => 'MMMM',
              '{#5}' => 'HH',
              '{#6}' => 'mm',
              '{#7}' => 'ss',
            ));
                $fmt = datefmt_create(
                    $language ,
                    IntlDateFormatter::FULL,
                    IntlDateFormatter::FULL,
                    $curTz,
                    IntlDateFormatter::GREGORIAN,
                    $IntlFormat
                );
                $replace = $fmt ? datefmt_format($fmt, $dt) : "???";
                $strDate = str_replace($match[0], $replace, $strDate);
            }
    
            return $strDate;
        }
		else
			return $dt->format($format); 
	}

	/**
	 * in: start_date 'YYYY-MM-DD' , end_date 'YYYY-MM-DD' , date_from_user 'YYYY-MM-DD'
	 * out: true if date is contained in date range false otherwise
	 */
	function check_in_range($start_date, $end_date, $date_from_user){
	// Convert to timestamp

	$start_ts = strtotime($start_date);
	$end_ts = strtotime($end_date);
	$user_ts = strtotime($date_from_user);

	// Check that user date is between start & end
	return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
	}

	/**
	 * in: array => [day1] = 0 , [day2] = 1 ....ecct, in = number_of_week: 0 Monday
	 * out: false if day of week is not in array, true otherwise
	 */
	function check_in_day_of_week($array = array(),$day_of_week=null){
			switch($day_of_week){
				case 0: //Domenica
					if(!empty($array['day_7']))
						return true;
					break;
				case 1: //Lunedi
					if(!empty($array['day_1']))
						return true;
					break;
				case 2:
					if(!empty($array['day_2']))
						return true;
						break;
				case 3:
					if(!empty($array['day_3']))
						return true;
					break;
				case 4:
					if(!empty($array['day_4']))
						return true;
					break;
				case 5:
					if(!empty($array['day_5']))
						return true;
					break;
				case 6:
					if(!empty($array['day_6']))
						return true;
					break;
			}
			return false;
	}

	function array_change_key_case_recursive_lower($arr){
		return array_map(function($item){
			if(is_array($item))
				$item = array_change_key_case_recursive_lower($item);
			return $item;
		},array_change_key_case($arr));
	}
	function arrayRecursiveDiff($aArray1, $aArray2, $type_null=false) {
		$aReturn = array();
		foreach ($aArray1 as $mKey => $mValue) {
			if (array_key_exists($mKey, $aArray2)) {
				if (is_array($mValue)) {
					$aRecursiveDiff = arrayRecursiveDiff($mValue, $aArray2[$mKey],$type_null);
					if (count($aRecursiveDiff)) 
						$aReturn[$mKey] = $aRecursiveDiff; 
				} 
				else{
					if (empty($type_null) && $mValue != $aArray2[$mKey]) 
						$aReturn[$mKey] = $mValue;
					elseif(!empty($type_null) && empty($mValue) && !empty($aArray2[$mKey]))
						$aReturn[$mKey] = $mValue;
					
				}
			} 
			else
				$aReturn[$mKey] = $mValue;
			
		}
		return $aReturn;
	} 
	// Funzione per convertire un colore esadecimale in RGB
	function hexToRgb($hex) {
		$hex = ltrim($hex, '#');
		$bigint = hexdec($hex);
		$r = ($bigint >> 16) & 255;
		$g = ($bigint >> 8) & 255;
		$b = $bigint & 255;
		return [$r, $g, $b];
	}
	// Funzione per calcolare la luminosità relativa di un colore
	function getRelativeLuminance($color) {
		list($r, $g, $b) = $color;
		$r /= 255;
		$g /= 255;
		$b /= 255;
		$r = ($r <= 0.04045) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
		$g = ($g <= 0.04045) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
		$b = ($b <= 0.04045) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);
		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}
	// Funzione per calcolare il rapporto di contrasto tra due colori
	function getContrastRatio($color1, $color2) {
		$luminance1 = getRelativeLuminance($color1);
		$luminance2 = getRelativeLuminance($color2);
		$contrastRatio = (max($luminance1, $luminance2) + 0.05) / (min($luminance1, $luminance2) + 0.05);
		return $contrastRatio;
	}
	function getBestTextColor($textColorHex, $bgColorHex) {
    // Converti i colori da esadecimale a RGB
    $rgbColor1 = hexToRgb($textColorHex);
    $rgbColor2 = hexToRgb('#fff');
    $contrastRatio1 = getContrastRatio($rgbColor1, hexToRgb($bgColorHex));
    $contrastRatio2 = getContrastRatio($rgbColor2, hexToRgb($bgColorHex));
    $contrastThreshold = 4.5;
    if ($contrastRatio1 >= $contrastThreshold && $contrastRatio1 >= $contrastRatio2) {
        return $textColorHex;
    }
    return '#fff';
}
	/** 
  * Regular expression to validate different types of phone numbers 
  * Returns the sanitized number if it is valid 
  * Else it returns false 
  */ 
  function sanitize_PhoneNumber($toSanitizeNumber) 
  { 
	// simple pattern 
	$pattern = '/^[0-9\-\(\)\/\+\s]*$/'; 
  
	// blacklist phone numbers 
	$blacklist = array( 
		"0", 
		"00", 
		"000", 
		"0000", 
		"00000", 
		"000000", 
		"0000000", 
		"00000000", 
		"000000000", 
		"0000000000", 
		"00000000000", 
		"000000000000", 
		"0000000000000", 
		"00000000000000", 
		"000000000000000", 
		"1", 
		"11", 
		"111", 
		"1111", 
		"11111", 
		"111111", 
		"1111111", 
		"11111111", 
		"111111111", 
		"1111111111", 
		"11111111111", 
		"111111111111", 
		"1111111111111", 
		"11111111111111", 
		"111111111111111", 
		"2", 
		"22", 
		"222", 
		"2222", 
		"22222", 
		"222222", 
		"2222222", 
		"22222222", 
		"222222222", 
		"2222222222", 
		"22222222222", 
		"222222222222", 
		"2222222222222", 
		"22222222222222", 
		"222222222222222", 
		"3", 
		"33", 
		"333", 
		"3333", 
		"33333", 
		"333333", 
		"3333333", 
		"33333333", 
		"333333333", 
		"3333333333", 
		"33333333333", 
		"333333333333", 
		"3333333333333", 
		"33333333333333", 
		"333333333333333", 
		"4", 
		"44", 
		"444", 
		"4444", 
		"44444", 
		"444444", 
		"4444444", 
		"44444444", 
		"444444444", 
		"4444444444", 
		"44444444444", 
		"444444444444", 
		"4444444444444", 
		"44444444444444", 
		"444444444444444", 
		"012", 
		"0123", 
		"01234", 
		"012345", 
		"0123456", 
		"01234567", 
		"012345678", 
		"0123456789", 
		"123", 
		"1234", 
		"12345", 
		"123456", 
		"1234567", 
		"12345678", 
		"123456789", 
		"1234567890", 
		"12345678910", 
		"234", 
		"2345", 
		"23456", 
		"234567", 
		"2345678", 
		"23456789", 
		"345", 
		"3456", 
		"34567", 
		"345678", 
		"3456789", 
		"456", 
		"4567", 
		"45678", 
		"456789" 
	); 
  
  
   	$number = $toSanitizeNumber; 
	preg_match($pattern, $number, $matches); 
  
	$number = (strpos($number, '+')===0?'+':'').preg_replace("/[^0-9]/", "", $number ); 
  
	if(empty($number) || strlen($number)<6 || in_array(preg_replace("/[^0-9]/", "", $number ), $blacklist)) $matches=null; 
	if(!empty($matches)){ 
	  // echo "Valid Phone Number!!\n"; 
	  return $number; 
	}else{ 
	  // echo "<b>INVALID</b>!!\n"; 
	// sendTelegramSystemNotification('Numero NON Valido: '.$toSanitizeNumber); 
	  return false; 
	} 
  }


	/*
		add spaces to string or crop it by len
	*/
	function str_fit($len, $str) {
		if (strlen($str) > $len) return substr($str,0,$len);
		return str_pad($str, $len);
	}
	function monthNameIta($month) {
		if(!is_numeric($month)) return false;
		$arr = array(1=>'gennaio', 'febbraio', 'marzo', 'aprile',
					'maggio', 'giugno', 'luglio', 'agosto',
					'settembre', 'ottobre', 'novembre','dicembre');
		return $arr[$month];
	}

	function dayNameIta($day, $short=false) {
	//ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0) =	1 (for Monday) through 7 (for Sunday)
		if(!is_numeric($day)) return false;
		$arr = array(1=>'lunedì', 'martedì', 'mercoledì', 'giovedì',
					'venerdì', 'sabato', 'domenica');
		$arrshort = array(1=>'LUN', 'MAR', 'MER', 'GIO',
					'VEN', 'SAB', 'DOM');
		if(!$short)
		return $arr[$day];
		return $arrshort[$day];
	}
	function psTypeToText($ps_type) {
		if(!is_numeric($ps_type)) return false;
		$arr = array(16=>'ospite singolo', 'capofamiglia', 'capogruppo', 'familiare',
					'membro del gruppo');
		return $arr[$ps_type];
	}
	/*
		age calculator
		input date format: YYYY-MM-DD
	*/
	function getAge($from, $to) {
		list($year,$month,$day) = explode("-",$from);
		list($year_diff,$month_diff,$day_diff) = explode("-",$to);
		// $year_diff-=$year;
		// $month_diff-=$month;
		// $day_diff-=$day;
		// if ($day_diff < 0 || $month_diff < 0) $year_diff--;
		// if($year_diff < 0) $year_diff = 0;
		$age = (date("md", date("U", mktime(0, 0, 0, $month, intval($day), $year))) > date("md", date("U", mktime(0, 0, 0, $month_diff, intval($day_diff), $year_diff))) ? (($year_diff-$year)-1):($year_diff-$year));
		//return $year_diff;
		return $age;
	}
	/*
		build dressing html string
	*/
	function htmlDressing($d, $s, $c) {
		$str_dressing = "";
		if($d == 0){
			switch ($s) {
				case 1 :
					$str_dressing = "S";
					break;
				case 2 :
					$str_dressing = "D";
					break;
				case 3 :
					$str_dressing = "T";
					break;
				case 4 :
					$str_dressing = "Q";
					break;
			}
			if($s > 4){
				$str_dressing = $s."L";
			}
			$cot = $c > 0 ? ($c == 1 ? "C" : $c."C") : "";

			if(($s > 0) && ($c >0)) {
				$str_dressing .= "+".$cot;
			} else {
				$str_dressing .= $cot;
			}
		} else {
			if($d > 0) {

				$str_dressing .= $d == 1 ? "M" : $d."M";
			}
			if($s > 0) {
				if($str_dressing != "")
					$str_dressing .= $s==1 ? "+L" : "+".$s."L";
				else
					$str_dressing .= $s==1 ? "L" : $s."L";
			}
			if($c > 0) {
				if($str_dressing != "")
					$str_dressing .= $c==1 ? "+C" : "+".$c."C";
				else
					$str_dressing .= $c==1 ? "C" : "".$c."C";
			}
		}
		return $str_dressing;
	}
	// valid username, alphanumeric & longer than or equals 5 chars
	// return true or false
	function validateUsername( $username ) {
		if(preg_match('/^[a-zA-Z0-9]{5,}$/', $username)) {
			return true;
		}
		return false;
	}

	/**
	* Dato un array e una lingua da cercare (iso) restuisce la traduzione
	* se esiste, altrimenti la prima.
	* Se l'array è vuoto resituisce false.
	*/
	function getTranslationLanguages ($json = array(), $language=null){
		if(empty($json)) return false;
		if(is_string($json)) $json = _json_decode($json,true);
		if(is_object($json)) $json = (array) $json;
		if(!is_array($json)) return 'n.d.';
		if(empty($language)) $language='it';
		$language = strtolower($language);

		foreach($json as $languagekey => $text){
			$languagekey = strtolower($languagekey);
			if($languagekey == $language)
				return $text;
		}
		return array_values($json)[0];
	}
	function max_attribute_in_array($array, $prop) {
    return array_map(function($o) use($prop) {
			return $o->$prop;
		},$array);
	}
	// valid password, alphanumeric & longer than or equals 5 chars (not only numbers)
	// return true or false
	function validatePassword( $password ) {
		if(is_numeric( $password )) return false;
		if(preg_match('/^[a-zA-Z0-9]{5,}$/', $password)) {
			return true;
		}
		return false;
	}

	// valid email
	// return true or false
	function validateEmail( $email ) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return true;
		}
		return false;
	}

	// valid fullname, only letters
	// return true or false
	/*
		/[a-zA-Z'\xE0\xE8\xE9\xF9\xF2\xEC\x27]\s+[a-zA-Z'\xE0\xE8\xE9\xF9\xF2\xEC\x27]/
	*/
	function validateFullname( $fullname ) {
		if(preg_match("/[a-zA-Z'èéòàùì]\s+[a-zA-Z'èéòàùì]/", $fullname)) {
			return true;
		}
		return false;
	}

	// valid phone number, only numbers and chars: +, space, -, (, ).
	// return true or false
	function validatePhone( $phone ) {
		if(preg_match("/^[\+\s\-\(\)0-9]{1,}$/", $phone)) {
			return true;
		}
		return false;
	}

	function _json_decode($arr,$type=false){
		if(is_array($arr)) return $arr;
		return !empty($arr)?json_decode($arr,$type):array();
	}

  function deepJsonDecode($input, $assoc = false) {
    if (is_string($input)) {
        $decoded = json_decode($input, $assoc);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_string($decoded) || is_array($decoded)) {
                return deepJsonDecode($decoded, $assoc);
            }
            return $decoded;
        }

        return $input;
    } elseif (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = deepJsonDecode($value, $assoc);
        }
        return $input;
    }

    return $input;
	}
	// return a string represent ip address of client
	function getIP() {
		$ip = '';
		if (getenv("HTTP_CLIENT_IP"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("REMOTE_ADDR"))
			$ip = getenv("REMOTE_ADDR");
		else
			$ip = "UNKNOWN";
		return $ip;
	}



	/*
		returns an alphanumeric code of the specified length
	*/
	function getCode($length = 8){
		$code = array();
		$c = '';
		for ($i = 0; $i < $length; $i++){
			$code[0] = rand(1, 9);
			$code[1] = chr(rand(97, 122));
			$c .= $code[rand(0, 1)];
		}
		return $c;
	}
	function createSlug($str, $delimiter = '-', $max_length=30){
		$slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
		return substr($slug,0,$max_length);
	}

	function mysqliType($code){
		$mysqli_type = array();
		$mysqli_type[0] = "DECIMAL";
		$mysqli_type[1] = "TINYINT";
		$mysqli_type[2] = "SMALLINT";
		$mysqli_type[3] = "INTEGER";
		$mysqli_type[4] = "FLOAT";
		$mysqli_type[5] = "DOUBLE";

		$mysqli_type[7] = "TIMESTAMP";
		$mysqli_type[8] = "BIGINT";
		$mysqli_type[9] = "MEDIUMINT";
		$mysqli_type[10] = "DATE";
		$mysqli_type[11] = "TIME";
		$mysqli_type[12] = "DATETIME";
		$mysqli_type[13] = "YEAR";
		$mysqli_type[14] = "DATE";

		$mysqli_type[16] = "BIT";

		$mysqli_type[246] = "DECIMAL";
		$mysqli_type[247] = "ENUM";
		$mysqli_type[248] = "SET";
		$mysqli_type[249] = "TINYBLOB";
		$mysqli_type[250] = "MEDIUMBLOB";
		$mysqli_type[251] = "LONGBLOB";
		$mysqli_type[252] = "BLOB";
		$mysqli_type[253] = "VARCHAR";
		$mysqli_type[254] = "CHAR";
		$mysqli_type[255] = "GEOMETRY";
		if (array_key_exists($code, $mysqli_type)) {
			return $mysqli_type[$code];
		} else return false;
	}
	/*
	*	20150602	calcolo sconto percentuale
	*/
	function calcPercVal($total, $percentage) {
				$discount_value = ($total / 100) * $percentage;
		$discount_value = number_format($discount_value, 2, '.', '');

				return $discount_value;
		}

		function squashCharacters($str){
			static $normalizeChars = null;
			if ($normalizeChars === null) {
					$normalizeChars = array(
							'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'Ae',
							'Ç'=>'C',
							'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E',
							'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I',
							'Ð'=>'Dj',
							'Ñ'=>'N',
							'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
							'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U',
							'Ý'=>'Y',
							'Þ'=>'B',
							'ß'=>'Ss',
							'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'ae',
							'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e',
							'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
							'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',
							'ù'=>'u', 'ú'=>'u', 'û'=>'u',
							'ý'=>'y',
							'þ'=>'b',
							'ÿ'=>'y',
							'Š'=>'S', 'š'=>'s', 'ś' => 's',
							'Ž'=>'Z', 'ž'=>'z',
							'ƒ'=>'f',
				'’' => '\''
					);
			}
			return strtr($str, $normalizeChars);
	}


	function redirect($url){
		header('Location: ' . $url);
		die();
	}

	function hidden_redirect($path){
		require_once($path);
		exit();
	}
	function rgb_to_hex( string $rgba ) : string {
        if ( strpos( $rgba, '#' ) === 0 ) {
            return $rgba;
        }

        preg_match( '/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i', $rgba, $by_color );

        return sprintf( '#%02x%02x%02x', $by_color[1], $by_color[2], $by_color[3] );
    }
	function color_inverse($color){
		if ( strpos( strtolower($color), 'rgb' ) === 0 ) 
            $color=rgb_to_hex($color);
        
		$color = str_replace('#', '', $color);
		if (strlen($color) != 6){ return '000000'; }
		$rgb = '';
		for ($x=0;$x<3;$x++){
				$c = 255 - hexdec(substr($color,(2*$x),2));
				$c = ($c < 0) ? 0 : dechex($c);
				$rgb .= (strlen($c) < 2) ? '0'.$c : $c;
		}
		return '#'.$rgb;
	}
	function getContrastColor($hexColor){
		if ( strpos( strtolower($hexColor), 'rgb' ) === 0 ) 
		$hexColor=rgb_to_hex($hexColor);
        // hexColor RGB
        $R1 = hexdec(substr($hexColor, 1, 2));
        $G1 = hexdec(substr($hexColor, 3, 2));
        $B1 = hexdec(substr($hexColor, 5, 2));

        // Black RGB
        $blackColor = "#000000";
        $R2BlackColor = hexdec(substr($blackColor, 1, 2));
        $G2BlackColor = hexdec(substr($blackColor, 3, 2));
        $B2BlackColor = hexdec(substr($blackColor, 5, 2));

         // Calc contrast ratio
         $L1 = 0.2126 * pow($R1 / 255, 2.2) +
               0.7152 * pow($G1 / 255, 2.2) +
               0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
              0.7152 * pow($G2BlackColor / 255, 2.2) +
              0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
        }

        // If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return '#000000';
        } else { 
            // if not, return white color.
            return '#FFFFFF';
        }
	}

	function order_type($type){
		if(!empty(order_types()[$type])) return order_types()[$type]['name'];
		return 'N.A.';
	}
	function payment_types(){
		$order_types = order_types();
		return array_filter($order_types, function($v, $k){
			return $k!=4;
		}, ARRAY_FILTER_USE_BOTH);
	}

	function order_types(){
		return [
			1 => [
				"name" => 'Contanti',
			],
			5 => [
				"name" => 'Carta di credito',
			],
			2 => [
				"name" => 'Card',
			],
			3 => [
				"name" => 'Addebito',
			],
			4 => [
				"name" => 'Ricarica card',
			],
			7 => [
				"name" => 'Voucher',
			],
			8 => [
				"name" => 'Assegno',
			],
			9 => [
				"name" => 'Bonifico',
			],
			10 => [
				"name" => 'Crypto',
			]
		];
	}

	//if it is an important file, it returns false
	function checkExtension($url){
		$extension = pathinfo($url,PATHINFO_EXTENSION);
		if(empty($extension)) return true;
		$arrExtension = array("css","js","jpeg","jpg","gif","png","woff2","woff","ttf","map","ico","pdf","eot","txt","svg","mp3","shortcut");
		foreach ($arrExtension as $key => $value) {
				if(strpos(mb_strtoupper($value),mb_strtoupper($extension)) === false) return true;
		}
		return false;
	}

	function map_array_field(&$array, $field_key, $callback){
		if(!empty($array)) foreach($array as $key=>&$row){
		$row[$field_key] = $callback($row, $key);
		}
	}
	function array_column_edit($array, $field_key,$id, $callback){
		map_array_field($array, $field_key, $callback);
		return array_column($array, $field_key,$id);
  	}

	function returnResults($arr,$msg_success="",$msg_error=""){
		if(!empty($arr)){
			$status=true;
			foreach ($arr as $value) {
				if(!$value['success']) $status=false;
			}
			if($status)
				return json_encode(array('success' => true,'data'=>$arr,'msg'=>$msg_success));
			else
				return json_encode(array('success' => false,'data'=>$arr,'error'=>$msg_error));
		}else {
			return json_encode(array('success' => true,'data'=>$arr,'msg'=>"Nessuna modifica effettuata."));
		}
	}

	function exit_with_success($data,$msg=''){
		die(json_encode(array("success"=>true, "data"=>$data,'msg'=>$msg)));
	}

	function exit_with_error($message,$data = null,$error_code=null){
		die(json_encode(["success"=>false, "error"=>$message, "payload"=>$data,'error_code'=>$error_code]));
	}

	function createPeriod($start,$end,$str_interval='P1D',$inclusive = false){
		$start = new DateTime($start);// format 2021-09-01
		$end = new DateTime($end);
		if($inclusive)
			$end->modify('+1 day');
		$interval = new DateInterval($str_interval);
		$dateRange = new DatePeriod($start, $interval, $end);
		return $dateRange;
	}
	function createTimeIntervalsArray($start, $end, $step) {
    $result = [];

    $currentTime = strtotime($start);
    $endTime = strtotime($end);
    while ($currentTime < $endTime) {
        $intervalStart = date("H:i:s", $currentTime);
        $currentTime += $step;
        $intervalEnd = date("H:i:s", $currentTime);
        $result[] = array(
            'start' => $intervalStart,
            'end' => $intervalEnd
        );
    }
    return $result;
	}
	function timeToSeconds($time) {
    if (strpos($time, '.') !== false) {
        // Formato 'H:i:s.u'
        list($hours, $minutes, $seconds, $microseconds) = sscanf($time, '%d:%d:%d.%d');
    } else {
        // Formato 'H:i:s'
        list($hours, $minutes, $seconds) = sscanf($time, '%d:%d:%d');
        $microseconds = 0; // Imposta i microsecondi a zero per il formato senza millisecondi.
    }
    return $hours * 3600 + $minutes * 60 + $seconds + ($microseconds / 1000000);
	}
	function secondsToTime($seconds) {
    $hours = floor($seconds / 3600);
    $seconds %= 3600;
    $minutes = floor($seconds / 60);
    $seconds %= 60;
    $microseconds = ($seconds - floor($seconds)) * 1000000;

    $time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

    return $time;
	}

	function createArrayPeriod($start,$end,$str_interval='P1D',$inclusive = true){
		$perdiod = createPeriod($start,$end,'P1D',$inclusive);
		$result = [];
		foreach($perdiod as $date){
			$date = $date->format('Y-m-d');
			$result[$date] = $date;
		}
		return $result;
	}

	function splitDateRangeToWeek($start,$end,$time_zone='N',$n=7){ //w start sunday 0 and saturday 6
    $dateRange =createPeriod($start,date('Y-m-d',strtotime($end.' +1 day')));;

    $weekNumber = 1;
    $weeks = array();
    foreach ($dateRange as $date) {
        $weeks[$weekNumber][] = $date->format('Y-m-d');
        if ($date->format($time_zone) == $n) {
            $weekNumber++;
        }
    }
		return $weeks;
	}

	function splitDateRangeToDays($start,$end,$time_zone='N',$n=7,$format='Y-m-d',$change=false){
		$dateRange= createPeriod($start,date('y-m-d',strtotime($end.' +1 day')));
		$days = array();
		foreach ($dateRange as $date){
			$d=$date->format($format);
			if($change) $d=str_replace("-","/",$d);
			$days[] = $d;
		}
		return $days;
	}

	function getRangeValidRuleByRange($range,$start_day=0,$end_day=6,$time_zone='N'){ //w start sunday 0 and saturday 6, N lun:1 dom:7
		$dateRange =createPeriod($range['from'],date('y-m-d',strtotime($range['to'].' +1 day')));;
		$new_range = array('from'=>"",'to'=>"");
		foreach ($dateRange as  $date)
			if($date->format($time_zone) == $start_day){
				$new_range['from'] = $date->format('Y-m-d');
				break;
			}
		foreach (array_reverse(iterator_to_array($dateRange)) as  $date)
			if($date->format($time_zone) == $end_day){
				$new_range['to'] = $date->format('Y-m-d');
				break;
			}
			if(strtotime($new_range['from']) >= strtotime($new_range['to']))
				return array('success'=>false,'data'=>$range,'error'=>"Range di date troppo corto.");
			else
				return array('success'=>true,'data'=>$new_range);
	}

	function generateModalError($modal_id,$modal_title,$msg){
		return '
		<div id="'.$modal_id.'" class="modal fade">
			<div class="modal-dialog modal-dialog-centered modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">'.$modal_title.'</h4>
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">×</span>
							<span class="sr-only">close</span>
						</button>
					</div>
						<div class="modal-body">
							<div class="row mx-0 justify-content-start">
								<h4 class="ml-2">'.$msg.'</h4>
							</div>
						</div>
				</div>
			</div>
		</div>';
	}
	function getNDayByRange($start,$end,$n,$type="start"){
		$days = splitDateRangeToDays($start,$end);
		if($n > count($days) || $n<1 ) return exit_with_error("Number of invalid days.");
		if(!empty($type) && $type=="start"){
			return $days[($n-1)];
		}
		elseif(!empty($type) && $type=="end"){
			return $days[count($days)-(1*$n)];
		}
	}
	//range(from, to)
	//output (range_1(from,date-1),range_2(date,date),range_3(date+1,to))
	function splitRangeDateOld($range,$sub_range,$format='Y-m-d',$rule=false,$included=true,$rules=['start','end']){
		$new_range = array();
		$dateRange = createPeriod($range['from'], date('Y-m-d',strtotime($range['to'].' +1 day')) );
		$max_days = (date_diff(date_create($range['from']),date_create($range['to']))->format('%a')+1);
		// $dateRange = createPeriod($range['from'], $range['to']);
		foreach ($dateRange as $date){
			$d=$date->format($format);
			if ( (strtotime($d) < strtotime($sub_range['from']) || (empty($included) && strtotime($d) <= strtotime($sub_range['from']) ) ) && !empty(array_intersect(['start','start_multi_service'],$rules)) ){
				// se la data presa in esame è inferiore alla data di inizio della prenotazione
				if(empty($new_range[(empty($rule)?0:'start')]))
					$new_range[(empty($rule)?0:'start')]=array('from'=>$d,'to'=>$d);
				else if(!empty($new_range[(empty($rule)?0:'start')]) && strtotime($new_range[(empty($rule)?0:'start')]['from'])> strtotime($d))
					$new_range[(empty($rule)?0:'start')]['from'] = $d;
				else if(!empty($new_range[(empty($rule)?0:'start')]) && strtotime($new_range[(empty($rule)?0:'start')]['to'])< strtotime($d))
					$new_range[(empty($rule)?0:'start')]['to'] = $d;
			}else if( (strtotime($sub_range['to'])<strtotime($d) || (empty($included) && strtotime($sub_range['to'])<=strtotime($d)) )  && !empty(array_intersect(['end','end_multi_service'],$rules)) ){
				// se la data presa in esame è maggiore della data di fine della prenotazione
				if(empty($new_range[(empty($rule)?2:'end')]))
					$new_range[(empty($rule)?2:'end')]=array('from'=>$d,'to'=>$d);
				else if(!empty($new_range[(empty($rule)?2:'end')]) && strtotime($new_range[(empty($rule)?2:'end')]['from'])> strtotime($d))
					$new_range[(empty($rule)?2:'end')]['from'] = $d;
				else if(!empty($new_range[(empty($rule)?2:'end')]) && strtotime($new_range[(empty($rule)?2:'end')]['to'])< strtotime($d))
					$new_range[(empty($rule)?2:'end')]['to'] = $d;
			}
		}

		if(!empty($new_range[(empty($rule)?0:'start')]['to']) && !empty($new_range[(empty($rule)?2:'end')]['from']) && (date_diff(date_create(date('Y-m-d',strtotime($new_range[(empty($rule)?0:'start')]['to'].' +1 day'))),date_create(date('Y-m-d',strtotime($new_range[(empty($rule)?2:'end')]['from'].' -1 day'))))->format('%R%a'))>=0 ){
			$new_range[(empty($rule)?1:'middle')] = array(
				'from' => date('Y-m-d',strtotime($new_range[(empty($rule)?0:'start')]['to'].' +1 day')),
				'to' => date('Y-m-d',strtotime($new_range[(empty($rule)?2:'end')]['from'].' -1 day'))
			);
		}
		elseif((empty($new_range[(empty($rule)?0:'start')]['to']) || empty($new_range[(empty($rule)?2:'end')]['from'])) ){
			if( !empty($new_range[(empty($rule)?0:'start')]['to']) && (date_diff(date_create(date('Y-m-d',strtotime($new_range[(empty($rule)?0:'start')]['to'].' +1 day'))),date_create(date('Y-m-d',strtotime($range['to']))))->format('%R%a'))>=0 )
				$new_range[(empty($rule)?1:'middle')] = array(
					'from' => date('Y-m-d',strtotime($new_range[(empty($rule)?0:'start')]['to'].' +1 day')),
					'to' => $sub_range['to']
				);
			elseif( !empty($new_range[(empty($rule)?2:'end')]['from']) && (date_diff(date_create(date('Y-m-d',strtotime($range['from']))),date_create(date('Y-m-d',strtotime($new_range[(empty($rule)?2:'end')]['from'].' -1 day'))))->format('%R%a'))>=0 )
				$new_range[(empty($rule)?1:'middle')] = array(
					'from' => $sub_range['from'],
					'to' => date('Y-m-d',strtotime($new_range[(empty($rule)?2:'end')]['from'].' -1 day'))
				);
		}
		// $new_range[(empty($rule)?1:'middle')] = array(
		// 	'from' => $sub_range['from'],
		// 	'to' => $sub_range['to']
		// );
		// ritorna 3 range di date
		return $new_range;
	}
	function splitRangeDate($range,$sub_range,$format='Y-m-d',$rule=false,$included=true,$rules=['start','end']){
		$new_range = [];
		$days_available = (date_diff(date_create($range['from']),date_create($range['to']))->format('%a')+1);

		if(strtotime($sub_range['from'])<strtotime($range['from'])) $sub_range['from']=$range['from'];
		if(strtotime($sub_range['to'])>strtotime($range['to'])) $sub_range['to']=$range['to'];
		
		if((date_diff(date_create(date('Y-m-d',strtotime($sub_range['from'].($included?'+1 day':'')))),date_create(date('Y-m-d',strtotime($sub_range['to'].($included?'-1 day':'')))))->format('%R%a'))>=0 && $days_available>=1){
			$new_range[(!$rule?1:'middle')] = array(
				'from' => date('Y-m-d',strtotime($sub_range['from'].($included?'+1 day':''))),
				'to' => date('Y-m-d',strtotime($sub_range['to'].($included?'-1 day':'')))
			);
			$days_available-= (date_diff(date_create(date('Y-m-d',strtotime($sub_range['from'].($included?'+1 day':'')))),date_create(date('Y-m-d',strtotime($sub_range['to'].($included?'-1 day':'')))))->format('%a')+1);
		}

		if((date_diff(date_create(date('Y-m-d',strtotime($range['from']))),date_create(date('Y-m-d',strtotime($sub_range['from'].($included?'':'-1 day')))))->format('%R%a'))>=0 && $days_available>=1 && !empty(array_intersect(['start','start_multi_service'],$rules))){
			$new_range[(!$rule?0:'start')] = array(
				'from' => date('Y-m-d',strtotime($range['from'])),
				'to' => date('Y-m-d',strtotime($sub_range['from'].($included?'':'-1 day')))
			);
			$days_available-= (date_diff(date_create(date('Y-m-d',strtotime($range['from']))),date_create(date('Y-m-d',strtotime($sub_range['from'].($included?'':'-1 day')))))->format('%a')+1);
		}
			
		if((date_diff(date_create(date('Y-m-d',strtotime($sub_range['to'].($included?'':'+1 day')))),date_create(date('Y-m-d',strtotime($range['to']))))->format('%R%a'))>=0 && $days_available>=1 && !empty(array_intersect(['end','end_multi_service'],$rules))){
			$new_range[(!$rule?2:'end')] = array(
				'from' => date('Y-m-d',strtotime($sub_range['to'].($included?'':'+1 day'))),
				'to' => date('Y-m-d',strtotime($range['to']))
			);
			$days_available-= (date_diff(date_create(date('Y-m-d',strtotime($sub_range['to'].($included?'':'+1 day')))),date_create(date('Y-m-d',strtotime($range['to']))))->format('%a')+1);
		}
		return $new_range;
	}

	function firstDayAvailable($date,$ranges=array()) {

		$period = createPeriod($date['from'],date('y-m-d',strtotime($date['to'].' +1 day')));
		$firstDayAvailable=false;

		$busy_days=array();
		foreach ($ranges as $range){
			$busy_period = createPeriod($range['date_from'],date('y-m-d',strtotime($range['date_to'].' +1 day')));
			foreach ($busy_period as $day){
				$busy_days[]= strtotime($day->format('Y-m-d'));
			}
		}
		$all_days=array();
		foreach ($period as $day){
			$all_days[]= strtotime($day->format('Y-m-d'));
		}
		if(!empty(array_diff($all_days,$busy_days)))
			$firstDayAvailable = date('Y-m-d',min(array_diff($all_days,$busy_days)));
		return $firstDayAvailable;
	}

	function transliterateString($txt) {
        $transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'E', 'ё' => 'e', 'Ё' => 'E', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
        return str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);
  }


	function explain($variable){
		$style = "position: relative;background: #333;white-space: pre;word-wrap: break-word;overflow: scroll;max-width: 100%;max-height: 600px;color: #ededed;margin: 1rem;border-radius: 1rem;tab-size: 5px;";
		$code_style = "display: block;margin: 0 0 0 10px;padding: 15px 16px 14px;border-left: 1px solid #555;overflow-x: auto;font-size: 13px;line-height: 19px;color:#4dd0e1";
		echo '<pre style="'.$style.'"><code style="'.$code_style.'">'.htmlspecialchars(print_r($variable,true)).'</code></pre>';
	}

	function checkTypeDevice($useragent){
		if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
			return 'mobile';
		else return 'desktop';
	}
	function guidv4($data = null) {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
