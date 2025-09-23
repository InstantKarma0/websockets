<?php

namespace App\Controller;

use App\Entity\Conversations;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

    private const string USER_CONVERSATIONS_TOPICS = 'conversations_{userUuid}';

    public function __construct(private HubInterface $hub)
    {
    }


    #[Route('/conversations', name: 'app_conversations')]
    public function index(Request $request, Authorization $mercureAuth): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render('chat/index.html.twig', [
            "user" => $user,
        ]);
    }

    #[Route('/conversations/add', name: 'app_conversation_add')]
    public function new(EntityManagerInterface $em): Response
    {
        throw $this->createAccessDeniedException();
        $user1 = $em->getRepository(User::class)->find(1);
        $user2 = $em->getRepository(User::class)->find(2);

        $conv = new Conversations();
        $conv->addUser($user1);
        $conv->addUser($user2);

        $em->persist($conv);
        $em->flush();

        return new Response('Conversation created with UUID: ' . $conv->getUuid());
    }

    #[Route('/conversations/{uuid}', name: 'app_conversation_show')]
    public function show(string $uuid, EntityManagerInterface $em, Request $request, HubInterface $hub): Response
    {
        $conversations = $em->getRepository(Conversations::class)->findOneBy(['uuid' => $uuid]);

        if (!$conversations) {
            throw $this->createNotFoundException('Conversation not found');
        }

        if (!$conversations->getUsers()->contains($this->getUser())) {
            throw $this->createNotFoundException('Conversation not found');
        }


        $form = $this->createFormBuilder()
            ->add('message', TextType::class, ['attr' => ['autocomplete' => 'off']])
            ->add('send', SubmitType::class)
            ->getForm();

        $emptyForm = clone $form; // Used to display an empty form after a POST request
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // 🔥 The magic happens here! 🔥
            // The HTML update is pushed to the client using Mercure
            $hub->publish(new Update(
                "conversation_{$conversations->getUuid()->toString()}",
                $this->renderView("chat/conversation.stream.html.twig", [
                    "message" => $form->get('message')->getData(),
                ]),
                true
            ));

            // Force an empty form to be rendered below
            // It will replace the content of the Turbo Frame after a post
            $form = $emptyForm;
        }


        return $this->render('chat/conversation.html.twig', [
            'conversation' => $conversations,
            'user' => $this->getUser(),
            'form' => $form->createView(),
        ]);

    }

    #[Route('/conversations/update')]
    public function updateConversations(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->hub->publish(new Update(
            str_replace("{userUuid}", $user->getUuid()->toString(),self::USER_CONVERSATIONS_TOPICS),
            $this->renderView("chat/message.stream.html.twig", [
                "user" => $user,
            ]),
            true
        ));

        return new Response('Conversation update sent!');
    }

}
