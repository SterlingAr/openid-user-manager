<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request)
    {
        // login challenge bullshit
        // yes, user exists :)
        return new Response("Login", 200);
    }

    /**
     * @Route("/consent", name="consent")
     */
    public function consent(Request $request)
    {
        // consent challenge bullshit
        return new Response("Consent", 200);
    }
}
