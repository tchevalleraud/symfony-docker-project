<?php
    namespace App\UI\AdminOffice\Authentication\Provider;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("authentication/provider/ldap", "authentication.provider.ldap.")
     */
    class LDAPController extends AbstractController {

        /**
         * @Route(".html", name="index", methods={"GET"})
         */
        public function index(){
            return $this->render("AdminOffice/Authentication/Provider/LDAP/index.html.twig");
        }

    }