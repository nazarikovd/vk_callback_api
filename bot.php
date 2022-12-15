<?php
require_once('callback/CallBackApi.php'); //подключаем библиотеку каллбеков
require_once('callback/Utils.php'); //подключаем библиотеку функций для работы с вк
$input = file_get_contents('php://input'); //получаем данные от ВКонтакте





$api = new CallBack($input); //создаем новый класс каллбеков
$vk = new VKApi(); //создаем новый класс функций для работы с вк

$event = $api->parseEvent(); //получаем событие от ВКонтакте

switch($event->type){ //проверяем тип события
	
	
	case 'message': //если это новое сообщение
	
		$object = $event->object; //получаем объект сообщения
		if(empty($object))
				$api->finish(); //если событие пустое??? умираем с ок
		require_once('events/Messages.php'); //подключаем ответчик на сообщения
	break;
	
	
	default: //если это что то другое
	
		$api->finish(); //умираем с ок
	break;

}
	
	
	








