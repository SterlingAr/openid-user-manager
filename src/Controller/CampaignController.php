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

class CampaignController extends AbstractController
{
    /**
     * @Route("/api/v1/campaigns", name="get_campaigns")
     * @param Request $request
     * @return Response
     */
    public function getCampaigns(Request $request)
    {
        //
        $campaigns = [
            'uuid-0-0-0',
            'uuid-1-1-1'
        ];
        $response = [
            'campaigns' => $campaigns
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