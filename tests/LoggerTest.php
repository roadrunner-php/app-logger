<?php

declare(strict_types=1);

namespace RoadRunner\Logger\Tests;

use Mockery as m;
use RoadRunner\Logger\Logger;
use RoadRunner\Logger\Exception\LoggerException;
use RoadRunner\Logger\LogLevel;
use Spiral\Goridge\RPC\Codec\ProtobufCodec;
use Spiral\Goridge\RPC\RPCInterface;

final class LoggerTest extends TestCase
{
    private Logger $logger;
    private RpcMock $rpc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rpc = new RpcMock($rpc = m::mock(RPCInterface::class));

        $this->rpc->assertServicePrefix('app');

        $this->logger = new Logger($rpc);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testErrorCall(): void
    {
        $this->rpc->assertCalled(LogLevel::Error, 'foo');
        $this->logger->error('foo');
    }

    public function testErrorCallWithContext(): void
    {
        $this->rpc
            ->assertDefinedCodec(ProtobufCodec::class)
            ->assertCalledWithContext(LogLevel::Error, [
                'message' => 'some message',
                'logAttrs' => [
                    [
                        'key' => 'foo',
                        'value' => 'bar',
                    ],
                ],
            ]);

        $this->logger->error('some message', ['foo' => 'bar']);
    }

    public function testIfErrorCallFailedThrowAnException(): void
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->rpc->callShouldThrowException(new LoggerException('Something went wrong'));
        $this->logger->error('foo');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testWarningCall(): void
    {
        $this->rpc->assertCalled(LogLevel::Warning, 'foo');
        $this->logger->warning('foo');
    }

    public function testWarningCallWithContext(): void
    {
        $this->rpc
            ->assertDefinedCodec(ProtobufCodec::class)
            ->assertCalledWithContext(LogLevel::Warning, [
                'message' => 'some message',
                'logAttrs' => [
                    [
                        'key' => 'foo',
                        'value' => 'bar',
                    ],
                ],
            ]);

        $this->logger->warning('some message', ['foo' => 'bar']);
    }

    public function testIfWarningCallFailedThrowAnException(): void
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->rpc->callShouldThrowException(new LoggerException('Something went wrong'));
        $this->logger->warning('foo');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testInfoCall(): void
    {
        $this->rpc->assertCalled(LogLevel::Info, 'foo');
        $this->logger->info('foo');
    }

    public function testInfoCallWithContext(): void
    {
        $this->rpc
            ->assertDefinedCodec(ProtobufCodec::class)
            ->assertCalledWithContext(LogLevel::Info, [
                'message' => 'some message',
                'logAttrs' => [
                    [
                        'key' => 'foo',
                        'value' => 'bar',
                    ],
                ],
            ]);

        $this->logger->info('some message', ['foo' => 'bar']);
    }

    public function testIfInfoCallFailedThrowAnException(): void
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->rpc->callShouldThrowException(new LoggerException('Something went wrong'));
        $this->logger->info('foo');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDebugCall(): void
    {
        $this->rpc->assertCalled(LogLevel::Debug, 'foo');
        $this->logger->debug('foo');
    }

    public function testDebugCallWithContext(): void
    {
        $this->rpc
            ->assertDefinedCodec(ProtobufCodec::class)
            ->assertCalledWithContext(LogLevel::Debug, [
                'message' => 'some message',
                'logAttrs' => [
                    [
                        'key' => 'foo',
                        'value' => 'bar',
                    ],
                ],
            ]);

        $this->logger->debug('some message', ['foo' => 'bar']);
    }

    public function testIfDebugCallFailedThrowAnException(): void
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->rpc->callShouldThrowException(new LoggerException('Something went wrong'));
        $this->logger->debug('foo');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testLogCall(): void
    {
        $this->rpc->assertCalled(LogLevel::Log, 'foo');
        $this->logger->log('foo');
    }

    public function testLogCallWithContext(): void
    {
        $this->rpc
            ->assertDefinedCodec(ProtobufCodec::class)
            ->assertCalledWithContext(LogLevel::Log, [
                'message' => 'some message',
                'logAttrs' => [
                    [
                        'key' => 'foo',
                        'value' => 'bar',
                    ],
                ],
            ]);

        $this->logger->log('some message', ['foo' => 'bar']);
    }

    public function testIfLogCallFailedThrowAnException(): void
    {
        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->rpc->callShouldThrowException(new LoggerException('Something went wrong'));
        $this->logger->log('foo');
    }
}
