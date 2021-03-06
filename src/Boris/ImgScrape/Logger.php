<?php

namespace Boris\ImgScrape;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monologger;

/**
 * Logger.php
 * Author: Boris Pavlov <borispavlov0 at gmail.com>
 * Date: 20-Dec-2014
 */
class Logger
{
    const INFO = Monologger::INFO;
    const NOTICE = Monologger::NOTICE;
    const DEBUG = Monologger::DEBUG;
    const ERROR = Monologger::ERROR;
    /**
     *
     * @var array
     */
    private $config;
    /**
     *
     * @var Monologger
     */
    private $logger;

    /**
     * Default constructor. Please refer to the configuration reference for the format of $config
     *
     * @param array $config
     */

    public function __construct(array $config = null)
    {
        $this->config = require __DIR__ . '/config/config_logger.php';
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }

        $this->createLogger($this->config['handlers']);
    }

    /**
     * Initializes the logger
     *
     * @param array $handlers
     */
    public function createLogger($handlers)
    {
        $output = "[%datetime%] [%level_name%] - %message% %context% %extra%\n";
        $formatter = new LineFormatter($output);

        $this->logger = new Monologger('imgScrape');

        foreach ($handlers as $handler) {
            $handler = new StreamHandler($handler['dir'], $this->getMappedHandlerLevel($handler['level']));
            $handler->setFormatter($formatter);

            $this->logger->pushHandler($handler);
        }

        $this->logger->pushProcessor(function ($record) {
            $record['extra']['PID'] = getmypid();

            return $record;
        });
    }

    /**
     * Mapping function for easier use
     *
     * @param string $level
     *
     * @return string
     */
    private function getMappedHandlerLevel($level)
    {
        switch ($level) {
            case 'debug':
                return Monologger::DEBUG;
                break;
            case 'notice':
                return Monologger::NOTICE;
                break;
            case 'error':
                return Monologger::ERROR;
                break;
            case 'info':
            default:
                return Monologger::INFO;
        }
    }

    /**
     * Log function to trigger "log" on the main Logger instance
     *
     * @param string $level
     * @param string $message
     */
    public function log($level, $message)
    {
        if ($this->config['enabled']) {
            $this->logger->log($level, $message);
        }
    }
}
