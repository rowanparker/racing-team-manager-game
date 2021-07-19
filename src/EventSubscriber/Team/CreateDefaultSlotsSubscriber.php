<?php

namespace App\EventSubscriber\Team;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\DriverSlot;
use App\Entity\GarageSlot;
use App\Entity\MechanicSlot;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This subscriber ensures that Team entities are created with the correct slots.
 */
class CreateDefaultSlotsSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['createDefaultSlots', EventPriorities::PRE_WRITE],
        ];
    }

    public function createDefaultSlots(ViewEvent $event)
    {
        $team = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ( ! $team instanceof Team || Request::METHOD_POST !== $method) {
            return;
        }

        $garageSlot = new GarageSlot();
        $driverSlot = new DriverSlot();
        $mechanicSlot = new MechanicSlot();

        $this->entityManager->persist($garageSlot);
        $this->entityManager->persist($driverSlot);
        $this->entityManager->persist($mechanicSlot);

        $team->addGarageSlot($garageSlot);
        $team->addDriverSlot($driverSlot);
        $team->addMechanicSlot($mechanicSlot);
    }
}
