<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column]
    private ?\DateTime $applyDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Offer $offer = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    private ?ApplicationMethod $applicationMethod = null;

    /**
     * @var Collection<int, Reminder>
     */
    #[ORM\OneToMany(targetEntity: Reminder::class, mappedBy: 'application')]
    private Collection $reminders;

    /**
     * @var Collection<int, StatusApplication>
     */
    #[ORM\OneToMany(targetEntity: StatusApplication::class, mappedBy: 'application')]
    private Collection $applicationsStatus;

    public function __construct()
    {
        $this->reminders = new ArrayCollection();
        $this->applicationsStatus = new ArrayCollection();
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

    public function getApplyDate(): ?\DateTime
    {
        return $this->applyDate;
    }

    public function setApplyDate(\DateTime $applyDate): static
    {
        $this->applyDate = $applyDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): static
    {
        $this->offer = $offer;

        return $this;
    }

    public function getApplicationMethod(): ?ApplicationMethod
    {
        return $this->applicationMethod;
    }

    public function setApplicationMethod(?ApplicationMethod $applicationMethod): static
    {
        $this->applicationMethod = $applicationMethod;

        return $this;
    }

    /**
     * @return Collection<int, Reminder>
     */
    public function getReminders(): Collection
    {
        return $this->reminders;
    }

    public function addReminder(Reminder $reminder): static
    {
        if (!$this->reminders->contains($reminder)) {
            $this->reminders->add($reminder);
            $reminder->setApplication($this);
        }

        return $this;
    }

    public function removeReminder(Reminder $reminder): static
    {
        if ($this->reminders->removeElement($reminder)) {
            // set the owning side to null (unless already changed)
            if ($reminder->getApplication() === $this) {
                $reminder->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Status>
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    public function addStatus(Status $status): static
    {
        if (!$this->statuses->contains($status)) {
            $this->statuses->add($status);
            $status->addApplication($this);
        }

        return $this;
    }

    public function removeStatus(Status $status): static
    {
        if ($this->statuses->removeElement($status)) {
            $status->removeApplication($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, StatusApplication>
     */
    public function getApplicationsStatus(): Collection
    {
        return $this->applicationsStatus;
    }

    public function addApplicationsStatus(StatusApplication $applicationsStatus): static
    {
        if (!$this->applicationsStatus->contains($applicationsStatus)) {
            $this->applicationsStatus->add($applicationsStatus);
            $applicationsStatus->setApplication($this);
        }

        return $this;
    }

    public function removeApplicationsStatus(StatusApplication $applicationsStatus): static
    {
        if ($this->applicationsStatus->removeElement($applicationsStatus)) {
            // set the owning side to null (unless already changed)
            if ($applicationsStatus->getApplication() === $this) {
                $applicationsStatus->setApplication(null);
            }
        }

        return $this;
    }

}
