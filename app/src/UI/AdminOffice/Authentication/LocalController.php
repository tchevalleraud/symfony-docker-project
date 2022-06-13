<?php
    namespace App\UI\AdminOffice\Authentication;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("authentication/local", name="authentication.local.")
     */
    class LocalController extends AbstractController {

        /**
         * @Route(".html", name="index", methods={"GET"})
         */
        public function index(){
            return $this->render("AdminOffice/Authentication/Local/index.html.twig");
        }

        /**
         * @Route("/configuration.html", name="configuration", methods={"GET", "POST"})
         */
        public function configuration(Request $request){
            return $this->render("AdminOffice/Authentication/Local/configuration.html.twig");
        }

    }