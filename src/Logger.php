<?php

declare(strict_types=1);

namespace RoadRunner\Logger;

use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\Goridge\RPC\RPCInterface;

final class Logger
{
    private RPCInterface $rpc;

    public function __construct(RPCInterface $rpc)
    {
        $this->rpc = $rpc->withServicePrefix('app-logger');
    }

    public function error(string $message): void
    {
        try {
            $this->rpc->call('Error', $message);
        } catch (ServiceException $e) {
            $this->handleError($e);
        }
    }

    public function warning(string $message): void
    {
        try {
            $this->rpc->call('Warning', $message);
        } catch (ServiceException $e) {
            $this->handleError($e);
        }
    }

    public function info(string $message): void
    {
        try {
            $this->rpc->call('Info', $message);
        } catch (ServiceException $e) {
            $this->handleError($e);
        }
    }

    public function debug(string $message): void
    {
        try {
            $this->rpc->call('Debug', $message);
        } catch (ServiceException $e) {
            $this->handleError($e);
        }
    }

    public function log(string $message): void
    {
        try {
            $this->rpc->call('Log', $message);
        } catch (ServiceException $e) {
            $this->handleError($e);
        }
    }

    /**
     * @param ServiceException $e
     * @throws Exception\LoggerException
     */
    private function handleError(ServiceException $e): void
    {
        $message = \str_replace(["\t", "\n"], ' ', $e->getMessage());

        throw new Exception\LoggerException($message, (int)$e->getCode(), $e);
    }
}
