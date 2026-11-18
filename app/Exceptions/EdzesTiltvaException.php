<?php

namespace App\Exceptions;

use App\Services\Kapu\KapuDontes;
use RuntimeException;

/** Az EdzesKapu TILTVA döntése: a generálás blokkolva, az üzenet elmagyarázza miért és mi hiányzik. */
final class EdzesTiltvaException extends RuntimeException
{
    public function __construct(public readonly KapuDontes $dontes)
    {
        parent::__construct($dontes->uzenet());
    }
}
