<?php

/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Controller\Api;

use App\Service\OptionsResolver;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSAnnotations;
use FOS\RestBundle\View\View;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;

class FormController extends AbstractFOSRestController
{

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * SearchController constructor.
     *
     * @param OptionsResolver $optionsResolver
     */
    public function __construct(
        OptionsResolver $optionsResolver
    ) {
        $this->optionsResolver      = $optionsResolver;
    }


    /**
     * Get settings by context.
     *
     * @FOSAnnotations\Get("/settings")
     *
     * @SWG\Tag(name="Form")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Object of resolved settings.",
     *     @SWG\Schema(
     *         type="object",
     *         example="{'ui-show-logo': '1', 'ui-show-search-summary-bloc': '0', 'ui-show-search-details-bloc': null}"
     *     )
     * )
     * @SWG\Parameter(
     *     name="family",
     *     in="query",
     *     type="string",
     *     required=false,
     *     description="Family code"
     * )
     * @SWG\Parameter(
     *     name="structure",
     *     in="query",
     *     type="string",
     *     required=false,
     *     description="Structure code"
     * )
     *
     * @Security(name="bearer")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function settingsAction(Request $request)
    {
        /**
         * @var $entity \App\Entity\Entity
         */
        $entity = $this->getUser();

        $options = $this->optionsResolver->resolve([], [
            'entity' => $entity->getCode(),
            'family' => $request->get('family'),
            'structure' => $request->get('structure')
        ]);

        $view = View::create()->setData($options);


        return $this->getViewHandler()->handle($view);
    }
}
