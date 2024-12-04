<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;



class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private $JWTManager;
    

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository,JWTTokenManagerInterface $jwtManager)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->JWTManager = $jwtManager;
    }
    #[Route('v1/login/user',methods:["POST"], name: 'app_post_user')]
    public function loginx(Request $request,UserPasswordHasherInterface $passwordHash): JsonResponse
    {
        $email = $request->get('email');
        $password = $request->get('password');

        if(empty($email) || empty($password)) {
            return $this->json([
                'error' => true,
                'message' => 'Email ou password manquants.'
            ], 400);
        }


        $UserLogin =$this->userRepository->findOneBy(['email'=> $email]);
        if(!$UserLogin){
            return $this->json([
                'error' => true,
                'message' => 'Email incorret.'
            ], 400);
        }
        $passwordDehash =$passwordHash->isPasswordValid($UserLogin, $password); 

        if(!$passwordDehash){
            return $this->json([
                'error' => true,
                'message' => 'password incorret.'
            ], 400);
        }

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'token' => $this->JWTManager->create($UserLogin),
        ]);
    }
}
