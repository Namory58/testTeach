<?php

namespace App\Controller;

use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;




class ProduitController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ProduitRepository $produitsRepository;
    private CategorieRepository $categorieRepository;

    private $tokenVerifier;

    public function __construct(EntityManagerInterface $entityManager, ProduitRepository $produitsRepository, CategorieRepository $categoriesRepository,TokenServiceController $tokenServiceController)
    {
        $this->entityManager = $entityManager;
        $this->produitsRepository = $produitsRepository;
        $this->categorieRepository = $categoriesRepository;
        $this->tokenVerifier = $tokenServiceController;
    }
    #[Route('v1/getAll/produit', methods: ['GET'], name: 'app_produit')]
    public function index(): JsonResponse
    {
        $produits = $this->produitsRepository->findAll();

        $AllProduits = [];

        foreach ($produits as $produit) {
            $AllProduits[] = [
                'id' => $produit->getId(),
                'name' => $produit->getNom(),
                'categorie' => [
                    'id' => $produit->getId(),
                    'nom' => $produit->getCategorie()->getNom(),
                ],
                'description' => $produit->getDescription(),
                'prix' => $produit->getPrix()
            ];
        }

        return $this->json([
            'error' => false,
            'message' => 'succès',
            'data' => $AllProduits,
        ]);
    }
    #[Route('v1/add/produit', methods: ['POST'], name: 'app_post_produit')]
    public function addProduit(Request $request): JsonResponse
    {
        $name = $request->request->get('nom');
        $description = $request->request->get('description');
        $prix = $request->request->get('prix');
        $categorie = $request->request->get('categorie');


        if (empty($name) || empty($categorie) || empty($description) || empty($prix)) {
            return $this->json([
                "error" => true,
                "message" => "les paramètres nom ,description,prix,categorie sont obligatoires .",
            ], 200);
        }

        if (!is_numeric($categorie)) {
            return $this->json([
                "error" => true,
                "message" => "La catégorie doit être un nombre.",
            ], 400);
        }


        if (!is_numeric($prix)) {
            return $this->json([
                "error" => true,
                "message" => "Le prix doit être un nombre ou un float.",
            ], 400);
        }
        $checkProduitNameExist = $this->produitsRepository->findOneBy(["nom" => $name]);

        if ($checkProduitNameExist) {
            return $this->json([
                "error" => true,
                "message" => "Le nom du produit existe .",
            ], 200);
        }

        $numberCategorie = (int) trim($categorie);
        $numberCategoriePrix = (float) trim($prix);


        $checkCategorieExist = $this->categorieRepository->find($numberCategorie);

        if (!$checkCategorieExist) {
            return $this->json([
                "error" => true,
                "message" => "La catégorie n'existe pas .",
            ], 400);
        }

       
        $produit = new Produit();
        $produit->setNom($name);
        $produit->setCategorie($checkCategorieExist); 
        $produit->setDescription($description);
        $produit->setPrix($numberCategoriePrix);
        $produit->setCreateAt(new \DateTimeImmutable('now'));

     
        $this->entityManager->persist($produit);
        $this->entityManager->flush();

        return $this->json([
            "error" => false,
            "message" => "Produit créé avec succès.",
            "produit" => [
                "nom" => $produit->getNom(),
                "description" => $produit->getDescription(),
                "prix" => $produit->getPrix(),
                "categorie" => $produit->getCategorie()->getNom(), 
            ],
        ], 201);
    }
    #[Route('v1/edit/produit/{id}', methods: ['PUT'], name: 'app_put_produit')]
    public function EditProduit(int $id, Request $request): JsonResponse
    {
        /*
        $currentUser = $this->tokenVerifier->checkToken($request, null);

        if (gettype($currentUser) == 'boolean') {
            return $this->tokenVerifier->sendJsonErrorToken();
        }*/
        $checkProduitsExist = $this->produitsRepository->find($id);

        if (!$checkProduitsExist) {
            return $this->json([
                "error" => true,
                "message" => "Le produit avec l'ID donné n'existe pas.",
            ], 404);
        }

        $name = $request->request->get('nom');
        $description = $request->request->get('description');
        $prix = $request->request->get('prix');
        $categorie = $request->request->get('categorie');

        if ($categorie !== null && !is_numeric($categorie)) {
            return $this->json([
                "error" => true,
                "message" => "La catégorie doit être un nombre.",
            ], 400);
        }

        if ($prix !== null && !is_numeric($prix)) {
            return $this->json([
                "error" => true,
                "message" => "Le prix doit être un nombre ou un float.",
            ], 400);
        }

        $numberCategorie = (int) trim($categorie);
        $numberCategoriePrix = (float) trim($prix);


        if ($categorie !== null) {
            $checkCategorieExist = $this->categorieRepository->find($numberCategorie);
            if (!$checkCategorieExist) {
                return $this->json([
                    "error" => true,
                    "message" => "La catégorie n'existe pas.",
                ], 400);
            }
        }

        if ($name !== null) {
            $checkProduitsExist->setNom($name);
        }

        if ($description !== null) {
            $checkProduitsExist->setDescription($description);
        }

        if ($prix !== null) {
            $checkProduitsExist->setPrix($numberCategoriePrix);
        }

        if ($categorie !== null) {
            $checkProduitsExist->setCategorie($checkCategorieExist);
        }
        $checkProduitsExist->setCreateAt(new \DateTimeImmutable('now'));


        $this->entityManager->persist($checkProduitsExist);
        $this->entityManager->flush();


        return $this->json([
            "error" => false,
            "message" => "Produit  modifié avec succès.",
            "produit" => [
                "nom" => $checkProduitsExist->getNom(),
                "description" => $checkProduitsExist->getDescription(),
                "prix" => $checkProduitsExist->getPrix(),
                "categorie" => $checkProduitsExist->getCategorie()->getNom(), 
            ],
        ], 201);
    }

    #[Route('v1/delete/produit/{id}', methods: ['DELETE'], name: 'app_delete_produit')]
    public function DeleteProduit(int $id): JsonResponse
    {
        $checkProduitsExist = $this->produitsRepository->find($id);
        if (!$checkProduitsExist) {
            return $this->json([
                "error" => true,
                "message" => "Aucun produit  avec ce Id.",
            ], 400);
        }
        $this->entityManager->remove($checkProduitsExist);
        $this->entityManager->flush();

        return $this->json([
            "error" => false,
            "message" => "Produit supprimé avec succès.",
        ], 200);
    }
}
