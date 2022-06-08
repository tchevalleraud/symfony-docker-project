<?php
    namespace App\UI\AdminOffice\Authentication;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    }