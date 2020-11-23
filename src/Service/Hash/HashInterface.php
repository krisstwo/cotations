<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Service\Hash;


/**
 * See https://gitlab.com/cleonet-solutions/point-s/lease19/blob/master/src/src/Service/Hashing.php
 * @package App\Service
 */
interface HashInterface
{
    /**
     * @param string $hash
     * @param array|string $data
     *
     * @return bool
     */
    public function verifyHash($hash, $data): bool;

    /**
     * @param array|string $data
     *
     * @return string
     */
    public function hash($data): string;
}