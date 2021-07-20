<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\HiredMechanicRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ApiResource(
    collectionOperations: [
        'post' => [
            'security' => 'is_granted("ROLE_USER")'
        ]
    ],
    itemOperations: [
        'get' => [
            'security' => 'is_granted("ROLE_USER")',
        ],
        'delete' => [
            'security' => 'is_granted("ROLE_USER")',
        ]
    ],
)]
#[UniqueEntity("mechanicSlot", message: 'Mechanic slot is already full.')]
/**
 * @ORM\Entity(repositoryClass=HiredMechanicRepository::class)
 */
class HiredMechanic
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity=MechanicSlot::class, inversedBy="hiredMechanic", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private MechanicSlot $mechanicSlot;

    /**
     * @ORM\ManyToOne(targetEntity=Mechanic::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Mechanic $mechanic;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMechanicSlot(): ?MechanicSlot
    {
        return $this->mechanicSlot;
    }

    public function setMechanicSlot(MechanicSlot $mechanicSlot): self
    {
        $this->mechanicSlot = $mechanicSlot;

        return $this;
    }

    public function getMechanic(): ?Mechanic
    {
        return $this->mechanic;
    }

    public function setMechanic(Mechanic $mechanic): self
    {
        $this->mechanic = $mechanic;

        return $this;
    }
}
