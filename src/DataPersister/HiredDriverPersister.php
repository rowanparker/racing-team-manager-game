<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\HiredDriver;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;

class HiredDriverPersister implements DataPersisterInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supports($data): bool
    {
        return $data instanceof HiredDriver;
    }

    /**
     * @param HiredDriver $data
     * @return object|void
     */
    public function persist($data)
    {
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        /** @var Team $team */
        $team = $this->entityManager->getRepository(Team::class)
            ->find($data->getDriverSlot()->getTeam()->getId());

        $cost = $data->getDriver()->getMarketPrice();

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
