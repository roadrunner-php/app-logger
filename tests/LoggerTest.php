<?php

declare(strict_types=1);

namespace RoadRunner\Logger\Tests;

use Mockery as m;
use RoadRunner\Logger\Logger;
use RoadRunner\Logger\Exception\LoggerException;
use RoadRunner\Logger\LogLevel;
use Spiral\Goridge\RPC\Codec\ProtobufCodec;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\Goridge\RPC\RPCInterface;
use Testo\Data\DataProvider;
use Testo\Expect;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class LoggerTest
{
    private Logger $logger;
    private RpcMock $rpc;

    public static function contextValues(): iterable
    {
        yield 'string' => ['bar', 'bar'];
        yield 'stringable' => [new class implements \Stringable {
            #[\Override]
            public function __toString(): string
            {
                return 'stringable';
            }
        }, 'stringable'];
        yield 'int' => [42, '42'];
        yield 'float' => [1.5, '1.5'];
        yield 'bool' => [true, 'true'];
        yield 'null' => [null, 'null'];
        yield 'list' => [[1, 'two'], '[1,"two"]'];
        yield 'map' => [['a' => ['b' => 1]], '{"a":{"b":1}}'];
    }

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
        Expect::exception(LoggerException::class)->withMessageContaining('Something went wrong');

        $this->rpc->callShouldThrowException(new LoggerException('Something went wrong'));
        $this->logger->error('foo');
    }

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
        Expect::exception(LoggerException::class)->withMessageContaining('Something went wrong');

        $this->rpc->callShouldThrowException(new LoggerException('Something went wrong'));
        $this->logger->warning('foo');
    }

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
        Expect::exception(LoggerException::class)->withMessageContaining('Something went wrong');

        $this->rpc->callShouldThrowException(new LoggerException('Something went wrong'));
        $this->logger->info('foo');
    }

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
        Expect::exception(LoggerException::class)->withMessageContaining('Something went wrong');

        $this->rpc->callShouldThrowException(new LoggerException('Something went wrong'));
        $this->logger->debug('foo');
    }

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
        Expect::exception(LoggerException::class)->withMessageContaining('Something went wrong');

        $this->rpc->callShouldThrowException(new LoggerException('Something went wrong'));
        $this->logger->log('foo');
    }

    #[DataProvider('contextValues')]
    public function testContextValueIsSentAsString(mixed $value, string $expected): void
    {
        $this->rpc
            ->assertDefinedCodec(ProtobufCodec::class)
            ->assertCalledWithContext(LogLevel::Info, [
                'message' => 'some message',
                'logAttrs' => [
                    ['key' => 'foo', 'value' => $expected],
                ],
            ]);

        $this->logger->info('some message', ['foo' => $value]);
    }

    public function testContextKeepsOrderOfAttributes(): void
    {
        $this->rpc
            ->assertDefinedCodec(ProtobufCodec::class)
            ->assertCalledWithContext(LogLevel::Debug, [
                'message' => 'some message',
                'logAttrs' => [
                    ['key' => 'first', 'value' => '1'],
                    ['key' => 'second', 'value' => 'two'],
                ],
            ]);

        $this->logger->debug('some message', ['first' => 1, 'second' => 'two']);
    }

    public function testContextValueThatCannotBeEncodedIsSkipped(): void
    {
        $this->rpc
            ->assertDefinedCodec(ProtobufCodec::class)
            ->assertCalledWithContext(LogLevel::Warning, [
                'message' => 'some message',
                'logAttrs' => [
                    ['key' => 'kept', 'value' => 'yes'],
                ],
            ]);

        $this->logger->warning('some message', ['nan' => \NAN, 'kept' => 'yes']);
    }

    public function testStringableMessage(): void
    {
        $this->rpc->assertCalled(LogLevel::Error, 'stringable');

        $this->logger->error(new class implements \Stringable {
            #[\Override]
            public function __toString(): string
            {
                return 'stringable';
            }
        });
    }

    public function testStringableMessageWithContext(): void
    {
        $this->rpc
            ->assertDefinedCodec(ProtobufCodec::class)
            ->assertCalledWithContext(LogLevel::Log, [
                'message' => 'stringable',
                'logAttrs' => [
                    ['key' => 'foo', 'value' => 'bar'],
                ],
            ]);

        $this->logger->log(new class implements \Stringable {
            #[\Override]
            public function __toString(): string
            {
                return 'stringable';
            }
        }, ['foo' => 'bar']);
    }

    public function testServiceExceptionIsWrappedIntoLoggerException(): void
    {
        $previous = new ServiceException("Something\twent\nwrong", 42);
        Expect::exception(LoggerException::class, same: true)
            ->withMessage('Something went wrong')
            ->withCode(42)
            ->withPrevious($previous);

        $this->rpc->callShouldThrowException($previous);
        $this->logger->error('foo');
    }

    public function testServiceExceptionWithContextIsWrappedIntoLoggerException(): void
    {
        $previous = new ServiceException('Something went wrong', 42);
        Expect::exception(LoggerException::class, same: true)
            ->withMessage('Something went wrong')
            ->withCode(42)
            ->withPrevious($previous);

        $this->rpc
            ->assertDefinedCodec(ProtobufCodec::class)
            ->callShouldThrowException($previous);
        $this->logger->error('foo', ['foo' => 'bar']);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->rpc = new RpcMock($rpc = m::mock(RPCInterface::class));

        $this->rpc->assertServicePrefix('app');

        $this->logger = new Logger($rpc);
    }
}
