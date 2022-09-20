<?php

declare(strict_types=1);

namespace Shallowman\Laralog\Processor;

use Monolog\Processor\ProcessorInterface;
use Monolog\ResettableInterface;

/**
 * Adds a unique identifier into records.
 *
 * @author Simon Mönch <sm@webfactory.de>
 */
class UidProcessor implements ProcessorInterface, ResettableInterface
{
    /** @var string */
    public static $uid;

    public function __construct(int $length = 7)
    {
        if ($length > 32 || $length < 1) {
            throw new \InvalidArgumentException('The uid length must be an integer between 1 and 32');
        }

        static::$uid = $this->generateUid($length);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $record): array
    {
        $record['extra']['uid'] = static::$uid;

        return $record;
    }

    public function getUid(): string
    {
        return static::$uid;
    }

    public function reset()
    {
        $length = strlen(static::$uid);
        static::$uid = null;
        static::$uid = $this->generateUid($length);
    }

    private function generateUid(int $length): string
    {
        if (!empty(static::$uid)) {
            return static::$uid;
        }

        return static::$uid = substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);
    }
}
