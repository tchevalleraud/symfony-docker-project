<?php
    namespace App\Tests\PantherTestCase\FrontOffice;

    use App\Tests\PantherTestCase;

    class DashboardTest extends PantherTestCase {

        public function test_FR_Index(){
            $this->client->request('GET', '/');
        }

    }