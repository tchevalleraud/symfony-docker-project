<?php
    namespace App\Application\Services;

    use App\Domain\_local\System\Entity\Setting;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\HttpKernel\KernelInterface;

    class SettingService {

        private EntityManagerInterface $entityManager;
        private KernelInterface $kernel;

        public function __construct(KernelInterface $kernel){
            $this->kernel = $kernel;

            $this->entityManager = $this->kernel->getContainer()->get('doctrine')->getManager('local');
        }

        public function getSetting($key){
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy(['setting' => $key]);
            if($setting->getType() == "boolean"){
                if($setting->getValue() == "1") return true;
                else return false;
            } elseif($setting->getType() == "array"){
                return json_decode($setting->getValue());
            } elseif($setting->getType() == "string"){
                if($setting->getValue() == "NULL") return null;
                else return $setting->getValue();
            } elseif($setting->getType() == "integer"){
                if($setting->getValue() == "NULL") return null;
                else return (int)$setting->getValue();
            }
            dump($setting);
            die();
            throw new \Exception("Erreur");
        }

        public function setSeeting($key, $value, $type = null){
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy(['setting' => $key]);
            if($setting){
                if($type !== null) $setting->setType($type);

                if($setting->getType() == "boolean"){
                    die('boolean');
                } elseif($setting->getType() == "integer"){
                    if($value == null) $setting->setValue('NULL');
                    else $setting->setValue($value);
                }
            }

            $this->entityManager->persist($setting);
            $this->entityManager->flush();
        }

    }