<?php
    namespace App\Domain\_local\System\Forms;

    use App\Application\Services\SettingService;

    class SettingLdap {

        private $security_ldap_authentication_username;
        private $security_ldap_authentication_password;

        public function __construct(SettingService $settingService){
            $this->setSecurityLdapAuthenticationUsername($settingService->getSetting('security.ldap.authentication.username'));
            $this->setSecurityLdapAuthenticationPassword($settingService->getSetting('security.ldap.authentication.password'));
        }

        public function getSecurityLdapAuthenticationUsername(){
            return $this->security_ldap_authentication_username;
        }

        public function setSecurityLdapAuthenticationUsername($security_ldap_authentication_username): self {
            $this->security_ldap_authentication_username = $security_ldap_authentication_username;
            return $this;
        }

        public function getSecurityLdapAuthenticationPassword(){
            return $this->security_ldap_authentication_password;
        }

        public function setSecurityLdapAuthenticationPassword($security_ldap_authentication_password): self {
            $this->security_ldap_authentication_password = $security_ldap_authentication_password;
            return $this;
        }

    }