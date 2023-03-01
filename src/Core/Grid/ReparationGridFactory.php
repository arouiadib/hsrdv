<?php


namespace PrestaShop\Module\HsRdv\Core\Grid;

use PrestaShop\Module\HsRdv\Core\Grid\Definition\Factory\ReparationDefinitionFactory;
use PrestaShop\Module\HsRdv\Core\Search\Filters\ReparationFilters;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Filter\GridFilterFormFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Grid;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use PrestaShop\PrestaShop\Adapter\Shop\Context;

/**
 * Class ReparationGridFactory.
 */
final class ReparationGridFactory
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var HookDispatcherInterface
     */
    private $hookDispatcher;

    /**
     * @var GridDataFactoryInterface
     */
    private $dataFactory;

    /**
     * @var GridFilterFormFactoryInterface
     */
    private $filterFormFactory;

    /**
     * @var Context
     */
    private $shopContext;
    /**
     * HookGridFactory constructor.
     *
     * @param TranslatorInterface $translator
     * @param HookDispatcherInterface $hookDispatcher
     * @param GridDataFactoryInterface $dataFactory
     * @param GridFilterFormFactoryInterface $filterFormFactory
     */
    public function __construct(
        TranslatorInterface $translator,
        GridDataFactoryInterface $dataFactory,
        HookDispatcherInterface $hookDispatcher,
        GridFilterFormFactoryInterface $filterFormFactory,
        Context $shopContext
    ) {
        $this->translator = $translator;
        $this->hookDispatcher = $hookDispatcher;
        $this->dataFactory = $dataFactory;
        $this->filterFormFactory = $filterFormFactory;
        $this->shopContext = $shopContext;
    }

    /**
     * @param array $hooks
     * @param array $filtersParams
     *
     * @return Grid[]
     */
    public function getGrid(array $filtersParams)
    {
        $filters = new ReparationFilters($filtersParams);
        $gridFactory = $this->buildGridFactory();
        $grid = $gridFactory->getGrid($filters);

        return $grid;
    }

    /**

     * @return GridFactory
     */
    private function buildGridFactory()
    {
        $definitionFactory = new ReparationDefinitionFactory(null, $this->shopContext);
        $definitionFactory->setTranslator($this->translator);
        $definitionFactory->setHookDispatcher($this->hookDispatcher);

        return new GridFactory(
            $definitionFactory,
            $this->dataFactory,
            $this->filterFormFactory
        );
    }
}
