<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DriverSlotRepository;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    collectionOperations: [
        'get' => [
            'security' => 'is_granted("ROLE_USER")'
        ],
        'post' => [
            'security' => 'is_granted("ROLE_USER")'
        ]
    ],
        itemOperations: [
        'get' => [
            'security' => 'is_granted("ROLE_USER")',
        ],
        'delete' => [
            'security' => 'is_granted("ROLE_ADMIN")',
        ]
    ],
)]
/**
 * @ORM\Entity(repositoryClass=DriverSlotRepository::class)
 */
class DriverSlot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="driverSlots")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Team $team;

    /**
     * @ORM\OneToOne(targetEntity=HiredDriver::class, mappedBy="driverSlot", cascade={"persist", "remove"})
     */
    private ?HiredDriver $hiredDriver = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getHiredDriver(): ?HiredDriver
    {
        return $this->hiredDriver;
    }

    public function setHiredDriver(HiredDriver $hiredDriver): self
    {
        // set the owning side of the relation if necessary
        if ($hiredDriver->getDriverSlot() !== $this) {
            $hiredDriver->setDriverSlot($this);
        }

        $this->hiredDriver = $hiredDriver;

        return $this;
    }
}
