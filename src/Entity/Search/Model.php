<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Entity\Search;

use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

class Model
{
    /**
     * @var string
     * @Groups({"model_read", "model_easyprice_read", "elastica"})
     * @SWG\Property(example="001979897")
     */
    public $id;

    /**
     * @var string
     * @Groups({"model_read", "model_easyprice_read", "elastica"})
     * @SWG\Property(example="Ordinateurs portables DELL Latitude E6420 Intel Core i7 4 Go 256 Go 14'' Noir, Argent")
     */
    public $lib_model;

    /**
     * @var string
     * @Groups({"elastica"})
     */
    public $lib_model_noindex;

    /**
     * @var string
     * @Groups({"elastica"})
     */
    public $lib_model_nGram;

    /**
     * @var string
     * @Groups({"elastica"})
     */
    public $lib_model_edgeNGram;

    /**
     * @var string
     * @Groups({"model_read", "elastica"})
     * @SWG\Property(example="DELL")
     */
    public $lib_marque;

    /**
     * @var int
     * @Groups({"model_read", "elastica"})
     * @SWG\Property(example=188)
     */
    public $id_structure;

    /**
     * @var string
     * @Groups({"model_read", "elastica"})
     * @SWG\Property(example="Ordinateurs portables")
     */
    public $lib_structure;

    /**
     * @var string
     * @Groups({"model_read", "elastica"})
     * @SWG\Property(example="ipor")
     */
    public $id_sfam;

    /**
     * @var string
     * @Groups({"model_read", "elastica"})
     * @SWG\Property(example="PC portables")
     */
    public $lib_sfam;

    /**
     * @var string
     * @Groups({"elastica"})
     */
    public $nbjep;

    /**
     * @var string
     * @Groups({"elastica"})
     */
    public $nbpv_min;

    /**
     * @var string
     * @Groups({"model_read", "model_easyprice_read", "elastica"})
     * @SWG\Property(example="2019-09-10")
     */
    public $tstamp;

    /**
     * @var string
     * @Groups({"model_read", "model_easyprice_read", "elastica"})
     * @SWG\Property(example="3700868410795")
     */
    public $code_barre;

    /**
     * @var string
     * @Groups({"elastica"})
     */
    public $publication;

    /**
     * @var string
     * @Groups({"model_read", "elastica"})
     * @SWG\Property(example="http://cdn.easycash.fr/img/prod/0/0/1/9/7/9/8/9/7/prod_78x56/32787583_4858.jpg")
     */
    public $vignette;

    /**
     * @var string
     * @Groups({"model_read", "elastica"})
     * @SWG\Property(example="http://cdn.easycash.fr/img/prod/0/0/1/9/7/9/8/9/7/prod_900x650/32787583_4858.jpg")
     */
    public $image;

    /**
     * @var \stdClass
     * @Groups({"model_read", "elastica"})
     * @SWG\Property(type="object", example="{ 'Gamme de processeur': 'Intel Core i7', 'Capacité HDD': '256', 'Nom modèle': 'Latitude E6420', 'Mémoire vive': '4', 'Système exploit_': 'Windows 7 Home Premium', 'Capacité SSD': '0', 'Écran tactile': 'Non', 'Taille d'écran': '14' }")
     */
    public $champs_vitrine;

    /**
     * @var \stdClass
     * @Groups({"model_read", "elastica"})
     * @SWG\Property(type="object", example="{ 'Gamme de processeur': 'Intel Core i7', 'Capacité HDD': '256', 'Nom modèle': 'Latitude E6420', 'Mémoire vive': '4', 'Système exploit_': 'Windows 7 Home Premium', 'Capacité SSD': '0', 'Écran tactile': 'Non', 'Taille d'écran': '14' }")
     */
    public $champs_princ;

    /**
     * @var \stdClass
     * @Groups({"model_read", "elastica"})
     * @SWG\Property(type="object", example="{ 'Gamme de processeur': 'Intel Core i7', 'Capacité HDD': '256', 'Nom modèle': 'Latitude E6420', 'Mémoire vive': '4', 'Système exploit_': 'Windows 7 Home Premium', 'Capacité SSD': '0', 'Écran tactile': 'Non', 'Taille d'écran': '14' }")
     */
    public $champs_ep_attribut;

    /**
     * @var \stdClass
     * @Groups({"model_easyprice_read", "elastica"})
     * @SWG\Property(
     *     type="object",
     *
     *     @SWG\Property(property="date", type="string", example="2019-12-12"),
     *     @SWG\Property(property="apa", type="string", example="7.7"),
     *     @SWG\Property(property="apa_max", type="string", example="7.7"),
     *     @SWG\Property(property="apv", type="string", example="null"),
     *     @SWG\Property(property="apv_max", type="string", example="null"),
     *     @SWG\Property(property="apweb", type="string", example="null"),
     *     @SWG\Property(property="bpa", type="string", example="7"),
     *     @SWG\Property(property="bpa_max", type="string", example="7"),
     *     @SWG\Property(property="bpv", type="string", example="11.99"),
     *     @SWG\Property(property="bpv_max", type="string", example="11.99"),
     *     @SWG\Property(property="bpweb", type="string", example="11.99"),
     *     @SWG\Property(property="cpa", type="string", example="6.3"),
     *     @SWG\Property(property="cpa_max", type="string", example="6.3"),
     *     @SWG\Property(property="cpv", type="string", example="null"),
     *     @SWG\Property(property="cpv_max", type="string", example="null"),
     *     @SWG\Property(property="cpweb", type="string", example="null"),
     *     @SWG\Property(property="source_apa", type="string", example="EP"),
     *     @SWG\Property(property="source_bpa", type="string", example="EP"),
     *     @SWG\Property(property="source_cpa", type="string", example="EP"),
     *     @SWG\Property(property="source_apv", type="string", example="null"),
     *     @SWG\Property(property="source_bpv", type="string", example="EP"),
     *     @SWG\Property(property="source_cpv", type="string", example="null"),
     * )
     */
    public $easyprice;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}