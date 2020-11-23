<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Service\Admin;


use Cocur\Slugify\Slugify;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

class YamlConfigurationFormBuilder implements ConfigurationFormBuilderInterface
{

    protected $configurationKeys = [];

    public function __construct($configurationKeys = [])
    {
        $this->configurationKeys = $configurationKeys;
    }

    /**
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {

        $validator          = Validation::createValidator();
        $ValidatorExtension = new ValidatorExtension($validator);

        $formFactory = Forms::createFormFactoryBuilder()->addExtension($ValidatorExtension)->getFormFactory();
        $formBuilder = $formFactory->createBuilder(FormType::class);

        foreach ($this->configurationKeys as $groupLabel => $groupConfig) {
            $slugify = new Slugify();
            $groupId = $slugify->slugify($groupLabel);
            $formBuilder->add($groupId, FormType::class, [
                'label' => $groupLabel,
                'translation_domain' => 'admin',
                'required' => false
            ]);

            foreach ($groupConfig as $key => $keyConfig) {
                $typeClass = class_exists($keyConfig['type']) ? $keyConfig['type'] : null;
                $typeClass = ( ! $typeClass && class_exists('App\Form\Type\\' . $keyConfig['type'])) ? ('App\Form\Type\\' . $keyConfig['type']) : $typeClass;
                $typeClass = ( ! $typeClass && class_exists('Symfony\Component\Form\Extension\Core\Type\\' . $keyConfig['type'])) ? ('Symfony\Component\Form\Extension\Core\Type\\' . $keyConfig['type']) : $typeClass;

                if ($typeClass) {
                    $formBuilder->get($groupId)->add($key, $typeClass, [
                        'label' => $keyConfig['label'],
                        'help' => $keyConfig['description'],
                        'required' => false,
                        'placeholder' => 'Default',
                        'translation_domain' => 'admin'
                    ]);
                }
            }
        }

        return $formBuilder;
    }
}