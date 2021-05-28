<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgetPasswordType;
use App\Form\ResettingPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {


        // On récupère les erreurs s'il y en a
        $error = $authenticationUtils->getLastAuthenticationError();
        // On récupère le dernier identifiant entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Fonction permettant aux utilisateurs de demander le changement de mot de passe en cas d'oubli/perte
     * @Route("/forget-password", name="forget-password")
     * @param EntityManagerInterface $entityManager
     * @param MailerInterface $mailer
     * @return Response
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function forgetPassword(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer)
    {

        if($this->getUser())
        {
            $this->addFlash(
                'info',
                "Vous ne pouvez pas demander le changement de mot de passe en étant connecté. Déconnectez-vous avant de pouvoir effectuer cette action."
            );
            return $this->redirectToRoute('app_login');
        }

        $forgetPasswordForm = $this->createForm(ForgetPasswordType::class);
        $forgetPasswordForm->handleRequest($request);
        if($forgetPasswordForm->isSubmitted())
        {
            $formData = $forgetPasswordForm->getData();
            $potentialUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $formData->getEmail()]);

            // Si l'entrée que l'utilisateur a entré est valide et que l'adresse mail entré correspond à un utilisateur alors on envoi un mail
            if($potentialUser)
            {
                $token = uniqid();

                $potentialUser->setToken($token);
                $entityManager->persist($potentialUser);
                $entityManager->flush();

                $email = (new Email())
                    ->from('contact@mickaelmoley.com')
                    ->to($potentialUser->getEmail())
                    ->subject('Récupération de mot de passe')
                    ->html(sprintf(
                        'Récupération de votre mot de passe, <a target="_blank" href="%s%s">cliquez ici</a>.',
                        $request->getSchemeAndHttpHost(),
                        $this->generateUrl('resetting-password', [
                            'token' => $token,
                            'email' => $potentialUser->getEmail()
                        ], true)
                    ));

                $mailer->send($email);
            }

            $this->addFlash('success', "Vous allez recevoir un e-mail contenant un lien pour définir votre nouveau mot de passe.");
            return $this->redirectToRoute('app_login');

        }

        return $this->render('security/forget_password.html.twig', [
            'forgetPasswordForm' => $forgetPasswordForm->createView()
        ]);
    }

    /**
     * Fonction permettant à l'utilisateur de changer son mot de passe si la demande est valide
     * @Route("/resetting-password",name="resetting-password")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return string
     */
    public function resettingPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $token = $request->query->get('token');
        $eMail = $request->query->get('email');

        if($token && $eMail)
        {
            $user = $entityManager->getRepository(User::class)->findOneBy([
                'email' => $eMail,
                'token' => $token
                ]);

            if($user)
            {
                $resettingPasswordForm = $this->createForm(ResettingPasswordType::class, $user);

                $resettingPasswordForm->handleRequest($request);

                if($resettingPasswordForm->isSubmitted() && $resettingPasswordForm->isValid())
                {
                    $user = $resettingPasswordForm->getData();

                    //On encode le mot de passe
                    $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
                    //On met le token à "null" pour que l'utilisateur ne puisse plus changer son mot de passe
                    $user->setToken(null);
                    //On sauvegarde les modifications
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', "Votre mot de passe a été changé.");
                    return $this->redirectToRoute('app_login');
                }

                return $this->render('security/resetting_password.html.twig', [
                    'form' => $resettingPasswordForm->createView()
                ]);
            }

            $this->addFlash('error', "Cette demande n'est pas valide. Veuillez resoumettre votre demande.");
            return $this->render('security/resetting_password.html.twig');
        }

        return $this->redirectToRoute('forget-password');
    }
    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
