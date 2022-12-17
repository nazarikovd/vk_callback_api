<?php

class VKApi
{
public function send ($peer, $message, $token, $attach=NULL, $keyboad=NULL) { 
$params = array( 
            'keyboard' => $keyboad,
            'message' => $message, 
            'peer_id' => $peer,
            'random_id' => '0',
            'v' => '5.100',
            'access_token' => $token,
            'attachment' => $attach
			);

return static::get('https://api.vk.com/method/messages.send?'.http_build_query($params));

}

public function sticker ($peer, $id, $token) { 
$params = array( 
            'peer_id' => $peer,
            'random_id' => '0',
            'v' => '5.100',
            'access_token' => $token,
            'sticker_id'=>$id
			);

return static::get('https://api.vk.com/method/messages.send?'.http_build_query($params));

}
public function resolveLink($link, $token){
	$params = array( 
            'screen_name' => $link,
            'v' => '5.100',
            'access_token' => $token
);
	$a = static::get('https://api.vk.com/method/utils.resolveScreenName?'.http_build_query($params));
		return json_decode($a)->response;
}

public function getVK($id, $token){
	if (strpos($id, '[id') !== false && strpos($id, '|') !== false)
    {
		$id = explode('|', $id)[0];
		$id = str_replace('[id', '', $id);
    }
	if (strpos($id, '[club') !== false && strpos($id, '|') !== false)
    {
		$id = explode('|', $id)[0];
		$id = -str_replace('[club', '', $id);
    }
	if (strpos($id, '[public') !== false && strpos($id, '|') !== false)
    {
		$id = explode('|', $id)[0];
		$id = -str_replace('[public', '', $id);
    }
	if(!is_numeric($id)){
	
	

		$obj = static::resolveLink($id, $token);
		$id = $obj->object_id;
		if($obj->type == 'group'){
			$id = -$id;
		}
	}
	if($id > 0){
		$type = 'user';
	$params = array( 
            'user_id' => $id,
			'fields' => 'photo100, photo200, domain, last_seen',
            'v' => '5.100',
            'access_token' => $token
			);
	$a = static::get('https://api.vk.com/method/users.get?'.http_build_query($params));
	}else{
		$type = 'group';
		$params = array(
            'group_id' => -$id,
            'v' => '5.100',
            'access_token' => $token
			);
	$a = static::get('https://api.vk.com/method/groups.getById?'.http_build_query($params));
	}
	$a = array(
		$type => json_decode($a)->response[0]
	);

	return json_decode(json_encode($a));
}
public function getUploadServer($peer, $token)
{
		$params['peer_id'] = $peer;
		$params['access_token'] = $token;
		$params['v'] = '5.100';
        $a = static::get('https://api.vk.com/method/photos.getMessagesUploadServer?'.http_build_query($params));
		return json_decode($a);
		}
public function savePhoto($photo, $server, $hash, $token)
    {
		$params['photo'] = $photo;
		$params['server'] = $server;
		$params['hash'] = $hash;
		
		$params['access_token'] = $token;
		$params['v'] = '5.100';
        $a = static::get('https://api.vk.com/method/photos.saveMessagesPhoto?'.http_build_query($params));
		return json_decode($a);
		
    }
public function uploadFile($server, $path)
    {
        $ch = curl_init($server);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);

        if (class_exists('\CURLFile')) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['file1' => new \CURLFile($path)]);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['file1' => "@$path"]);
        }

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
public function uploadPhoto($path, $peer, $token)
{
	$server = static::getUploadServer($peer, $token);
	$upload = json_decode(static::uploadFile($server->response->upload_url, $path));
	$save = static::savePhoto($upload->photo, $upload->server, $upload->hash, $token);
	$owner = $save->response[0]->owner_id;
	$photo = $save->response[0]->id;
	return 'photo'.$owner.'_'.$photo;
	

}
function compressImage($source_url, $quality) {
    $info = getimagesize($source_url);

    if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($source_url);
    elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($source_url);
    elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($source_url);

    //save file
    imagejpeg($image, $source_url, $quality);

    //return destination file
    return $source_url;
}
public function get($url, $headers = null, $postData = null)
	{
		
		$agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36';
		$ch = curl_init($url);
		if ($headers)
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		if ($postData)
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}
		
			
		
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
public function genBoard($labels){
	
	$label = explode(",", $labels);
	$buttons = array();
foreach ($label as $text)
{
	array_push($buttons, 
                    [["action" => [
                    "type" => "text",
                    "payload" => '{"button": "1"}',
                    "label" => $text],
                    "color" => "primary"]]
                );
	}
	
$keyb = [
                   
					"inline" => true,
                    "buttons" => $buttons

];
return json_encode($keyb);

}

public function easyMessage($message){
	
$message = explode(' ', $message);
if (strpos($message[0], '[') !== false && strpos($message[0], '|') !== false)
    {
        unset($message[0]);
        $message = array_values($message);
    }
    $message[0] = mb_convert_case($message[0], MB_CASE_LOWER, "UTF-8");
	return $message;
}

function reg($id){
$xml = file_get_contents('https://vk.com/foaf.php?id='.$id);
if(strpos($xml, 'banned') !== false){
	$result = array(
'date' => 'Пользователь заблокирован или удален',
'diff' => 0
);
return (object) $result;
}
$xml = preg_replace_callback_array(array(
			'~&(?:#\d+;?)?~' => function($match){ htmlspecialchars($match[0]); },
			'~(?:ya|foaf|img|dc|rdf|):~' => function(){}
		), $xml);

$xml = simplexml_load_string($xml);
$xml = json_encode($xml);
$xml = str_replace('@attributes', 'data', $xml);
$xml = json_decode($xml);
$res = $xml->Person->created->data->date;

$date = substr($res, 0, 10).' '.$time = substr($res, 11, 6);
$date2 = strtotime(substr($res, 0, 10));
$now = time();
$datediff = $now - $date2;
$result = array(
'date' => date("d.m.Y, H:i", strtotime($date)),
'diff' => round($datediff / (60 * 60 * 24))
);

return (object) $result;
}
}




