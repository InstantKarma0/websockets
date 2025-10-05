<?php

namespace App\Entity;

use App\Repository\ConversationMessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationMessageRepository::class)]
class ConversationMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'conversationMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $refConversation = null;

    #[ORM\ManyToOne(inversedBy: 'conversationMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $refUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getRefConversation(): ?Conversation
    {
        return $this->refConversation;
    }

    public function setRefConversation(?Conversation $refConversation): static
    {
        $this->refConversation = $refConversation;

        return $this;
    }

    public function getRefUser(): ?User
    {
        return $this->refUser;
    }

    public function setRefUser(?User $refUser): static
    {
        $this->refUser = $refUser;

        return $this;
    }
}
