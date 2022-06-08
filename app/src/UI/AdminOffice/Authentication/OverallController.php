<?php
    namespace App\UI\AdminOffice\Authentication;

    use App\Application\Services\SettingService;
    use App\Domain\_local\System\Forms\SettingGlobal;
    use App\Infrastructure\Forms\AdminOffice\Authentication\Globals\EditForm;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("authentication/overall", name="authentication.overall.")
     */
    class OverallController extends AbstractController {

        /**
         * @Route(".html", name="index", methods={"GET", "POST"})
         */
        public function index(Request $request, SettingService $settingService){
            $settingGlobal = new SettingGlobal($settingService);
            $form = $this->createForm(EditForm::class, $settingGlobal);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $settingService->setSeeting('security.session.idle', $settingGlobal->getSecuritySessionIdle());

                return $this->redirectToRoute('adminoffice.authentication.overall.index');
            }

            return $this->render("AdminOffice/Authentication/Overall/index.html.twig", [
                'form' => $form->createView()
            ]);
        }

    }