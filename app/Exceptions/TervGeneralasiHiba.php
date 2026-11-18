<?php

namespace App\Exceptions;

use RuntimeException;

/** A validátor hibát talált az elkészült tervvázlatban — ilyenkor semmi nem kerül mentésre. */
final class TervGeneralasiHiba extends RuntimeException
{
    /** @param list<string> $hibak */
    public function __construct(public readonly array $hibak)
    {
        parent::__construct("A terv nem menthető:\n - " . implode("\n - ", $hibak));
    }
}
