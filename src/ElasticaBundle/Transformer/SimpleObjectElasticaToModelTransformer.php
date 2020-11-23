<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\ElasticaBundle\Transformer;


use App\Entity\Search\Model;
use Elastica\Result;
use FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;
use Zend\Hydrator\ObjectPropertyHydrator;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper returns empty objects with mapped ids only
 */
class SimpleObjectElasticaToModelTransformer extends AbstractElasticaToModelTransformer
{

    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository.
     *
     * @param array $elasticaObjects of elastica objects
     *
     * @throws \RuntimeException
     *
     * @return array
     **/
    public function transform(array $elasticaObjects)
    {
        $objects = [];
        /**
         * @var $elasticaObject Result
         */
        foreach ($elasticaObjects as $elasticaObject) {
            $model = new Model();
            $model->id = $elasticaObject->getId();
            $hydrator = new ObjectPropertyHydrator();
            $hydrator->hydrate($elasticaObject->getSource(), $model);
            $objects[] = $model;
        }

        return $objects;
    }

    /**
     * Fetches objects by theses identifier values.
     *
     * @param array $identifierValues ids values
     * @param bool $hydrate whether or not to hydrate the objects, false returns arrays
     *
     * @return array of objects or arrays
     */
    protected function findByIdentifiers(array $identifierValues, $hydrate)
    {
        $objects = [];
        foreach ($identifierValues as $identifierValue) {
            $object = new Model();
            $object->id = $identifierValue;
            $objects[] = $object;
        }

        return $objects;
    }
}