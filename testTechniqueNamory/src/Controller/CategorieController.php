<?php

namespace App\Controller;

use App\Entity\Categorie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CategorieController extends AbstractController
{ private EntityManagerInterface $entityManager;
    private CategorieRepository $categoryRepository;
    private $tokenVerifier;
    public function __construct(EntityManagerInterface $entityManager, CategorieRepository $categoriesRepository,TokenServiceController $tokenServiceController)
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoriesRepository;
        $this->tokenVerifier = $tokenServiceController;
    }

    #[Route('v1/getAll/categorie', methods: ['GET'], name: 'app_get_all_categories')]
    public function getAll(): JsonResponse
    {
        $categories = $this->categoryRepository->findAll();

        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'id' => $category->getId(),
                'name' => $category->getNom(),
            ];
        }

        return $this->json([
            "error" => false,
            "message" => "Succès",
            "data" => $data,
        ], 200);
    }

    #[Route('v1/add/categorie', methods: ['POST'], name: 'app_add_categorie')]
    public function addCategorie(Request $request): JsonResponse
    {

        $name = $request->request->get('nom');
        $cheackNameExist = $this->categoryRepository->findOneBy(["nom" => $name]);

        if (empty($name)) {
            return $this->json([
                "error" => true,
                "message" => "Le nom de la catégorie est obligatoire.",
            ], 400);
        }

        if ($cheackNameExist) {
            return $this->json([
                "error" => true,
                "message" => "Une catégorie avec ce nom existe déjà.",
            ], 400);
        }
        $categorie = new Categorie();
        $categorie->setNom($name);
        $this->entityManager->persist($categorie);
        $this->entityManager->flush();

        return $this->json([
            "error" => false,
            "message" => "Catégorie ajoutée avec succès.",
            "data" => [
                "id" => $categorie->getId(),
                "name" => $categorie->getNom(),
            ],
        ], 201);
    }

    #[Route('v1/edit/categorie/{id}', methods: ['PUT'], name: 'app_edit_categorie')]
    public function EditCategory(int $id, Request $request): JsonResponse
    {
        /*
        $currentUser = $this->tokenVerifier->checkToken($request, null);

        if (gettype($currentUser) == 'boolean') {
            return $this->tokenVerifier->sendJsonErrorToken();
        }*/
        $cheackCategorie = $this->categoryRepository->find($id);
        $name = $request->request->get('nom');

        if (!$cheackCategorie) {
            return $this->json([
                "error" => true,
                "message" => "Aucune catégorie avec ce Id.",
            ], 400);
        }

        if(empty($name)){
            return $this->json([
                "error" => true,
                "message" => "Le nom de la catégorie est obligatoire.",
            ], 400);
        }

        $cheackNameExist = $this->categoryRepository->findOneBy(["nom" => $name]);
        
        if($cheackNameExist){
            return $this->json([
                "error" => true,
                "message" => "Une catégorie avec ce nom existe déjà.",
            ], 400);
        }

        $cheackCategorie->setNom($name);
        $this->entityManager->flush();

        return $this->json([
            "error" => false,
            "message" => "Modifier avec succès.",
            "data" => [
                "id" => $cheackCategorie->getId(),
                "name" => $cheackCategorie->getNom(),
            ],
        ], 200);

    }

    #[Route('v1/delete/categorie/{id}', methods: ['DELETE'], name: 'app_delete_categorie')]

    public function DeleteCategory(int $id,Request $request): JsonResponse
    {/*
        $currentUser = $this->tokenVerifier->checkToken($request, null);

        if (gettype($currentUser) == 'boolean') {
            return $this->tokenVerifier->sendJsonErrorToken();
        }*/
        $cheackCategorie = $this->categoryRepository->find($id);
        if (!$cheackCategorie) {
            return $this->json([
                "error" => true,
                "message" => "Aucune catégorie avec ce Id.",
            ], 400);
        }
        $this->entityManager->remove($cheackCategorie);
        $this->entityManager->flush();
        
        return $this->json([
            "error" => false,
            "message" => "Catégorie supprimée avec succès.",
        ], 200); 
    }
}
