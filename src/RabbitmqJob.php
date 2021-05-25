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
    public $logModelClass;

    public $messages;

    public function execute($queue)
    {
        /** @var Configuration $rabbitmq */
        $rabbitmq = \Yii::$app->get('rabbitmq');
        $producer = $rabbitmq->getProducer($this->producer);

        foreach ($this->messages as $message) {
            if ($this->logModelClass !== null) {
                /** @var RabbitmqLogModel $model */
                $model = new $this->logModelClass($message);
                $message = $model->toArray();
            }
            $producer->publish($message, $this->exchange, $this->key);
        }
    }
}
