<?php
    namespace App\Application\Security;

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

        private EntityManagerInterface $entityManager;
        private CsrfTokenManagerInterface $csrfTokenManager;
        private KernelInterface $kernel;
        private RouterInterface $router;
        private SettingService $settingService;
        private UserPasswordHasherInterface $passwordHasher;

        public function __construct(
            CsrfTokenManagerInterface $csrfTokenManager,
            EntityManagerInterface $entityManager,
            KernelInterface $kernel,
            RouterInterface $router,
            SettingService $settingService,
            UserPasswordHasherInterface $passwordHasher){
                $this->entityManager = $entityManager;
                $this->csrfTokenManager = $csrfTokenManager;
                $this->kernel = $kernel;
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
            if($this->settingService->getSetting("security.ldap.enabled")){
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username'], 'source' => 'ldap']);
                if(!$user){
                    $servers = $this->settingService->getSetting("security.ldap.connections");
                    foreach ($servers as $k => $v){
                        $ldap = Ldap::create('ext_ldap', ['host' => $v->ip, 'encryption' => $v->encryption]);
                        $ldap->bind($this->settingService->getSetting('security.ldap.authentication.username'), $this->settingService->getSetting('security.ldap.authentication.password'));
                        $query = $ldap->query('DC=int,DC=local', '(&(userPrincipalName='. $credentials['username'] .'))');
                        $results = $query->execute()->toArray();
                        if(sizeof($results) == 1) {
                            try{
                                $ldap->bind($results[0]->getDn(), $credentials['password']);

                                $user = new User();
                                $user->setUsername($credentials['username']);
                                $user->setSource("ldap");

                                $this->entityManager->persist($user);
                                $this->entityManager->flush();

                                return new SelfValidatingPassport(
                                    new UserBadge($credentials['username'])
                                );
                            } catch (\Exception $e){
                                throw new AuthenticationException($e->getMessage());
                            }
                        }
                    }
                } else {
                    $servers = $this->settingService->getSetting("security.ldap.connections");
                    foreach ($servers as $k => $v){
                        $ldap = Ldap::create('ext_ldap', ['host' => $v->ip, 'encryption' => $v->encryption]);
                        $ldap->bind($this->settingService->getSetting('security.ldap.authentication.username'), $this->settingService->getSetting('security.ldap.authentication.password'));
                        $query = $ldap->query('DC=int,DC=local', '(&(userPrincipalName='. $credentials['username'] .'))');
                        $results = $query->execute()->toArray();
                        if(sizeof($results) == 1) {
                            try {
                                $ldap->bind($results[0]->getDn(), $credentials['password']);

                                return new SelfValidatingPassport(
                                    new UserBadge($credentials['username'])
                                );
                            } catch (\Exception $e) {
                                throw new AuthenticationException($e->getMessage());
                            }
                        }
                    }
                }
            }

            /**
             * CatchAll
             */
            throw new AuthenticationCredentialsNotFoundException();
        }

        public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
            return new RedirectResponse($request->getSession()->getBag('attributes')->get('_security.backoffice.target_path'));
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