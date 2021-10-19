<?php
    namespace App\Infrastructure\Profiler;

    use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class HelperProfiler extends AbstractDataCollector {

        public function collect(Request $request, Response $response, \Throwable $exception = null) {
        }

        public static function getTemplate(): ?string {
            return '_profiler/Helper/template.html.twig';
        }

    }