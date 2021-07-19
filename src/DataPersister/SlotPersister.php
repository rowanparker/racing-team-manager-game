<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\DriverSlot;
use App\Entity\GarageSlot;
use App\Entity\MechanicSlot;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class SlotPersister implements DataPersisterInterface
{
    private ContainerBagInterface $params;
    private EntityManagerInterface $entityManager;

    public function __construct(ContainerBagInterface $params,
                                EntityManagerInterface $entityManager)
    {
        $this->params = $params;
        $this->entityManager = $entityManager;
    }

    public function supports($data): bool
    {
        return ($data instanceof DriverSlot ||
                $data instanceOf GarageSlot ||
                $data instanceof MechanicSlot);
    }

    public function persist($data): void
    {
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        /** @var Team $team */
        $team = $this->entityManager->getRepository(Team::class)
            ->find($data->getTeam()->getId());

        $cost = $this->params->get('app.slots.price');

        $team->setBalanceCredits($team->getBalanceCredits() - $cost);

        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }

    public function remove($data): void
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}
