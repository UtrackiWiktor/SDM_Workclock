<?php

namespace App\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class KeycloakAuthenticator extends AbstractLoginFormAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;
    private ClientRegistry $clientRegistry;
    private UrlGeneratorInterface $urlGenerator;

    private LoggerInterface $logger;
    public function __construct(ClientRegistry $clientRegistry, UrlGeneratorInterface $urlGenerator, LoggerInterface $logger)
    {
        $this->clientRegistry = $clientRegistry;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): bool
    {
        return $request->getPathInfo() === '/connect/keycloak/check' && $request->isMethod('GET');
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('keycloak');
        try {
            $accessToken = $client->getAccessToken([
                'code' => $request->query->get('code'),
            ]);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            throw new AuthenticationException('Could not get access token: ' . $e->getMessage());
        }
        $resourceOwner = $client->fetchUserFromToken($accessToken);
        $user = new KeycloakUser(
            $resourceOwner->getId(),
            $resourceOwner->getEmail(),
            ['ROLE_USER']
        );
        $this->logger->debug("Dump id token value for user: " . $user->getUsername() . ": " . $accessToken->getValues()['id_token'] ?? "[ERROR: id token could not be retrieved]");
        $request->getSession()->set("keycloak_token_id", $accessToken->getValues()['id_token'] ?? null);
        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), fn() => $user)
        );
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        return new RedirectResponse($targetPath ?: $this->urlGenerator->generate('home')); // Redirect to homepage or intended path
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $errorMessage = strtr($exception->getMessageKey() ?: 'Authentication failed.', $exception->getMessageData());
        return new Response($errorMessage, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->urlGenerator->generate('connect_keycloak_start')
        );
    }
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('connect_keycloak_start');
    }

}