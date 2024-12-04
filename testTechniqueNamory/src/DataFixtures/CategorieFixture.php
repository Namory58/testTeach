<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $listeCategorie = [
            'Verselle',
            'Technologie',
            'Santé',
            'Sport',
            'Éducation',
            'Cuisine',
            'Voyage',
            'Mode',
            'Art',
            'Musique',
            'Films',
            'Littérature',
            'Jeux vidéo',
            'Environnement',
            'Science',
            'Histoire',
            'Politique',
            'Finance',
        ];
        foreach ($listeCategorie as $nomCategorie) {
            $categorie = new Categorie();
            $categorie->setNom($nomCategorie);
            $manager->persist($categorie);
        }
        $manager->flush();;

        $manager->flush();
    }
}
