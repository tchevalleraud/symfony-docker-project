<?php
    namespace App\Domain\_local\System\Forms;

    use App\Application\Services\SettingService;

    class SettingLdap {

        private $security_ldap_connections;

        public function __construct(SettingService $settingService){
        }

        public function getSecurityLdapConnections(){
            return $this->security_ldap_connections;
        }

    }