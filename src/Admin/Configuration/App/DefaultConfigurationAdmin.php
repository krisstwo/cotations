<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Admin\Configuration\App;

use \Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class DefaultConfigurationAdmin extends AbstractAdmin
{

    protected $classnameLabel = 'Default configuration';

    protected $baseRoutePattern = 'config/app/default';
    protected $baseRouteName = 'config_app_default';

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('configure', 'configure');
        $collection->clearExcept(['configure']);
    }

    public function toString($object)
    {
        return $this->trans('Default');
    }

    public function getAccessMapping()
    {
        $this->accessMapping['configure'] = 'EDIT';

        return $this->accessMapping;
    }
}