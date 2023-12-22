<?php

declare(strict_types=1);

namespace RoadRunner\Logger\Tests;

use Mockery\MockInterface;
use PHPUnit\Framework\Assert;
use RoadRunner\AppLogger\DTO\V1\LogEntry;
use RoadRunner\Logger\LogLevel;

final class RpcMock
{
    public function __construct(
        private readonly MockInterface $mock,
    ) {
    }

    public function assertDefinedCodec(string $codec, int $times = 1): self
    {
        $this->mock
            ->shouldReceive('withCodec')
            ->times($times)
            ->with(\Mockery::type($codec))
            ->andReturnSelf();

        return $this;
    }

    public function assertServicePrefix(string $prefix, int $times = 1): self
    {
        $this->mock
            ->shouldReceive('withServicePrefix')
            ->times($times)
            ->with($prefix)
            ->andReturnSelf();

        return $this;
    }

    public function assertCalled(LogLevel $level, string $message, int $times = 1): self
    {
        $this->mock
            ->shouldReceive('call')
            ->times($times)
            ->with($level->name, $message)
            ->andReturnNull();

        return $this;
    }

    public function callShouldThrowException(\Throwable $e): self
    {
        $this->mock
            ->shouldReceive('call')
            ->once()
            ->andThrow($e);

        return $this;
    }

    public function assertCalledWithContext(LogLevel $level, array $message, int $times = 1): self
    {
        $this->mock
            ->shouldReceive('call')
            ->times($times)
            ->with(
                $level->name . 'WithContext',
                \Mockery::on(function (LogEntry $logEntry) use ($message): bool {
                    Assert::assertSame($logEntry->serializeToJsonString(), \json_encode($message));
                    return true;
                }),
            )
            ->andReturnNull();

        return $this;
    }
}
