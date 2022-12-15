<?php
require_once ('Config.php');

class CallBack
{
    public $data;

    public function __construct($data)
    {

        if (empty($data))
        {

            // no data no answer)
            
        }
        else
        {

            $this->data = json_decode($data);

        }

        if (Config::SECRET !== $this->data->secret)
        {

            die(json_encode(['error' => 'BAD_TOKEN']));

        }

        if (Config::GROUP_ID !== $this->data->group_id)
        {

            die(json_encode(['error' => 'GROUP_ID_WRONG']));

        }

        if ($this->data->type == 'confirmation')
        {
            $this->finish(Config::CONFIRMATION);

        }

    }

    public function parseEvent()
    {

        $data = $this->data;

        switch ($data->type)
        {

            case 'message_new':
                $a = array(
                    'type' => 'message',
                    'object' => $data->object
                );
                return (object) $a;
            break;

            default:
                return null;
            break;

        }

    }

    public function finish($text = null)
    {

        if (empty($text))
        {
            die('ok');
        }

        die($text);
    }

}

