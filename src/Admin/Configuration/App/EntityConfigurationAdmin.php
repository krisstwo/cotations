<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Admin\Configuration\App;

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

    protected $baseRoutePattern = 'config/app/entity';
    protected $baseRouteName = 'config_app_entity';

    public function getAccessMapping()
    {
        $this->accessMapping['configure'] = 'EDIT';

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

        // Do not use clear as it clears child routes also, child routes are handled by parent router ... 1 day spent here !
        $collection->remove('batch');
        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('delete');
        $collection->remove('show');
        $collection->remove('export');
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

    protected function configureTabMenu(ItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if ( ! $childAdmin && ! in_array($action, ['configure'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id    = $admin->getRequest()->get('id');

        if ($this->isGranted('LIST')) {
            $menu->addChild('Configure Families', [
                'uri' => $admin->generateUrl(FamilyConfigurationAdmin::class . '.list', ['id' => $id])
            ]);
            $menu->addChild('Configure Structures', [
                'uri' => $admin->generateUrl(StructureConfigurationAdmin::class . '.list', ['id' => $id])
            ]);
        }
    }
}