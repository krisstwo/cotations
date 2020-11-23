<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Service\Admin\Api;


use App\Form\Type\EnabledType;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validation;

/**
 * Inspired from vendor/nelmio/api-doc-bundle/Controller/SwaggerUiController.php
 *
 * Class RoutesConfigurationFormBuilder
 * @package App\Service\Admin\Api
 */
class RoutesConfigurationFormBuilder implements ConfigurationFormBuilderInterface
{
    /**
     * @var ContainerInterface
     */
    private $apiDocGeneratorLocator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * RoutesConfigurationFormBuilder constructor.
     *
     * @param ContainerInterface $apiDocGeneratorLocator
     * @param RouterInterface $router
     */
    public function __construct(ContainerInterface $apiDocGeneratorLocator, RouterInterface $router)
    {
        $this->apiDocGeneratorLocator = $apiDocGeneratorLocator;
        $this->router                 = $router;
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
        $areas       = ['default', 'internal'];

        foreach ($areas as $area) {
            $formBuilder->add($area, FormType::class, [
                'label' => ucfirst($area),
                'required' => false
            ]);

            // Get api specs from NelmioApiDocBundle service and group them by tags
            $apiSpec = $this->apiDocGeneratorLocator->get($area)->generate()->toArray();

            foreach ($apiSpec['paths'] as $path => $pathInfo) {
                $formBuilder->get($area)->add($this->getFormNameFromPath($path), FormType::class, [
                    'label' => $path,
                    'required' => false
                ]);
                foreach ($pathInfo as $method => $methodInfo) {
                    // Route matching needs HTTP method, so need to do it here
                    $context = new RequestContext('/', strtoupper($method));
                    $matcher = new UrlMatcher($this->router->getRouteCollection(), $context);
                    $routeMatch   = $matcher->match($path);
                    if (empty($routeMatch)) {
                        continue;
                    }

                    $formBuilder->get($area)->get($this->getFormNameFromPath($path))->add($routeMatch['_route'], EnabledType::class, [
                        'required' => false,
                        'label' => strtoupper($method),
                        'help' => $methodInfo['summary'],
                        'placeholder' => 'Default',
                        'translation_domain' => 'admin'
                    ]);
                }

            }
        }

        return $formBuilder;
    }

    private function getFormNameFromPath($path)
    {
        return str_replace(['/', '{', '}'], '_', $path);
    }
}