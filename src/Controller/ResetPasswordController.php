<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    /**
     * @param $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/mot-de-passe-oublie', name: 'app_reset_password')]
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $request->get('email'),
            ]);

            if ($user) {
                // Etape 1: Enregistrere en base de données la demande de reset password avec user token et createdAt.
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new \DateTimeImmutable());

                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                // Etape 2: Envoyer un email à l'utilisateur avec un lien lui permettant de mettre à jour son mot de passe.
                $url = $this->generateUrl('app_update_password', [
                    'token' => $reset_password->getToken(),
                ]);

                $content = "Bonjour" . $user->getFirstname() . ",<br/>Vous avez demandé à réinitialiser votre mot de passe sur La Boutique Algérienne<br/><br/>";
                $content .= "Merci de bien cliquer sur le lien suivant <a href='" . $url . "'>pour mettre à jour votre mot de passe.</a><br/>";

                $mail = new Mail();
                $mail->send($user->getEmail(), $user->getFullName(), "Réinitialiser votre mon mot de passe sur La Boutique Algérienne", $content);
                $this->addFlash('notice', 'Vous allez recevoir dans quelques secondes un mail avec la procédure à suivre pour réinitialiser votre mot de passe.');
            } else {
                $this->addFlash('notice', 'Cette adresse email est inconnue.');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    #[Route('/modifier-mot-de-passe-oublie/{token}', name: 'app_update_password')]
    public function update($token, Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneBy([
            'token' => $token,
        ]);

        if (!$reset_password) {
            return $this->redirectToRoute('app_reset_password');
        }

        // Vérifier que le createdAt = now - 3h
        $now = new \DateTimeImmutable();

        // Le lien a expiré.
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {
            $this->addFlash('notice', 'Votre demande de réinitialisation a expiré. Merci de la renouveller.');
            return $this->redirectToRoute('app_reset_password');
        }

        // Rendre une vue avec mot de passe et confirmer votre mot de passe.
        $form = $this->createForm(ResetPasswordType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new_password = $form->get('new_password')->getData();

            // Encodage des mot de passe.
            $password = $encoder->hashPassword($reset_password->getUser(), $new_password);
            $reset_password->getUser()->setPassword($password);

            // Flush en base de données.
            $this->entityManager->flush();

            // Redirection de l'utilisateur vers la page de connexion.
            $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
