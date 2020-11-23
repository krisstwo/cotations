<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Entity\Search;

use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

class Stock
{
    /**
     * @var string
     * @Groups({"stock_read", "elastica"})
     * @SWG\Property(example="001979897")
     */
    public $id;

    /**
     * @var string
     * @Groups({"stock_read", "elastica"})
     * @SWG\Property(type="integer", example="10")
     */
    public $A;

    /**
     * @var string
     * @Groups({"stock_read", "elastica"})
     * @SWG\Property(type="integer", example="10")
     */
    public $B;

    /**
     * @var string
     * @Groups({"stock_read", "elastica"})
     * @SWG\Property(type="integer", example="10")
     */
    public $C;

    /**
     * @var string
     * @Groups({"stock_read", "elastica"})
     * @SWG\Property(type="integer", example="10")
     */
    public $total;


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}