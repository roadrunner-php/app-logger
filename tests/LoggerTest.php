<?php

declare(strict_types=1);

namespace RoadRunner\Logger\Tests;

use Mockery as m;
use RoadRunner\Logger\Logger;
use RoadRunner\Logger\Exception\LoggerException;
use Spiral\Goridge\RPC\RPCInterface;

final class LoggerTest extends TestCase
{
    private Logger $logger;
    /** @var m\LegacyMockInterface|m\MockInterface|RPCInterface */
    private $rpc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rpc = m::mock(RPCInterface::class);

        $this->rpc
            ->shouldReceive('withServicePrefix')
            ->once()
            ->with('app')
            ->andReturnSelf();

        $this->logger = new Logger($this->rpc);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testErrorCall(): void
    {
        $this->rpc
            ->shouldReceive('call')
            ->once()
            ->with('Error', 'foo')
            ->andReturnNull();

        $this->logger->error('foo');
    }

    public function testIfErrorCallFailedThrowAnException(): void
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->rpc
            ->shouldReceive('call')
            ->once()
            ->andThrow(new LoggerException('Something went wrong'));

        $this->logger->error('foo');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testWarningCall(): void
    {
        $this->rpc
            ->shouldReceive('call')
            ->once()
            ->with('Warning', 'foo')
            ->andReturnNull();

        $this->logger->warning('foo');
    }

    public function testIfWarningCallFailedThrowAnException(): void
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->rpc
            ->shouldReceive('call')
            ->once()
            ->andThrow(new LoggerException('Something went wrong'));

        $this->logger->warning('foo');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testInfoCall(): void
    {
        $this->rpc
            ->shouldReceive('call')
            ->once()
            ->with('Info', 'foo')
            ->andReturnNull();

        $this->logger->info('foo');
    }

    public function testIfInfoCallFailedThrowAnException(): void
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->rpc
            ->shouldReceive('call')
            ->once()
            ->andThrow(new LoggerException('Something went wrong'));

        $this->logger->info('foo');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDebugCall(): void
    {
        $this->rpc
            ->shouldReceive('call')
            ->once()
            ->with('Debug', 'foo')
            ->andReturnNull();

        $this->logger->debug('foo');
    }

    public function testIfDebugCallFailedThrowAnException(): void
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->rpc
            ->shouldReceive('call')
            ->once()
            ->andThrow(new LoggerException('Something went wrong'));

        $this->logger->debug('foo');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testLogCall(): void
    {
        $this->rpc
            ->shouldReceive('call')
            ->once()
            ->with('Log', 'foo')
            ->andReturnNull();

        $this->logger->log('foo');
    }

    public function testIfLogCallFailedThrowAnException(): void
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->rpc
            ->shouldReceive('call')
            ->once()
            ->andThrow(new LoggerException('Something went wrong'));

        $this->logger->log('foo');
    }
}
