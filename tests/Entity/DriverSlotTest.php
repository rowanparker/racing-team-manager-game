<?php

namespace App\Tests\Entity;

use App\Entity\DriverSlot;

class DriverSlotTest extends AbstractSlotTest
{
    protected string $urlApi = '/api/driver_slots';
    protected string $humanName = 'driver';
    protected string $resourceClass = DriverSlot::class;
}
