<?php

namespace success\log\rabbitmq;

use mikemadisonweb\rabbitmq\Configuration;
use Ramsey\Uuid\Rfc4122\UuidV4;
use yii\base\ErrorHandler;
use yii\di\Instance;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\Target;
use yii\queue\Queue;

class RabbitmqTarget extends Target
{
    /**
     * @var Queue|string|array the queue component or component id or component config array
     */
    public $queue;

    /**
     * @var Configuration|string|array the rabbitmq component or component id or component config array
     */
    public $rabbitmq;
    public $exchange;
    public $key;
    public $producer;
    public $fields;

    public function init()
    {
        parent::init();

        $this->rabbitmq = Instance::ensure($this->rabbitmq, Configuration::class);
        $this->queue = Instance::ensure($this->queue, Queue::class);
    }

    public function export()
    {
        $messages = [];
        foreach ($this->messages as $message) {
            $messages[] = $this->formatMessage($message);
        }

        $job = new RabbitmqJob([
            'rabbitmq' => $this->rabbitmq,
            'exchange' => $this->exchange,
            'key' => $this->key,
            'producer' => $this->producer,
            'fields' => $this->fields,
            'messages' => $messages,
        ]);
        $this->queue->push($job);
    }

    public function formatMessage($message)
    {
        $_msg = $message;

        list($message, $level, $category, $timestamp) = $_msg;

        $level = Logger::getLevelName($level);

        $exception = '';
        if (!is_string($message)) {
            if ($message instanceof \Throwable) {
                $message = $message->getMessage();
                $exception = $this->formatExceptionMessage($message); // get the exception message detail with stack trace, may have previous exception
            } else {
                $message = VarDumper::export($message);
            }
        }

        $id = UuidV4::uuid4()->toString();

        $given = \DateTime::createFromFormat('U.u', YII_BEGIN_TIME, new \DateTimeZone('UTC'));
        if ($given instanceof \DateTime) {
            $timestamp = $given->format('Y-m-d H:i:s.u');
        } else {
            $timestamp = null;
        }
        unset($given);

        return compact('id', 'level', 'message', 'exception', 'category', 'timestamp');
    }

    /**
     * Format exception message
     *
     * @param \Throwable $exception
     * @return string
     */
    private function formatExceptionMessage(\Throwable $exception): string
    {
        $message = ErrorHandler::convertExceptionToVerboseString($exception);

        $previous = $exception->getPrevious();
        if ($previous === null) {
            return $message;
        } else {
            return $message . PHP_EOL . 'Next ' . $this->formatExceptionMessage($previous);
        }
    }
}
