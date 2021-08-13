<?php
namespace App\Controller;


use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationController extends AbstractController
{
    /**
     * Fonction permettant de rendre un formulaire d'inscription
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route("/inscription", name="app_register")
     * @throws \Exception
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, TokenGeneratorInterface $tokenGeneratorInterface, MailerInterface $mailer): Response
    {
        $user = new User();
        $registrationForm = $this->createForm(RegistrationType::class, $user);

        $registrationForm->handleRequest($request);

        if($registrationForm->isSubmitted() && $registrationForm->isValid())
        {
            // Gestion des images
            $pictureUser = $registrationForm->get("picture")->getData();
            if($pictureUser)
            {
                $pictureNameUser = uniqid().'.'.$pictureUser->guessExtension();
                $pictureUser->move($this->getParameter('app.user.images_dir'), $pictureNameUser);
                $user->setPicture($pictureNameUser);
            }


            //On encode le mot de passe
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));


            $entityManager->persist($user);
           

            //Construit le lien de confirmation
            $user->setToken($tokenGeneratorInterface->generateToken());
            $confirmationLink = $this->generateUrl('app_confirm_user', [
                'email' => $user->getEmail(),
                'token' => $user->getToken()
            ],UrlGeneratorInterface::ABSOLUTE_URL);
                


            //Envoyer un mail de confirmation à l'utilisateur
            $email = (new TemplatedEmail())
                ->from('contact@snowtricks.com')
                ->to($user->getEmail())
                ->subject('Confirmer votre inscription à SnowTricks !')
                ->htmlTemplate('emails/confirmation_user.html.twig')
                ->context([
                    'confirmation_link' => $confirmationLink
                ])
                ;


                $mailer->send($email);

            
            $entityManager->flush();

            $this->addFlash("success", "Nous vous avons envoyé un e-mail de confirmation afin de valider votre compte.");
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $registrationForm->createView()
        ]);
    }
    
        
    /**
     * confirmationUser
     *
     * @Route("/confirm-user", name="app_confirm_user", methods={"GET"})
     * @param  mixed $request
     * @return void
     */
    public function confirmationUser(Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $mail = $request->query->get('email');
        $token = $request->query->get('token');

        if($mail && $token)
        {
        
            $user = $entityManagerInterface->getRepository(User::class)->findOneBy(['email' => $mail,'token' => $token]);
            
            //Si l'utilisateur existe 
            if($user)
            {
                //Si le compte n'a pas été activé
                if(!$user->getEnabled())
                {
                    $user->setEnabled(true);
                    $entityManagerInterface->persist($user);
                    $entityManagerInterface->flush();
                    $this->addFlash('success', "Votre compte a bien été activé.");
                    return $this->redirectToRoute('trick_index');
                }
                else {
                    $this->addFlash('error', "Votre compte a déjà été activé.");
                    return $this->redirectToRoute('trick_index');
                }
            
            }
        }

        $this->addFlash('error', "Ce lien de confirmation est invalide.");
        return $this->redirectToRoute('trick_index');
    }
}