<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Service;


use App\Entity\Option;
use App\Helper\OptionNamePrefixHelper;
use App\Repository\OptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;

class OptionsResolver implements OptionsResolverInterface
{
    /**
     * @var SymfonyOptionsResolver
     */
    protected $optionResolver;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var array
     */
    protected $configurationKeys = [];


    /**
     * OptionsResolver constructor.
     */
    public function __construct(EntityManagerInterface $em, $configurationKeys = [])
    {
        $this->em             = $em;
        $this->optionResolver = new SymfonyOptionsResolver();
        $this->configurationKeys = $configurationKeys;
    }

    /**
     * Resolve options set based on a passed context
     *
     * @param array $options
     * @param array $context
     *
     * @return mixed
     */
    public function resolve(array $options = [], array $context = [])
    {
        /**
         * @var $optionsRepository OptionRepository
         */
        $optionsRepository = $this->em->getRepository(Option::class);
        $resolvedOptions = [];

        // Auto-fill options with all available if none provided
        if (empty($options)) {
            foreach ($this->configurationKeys as $groupLabel => $groupConfig) {
                foreach ($groupConfig as $key => $keyConfig) {
                    $options[] = $key;
                }
            }
        }

        // Fetch and populate default options
        foreach ($options as $optionId) {
            $option = $optionsRepository->get(OptionNamePrefixHelper::resolveFromContext() . str_replace('-', '_',
                    $optionId)); //TODO: find how forms transform field name to underscore and use it here

            if ($option && $option->getValue() !== null) {
                $this->optionResolver->setDefault($optionId, $option->getValue());
                $resolvedOptions[$optionId] = $option->getValue();
            }else {
                // Fallback value for options are "true" if not already saved from admin
                $this->optionResolver->setDefault($optionId, '1');
                $resolvedOptions[$optionId] = '1';
            }
        }

        // Merge context options gradually
        //TODO: can use some strategy pattern here, to extend context (new variable), merging order etc
        foreach ($options as $optionId) {
            if ( ! empty($context['entity'])) {
                $option = $optionsRepository->get(OptionNamePrefixHelper::resolveFromContext(['entity' => $context['entity']]) . str_replace('-',
                        '_',
                        $optionId));

                if ($option && $option->getValue() !== null) {
                    $this->optionResolver->resolve([$optionId => $option->getValue()]);
                    $resolvedOptions[$optionId] = $option->getValue();
                }
                if ( ! empty($context['family'])) {
                    $option = $optionsRepository->get(OptionNamePrefixHelper::resolveFromContext([
                            'entity' => $context['entity'],
                            'family' => $context['family']
                        ]) . str_replace('-', '_',
                            $optionId));

                    if ($option && $option->getValue() !== null) {
                        $this->optionResolver->resolve([$optionId => $option->getValue()]);
                        $resolvedOptions[$optionId] = $option->getValue();
                    }
                }

                if ( ! empty($context['structure'])) {
                    $option = $optionsRepository->get(OptionNamePrefixHelper::resolveFromContext([
                            'entity' => $context['entity'],
                            'structure' => $context['structure']
                        ]) . str_replace('-', '_',
                            $optionId));

                    if ($option && $option->getValue() !== null) {
                        $this->optionResolver->resolve([$optionId => $option->getValue()]);
                        $resolvedOptions[$optionId] = $option->getValue();
                    }
                }
            } else {
                if ( ! empty($context['family'])) {
                    $option = $optionsRepository->get(OptionNamePrefixHelper::resolveFromContext(['family' => $context['family']]) . str_replace('-',
                            '_',
                            $optionId));

                    if ($option && $option->getValue() !== null) {
                        $this->optionResolver->resolve([$optionId => $option->getValue()]);
                        $resolvedOptions[$optionId] = $option->getValue();
                    }
                }

                if ( ! empty($context['structure'])) {
                    $option = $optionsRepository->get(OptionNamePrefixHelper::resolveFromContext(['structure' => $context['structure']]) . str_replace('-',
                            '_',
                            $optionId));

                    if ($option && $option->getValue() !== null) {
                        $this->optionResolver->resolve([$optionId => $option->getValue()]);
                        $resolvedOptions[$optionId] = $option->getValue();
                    }
                }
            }
        }

        return $this->optionResolver->resolve($resolvedOptions);
    }
}