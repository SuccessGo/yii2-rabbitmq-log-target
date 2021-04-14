<?php

namespace success\log\rabbitmq;

use yii\base\Model;

class RabbitmqLogModel extends Model
{
    /**
     * @var string Log id
     */
    public $id;
    /**
     * @var string Log level
     */
    public $level;
    /**
     * @var string Log message
     */
    public $message;
    /**
     * @var string Log category
     */
    public $category;
    /**
     * @var string Log exception stack trace string
     */
    public $exception;
    /**
     * @var string Log time
     */
    public $timestamp;
}
