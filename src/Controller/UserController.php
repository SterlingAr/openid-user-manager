<?php
/**
 * @author: Marius Bora
 * Date: 20/2/20
 * Time: 17:26
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/api/v1/users", name="get_users")
     * @param Request $request
     * @return Response
     */
    public function getUsers(Request $request)
    {
        //
        $users = [
            '1',
            '2',
            '3'
        ];
        $response = [
            'users' => $users
        ];
        $auth = $request->headers->get('Authorization');

        if (!empty($auth)) {
            $response['userJwt'] = $auth;
        } else {
            return new Response('Missing Authorization Header', 400);
        }

        return new Response(json_encode($response), 200);
    }
}