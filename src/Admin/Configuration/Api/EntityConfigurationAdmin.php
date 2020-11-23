<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Admin\Configuration\Api;

use App\Entity\Entity;
use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class EntityConfigurationAdmin extends AbstractAdmin
{

    protected $classnameLabel = 'Entities';

    protected $baseRoutePattern = 'config/api/entity';
    protected $baseRouteName = 'config_api_entity';

    public function getAccessMapping()
    {
        $this->accessMapping['configure']        = 'EDIT';
        $this->accessMapping['regenerateSecret'] = 'EDIT';
        $this->accessMapping['generateWebUrl'] = 'EDIT';

        return $this->accessMapping;
    }

    public function toString($object)
    {
        return $object instanceof Entity
            ? $this->trans('Entity') . ' #' . $object->getCode()
            : $this->trans('Entity'); // shown in the breadcrumb on id=null views
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('configure', 'configure');
        $collection->add('regenerateSecret', 'regenerateSecret');
        $collection->add('generateWebUrl', 'generateWebUrl');

        $collection->clearExcept(['list', 'configure', 'regenerateSecret', 'generateWebUrl', 'batch']);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->add('_action', null, [
                'actions' => [
                    'configure' => [
                        'template' => 'admin/CRUD/list__action_configure.html.twig'
                    ],
                    'regenerateSecret' => [
                        'template' => 'admin/CRUD/list__action_regenerateSecret.html.twig'
                    ],
                    'generateWebUrl' => [
                        'template' => 'admin/CRUD/list__action_generateWebUrl.html.twig'
                    ],
                ]
            ])
            ->addIdentifier('id')
            ->add('code')
            ->add('label')
            ->add('secret');
    }

    protected function configureBatchActions($actions)
    {
        if ($this->hasAccess('edit')) {
            $actions['regenerateSecret'] = [
                'ask_confirmation' => true
            ];
        }

        return $actions;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('code')
                       ->add('label')
                       ->add('secret');
    }
}