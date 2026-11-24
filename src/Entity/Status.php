<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('IS_AUTHENTICATED_FULLY')"),
        new Get(security: "is_granted('IS_AUTHENTICATED_FULLY')"),
    ],
    normalizationContext: ['groups' => ['status:read']],
)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['status:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['status:read'])]
    private ?string $label = null;

    /**
     * @var Collection<int, StatusApplication>
     */
    #[ORM\OneToMany(targetEntity: StatusApplication::class, mappedBy: 'status')]
    private Collection $statusApplications;

    public function __construct()
    {
        $this->statusApplications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, StatusApplication>
     */
    public function getStatusApplications(): Collection
    {
        return $this->statusApplications;
    }

    public function addStatusApplication(StatusApplication $statusApplication): static
    {
        if (!$this->statusApplications->contains($statusApplication)) {
            $this->statusApplications->add($statusApplication);
            $statusApplication->setStatus($this);
        }

        return $this;
    }

    public function removeStatusApplication(StatusApplication $statusApplication): static
    {
        if ($this->statusApplications->removeElement($statusApplication)) {
            // set the owning side to null (unless already changed)
            if ($statusApplication->getStatus() === $this) {
                $statusApplication->setStatus(null);
            }
        }

        return $this;
    }

}