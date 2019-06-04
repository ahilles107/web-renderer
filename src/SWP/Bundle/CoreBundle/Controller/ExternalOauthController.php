<?php

declare(strict_types=1);

namespace SWP\Bundle\CoreBundle\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ExternalOauthController extends Controller
{
    /**
     * @Route("/connect/oauth", name="connect_oauth_start")
     */
    public function connectAction(Request $request)
    {
        $clientRegistry = $this->get('knpu.oauth2.registry');

        return $clientRegistry
            ->getClient('external_oauth')
            ->redirect([
                'openid', 'email'
            ]);
    }

    /**
     * This is the redirect route.
     * This is where the user is redirected after being authenticated by the OAuth server.
     * @Route("/connect/oauth/check", name="connect_oauth_check")
     */
    public function connectCheckAction(Request $request)
    {
        $clientRegistry = $this->get('knpu.oauth2.registry');
        $client = $clientRegistry->getClient('external_oauth');

        $accessToken = $client->getAccessToken();

        try {
            $oauthUser = $client->fetchUserFromToken($accessToken);
        } catch(IdentityProviderException $e) {
            var_dump($e->getMessage()); die;
        }
    }
}
