<?php

namespace App\Tests\Entity;

use App\Entity\MechanicSlot;

class MechanicSlotTest extends AbstractSlotTest
{
    protected string $urlApi = '/api/mechanic_slots';
    protected string $humanName = 'mechanic';
    protected string $resourceClass = MechanicSlot::class;
}
