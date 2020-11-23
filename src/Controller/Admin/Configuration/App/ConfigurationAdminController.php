<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Controller\Admin\Configuration\App;


use App\Entity\Option;
use App\Repository\OptionRepository;
use App\Service\Admin\ConfigurationFormBuilderInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Form\FormBuilderInterface;
use App\Helper\OptionNamePrefixHelper;
use App\Admin\Configuration\App\DefaultConfigurationAdmin;

class ConfigurationAdminController extends CRUDController
{
    /**
     * @var ConfigurationFormBuilderInterface
     */
    private $configurationFormBuilder;

    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * ConfigurationAdminController constructor.
     *
     * @param ConfigurationFormBuilderInterface $configurationFormBuilder
     */
    public function __construct(ConfigurationFormBuilderInterface $configurationFormBuilder)
    {
        $this->configurationFormBuilder = $configurationFormBuilder;
    }


    public function configureAction()
    {
        $this->admin->checkAccess('configure');

        // Setting subject object to admin
        if ( ! empty($this->admin->getClass())) {
            $id             = $this->getRequest()->get($this->admin->getIdParameter());
            $existingObject = $this->admin->getObject($id);
            $this->admin->setSubject($existingObject);
        }

        $this->optionRepository = $this->getDoctrine()->getRepository(Option::class);

        $optionPrefix = OptionNamePrefixHelper::resolveFromAdmin($this->admin);
        $formBuilder  = $this->configurationFormBuilder->getFormBuilder();
        /**
         * @var $group FormBuilderInterface
         */
        foreach ($formBuilder as $group) {
            /**
             * @var $child FormBuilderInterface
             */
            foreach ($group as $child) {
                // Remove placeholder option if default config admin (no default-default config possible)
                if (get_class($this->admin) === DefaultConfigurationAdmin::class) {
                    $fieldType = get_class($child->getType()->getInnerType());
                    $fieldOptions = $child->getFormConfig()->getOptions();
                    unset($fieldOptions['placeholder']);
                    $fieldOptions['required'] = true;
                    $group->add($child->getName(), $fieldType, $fieldOptions);

                // Reload child for subsequent calls
                $child = $group->get($child->getName());

                // Set default field data if found
                $option = $this->optionRepository->get($optionPrefix . $child->getName());
                if ($option && $option->getValue() !== null) {
                    $child->setData($option->getValue());
                    } else {
                        $child->setData(1);
                    }
                }else {
                    // Set default field data if found
                    $option = $this->optionRepository->get($optionPrefix . $child->getName());
                    if ($option) {
                        $child->setData($option->getValue());
                    }
                }
            }
        }
        $form = $formBuilder->getForm();

        $form->handleRequest();
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->getData() as $group => $data) {
                foreach ($data as $key => $value) {
                    $this->optionRepository->set($optionPrefix . $key, $value);
                }
            }
        }

        return $this->renderWithExtraParams('admin/configuration/app/default-configuration/configure.html.twig', [
            'form' => $form->createView(),
            'action' => 'configure',
            'object' => $this->admin->getSubject()
        ]);
    }
}