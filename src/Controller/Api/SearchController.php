<?php

/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Controller\Api;

use App\Entity\Entity;
use App\Entity\Search\Model as SearchModel;
use App\Service\OptionsResolver;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\Terms;
use FOS\ElasticaBundle\Finder\FinderInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSAnnotations;
use FOS\RestBundle\View\View;
use GuzzleHttp\Exception\GuzzleException;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SearchController extends AbstractFOSRestController
{

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var FinderInterface
     */
    private $easyPriceIndexFinder;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * SearchController constructor.
     *
     * @param SerializerInterface $serializer
     * @param NormalizerInterface $normalizer
     * @param FinderInterface $easyPriceIndexFinder
     * @param OptionsResolver $optionsResolver
     */
    public function __construct(
        SerializerInterface $serializer,
        NormalizerInterface $normalizer,
        FinderInterface $easyPriceIndexFinder,
        OptionsResolver $optionsResolver
    ) {
        $this->serializer           = $serializer; // Serializer is unused but invocation needed for normalizer to work ?! ...
        $this->normalizer           = $normalizer;
        $this->easyPriceIndexFinder = $easyPriceIndexFinder;
        $this->optionsResolver      = $optionsResolver;
    }


    /**
     * Search for models by a needle.
     *
     * @FOSAnnotations\Get("/model-by-needle")
     *
     * @SWG\Tag(name="Search")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Array of matching models.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\Search\Model::class, groups={"model_read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="needle",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="Searchword / model id / model barcode."
     * )
     * @SWG\Parameter(
     *     name="exclude",
     *     in="query",
     *     type="string",
     *     required=false,
     *     description="Exclude Books, DVDs and Blu-Rays."
     * )
     *
     * @Security(name="bearer")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getModelByNeedleAction(Request $request)
    {
        $needle = $request->get('needle');
        $needle = strtolower(trim($needle));
        $exclude = $request->get('exclude');

        $boolQuery = new BoolQuery();

        // id / barcode / label search
        if (preg_match('/\d{9}/', $needle) && strlen($needle) == 9) {
            $fieldQuery = new Match();
            $fieldQuery->setFieldQuery('_id', $needle);
            $boolQuery->addMust($fieldQuery);
        } elseif (preg_match('/\d{6,}/', $needle)) {
            $fieldQuery = new Match();
            $fieldQuery->setFieldQuery('code_barre', $needle);
            $boolQuery->addMust($fieldQuery);
        } else {
            $labelParts = preg_split("/(\s+|,|.|\||:|')/", $needle);

            $partsQuery = new Terms();
            $partsQuery->setTerms('lib_model', $labelParts);
            $boolQuery->addShould($partsQuery);

            $fieldQuery = new Match();
            $fieldQuery->setFieldQuery('lib_model', $needle);
            $fieldQuery->setFieldBoost('lib_model', 2);
            $boolQuery->addShould($fieldQuery);

            $boolQuery->setMinimumShouldMatch(1);

            $fieldQuery = new Match();
            $fieldQuery->setFieldQuery('publication', 'FP');
            $boolQuery->addMust($fieldQuery);
        }

        // Default excludes
        $fieldQuery = new Match();
        $fieldQuery->setFieldQuery('id_sfam', 'bopi');
        $boolQuery->addMustNot($fieldQuery);

        $fieldQuery = new Match();
        $fieldQuery->setFieldQuery('id_sfam', 'bor');
        $boolQuery->addMustNot($fieldQuery);

        $fieldQuery = new Match();
        $fieldQuery->setFieldQuery('id_sfam', 'bfor');
        $boolQuery->addMustNot($fieldQuery);

        // Explicit excludes
        if ($exclude === 'true') {
            $fieldQuery = new Match();
            $fieldQuery->setFieldQuery('id_sfam', 'lliv');
            $boolQuery->addMustNot($fieldQuery);

            $fieldQuery = new Match();
            $fieldQuery->setFieldQuery('id_sfam', 'lbd');
            $boolQuery->addMustNot($fieldQuery);

            $fieldQuery = new Match();
            $fieldQuery->setFieldQuery('id_sfam', 'lman');
            $boolQuery->addMustNot($fieldQuery);

            $fieldQuery = new Match();
            $fieldQuery->setFieldQuery('id_sfam', 'advd');
            $boolQuery->addMustNot($fieldQuery);

            $fieldQuery = new Match();
            $fieldQuery->setFieldQuery('id_sfam', 'ablu');
            $boolQuery->addMustNot($fieldQuery);
        }

        //TODO: paginate results

        $data = $this->easyPriceIndexFinder->find($boolQuery);

        $view = View::create()->setData($data);
        $view->getContext()->setGroups(['model_read']);


        return $this->getViewHandler()->handle($view);
    }

    /**
     * Easyprice summary for model.
     *
     * @FOSAnnotations\Get("/summary-by-model")
     *
     * @SWG\Tag(name="Search")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Easyprice summary data for model.",
     *     @SWG\Schema(type="array",
     *          @SWG\Items(ref=@Model(type=App\Entity\Search\Model::class, groups={"model_easyprice_read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="model",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="Model id to summarize data for"
     * )
     *
     * @Security(name="bearer")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getSummaryByModelAction(Request $request)
    {
        $needle = $request->get('model');

        $boolQuery = new BoolQuery();
        $fieldQuery = new Match();
        $fieldQuery->setFieldQuery('_id', $needle);
        $boolQuery->addMust($fieldQuery);

        /**
         * @var $models SearchModel[]
         */
        $models = $this->easyPriceIndexFinder->find($boolQuery);

        // Use serializer to convert data objects to arrays
        $normalizedData = [];
        /**
         * @var $model SearchModel
         */
        foreach ($models as $model) {
            $normalizedData[] = $this->normalizer->normalize($model, null, ['groups' => 'model_easyprice_read']);
        }

        /**
         * @var $entity Entity
         */
        $entity = $this->getUser();

        // Load user config options for summary data
        $options = $this->optionsResolver->resolve([
            'search-summary-pa',
            'search-summary-pv'
        ], [
            'entity' => $entity->getCode(),
            'family' => count($models) ? $models[0]->id_sfam : (new SearchModel())->id_sfam,
            'structure' => count($models) ? $models[0]->id_structure : (new SearchModel())->id_structure
        ]);

        // Loop over data and drop data keys which have been disabled by user config
        foreach ($normalizedData as $index => $modelData) {
            if ($options['search-summary-pa'] === '0') {
                unset($normalizedData[$index]['easyprice']['apa']);
                unset($normalizedData[$index]['easyprice']['bpa']);
                unset($normalizedData[$index]['easyprice']['cpa']);

                unset($normalizedData[$index]['easyprice']['source_apa']);
                unset($normalizedData[$index]['easyprice']['source_bpa']);
                unset($normalizedData[$index]['easyprice']['source_cpa']);
            }

            if ($options['search-summary-pv'] === '0') {
                unset($normalizedData[$index]['easyprice']['apv']);
                unset($normalizedData[$index]['easyprice']['bpv']);
                unset($normalizedData[$index]['easyprice']['cpv']);

                unset($normalizedData[$index]['easyprice']['source_apv']);
                unset($normalizedData[$index]['easyprice']['source_bpv']);
                unset($normalizedData[$index]['easyprice']['source_cpv']);
            }
        }

        $view = View::create()->setData($normalizedData);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Get model stock data (of current entity).
     *
     * @FOSAnnotations\Get("/model/{model}/stock/")
     *
     * @SWG\Tag(name="Search")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Stock data (of current entity) for model.",
     *     @SWG\Schema(type="array",
     *          @SWG\Items(ref=@Model(type=App\Entity\Search\Stock::class, groups={"stock_read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="model",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="Model id"
     * )
     *
     * @Security(name="bearer")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getModelStockAction(Request $request)
    {
        /**
         * @var $entity \App\Entity\Entity
         */
        $entity = $this->getUser();
        $client = new \GuzzleHttp\Client();
        
        try {
            $response = $client->request(
                'GET',
                sprintf('%s/easy-price/model-stock/%s/entity/%s', $this->getParameter('INTRANET_API_BASE_URL'), $request->get('model'), $entity->getCode()),
                [
                    'headers' => ['Accept' => 'application/json', 'Content-type' => 'application/json'],
                    'timeout' => $this->getParameter('INTRANET_API_TIMEOUT')
                ]
            );
            
            $data = json_decode($response->getBody(), true);
            $data['id'] = $request->get('model');
            $view = View::create()->setData($data);
            
            return $this->getViewHandler()->handle($view);
        } catch (GuzzleException $e) {
            throw new ServiceUnavailableHttpException($e->getMessage());
        }
    }

    /**
     * Model listings details.
     *
     * @FOSAnnotations\Get("/details-by-model")
     *
     * @SWG\Tag(name="Search")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Model listings.",
     *     @SWG\Schema(type="array",
     *          @SWG\Items(type="object")
     *     )
     * )
     * @SWG\Parameter(
     *     name="model",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="Model id to search listings for"
     * )
     *
     * @Security(name="bearer")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDetailsByModelAction()
    {
        $view = View::create()->setData([]);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Search for listings for a given family.
     *
     * @FOSAnnotations\Get("/family")
     *
     * @SWG\Tag(name="Search")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Model listings.",
     *     @SWG\Schema(type="array",
     *          @SWG\Items(type="object")
     *     )
     * )
     * @SWG\Parameter(
     *     name="family",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="Family id to search listings for"
     * )
     *
     * @Security(name="bearer")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getFamilyAction()
    {
        $view = View::create()->setData([]);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Render a listings summary image.
     *
     * @FOSAnnotations\Get("/summary-snapshot")
     *
     * @SWG\Tag(name="Search")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Listings summary image information object.",
     *     @SWG\Schema(type="object",
     *         @SWG\Property(property="link", type="string")
     *      )
     * )
     * @SWG\Parameter(
     *     name="model",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="Model id to summarize data for"
     * )
     *
     * @Security(name="bearer")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSummarySnapshotAction()
    {
        $view = View::create()->setData(['link' => null]);

        return $this->getViewHandler()->handle($view);
    }
}
