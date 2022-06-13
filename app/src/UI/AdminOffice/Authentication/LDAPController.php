<?php
    namespace App\UI\AdminOffice\Authentication;

    use App\Application\Services\LDAPService;
    use App\Application\Services\SettingService;
    use App\Domain\_local\System\Forms\SettingLdap;
    use App\Domain\_local\System\Forms\SettingLdapConnection;
    use App\Infrastructure\Forms\AdminOffice\Authentication\LDAP\EditForm;
    use App\Infrastructure\Forms\AdminOffice\Authentication\LDAP\EditServerForm;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    use Symfony\Component\Ldap\Ldap;
    use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("authentication/ldap", name="authentication.ldap.")
     */
    class LDAPController extends AbstractController {

        /**
         * @Route(".html", name="index", methods={"GET", "POST"})
         */
        public function index(Request $request, SettingService $settingService){
            $settingLdap = new SettingLdap($settingService);
            $connections = $settingService->getSetting('security.ldap.connections');

            $form = $this->createForm(EditForm::class, $settingLdap);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $settingService->setSetting('security.ldap.authentication.username', $settingLdap->getSecurityLdapAuthenticationUsername());

                if($settingLdap->getSecurityLdapAuthenticationPassword() !== null)
                    $settingService->setSetting('security.ldap.authentication.password', $settingLdap->getSecurityLdapAuthenticationPassword());

                $settingService->setSetting('security.ldap.search.user', $settingLdap->getSecurityLdapSearchUser());
                $settingService->setSetting('security.ldap.schema.user.object', $settingLdap->getSecurityLdapSchemaUserObject());
                $settingService->setSetting('security.ldap.schema.user.search', $settingLdap->getSecurityLdapSchemaUserSearch());
                $settingService->setSetting('security.ldap.enabled', $settingLdap->getSecurityLdapEnabled());

                return $this->redirectToRoute('adminoffice.authentication.ldap.index');
            }

            return $this->render("AdminOffice/Authentication/LDAP/index.html.twig", [
                'connections'   => $connections,
                'form'          => $form->createView()
            ]);
        }

        /**
         * @Route("/new.html", name="new", methods={"GET", "POST"})
         */
        public function new(Request $request, SettingService $settingService){
            $server = new SettingLdapConnection();
            $form = $this->createForm(EditServerForm::class, $server);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $servers = $settingService->getSetting('security.ldap.connections');
                $servers[] = [
                    'ip'            => $server->getIp(),
                    'encryption'    => $server->getEncryption()
                ];
                $settingService->setSetting('security.ldap.connections', $servers);
                return $this->redirectToRoute('adminoffice.authentication.ldap.index');
            }

            return $this->render("AdminOffice/Authentication/LDAP/new.html.twig", [
                'form'  => $form->createView()
            ]);
        }

        /**
         * @Route("/edit-{id}.html", name="edit", methods={"GET", "POST"})
         */
        public function edit(Request $request, SettingService $settingService){
            $id = $request->get('id');
            $servers = $settingService->getSetting('security.ldap.connections');

            if(array_key_exists($id, $servers)){
                $server = new SettingLdapConnection($servers[$id]);
                $form = $this->createForm(EditServerForm::class, $server);
                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid()){
                    $servers[$id] = [
                        'ip'            => $server->getIp(),
                        'encryption'    => $server->getEncryption()
                    ];
                    $settingService->setSetting('security.ldap.connections', $servers);
                    return $this->redirectToRoute('adminoffice.authentication.ldap.index');
                }

                return $this->render("AdminOffice/Authentication/LDAP/edit.html.twig", [
                    'form'      => $form->createView(),
                    'server'    => $server
                ]);
            } else {
                throw new NotFoundHttpException();
            }
        }

        /**
         * @Route("/delete-{id}.html", name="delete", methods={"GET"})
         */
        public function delete(Request $request, SettingService $settingService){
            $id = $request->get('id');
            $servers = $settingService->getSetting('security.ldap.connections');

            if(array_key_exists($id, $servers)){
                if($this->isCsrfTokenValid('delete-'. $id, $request->get('_token'))){
                    unset($servers[$id]);
                    $settingService->setSetting('security.ldap.connections', $servers);
                    return $this->redirectToRoute('adminoffice.authentication.ldap.index');
                }
            } else {
                throw new NotFoundHttpException();
            }
        }

        /**
         * @Route("/test.html", name="test", methods={"GET", "POST"})
         */
        public function test(LDAPService $LDAPService, Request $request, SettingService $settingService){
            $form = $this->createFormBuilder()->add('search', TextType::class)->add('Submit', SubmitType::class)->getForm();
            $form->handleRequest($request);

            $results = [];

            if($form->isSubmitted() && $form->isValid()){
                $servers = $LDAPService->getServers();
                foreach ($servers as $s){
                    if($LDAPService->testConnection($s)){
                        $results[] = [
                            'type'      => 'success',
                            'message'   => 'Successful connection to ldap <b>'. $s .'</b> server'
                        ];

                        $search = $LDAPService->searchUser($form->getData()['search']);
                        if($search !== false){
                            $results[] = [
                                'type'      => 'success',
                                'message'   => 'User <b>'. $form->getData()['search'] .'</b> was found on the LDAP server.<br/>The user\'s DN is: <b>'. $search .'</b>'
                            ];
                        } else {
                            $results[] = [
                                'type'      => 'error',
                                'message'   => 'The user was not found on the LDAP server'
                            ];
                        }
                    } else {
                        $results[] = [
                            'type'      => 'error',
                            'message'   => 'Failure connection to ldap <b>'. $s .'</b> server'
                        ];
                    }
                }
            }

            return $this->render("AdminOffice/Authentication/LDAP/test.html.twig", [
                'form'      => $form->createView(),
                'results'   => $results
            ]);
        }

        /**
         * @Route("/mapping.html", name="mapping", methods={"GET", "POST"})
         */
        public function mapping(){
            return $this->render("AdminOffice/Authentication/LDAP/mapping.html.twig");
        }

    }