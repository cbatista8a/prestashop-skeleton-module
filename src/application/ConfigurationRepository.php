<?php

namespace CubaDevOps\Skeleton\application;

use Configuration;
use Context;
use CubaDevOps\Skeleton\Domain\FormFieldsDefinition;
use Tools;

class ConfigurationRepository
{
    /**
     * @var Context
     */
    private $context;
    /**
     * @var int|null
     */
    private $shop_group;
    /**
     * @var int|null
     */
    private $shop_id;

    /*
     * Todo inject prestashop.adapter.legacy.configuration service once fully migrated
     * */
    public function __construct(Context $context)
    {
        $this->context = $context;
        $this->shop_group = $context->shop->getContextShopGroupID(true);
        $this->shop_id = $this->context->shop->id;
    }

    public function getModuleName()
    {
        return 'skeleton';
    }

    public function persist(FormFieldsDefinition $form)
    {
        /* we use base64 for avoid errors on unserialize because database field is not blob */
        Configuration::updateValue($form->getFormId(), base64_encode(serialize($form)), false, $this->shop_group, $this->shop_id);
    }

    /**
     * @param string $form_id
     * @return FormFieldsDefinition|null
     */
    public function findById(string $form_id)
    {
        $data = Configuration::get($form_id,null,$this->shop_group, $this->shop_id);
        return $data ? unserialize(base64_decode($data)) : null;
    }

    public function delete(string $form_id): bool
    {
        return Configuration::deleteByName($form_id);
    }
}