<?php

namespace success\log\rabbitmq;

use mikemadisonweb\rabbitmq\Configuration;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class RabbitmqJob extends BaseObject implements JobInterface
{
    public $rabbitmq = 'rabbitmq';
    public $key;
    public $exchange;
    public $producer;
    public $fields;

    public $messages;

    public function execute($queue)
    {
        /** @var Configuration $rabbitmq */
        $rabbitmq = \Yii::$app->get('rabbitmq');
        $producer = $rabbitmq->getProducer($this->producer);

        foreach ($this->messages as $message) {
            if (!empty($this->fields)) {
                $model = new RabbitmqLogModel($message);
                $message = $model->toArray($this->fields);
            }
            $producer->publish($message, $this->exchange, $this->key);
        }
    }
}
