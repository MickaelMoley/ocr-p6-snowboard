<?php
namespace App\Controller;


use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
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
            $entityManager->flush();

            $this->addFlash("success", "Votre compte a bien été créé.");
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $registrationForm->createView()
        ]);
    }
}