<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'conversation')]
    private Collection $users;

    /**
     * @var Collection<int, ConversationMessage>
     */
    #[ORM\OneToMany(targetEntity: ConversationMessage::class, mappedBy: 'refConversation', orphanRemoval: true)]
    private Collection $conversationMessages;

    public function __construct()
    {
        if ($this->uuid === null) {
            $this->uuid = Uuid::v7();
        }
        $this->users = new ArrayCollection();
        $this->conversationMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, ConversationMessage>
     */
    public function getConversationMessages(): Collection
    {
        return $this->conversationMessages;
    }

    public function addConversationMessage(ConversationMessage $conversationMessage): static
    {
        if (!$this->conversationMessages->contains($conversationMessage)) {
            $this->conversationMessages->add($conversationMessage);
            $conversationMessage->setRefConversation($this);
        }

        return $this;
    }

    public function removeConversationMessage(ConversationMessage $conversationMessage): static
    {
        if ($this->conversationMessages->removeElement($conversationMessage)) {
            // set the owning side to null (unless already changed)
            if ($conversationMessage->getRefConversation() === $this) {
                $conversationMessage->setRefConversation(null);
            }
        }

        return $this;
    }
}
