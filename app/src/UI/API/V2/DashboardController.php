<?php
    namespace App\UI\API\V2;

    use App\UI\API\APIExtendController;
    use OpenApi\Annotations as OA;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("", name="dashboard.")
     */
    class DashboardController extends APIExtendController {

        /**
         * @Route("/", name="index", methods={"GET"})
         * @OA\Get(
         *     path="/",
         *     @OA\Response(
         *         response=200,
         *         description="200 - OK"
         *     )
         * )
         */
        public function index(){
            return new JsonResponse([]);
        }

    }