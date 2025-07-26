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
}