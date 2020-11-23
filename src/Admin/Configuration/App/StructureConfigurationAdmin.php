<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Admin\Configuration\App;

use App\Entity\Family;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class StructureConfigurationAdmin extends AbstractAdmin
{

    protected $classnameLabel = 'Structures';

    protected $baseRoutePattern = 'config/app/structure';
    protected $baseRouteName = 'config_app_structure';

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('configure', 'configure');
        $collection->clearExcept(['list', 'configure']);
    }

    public function getAccessMapping()
    {
        $this->accessMapping['configure'] = 'EDIT';

        return $this->accessMapping;
    }

    public function toString($object)
    {
        return $object instanceof Family
            ? $this->trans('Structure') . ' #' . $object->getCode()
            : $this->trans('Structure'); // shown in the breadcrumb on id=null views
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->add('_action', null, [
                'actions' => [
                    'configure' => [
                        'template' => 'admin/CRUD/list__action_configure.html.twig'
                    ]
                ]
            ])
            ->addIdentifier('id')
            ->add('code')
            ->add('label');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('code')
                       ->add('label');
    }
}