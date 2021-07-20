<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\HiredMechanic;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;

class HiredMechanicPersister implements DataPersisterInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supports($data): bool
    {
        return $data instanceof HiredMechanic;
    }

    /**
     * @param HiredMechanic $data
     * @return object|void
     */
    public function persist($data)
    {
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        /** @var Team $team */
        $team = $this->entityManager->getRepository(Team::class)
            ->find($data->getMechanicSlot()->getTeam()->getId());

        $cost = $data->getMechanic()->getMarketPrice();

        $team->setBalanceCredits($team->getBalanceCredits() - $cost);

        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }

    public function remove($data)
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}
