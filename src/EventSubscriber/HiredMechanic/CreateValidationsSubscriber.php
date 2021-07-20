<?php

namespace App\EventSubscriber\HiredMechanic;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\HiredMechanic;
use App\Entity\Team;
use App\Entity\User;
use App\Exception\GameRulesException;
use App\Exception\InsufficientCreditsException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class CreateValidationsSubscriber implements EventSubscriberInterface
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
            KernelEvents::VIEW => ['createValidation', EventPriorities::PRE_WRITE],
        ];
    }

    public function createValidation(ViewEvent $event)
    {
        /** @var HiredMechanic $hiredMechanic */
        $hiredMechanic = $event->getControllerResult();

        if ( ! $hiredMechanic instanceof HiredMechanic) {
            return;
        }

        if ( ! $event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        /** @var Team $team */
        $team = $this->entityManager->getRepository(Team::class)
            ->find($user->getTeam()->getId());

        if ($team->getBalanceCredits() < $hiredMechanic->getMechanic()->getMarketPrice()) {
            throw new InsufficientCreditsException(
                sprintf('You need more credits to hire %s.', $hiredMechanic->getMechanic()->getName())
            );
        }

        foreach ($team->getMechanicSlots() as $mechanicSlot) {

            if (null === $mechanicSlot->getHiredMechanic()) {
                continue;
            }

            if ($mechanicSlot->getHiredMechanic()->getMechanic()->getId() !== $hiredMechanic->getMechanic()->getId()) {
                continue;
            }

            throw new GameRulesException(
                sprintf('You have already hired %s.', $hiredMechanic->getMechanic()->getName())
            );
        }
    }
}
