<?php 

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{

    public function checkPreAuth(UserInterface $user)
    {
        //On vérifie si le compte de l'utilisateur est bien activé
        //Si le compte n'a pas été activé, alors on ne l'autorise pas à se connecter à son compte.
        //Il doit valider son compte grâce au lien qu'il a reçu lors de l'inscription
        if (!$user->getEnabled()) {
            throw new CustomUserMessageAuthenticationException("Votre compte n'a pas été activé. Vous devez le valider avant de pouvoir y accéder.");
        }
    }

    public function checkPostAuth(UserInterface $user)
    {

    }
}