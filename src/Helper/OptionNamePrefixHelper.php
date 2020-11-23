<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Helper;


use App\Admin\Configuration\App\DefaultConfigurationAdmin;
use App\Admin\Configuration\App\EntityConfigurationAdmin;
use App\Admin\Configuration\App\FamilyConfigurationAdmin;
use App\Admin\Configuration\App\StructureConfigurationAdmin;
use App\Admin\Configuration\Api\EntityConfigurationAdmin as ApiEntityConfigurationAdmin;
use App\Entity\Entity;
use App\Entity\Family;
use App\Entity\Structure;
use Sonata\AdminBundle\Admin\AdminInterface;

class OptionNamePrefixHelper
{
    /**
     * @param AdminInterface $admin
     *
     * @return string
     * @throws \Exception
     */
    public static function resolveFromAdmin(AdminInterface $admin)
    {
        switch (get_class($admin)) {
            case DefaultConfigurationAdmin::class:
                return 'config_default_';
                break;
            case FamilyConfigurationAdmin::class:
                /**
                 * @var $entity Family
                 */
                $entity = $admin->getSubject();

                if ($admin->isChild() && get_class($admin->getParent()) === EntityConfigurationAdmin::class) {
                    /**
                     * @var $parentEntity Entity
                     */
                    $parentEntity = $admin->getParent()->getSubject();

                    return sprintf('config_entity_%s_family_%s_', $parentEntity->getCode(), $entity->getCode());
                } else {
                    return sprintf('config_family_%s_', $entity->getCode());
                }
                break;
            case StructureConfigurationAdmin::class:
                /**
                 * @var $entity Structure
                 */
                $entity = $admin->getSubject();

                if ($admin->isChild() && get_class($admin->getParent()) === EntityConfigurationAdmin::class) {
                    /**
                     * @var $parentEntity Entity
                     */
                    $parentEntity = $admin->getParent()->getSubject();

                    return sprintf('config_entity_%s_structure_%s_', $parentEntity->getCode(), $entity->getCode());
                } else {
                    return sprintf('config_structure_%s_', $entity->getCode());
                }
                break;
            case EntityConfigurationAdmin::class:
                /**
                 * @var $entity Entity
                 */
                $entity = $admin->getSubject();

                return sprintf('config_entity_%s_', $entity->getCode());
                break;
            case ApiEntityConfigurationAdmin::class:
                /**
                 * @var $entity Entity
                 */
                $entity = $admin->getSubject();

                return sprintf('api_entity_%s_', $entity->getCode());
                break;
            default:
                throw new \Exception(sprintf('Could not resolve option name prefix from admin class: "%s", throwing exception to prevent accidental overwrite of options',
                    get_class($admin)));
                break;
        }
    }

    public static function resolveFromContext(array $context = [])
    {
        if (empty($context)) {
            return 'config_default_';
        }

        if ( ! empty($context['entity'])) {
            if (empty($context['family']) && empty($context['structure'])) {
                return sprintf('config_entity_%s_', $context['entity']);
            } elseif (empty($context['structure'])) {
                return sprintf('config_entity_%s_family_%s_', $context['entity'], $context['family']);
            } elseif (empty($context['family'])) {
                return sprintf('config_entity_%s_structure_%s_', $context['entity'], $context['structure']);
            }
        }

        if ( ! empty($context['family'])) {
            return sprintf('config_family_%s_', $context['family']);
        }

        if ( ! empty($context['structure'])) {
            return sprintf('config_structure_%s_', $context['structure']);
        }
    }
}