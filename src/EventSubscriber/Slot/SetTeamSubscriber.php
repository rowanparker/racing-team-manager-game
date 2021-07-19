<?php

namespace App\EventSubscriber\Slot;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\DriverSlot;
use App\Entity\GarageSlot;
use App\Entity\MechanicSlot;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

/**
 * This subscriber sets the Team of a created slot to the current user.
 */
class SetTeamSubscriber implements EventSubscriberInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setTeam', EventPriorities::PRE_VALIDATE]
        ];
    }

    public function setTeam(ViewEvent $event)
    {
        $slot = $event->getControllerResult();

        if ( ! $slot instanceof DriverSlot &&
             ! $slot instanceOf GarageSlot &&
             ! $slot instanceof MechanicSlot) {
            return;
        }

        if ( ! $event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $slot->setTeam($user->getTeam());
    }
}
