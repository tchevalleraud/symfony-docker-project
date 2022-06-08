<?php
    namespace App\UI\AdminOffice\Authentication;

    use App\Application\Services\SettingService;
    use App\Domain\_local\System\Forms\SettingLdap;
    use App\Infrastructure\Forms\AdminOffice\Authentication\LDAP\EditForm;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("authentication/ldap", name="authentication.ldap.")
     */
    class LDAPController extends AbstractController {

        /**
         * @Route(".html", name="index", methods={"GET"})
         */
        public function index(Request $request, SettingService $settingService){
            $settingLdap = new SettingLdap($settingService);
            $connections = $settingService->getSetting('security.ldap.connections');

            $form = $this->createForm(EditForm::class, $settingLdap);
            $form->handleRequest($request);

            return $this->render("AdminOffice/Authentication/LDAP/index.html.twig", [
                'connections'   => $connections,
                'form'          => $form->createView()
            ]);
        }

    }