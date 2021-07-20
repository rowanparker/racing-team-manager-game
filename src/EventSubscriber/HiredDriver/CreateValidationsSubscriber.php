<?php

namespace App\EventSubscriber\HiredDriver;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\HiredDriver;
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
        /** @var HiredDriver $hiredDriver */
        $hiredDriver = $event->getControllerResult();

        if ( ! $hiredDriver instanceof HiredDriver) {
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

        if ($team->getBalanceCredits() < $hiredDriver->getDriver()->getMarketPrice()) {
            throw new InsufficientCreditsException(
                sprintf('You need more credits to hire %s.', $hiredDriver->getDriver()->getName())
            );
        }

        foreach ($team->getDriverSlots() as $driverSlot) {

            if (null === $driverSlot->getHiredDriver()) {
                continue;
            }

            if ($driverSlot->getHiredDriver()->getDriver()->getId() !== $hiredDriver->getDriver()->getId()) {
                continue;
            }

            throw new GameRulesException(
                sprintf('You have already hired %s.', $hiredDriver->getDriver()->getName())
            );
        }
    }
}
