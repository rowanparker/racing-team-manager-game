<?php

namespace App\Tests\Entity;

use App\Entity\GarageSlot;

class GarageSlotTest extends AbstractSlotTest
{
    protected string $urlApi = '/api/garage_slots';
    protected string $humanName = 'garage';
    protected string $resourceClass = GarageSlot::class;
}
