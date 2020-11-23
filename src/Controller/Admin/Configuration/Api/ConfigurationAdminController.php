<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Controller\Admin\Configuration\Api;


use App\Entity\Option;
use App\Repository\OptionRepository;
use App\Service\Admin\Api\ConfigurationFormBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Form\FormBuilderInterface;
use App\Helper\OptionNamePrefixHelper;
use Symfony\Component\Routing\RouterInterface;

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
    public function __construct(ConfigurationFormBuilderInterface $configurationFormBuilder, EntityManagerInterface $em)
    {
        $this->configurationFormBuilder = $configurationFormBuilder;
        $this->optionRepository         = $em->getRepository(Option::class);
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


        // Populate form with existing values
        $optionPrefix = OptionNamePrefixHelper::resolveFromAdmin($this->admin);
        $formBuilder  = $this->configurationFormBuilder->getFormBuilder();
        /**
         * @var $route FormBuilderInterface
         */
        foreach ($formBuilder as $area) {
            foreach ($area as $path) {
                foreach ($path as $route) {
                    $option = $this->optionRepository->get($optionPrefix . $route->getName());
                    if ($option) {
                        $route->setData($option->getValue());
                    }
                }
            }
        }
        $form = $formBuilder->getForm();

        $form->handleRequest();
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->getData() as $area => $paths) {
                foreach ($paths as $path => $routes) {
                    foreach ($routes as $route => $value) {
                        $this->optionRepository->set($optionPrefix . $route, $value);
                    }
                }
            }
        }

        return $this->renderWithExtraParams('admin/configuration/api/configure.html.twig', [
            'form' => $form->createView(),
            'action' => 'configure',
            'object' => $this->admin->getSubject()
        ]);
    }
}