<?php

namespace App\EventSubscriber\Slot;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\DriverSlot;
use App\Entity\GarageSlot;
use App\Entity\MechanicSlot;
use App\Entity\Team;
use App\Entity\User;
use App\Exception\InsufficientCreditsException;
use App\Exception\InsufficientFreeSlots;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

/**
 * This subscriber ensures that a user can purchase (POST) a Slot.
 * Current constraints are:
 * - has sufficient credits
 * - has free space
 */
class CheckSlotPurchaseConstraintsSubscriber implements EventSubscriberInterface
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private ContainerBagInterface $params;

    public function __construct(Security $security,
                                EntityManagerInterface $entityManager,
                                ContainerBagInterface $params)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['checkSlotPurchaseConstraints', EventPriorities::PRE_WRITE],
        ];
    }

    public function checkSlotPurchaseConstraints(ViewEvent $event)
    {
        $slot = $event->getControllerResult();

        if ( ! $event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        if ( ! $slot instanceof DriverSlot &&
            ! $slot instanceOf GarageSlot &&
            ! $slot instanceof MechanicSlot) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        /** @var Team $team */
        $team = $this->entityManager->getRepository(Team::class)
            ->find($user->getTeam()->getId());

        $name = null;
        $count = null;

        if ($slot instanceof DriverSlot) {
            $name = 'driver';
            $count = count($team->getDriverSlots());
        } elseif ($slot instanceof GarageSlot) {
            $name = 'garage';
            $count = count($team->getGarageSlots());
        } elseif ($slot instanceof MechanicSlot) {
            $name = 'mechanic';
            $count = count($team->getMechanicSlots());
        }

        if (null === $name || null === $count) {
            return;
        }

        if ($team->getBalanceCredits() < $this->params->get('app.slots.price')) {
            throw new InsufficientCreditsException(
                sprintf('You need more credits to purchase a %s slot.', $name)
            );
        }

        if ($count >= $this->params->get('app.slots.max')) {
            throw new InsufficientFreeSlots(
                sprintf('You have already unlocked all your %s slots.', $name)
            );
        }
    }
}
