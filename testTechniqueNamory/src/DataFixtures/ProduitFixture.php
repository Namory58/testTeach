<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Repository\CategorieRepository;

class ProduitFixture extends Fixture
{
    private CategorieRepository $categoriesRepository;

    public function __construct(CategorieRepository $categoriesRepository)
    {
        $this->categoriesRepository = $categoriesRepository; 
    }
    public function load(ObjectManager $manager): void
    {
        $categories = $this->categoriesRepository->findAll(); 
        for ($i = 1; $i <= 50; $i++) {
            $produit = new Produit();
            $produit->setNom('Produit ' . $i);
            $produit->setDescription('Description pour le produit ' . $i);
            $produit->setPrix(rand(10, 100)); 
            $produit->setCreateAt(new \DateTimeImmutable('now'));

            $categorie = $categories[array_rand($categories)];
            $produit->setCategorie($categorie);
            $manager->persist($produit);
        }
        $manager->flush();
        $manager->flush();
    }
}
