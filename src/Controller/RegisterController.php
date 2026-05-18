<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;

class RegisterController extends AbstractController
{
    public function index(UserPasswordHasherInterface $passwordHasher, Request $request) : Response
    {
        $data = $request->request->all();
        $user = new User();
        $user->setEmail("mohamed@gmail.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setUsername("mohamed");
        $plainTextPassword = "sme hashed password";
        $hashedPassword = $passwordHasher->hashPassword($user, $plainTextPassword);
        $user->setPassword($hashedPassword);
        return $this->json(['message' => 'User created', 'user' => $user, 'request' => $data], Response::HTTP_CREATED);
    }
}
