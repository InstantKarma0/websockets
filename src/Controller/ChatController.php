<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\ConversationMessage;
use App\Entity\User;
use App\Form\ConversationMessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\Turbo\TurboBundle;

class ChatController extends AbstractController
{

    private const string USER_CONVERSATION_TOPICS = 'user_{userUuid}_conversations';
    private const string USER_CONVERSATION_MESSAGE_TOPIC = 'user_{userUuid}_conversation_{conversationUuid}';

    public function __construct(
        private HubInterface $hub,
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory,
    )
    {
    }


    #[Route('/conversation', name: 'app_conversation')]
    public function index(Request $request, Authorization $mercureAuth): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render('chat/index.html.twig', [
            "user" => $user,
        ]);
    }

    #[Route('/conversation/add', name: 'app_conversation_add')]
    public function new(EntityManagerInterface $em): Response
    {
        throw $this->createAccessDeniedException();
        $user1 = $em->getRepository(User::class)->find(1);
        $user2 = $em->getRepository(User::class)->find(2);

        $conv = new Conversation();
        $conv->addUser($user1);
        $conv->addUser($user2);

        $em->persist($conv);
        $em->flush();

        return new Response('Conversation created with UUID: ' . $conv->getUuid());
    }

    #[Route('/conversation/{uuid}', name: 'app_conversation_show')]
    public function show(string $uuid, EntityManagerInterface $em): Response
    {
        $conversation = $em->getRepository(Conversation::class)->findOneBy(['uuid' => $uuid]);

        if (!$conversation) {
            throw $this->createNotFoundException('Conversation not found');
        }

        if (!$conversation->getUsers()->contains($this->getUser())) {
            throw $this->createNotFoundException('Conversation not found');
        }

        $form = $this->formFactory->create(
            ConversationMessageType::class,
            null,
            [
                'action' => $this->generateUrl('app_conversation_update', ['uuid' => $conversation->getUuid()]),
            ]
        );


        return $this->render('chat/conversation.html.twig', [
            'conversation' => $conversation,
            'user' => $this->getUser(),
            'form' => $form->createView(),
            'topic' => str_replace(
                ["{userUuid}", "{conversationUuid}"],
                [$this->getUser()->getUuid()->toString(), $conversation->getUuid()->toString()]
                , self::USER_CONVERSATION_MESSAGE_TOPIC
            ),
        ]);

    }

    #[Route('/conversation/{uuid}/update', name: 'app_conversation_update', methods: ['POST'])]
    public function updateConversation(string $uuid, Request $request): Response
    {


        $conversation = $this->entityManager->getRepository(Conversation::class)->findOneBy(['uuid' => $uuid]);
        if (null === $conversation) {
            return new Response('Conversation not found', 404);
        }

        $form = $this->formFactory->create(ConversationMessageType::class, null);
        $formClone = clone $form;
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new Response('Invalid form submission', 400);
        }


        $message = $form->get('message')->getData();
        if (null === $message || empty(trim($message))) {
            return new Response('No message provided', 400);
        }

        /** @var User $user */
        $user = $this->getUser();

        $conversationMessage = new ConversationMessage();
        $conversationMessage->setRefConversation($conversation)
            ->setContent($message)
            ->setRefUser($user);

        $conversation->addConversationMessage($conversationMessage);

        $this->entityManager->persist($conversationMessage);
        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        foreach ($conversation->getUsers() as $conversationUser) {
            $this->hub->publish(new Update(
                str_replace(
                    ["{userUuid}", "{conversationUuid}"],
                    [$conversationUser->getUuid()->toString(), $conversation->getUuid()->toString()],
                    self::USER_CONVERSATION_MESSAGE_TOPIC
                ),
                $this->renderView("chat/conversation.stream.html.twig", [
                    "sender" => $user,
                    "user" => $conversationUser,
                    "message" => $message,
                ]),
                true
            ));
        }

        return $this->renderBlock("chat/conversation.html.twig", 'form', [
            'form' => $formClone->createView(),
        ]);
    }

}
