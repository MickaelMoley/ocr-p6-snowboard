<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class TrickController extends AbstractController
{
    /**
     * Fonction pour afficher la page d'accueil ainsi que la liste de tous les tricks
     * @Route("/", name="trick_index", methods={"GET"})
     * @param TrickRepository $trickRepository
     * @return Response
     */
    public function index(TrickRepository $trickRepository, Request $request): Response
    {
        /**
         * Gérer le chargement des figures
         */
        $defaultLimitTricks = 3;
        $requestLimitTrick = $request->query->get('limit');
        $limit = null;
        /*
         *
         *Si l'utilisateur clique sur le bouton "Voir plus", il sera redirigé sur la même page avec le paramètre 'limit'.
         * Ce qui permettra de définir une nouvelle limite de charger les figures.
         */
        if($requestLimitTrick)
        {
            $limit = $requestLimitTrick;
        }
        else {
            $limit = $defaultLimitTricks;
        }

        /**
         * On récupère un certain nombre de tricks
         */
        $tricks = $trickRepository->loadTricks($limit);

        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks,
            'limitTrick' => ($limit + $defaultLimitTricks)
        ]);
    }

    /**
     * Fonction pour la création d'un trick
     * @Route("/new", name="trick_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        if($this->getUser())
        {
            $trick = new Trick();
            $form = $this->createForm(TrickType::class, $trick);
            $form->handleRequest($request);



            if ($form->isSubmitted() && $form->isValid()) {

                $trick->setCreatedAt(new \DateTime('now'));
                $trick->setUpdatedAt(new \DateTime('now'));
                $trick->setSlug($this->slugify($trick->getName()));


                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($trick);
                $entityManager->flush();
                $this->addFlash('success', "La figure ". $trick->getName() ." a bien été créée.");
                return $this->redirectToRoute('trick_edit', ['slug' => $trick->getSlug()]);
            }

            return $this->render('trick/new.html.twig', [
                'trick' => $trick,
                'form' => $form->createView(),
            ]);
        }

        $this->addFlash('error', "Vous n'êtes pas autorisé à créer une nouvelle figure. Veuillez vous connecter afin de pouvoir créer une figure.");
        return $this->redirectToRoute('trick_index');
    }

    /**
     * Fonction pour afficher un trick
     * @Route("/{slug}", name="trick_show", methods={"GET", "POST"})
     * @param Trick $trick
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function show(Trick $trick, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); //On récupère l'utilisateur actuel
        /**
         * Gérer le chargement des commentaires
         */
        $defaultLimitComment = 2;
        $requestCommentLimit = $request->query->get('commentLimit');
        $limit = null;
        /*
         *
         *Si l'utilisateur clique sur le bouton "Voir plus", il sera redirigé sur la même page avec le paramètre 'commentList'.
         * Ce qui permettra de définir une nouvelle limit de charger les commentaires.
         */
        if($requestCommentLimit)
        {
            $limit = $requestCommentLimit;
        }
        else {
            $limit = $defaultLimitComment;
        }

        $comments = $entityManager->getRepository(Comment::class)->loadComment($trick, $limit);


        //Si un utilisateur est connecté alors on affiche le formulaire de commentaire
       if($user)
       {
           $comment = new Comment();
           $comment->setAuthor($user); //On défini l'auteur du commentaire
           $formComment = $this->createForm(CommentType::class, $comment);

           $formComment->handleRequest($request);
            //Si le formulaire a été soumis et que celui-ci est valide
           if($formComment->isSubmitted() && $formComment->isValid()){
               $comment->setCreatedAt(new \DateTime('now'));
               $comment->setTrick($trick); //On le relié à ce trick

               $entityManager->persist($comment);
               $entityManager->flush();

           }

           return $this->render('trick/show.html.twig', [
               'trick' => $trick,
               'comments' => $comments,
               'form' => $formComment->createView(),
               'limitComment' => ($limit + $defaultLimitComment)
        ]);
       }

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'limitComment' => ($limit + $defaultLimitComment)
        ]);
    }

    /**
     *
     * Fonction pour la modification d'un trick
     * @Route("/{slug}/edit", name="trick_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Trick $trick
     * @return Response
     */
    public function edit(Request $request, Trick $trick): Response
    {
        if($this->getUser())
        {
            $form = $this->createForm(TrickType::class, $trick);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $trick = $form->getData();

                foreach ($trick->getMedias() as $media)
                {
                    $media->setTrick($trick);
                    $this->getDoctrine()->getManager()->persist($media);
                }

                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('trick_edit', ['slug' => $trick->getSlug()]);
            }

            return $this->render('trick/edit.html.twig', [
                'trick' => $trick,
                'form' => $form->createView(),
            ]);
        }
        $this->addFlash('error', "Vous n'êtes pas autorisé à modifier cette figure. Veuillez vous connecter afin de pouvoir modifier cette figure.");
        return $this->redirectToRoute('trick_show', ['slug' => $trick->getSlug()]);
    }

    /**
     * Fonction de suppression d'un trick
     * @Route("/{id}/delete", name="trick_delete", methods={"POST"})
     * @param Request $request
     * @param Trick $trick
     * @return Response
     */
    public function delete(Request $request, Trick $trick): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($trick);
            $entityManager->flush();
        }

        return $this->redirectToRoute('trick_index');
    }


    /**
     * @param string $name
     */
    public function slugify(?string $name)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }
}
