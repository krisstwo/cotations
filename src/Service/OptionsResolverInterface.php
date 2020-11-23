<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Service;


interface OptionsResolverInterface
{
    /**
     * Resolve options set based on a passed context
     * @param array $options
     * @param array $context
     *
     * @return mixed
     */
    public function resolve(array $options = [], array $context = []);
}