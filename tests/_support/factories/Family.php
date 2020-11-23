<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

use App\Entity\Family as Family;
use League\FactoryMuffin\Faker\Facade as Faker;

$fm->define(Family::class)->setDefinitions([
    'code' => Faker::word(),
    'label' => Faker::word()
]);