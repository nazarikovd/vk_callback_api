<?php

$message = $vk->easyMessage($object->text); //парсим сообщение для удобства (убирает @club и делает массив всех слом разделенных пробелом)
$peer = $object->peer_id; //получаем id диалога
$from = $object->from_id; //получаем от кого сообщение
$token = Config::ACCESS_TOKEN; //получаем токен для ответа из конфига

switch($message[0]){ //смотрим первое слово сообщения
		
	case '.test':
	
		$vk->send($peer, 'Бот работает!!!', $token); //отвечаем что все чики пуки
        break;
		
	case '.upload':
	
		$photo = $vk->uploadPhoto('image.png', 0, $token); //загружаем картинку image.png от имени группы (peer = 0)
		
		$vk->send($peer, 'Тестирование загрузки изображения', $token, $photo); //отвечаем и крепим картинку
		break;
		
	case '.помощь':
	
		$vk->send($peer, 'Команды:', $token, '', $vk->genBoard('.test,.upload,.link')); // отправляем кнопки
		break;
		
	case '.link':
	
        if (!empty($object->attachments)) //если к сообщению прикрепленна картинка
			$vk->send($peer, end($object->attachments[0]->photo->sizes)->url, $token); //отправляем прямую ссылку на картинку
			
        break;

}

$api->finish(); //отправляем Вконтакте ОК (все чики пуки)
