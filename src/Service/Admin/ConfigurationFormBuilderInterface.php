<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Service\Admin;


use Symfony\Component\Form\FormBuilderInterface;

interface ConfigurationFormBuilderInterface
{
    /**
     * Builds and returns the configuration form builder.
     * @return FormBuilderInterface
     */
    public function getFormBuilder();
}