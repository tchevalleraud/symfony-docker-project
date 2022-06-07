<?php
    namespace App\Application\Security;

    use App\Application\Services\SettingService;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\SessionInterface;
    use Symfony\Component\HttpKernel\Event\RequestEvent;
    use Symfony\Component\HttpKernel\KernelInterface;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
    use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

    class SessionIdle {

        protected AuthorizationCheckerInterface $authorizationChecker;
        protected KernelInterface $kernel;
        protected TokenStorageInterface $tokenStorage;
        protected RouterInterface $router;
        protected SessionInterface $session;
        protected SettingService $settingService;

        protected $maxIdleTime = 3600;

        public function __construct(
            AuthorizationCheckerInterface $authorizationChecker,
            TokenStorageInterface $tokenStorage,
            RouterInterface $router,
            SettingService $settingService
        ){
            $this->authorizationChecker = $authorizationChecker;
            $this->tokenStorage = $tokenStorage;
            $this->router = $router;
            $this->session = new Session();
            $this->settingService = $settingService;

            $this->maxIdleTime = $this->settingService->getSetting("security.session.idle");
        }

        public function onKernelRequest(RequestEvent $event){
            if (!$event->isMainRequest() || !$this->isUserLoggedIn()) {
                return;
            }

            $this->session->remove('_security.idle');
            $this->session->remove('_security.idle.remaining');

            if($this->maxIdleTime > 0){
                $this->session->start();
                $lapse = time() - $this->session->getMetadataBag()->getLastUsed();
                if ($lapse > $this->maxIdleTime) {
                    $this->tokenStorage->setToken(null);
                    $event->setResponse(new RedirectResponse($this->router->generate('root')));
                } else {
                    $this->session->set('_security.idle', $lapse);
                    $this->session->set('_security.idle.remaining', $this->maxIdleTime - $lapse);
                }
            }
        }

        protected function isUserLoggedIn(){
            try {
                return $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED');
            } catch (AuthenticationCredentialsNotFoundException $exception) {
            }
            return false;
        }


    }