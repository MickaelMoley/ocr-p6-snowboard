<?php

namespace App\DataFixtures;

use App\Entity\Media;
use App\Entity\Trick;
use App\Entity\TrickCategory;
use App\Repository\TrickCategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TrickFixtures extends Fixture
{

    private TrickCategoryRepository $trickCategoryRepository;

    public function __construct(TrickCategoryRepository $trickCategoryRepository)
    {
        $this->trickCategoryRepository = $trickCategoryRepository;
    }

    public function load(ObjectManager $manager)
    {
        $this->createBaseTrickCategory($manager);
        $dataTricks = $this->loadDataFromJSON();


        foreach ($dataTricks as $dataTrick)
        {
            //On commence à créer un Trick
            $trick = new Trick();
            $trick->setName($dataTrick->name);
            $trick->setDescription($dataTrick->description);
            $trick->setSlug($this->slugify($trick->getName()));
            $trick->setCreatedAt(new \DateTime('now'));
            $trick->setUpdatedAt(new \DateTime('now'));
            $trick->setCategory($this->trickCategoryRepository->findOneBy(['name' => $dataTrick->category]));

            foreach ($dataTrick->medias as $dataTrickMedia)
            {
                $media = new Media();
                $media->setName($dataTrickMedia->name);
                $media->setType($dataTrickMedia->type);
                $media->setLink($dataTrickMedia->link);
                $media->setTrick($trick);
                $trick->addMedia($media);
                $manager->persist($media);
            }

            $manager->persist($trick);

        }

        $manager->flush();
    }

    /**
     * Fonction permettant de charger le contenu du fichier JSON
     * @return mixed
     */

    private function loadDataFromJSON()
    {
        // On récupère les données depuis un fichier JSON qui contient une liste de figure à charger.
        $json = file_get_contents(__DIR__.'/Source/tricks.json');
        return json_decode($json);

    }

    /**
     * Fonction permettant de convertir une valeur au format URL
     * @param string $name
     */
    private function slugify(?string $name)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }

    /**
     * Fonction permettant de créer des categories de figures
     */
    private function createBaseTrickCategory(ObjectManager $manager)
    {
        $listCategories = ['Flip', 'Autres'];

        foreach ($listCategories as $categoryName)
        {
            $category = new TrickCategory();
            $category->setName($categoryName);
            $manager->persist($category);
        }
        //On sauvegarde tous les categories
        $manager->flush();

    }
}
