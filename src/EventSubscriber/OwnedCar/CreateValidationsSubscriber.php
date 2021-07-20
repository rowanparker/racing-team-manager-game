<?php

namespace App\EventSubscriber\OwnedCar;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\OwnedCar;
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
        /** @var OwnedCar $ownedCar */
        $ownedCar = $event->getControllerResult();

        if ( ! $ownedCar instanceof OwnedCar) {
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

        $carName = sprintf('%s, %s, %s',
            $ownedCar->getCar()->getMake(),
            $ownedCar->getCar()->getModel(),
            $ownedCar->getCar()->getYear(),
        );

        if ($team->getBalanceCredits() < $ownedCar->getCar()->getMarketPrice()) {
            throw new InsufficientCreditsException(
                sprintf('You need more credits to buy a %s.', $carName)
            );
        }

        foreach ($team->getGarageSlots() as $garageSlot) {

            if (null === $garageSlot->getOwnedCar()) {
                continue;
            }

            if ($garageSlot->getOwnedCar()->getCar()->getId() !== $ownedCar->getCar()->getId()) {
                continue;
            }

            throw new GameRulesException(
                sprintf('You have already bought a %s.', $carName)
            );
        }
    }
}
