<?php

namespace App\EventSubscriber\HiredMechanic;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\HiredMechanic;
use App\Entity\Team;
use App\Entity\User;
use App\Exception\GameRulesException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class DeleteValidationSubscriber implements EventSubscriberInterface
{
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(Security $security,
                                EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['deleteValidation', EventPriorities::PRE_WRITE],
        ];
    }

    public function deleteValidation(ViewEvent $event)
    {
        /** @var HiredMechanic $hiredMechanic */
        $hiredMechanic = $event->getControllerResult();

        if ( ! $hiredMechanic instanceof HiredMechanic) {
            return;
        }

        if ( ! $event->getRequest()->isMethod(Request::METHOD_DELETE)) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        /** @var Team $team */
        $team = $this->entityManager->getRepository(Team::class)
            ->find($user->getTeam()->getId());

        $countUsedSlots = 0;

        foreach ($team->getMechanicSlots() as $mechanicSlot) {
            if (null !== $mechanicSlot->getHiredMechanic()) {
                $countUsedSlots++;
            }
        }

        if ($countUsedSlots < 2) {
            throw new GameRulesException(
                sprintf('You can not fire your last mechanic.')
            );
        }
    }
}
