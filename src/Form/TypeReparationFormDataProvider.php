<?php

namespace PrestaShop\Module\HsRdv\Form;

use PrestaShop\Module\HsRdv\Cache\LinkBlockCacheInterface;
use PrestaShop\Module\HsRdv\Model\TypeReparation;
use PrestaShop\Module\HsRdv\Repository\TypeReparationRepository;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleRepository;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

/**
 * Class TypeReparationFormDataProvider
 */
class TypeReparationFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var int|null
     */
    private $idTypeReparation;

    /**
     * @var TypeReparationRepository
     */
    private $repository;

    /**
     * @var LinkBlockCacheInterface
     */
    private $cache;

    /**
     * @var ModuleRepository
     */
    private $moduleRepository;

    /**
     * @var array
     */
    private $languages;

    /**
     * @var int
     */
    private $shopId;

    /**
     * LinkBlockFormDataProvider constructor.
     *
     * @param TypeReparationRepository $repository
     * @param LinkBlockCacheInterface $cache
     * @param ModuleRepository $moduleRepository
     * @param array $languages
     * @param int $shopId
     */
    public function __construct(
        TypeReparationRepository $repository,
        LinkBlockCacheInterface $cache,
        ModuleRepository $moduleRepository,
        array $languages,
        $shopId
    ) {
        $this->repository = $repository;
        $this->cache = $cache;
        $this->moduleRepository = $moduleRepository;
        $this->languages = $languages;
        $this->shopId = $shopId;
    }

    /**
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getData()
    {

        if (null === $this->idTypeReparation) {
            return [];
        }

        $typeReparation = new TypeReparation($this->idTypeReparation);

        $arrayTypeReparation = $typeReparation->toArray();

        return ['type_reparation' => [
            'id_type_reparation' => $arrayTypeReparation['id_type_reparation'],
            'name' => $arrayTypeReparation['name'],
        ]];
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \PrestaShop\PrestaShop\Adapter\Entity\PrestaShopDatabaseException
     */
    public function setData(array $data)
    {
        $typeReparation = $data['type_reparation'];
        $errors = $this->validateTypeReparation($typeReparation);
        if (!empty($errors)) {
            return $errors;
        }

        if (empty($typeReparation['id_type_reparation'])) {
            $typeReparationId = $this->repository->create($typeReparation);
            $this->setIdTypeReparation($typeReparationId);
        } else {
            $typeReparationId = $typeReparation['id_type_reparation'];
            $this->repository->update($typeReparationId, $typeReparation);
        }

        $this->cache->clearModuleCache();
        return [];
    }

    /**
     * @return int
     */
    public function getIdTypeReparation()
    {
        return $this->idTypeReparation;
    }

    /**
     * @param int $idTypeReparation
     *
     * @return TypeReparationFormDataProvider
     */
    public function setIdTypeReparation($idTypeReparation)
    {
        $this->idTypeReparation = $idTypeReparation;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function validateTypeReparation(array $data)
    {
        $errors = [];

        if (!isset($data['name'])) {
            $errors[] = [
                'key' => 'Merci de donner un nom au type de réparation',
                'domain' => 'Admin.Catalog.Notification',
                'parameters' => [],
            ];
        }

        return $errors;
    }

}
