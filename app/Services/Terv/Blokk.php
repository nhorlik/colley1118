<?php

namespace App\Services\Terv;

/** A napsablon egy helyének (slotjának) jellege — a pontozás erő-blokk büntetéséhez kell. */
enum Blokk: string
{
    case ERO = 'ero';
    case CORE = 'core';
    case REHAB = 'rehab';
    case MOBIL = 'mobil';
    case KARDIO = 'kardio';
}
