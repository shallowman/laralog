<?php

namespace Shallowman\Laralog\Traits;

use function mb_substr;

trait ClipLogTrait
{
    public static $defaultClippedLen = 1000;

    protected static $shouldLabelExceptedUriTag = false;

    protected static $shouldLabelClippedLogTag = false;

    protected static $tags = [];

    public static function shouldClipped(string $log): bool
    {
        $len = self::getConfigClippedLen();

        return static::$shouldLabelClippedLogTag = (self::POSITIVE_INFINITY !== config('laralog.log_clipped_length')
            && (mb_strlen($log) > $len));
    }

    public static function clipLog(string $log): string
    {
        if (!self::shouldClipped($log)) {
            return $log;
        }
        static::labelTag();
        $len = self::getConfigClippedLen();

        return mb_substr($log, 0, $len).'...clipped';
    }

    public static function getConfigClippedLen(): int
    {
        $length = config('laralog.log_clipped_length');

        return is_numeric($length) ? (int) $length : self::$defaultClippedLen;
    }

    public static function labelTag()
    {
        if (static::$shouldLabelClippedLogTag) {
            static::$tags[] = 'clipped';
        }

        if (static::$shouldLabelExceptedUriTag) {
            static::$tags[] = 'excludeUri';
        }
    }

    public static function label(): string
    {
        $tag = implode(',', array_unique(static::$tags));
        static::clearTags();

        return $tag;
    }

    public static function clearTags()
    {
        static::$tags = [];
    }
}
