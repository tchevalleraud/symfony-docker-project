<?php
    namespace App\Application\Security;

    use App\Application\Services\LDAPService;
    use App\Application\Services\SettingService;
    use App\Domain\_mysql\System\Entity\User;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\KernelInterface;
    use Symfony\Component\Ldap\Ldap;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
    use Symfony\Component\Security\Core\Security;
    use Symfony\Component\Security\Csrf\CsrfToken;
    use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
    use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
    use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
    use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
    use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
    use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

    class MultiAuthenticator extends AbstractAuthenticator {

        private CsrfTokenManagerInterface $csrfTokenManager;
        private EntityManagerInterface $entityManager;
        private KernelInterface $kernel;
        private LDAPService $LDAPService;
        private RouterInterface $router;
        private SettingService $settingService;
        private UserPasswordHasherInterface $passwordHasher;

        public function __construct(
            CsrfTokenManagerInterface $csrfTokenManager,
            EntityManagerInterface $entityManager,
            KernelInterface $kernel,
            LDAPService $LDAPService,
            RouterInterface $router,
            SettingService $settingService,
            UserPasswordHasherInterface $passwordHasher){
                $this->entityManager = $entityManager;
                $this->csrfTokenManager = $csrfTokenManager;
                $this->kernel = $kernel;
                $this->LDAPService = $LDAPService;
                $this->router = $router;
                $this->settingService = $settingService;
                $this->passwordHasher = $passwordHasher;
        }

        public function supports(Request $request): ?bool {
            if($request->request->get('_username')) return true;
            else return false;
        }

        public function authenticate(Request $request): Passport {
            $credentials = $this->getCredentials($request);

            $token = new CsrfToken('authenticate', $credentials['csrf_token']);
            if(!$this->csrfTokenManager->isTokenValid($token)){
                throw new InvalidCsrfTokenException();
            }

            /**
             * Local authentication
             */
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username'], 'source' => 'local']);
            if($user){
                if($this->passwordHasher->isPasswordValid($user, $credentials['password'])){
                    return new Passport(
                        new UserBadge($credentials['username']),
                        new PasswordCredentials($credentials['password'])
                    );
                } else {
                    throw new AuthenticationCredentialsNotFoundException();
                }
            }

            /**
             * LDAP Authentication
             */
            if($this->settingService->getSetting("security.ldap.enabled")) {
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username'], 'source' => 'ldap']);
                $servers = $this->LDAPService->getServers();
                foreach ($servers as $s) {
                    if ($this->LDAPService->testConnection($s)) {
                        $search = $this->LDAPService->searchUser($credentials['username']);
                        if ($search !== false) {
                            if ($this->LDAPService->testConnection($s, $search, $credentials['password'])) {
                                if (!$user) {
                                    $user = new User();
                                    $user->setUsername($credentials['username']);
                                    $user->setSource("ldap");

                                    $this->entityManager->persist($user);
                                    $this->entityManager->flush();
                                }

                                return new SelfValidatingPassport(new UserBadge($credentials['username']));
                            } else throw new AuthenticationException();
                        } else throw new AuthenticationException();
                    } else throw new AuthenticationException();
                }
            }

            /**
             * CatchAll
             */
            throw new AuthenticationCredentialsNotFoundException();
        }

        public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
            return new RedirectResponse($request->getSession()->getBag('attributes')->get('_security.'.$firewallName.'.target_path'));
        }

        public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
            if ($request->hasSession()) {
                $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
            }

            return new RedirectResponse($this->router->generate($request->attributes->get('_route')));
        }

        private function getCredentials(Request $request): array {
            $credentials = [];
            $credentials['csrf_token'] = $request->request->get('_csrf_token');

            $credentials['username'] = $request->request->get('_username');
            $credentials['password'] = $request->request->get('_password');

            $request->getSession()->set(Security::LAST_USERNAME, $credentials['username']);

            return $credentials;
        }

    }