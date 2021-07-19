<?php

namespace App\EventSubscriber\Team;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Team;
use App\Entity\User;
use App\Exception\UserAlreadyHasTeamException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

/**
 * This subscriber ensures that a user can only create a Team entity
 * for their own user.
 *
 * It throws an exception if the user has already registered a team.
 */
class SetUserForTeamSubscriber implements EventSubscriberInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setUserForTeam', EventPriorities::PRE_WRITE],
        ];
    }

    public function setUserForTeam(ViewEvent $event)
    {
        $team = $event->getControllerResult();

        if ( ! $team instanceof Team || ! $event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        if ($user->getTeam() !== null) {
            throw new UserAlreadyHasTeamException();
        }

        $team->setUser($user);
    }
}
