<?php

namespace App\Entity;

use App\Repository\StatusApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusApplicationRepository::class)]
class StatusApplication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updateDate = null;

    /**
     * @var Collection<int, Status>
     */
    #[ORM\ManyToMany(targetEntity: Status::class, inversedBy: 'statusApplications')]
    private Collection $status;

    /**
     * @var Collection<int, Application>
     */
    #[ORM\ManyToMany(targetEntity: Application::class, inversedBy: 'statusApplications')]
    private Collection $application;

    public function __construct()
    {
        $this->status = new ArrayCollection();
        $this->application = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdateDate(): ?\DateTimeImmutable
    {
        return $this->updateDate;
    }

    public function setUpdateDate(\DateTimeImmutable $updateDate): static
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * @return Collection<int, Status>
     */
    public function getStatus(): Collection
    {
        return $this->status;
    }

    public function addStatus(Status $status): static
    {
        if (!$this->status->contains($status)) {
            $this->status->add($status);
        }

        return $this;
    }

    public function removeStatus(Status $status): static
    {
        $this->status->removeElement($status);

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
}
