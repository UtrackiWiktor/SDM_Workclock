<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class KeycloakController extends AbstractController
{
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/connect/keycloak', name: 'connect_keycloak_start')]
    public function connectKeycloak(ClientRegistry $clientRegistry): RedirectResponse
    {
        /** @var \KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface $client */
        $client = $clientRegistry->getClient('keycloak');

        return $client->redirect(['openid', 'profile', 'email']); // Request the scopes
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request, TokenStorageInterface $tokenStorage, SessionInterface $session): RedirectResponse
    {
        $tokenId = $request->getSession()->get('keycloak_token_id');
        $this->logger->debug("logout method in KeycloakController: tokenId (" . gettype($tokenId) . ") dump: " . $tokenId ?? "[ERROR: tokenId is null]");
        dump($request->getSession()->all());
        // Clear Symfony session
        $tokenStorage->setToken(null);
        $session->invalidate();

        $keycloakLogoutUrl = sprintf(
            'http://localhost:8080/realms/workclock/protocol/openid-connect/logout?post_logout_redirect_uri=%s&client_id=%s',
            urlencode('http://localhost:8000/'),//urlencode($this->generateUrl('home', [], UrlGeneratorInterface::ABSOLUTE_URL)), // after logout redirect
            urlencode((string) $tokenId)
        );

        return new RedirectResponse($keycloakLogoutUrl);
    }
}