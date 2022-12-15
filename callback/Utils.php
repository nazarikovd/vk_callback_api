<?php

/**
* Function list:

	send(*чат, *текст, *токен, аттач, клавиатура)	отправляем сообщение

	sticker(*чат, *ид, *токен)						отправляем стикер

	resolveLink(*ссылка, *токен)					получаем id по короткой ссылке

	getVK(*ид или ссылка, *токен)					получаем объект по id или ссылке на группу / юзера

	getUploadServer(*чат, *токен)					получаем сервер для загрузки фото в лс

	savePhoto(*фото, *сервер, *хеш, *токен)			сохраняем фото в лс

	uploadFile(*путь, *сервер)						грузим фото на сервер вк

	uploadPhoto(*путь, *токен)						возвращает фото для аттача по его path

	compressImage(*путь, *качество)					сжать картинку

	get(*юрл, хедеры, пост)							простой curl с POST и headers

	genBoard(*"кнопка1,кнопка2,кнопка3")			простая генерация клавиатуры

	easyMessage(*сообщение)							парсит сообщение для удобства (убирает @club и делает массив всех слом разделенных пробелом)

* Classes list:
* - VKApi
*/

class VKApi
{
    public function send($peer, $message, $token, $attach = NULL, $keyboad = NULL)
    {
        $params = array(
            'keyboard' => $keyboad,
            'message' => $message,
            'peer_id' => $peer,
            'random_id' => '0',
            'v' => '5.100',
            'access_token' => $token,
            'attachment' => $attach
        );

        return static ::get('https://api.vk.com/method/messages.send?' . http_build_query($params));

    }

    public function sticker($peer, $id, $token)
    {
        $params = array(
            'peer_id' => $peer,
            'random_id' => '0',
            'v' => '5.100',
            'access_token' => $token,
            'sticker_id' => $id
        );

        return static ::get('https://api.vk.com/method/messages.send?' . http_build_query($params));

    }
    public function resolveLink($link, $token)
    {
        $params = array(
            'screen_name' => $link,
            'v' => '5.100',
            'access_token' => $token
        );
        $a = static ::get('https://api.vk.com/method/utils.resolveScreenName?' . http_build_query($params));
        return json_decode($a)->response;
    }

    public function getVK($id, $token)
    {

        if (!is_numeric($id))
        {
            $obj = static ::resolveLink($id, $token);
            $id = $obj->object_id;
            if ($obj->type == 'group')
            {
                $id = - $id;
            }
        }
        if ($id > 0)
        {
            $type = 'user';
            $params = array(
                'user_id' => $id,
                'fields' => 'photo100, photo200, domain, last_seen',
                'v' => '5.100',
                'access_token' => $token
            );
            $a = static ::get('https://api.vk.com/method/users.get?' . http_build_query($params));
        }
        else
        {
            $type = 'group';
            $params = array(
                'group_id' => - $id,
                'v' => '5.100',
                'access_token' => $token
            );
            $a = static ::get('https://api.vk.com/method/groups.getById?' . http_build_query($params));
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
        $a = static ::get('https://api.vk.com/method/photos.getMessagesUploadServer?' . http_build_query($params));
        return json_decode($a);
    }
    public function savePhoto($photo, $server, $hash, $token)
    {
        $params['photo'] = $photo;
        $params['server'] = $server;
        $params['hash'] = $hash;

        $params['access_token'] = $token;
        $params['v'] = '5.100';
        $a = static ::get('https://api.vk.com/method/photos.saveMessagesPhoto?' . http_build_query($params));
        return json_decode($a);

    }
    public function uploadFile($server, $path)
    {
        $ch = curl_init($server);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);

        if (class_exists('\CURLFile'))
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['file1' => new \CURLFile($path) ]);
        }
        else
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['file1' => "@$path"]);
        }

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    public function uploadPhoto($path, $peer, $token)
    {
        $server = static ::getUploadServer($peer, $token);
        $upload = json_decode(static ::uploadFile($server
            ->response->upload_url, $path));
        $save = static ::savePhoto($upload->photo, $upload->server, $upload->hash, $token);
        $owner = $save->response[0]->owner_id;
        $photo = $save->response[0]->id;
        return 'photo' . $owner . '_' . $photo;

    }
    function compressImage($source_url, $quality)
    {
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
    public function genBoard($labels)
    {
        $label = explode(",", $labels);
        $buttons = array();
        foreach ($label as $text)
        {
            array_push($buttons, [["action" => ["type" => "text", "payload" => '{"button": "1"}', "label" => $text], "color" => "primary"]]);
        }

        $keyb = [

        "inline" => true, "buttons" => $buttons

        ];
        return json_encode($keyb);
    }
    public function easyMessage($message)
    {

        $message = explode(' ', $message);
        if (strpos($message[0], '[') !== false && strpos($message[0], '|') !== false)
        {
            unset($message[0]);
            $message = array_values($message);
        }
        $message[0] = mb_convert_case($message[0], MB_CASE_LOWER, "UTF-8");
        return $message;
    }
}

