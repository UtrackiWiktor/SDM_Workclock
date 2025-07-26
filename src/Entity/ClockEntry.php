<?php

namespace App\Entity;

use App\Repository\ClockEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClockEntryRepository::class)]
#[ORM\Table(name: 'clock_entry', schema: 'workclock')]
class ClockEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $startTime = null;

    #[ORM\Column]
    private ?\DateTime $endTime = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 36)]
    private ?string $userKeycloakId;

    #[ORM\ManyToOne(targetEntity: ProjectItem::class)]
    #[ORM\JoinColumn(
        name: "project_id",              // force the FK column name
        referencedColumnName: "id",      // make explicit even if it's the default
        nullable: true,
        onDelete: "SET NULL"             // optional, just example
    )]
    private ?ProjectItem $project = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTime $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTime $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUserKeycloakId(): ?string
    {
        return $this->userKeycloakId;
    }

    public function setUserKeycloakId(?string $userKeycloakId): void
    {
        $this->userKeycloakId = $userKeycloakId;
    }

    public function getProject(): ?ProjectItem
    {
        return $this->project;
    }

    public function setProject(?ProjectItem $project): void
    {
        $this->project = $project;
    }

}
