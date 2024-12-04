<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use App\Controller\TokenServiceController;
use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;
use App\Controller\CategorieController;

class CategorieControllerTest extends TestCase
{
    private $entityManager;
    private $categorieRepository;
    private $tokenServiceController;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->categorieRepository = $this->createMock(CategorieRepository::class);
        $this->tokenServiceController = $this->createMock(TokenServiceController::class);
    }

    public function testGetAllCategories()
    {
        // Mock categories
        $categorie1 = $this->createMock(Categorie::class);
        $categorie2 = $this->createMock(Categorie::class);

        $categorie1->method('getId')->willReturn(1);
        $categorie1->method('getNom')->willReturn('Catégorie 1');

        $categorie2->method('getId')->willReturn(2);
        $categorie2->method('getNom')->willReturn('Catégorie 2');

        $this->categorieRepository->method('findAll')->willReturn([$categorie1, $categorie2]);

        // Create controller
        $controller = new CategorieController(
            $this->entityManager,
            $this->categorieRepository,
            $this->tokenServiceController
        );

        $container = new Container();
        $controller->setContainer($container);

        $response = $controller->getAll();
        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['error']);
        $this->assertEquals('Succès', $data['message']);
        $this->assertCount(2, $data['data']);
        $this->assertEquals(1, $data['data'][0]['id']);
        $this->assertEquals('Catégorie 1', $data['data'][0]['name']);
    }

    public function testAddCategorie()
    {
        $request = new Request([], [
            'nom' => 'Nouvelle Catégorie',
        ]);

        $this->categorieRepository->method('findOneBy')->willReturn(null);  // No existing category with this name
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        // Create controller
        $controller = new CategorieController(
            $this->entityManager,
            $this->categorieRepository,
            $this->tokenServiceController
        );

        $container = new \Symfony\Component\DependencyInjection\Container();
        $controller->setContainer($container);

        $response = $controller->addCategorie($request);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['error']);
        $this->assertEquals('Catégorie ajoutée avec succès.', $data['message']);
    }
    public function testErrorAdd()
    {
        $request = new Request([], [
            'nom' => "",
        ]);

        $this->categorieRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $controller = new CategorieController(
            $this->entityManager,
            $this->categorieRepository,
            $this->tokenServiceController
        );

        $container = new Container();
        $controller->setContainer($container);

        $response = $controller->addCategorie($request);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);


        $this->assertTrue($data['error']);
        $this->assertEquals('Le nom de la catégorie est obligatoire.', $data['message']);
        $this->assertEquals(400, $response->getStatusCode());
    }
    public function testErrorAddWhenCategoryAlreadyExists()
    {

        $request = new Request([], [
            'nom' => 'Nouvelle Catégorie',
        ]);

        $existingCategory = $this->mockCategorie(1, 'Catégorie 1');
        $this->categorieRepository->method('findOneBy')->willReturn($existingCategory);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $controller = new CategorieController(
            $this->entityManager,
            $this->categorieRepository,
            $this->tokenServiceController
        );

        $container = new \Symfony\Component\DependencyInjection\Container();
        $controller->setContainer($container);


        $response = $controller->addCategorie($request);

        $this->assertInstanceOf(JsonResponse::class, $response);


        $data = json_decode($response->getContent(), true);


        $this->assertTrue($data['error']);
        $this->assertEquals('Une catégorie avec ce nom existe déjà.', $data['message']);


        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testEditCategorie()
    {
        $request = new Request([], [
            'nom' => 'Catégorie modifiée',
        ]);

        $categorie = $this->createMock(Categorie::class);
        $categorie->method('getId')->willReturn(1);
        $categorie->method('getNom')->willReturn('Ancienne Catégorie');

        $this->categorieRepository->method('find')->willReturn($categorie); // Return the mock category
        $this->categorieRepository->method('findOneBy')->willReturn(null); // No duplicate category with the new name
        $this->entityManager->expects($this->once())->method('flush'); // Ensure flush is called

        // Create controller
        $controller = new CategorieController(
            $this->entityManager,
            $this->categorieRepository,
            $this->tokenServiceController
        );

        $container = new \Symfony\Component\DependencyInjection\Container();
        $controller->setContainer($container);

        $response = $controller->EditCategory(1, $request);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['error']);
        $this->assertEquals('Modifier avec succès.', $data['message']);
    }
    
    public function testDeleteCategorie()
    {
        $categorie = $this->createMock(Categorie::class);
        $categorie->method('getId')->willReturn(1);
        $this->categorieRepository->method('find')->willReturn($categorie); 
        $this->entityManager->expects($this->once())->method('remove');
        $this->entityManager->expects($this->once())->method('flush');

        $request = new Request();

        // Create controller
        $controller = new CategorieController(
            $this->entityManager,
            $this->categorieRepository,
            $this->tokenServiceController
        );

        $container = new \Symfony\Component\DependencyInjection\Container();
        $controller->setContainer($container);

        $response = $controller->DeleteCategory(1, $request);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['error']);
        $this->assertEquals('Catégorie supprimée avec succès.', $data['message']);
    }

    public function testEditCategoryWhenCategoryWithNameAlreadyExists()
{

    $request = new Request([], [
        'nom' => 'Catégorie Existence',
    ]);

    $existingCategory = $this->mockCategorie(1, 'Catégorie Existence');
    $this->categorieRepository->method('find')->willReturn($existingCategory); 

    
    $this->categorieRepository->method('findOneBy')->willReturn($existingCategory);  

   
    $controller = new CategorieController(
        $this->entityManager,
        $this->categorieRepository,
        $this->tokenServiceController
    );

    $container = new \Symfony\Component\DependencyInjection\Container();
    $controller->setContainer($container);

    
    $response = $controller->EditCategory(1, $request);  

    $this->assertInstanceOf(JsonResponse::class, $response);

    $data = json_decode($response->getContent(), true);

   
    $this->assertTrue($data['error']);
    $this->assertEquals('Une catégorie avec ce nom existe déjà.', $data['message']);
    $this->assertEquals(400, $response->getStatusCode());  
}

    private function mockCategorie(int $id, string $nom): Categorie
    {
        $categorie = new Categorie();
        $categorie->setNom($nom);

        // Use reflection to set the ID
        $reflection = new \ReflectionClass($categorie);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($categorie, $id);

        return $categorie;
    }
}
