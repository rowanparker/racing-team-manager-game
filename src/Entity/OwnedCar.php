<?php

namespace App\Entity;

use App\Repository\OwnedCarRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OwnedCarRepository::class)
 */
class OwnedCar
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity=GarageSlot::class, inversedBy="ownedCar", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private GarageSlot $garageSlot;

    /**
     * @ORM\ManyToOne(targetEntity=Car::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Car $car;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGarageSlot(): ?GarageSlot
    {
        return $this->garageSlot;
    }

    public function setGarageSlot(GarageSlot $garageSlot): self
    {
        $this->garageSlot = $garageSlot;

        return $this;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(Car $car): self
    {
        $this->car = $car;

        return $this;
    }
}
