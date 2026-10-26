<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    /**
     * @var Collection<int, StatusApplication>
     */
    #[ORM\OneToMany(targetEntity: StatusApplication::class, mappedBy: 'status')]
    private Collection $statusApplications;

    public function __construct()
    {
        $this->application = new ArrayCollection();
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
     * @return Collection<int, Application>
     */
    public function getApplication(): Collection
    {
        return $this->application;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->application->contains($application)) {
            $this->application->add($application);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        $this->application->removeElement($application);

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
