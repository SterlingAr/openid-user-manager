<?php
/**
 * @author: Marius Bora
 * Date: 20/2/20
 * Time: 10:46
 */

namespace App\Controller;

use App\Form\Type\ConsentType;
use App\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AuthController extends AbstractController
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $hydraBaseUrl;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
        $this->hydraBaseUrl = 'http://hydra:4445';
    }

    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @return Response
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function login(Request $request)
    {
        $challenge = $request->query->get('login_challenge');

        $uri = sprintf($this->hydraBaseUrl . '/oauth2/auth/requests/login?login_challenge=%s', $challenge);
        $loginRes = $this->httpClient->request('GET', $uri);
        $loginData = json_decode($loginRes->getContent(), true);

        if ($loginData['skip']) {
            return new Response('There is an active session, we can skip', 200);
        } else {
            $form = $this->createForm(UserType::class, null, [
                'action' => $this->generateUrl('login_submit')
            ]);
            $form->add('login_challenge', HiddenType::class, ['data' => $challenge]);
            return $this->render('auth/login_form.html.twig', [
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/login_submit", name="login_submit")
     * @param Request $request
     * @return Response
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     * No need to check credentials, we'll assume its all okay and proceed to the next steps.
     * We fetch the login challenge from the form and authorize it, this will return a link to which we will redirect the user agent.
     */
    public function loginSubmit(Request $request)
    {
        $data = $request->request->get('user');
        $challenge = $data['login_challenge'];

        $uri = sprintf($this->hydraBaseUrl .'/oauth2/auth/requests/login/accept?login_challenge=%s', $challenge);

        // more info about possible parameters: https://www.ory.sh/docs/hydra/sdk/api#accept-a-login-request
        $requestBody = [
            'subject' => "1", // we should know the user id by now,
            'remember' => true
        ];
        $loginRes = $this->httpClient->request('PUT', $uri, [
            'body' => json_encode($requestBody),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $loginData = json_decode($loginRes->getContent(), true);
        return new RedirectResponse( $loginData['redirect_to']);
    }

    /**
     * @Route("/consent", name="consent")
     */
    public function consent(Request $request)
    {
        $challenge = $request->query->get('consent_challenge');

        $uri = sprintf($this->hydraBaseUrl . '/oauth2/auth/requests/consent?consent_challenge=%s', $challenge);
        $consentRes = $this->httpClient->request('GET', $uri);
        $consentData = json_decode($consentRes->getContent(), true);

        if ($consentData['skip']) {
            return new Response('User already consented, we can skip', 200);
        } else {
            $form = $this->createForm(ConsentType::class, null, [
                'action' => $this->generateUrl('consent_submit')
            ]);
            $form->add('consent_challenge', HiddenType::class, ['data' => $challenge]);
            return $this->render('auth/consent_form.html.twig', [
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/consent_submit", name="consent_submit")
     * @param Request $request
     * @return Response
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     * No need to check credentials, we'll assume its all okay and proceed to the next steps.
     * We fetch the consent challenge from the form and authorize it, this will return a link to which we will redirect the user agent.
     */
    public function consentSubmit(Request $request)
    {
        $data = $request->request->get('consent');
        $challenge = $data['consent_challenge'];

        $scopes = [
            'offline',
            'offline_access',
            'openid',
            'profile',
        ];

        if ($data['users'])
            $scopes[] = 'users';


        $sessionData = [
            'email' => 'fulano@de.tal'
        ];

        $uri = sprintf($this->hydraBaseUrl . '/oauth2/auth/requests/consent/accept?consent_challenge=%s', $challenge);

        // more info about possible parameters: https://www.ory.sh/docs/hydra/sdk/api#accept-a-consent-request
        $requestBody = [
            'grant_scope' => $scopes,
            'session' => [
                'access_token' => $sessionData,
                'id_token' => $sessionData
            ]
        ];

        $consentRes = $this->httpClient->request('PUT', $uri, [
            'body' => json_encode($requestBody),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $consentData = json_decode($consentRes->getContent(), true);
        return new RedirectResponse( $consentData['redirect_to']);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(Request $request)
    {
        $challenge = $request->query->get('logout_challenge');

        $uri = sprintf($this->hydraBaseUrl . '/oauth2/auth/requests/logout?logout_challenge=%s', $challenge);
        $logoutRes = $this->httpClient->request('GET', $uri);
        $logoutData = json_decode($logoutRes->getContent(), true);

        // accept logout request
        $uri = sprintf($this->hydraBaseUrl . '/oauth2/auth/requests/logout/accept?logout_challenge=%s', $challenge);
        $logoutRes = $this->httpClient->request('PUT', $uri);

        $logoutData = json_decode($logoutRes->getContent(), true);
        return new RedirectResponse( $logoutData['redirect_to']);
        // redirect to login with "session_end" query parameter
    }

    /**
     * @Route("/error", name="error")
     */
    public function error(Request $request)
    {
        $data = $request->query->all();
        $data = json_encode($data);
        return new Response($data, 200);
    }
}
