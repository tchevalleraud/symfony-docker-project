<?php
    namespace App\Infrastructure\Forms\AdminOffice\Authentication\LDAP;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\PasswordType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;

    class EditForm extends AbstractType {

        public function buildForm(FormBuilderInterface $builder, array $options) {
            parent::buildForm($builder, $options);
            $builder
                ->add('security_ldap_authentication_username', TextType::class)
                ->add('security_ldap_authentication_password', PasswordType::class, [
                    'always_empty'  => false
                ])
                ->add('Submit', SubmitType::class);
        }

    }