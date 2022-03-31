<?php
/**
 * Copyright (c) 2022.  <CubaDevOps>
 *
 * @Author : Carlos Batista <cbatista8a@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

use CubaDevOps\utils\RoutesLoader;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CdSkeletron extends Module implements WidgetInterface
{
    private const PREFIX = 'CD_';
    /**
     * @var int
     */
    private $shop_context;
    /**
     * @var int|null
     */
    private $shop_group;
    /**
     * @var int
     */
    private $shop_id;

    private $html;
    private $routingConfigLoader;

    public function __construct()
    {
        $this->name = 'cdskeletron';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'Cuba Devops';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Skeletron Module');
        $this->description = $this->l('A base module for Prestashop development');

        $this->confirmUninstall = $this->l('Are you sure you want uninstall this module?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->shop_context = Shop::getContext();
        $this->shop_group = Shop::getContextShopGroupID();
        $this->shop_id = $this->context->shop->id;

        $this->autoload();
        $this->routingConfigLoader = new RoutesLoader($this->local_path . 'config');
    }

    private function autoload()
    {
        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/vendor/autoload.php')) {
            require_once _PS_MODULE_DIR_ . $this->name . '/vendor/autoload.php';
        }
    }


    public function install()
    {
        return parent::install() &&
            $this->registerHooks();
    }

    //TODO implement install and uninstall sql and tabs

    public function registerHooks()
    {
        $hooks = [
            'header',
            'backOfficeHeader',
            'moduleRoutes'
        ];
        return $this->registerHook($hooks);
    }

    public function uninstall()
    {
        $this->deleteConfigValues();
        return parent::uninstall();
    }

    protected function deleteConfigValues()
    {
        $fields = $this->getFormFields();

        foreach ($fields['single_lang'] as $field) {
            Configuration::deleteByName($field);
        }

        foreach ($fields['multi_lang'] as $lang_field) {
            Configuration::deleteByName($lang_field);
        }
    }

    /**
     * @return array
     */
    public function getFormFields(): array
    {
        return [
            'single_lang' => [
                self::PREFIX . 'ACTIVE',
                self::PREFIX . 'LIVE_MODE',
                self::PREFIX . 'TEXT',
            ],
            'multi_lang' => [],
        ];
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $this->renderConfigHeader();

        $this->postProcess();
        $this->html .= $this->renderForm($this->getConfigForm());

        return $this->html;
    }

    private function renderConfigHeader(): void
    {
        $this->context->smarty->assign('module_dir', $this->_path);
        $this->html = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    /**
     * Save form data.
     */
    protected function postProcess(): void
    {
        if (Tools::isSubmit(self::PREFIX . 'SAVE_PARAMS') === true) {
            $this->saveConfigFormValues();
        }
    }

    protected function saveConfigFormValues(): void
    {
        $fields = $this->getFormFields();

        foreach ($fields['single_lang'] as $field) {
            $this->updateConfigValue($field, Tools::getValue($field));
        }

        foreach ($fields['multi_lang'] as $lang_field) {
            $this->updateConfigValue($lang_field, $this->captureMultilingualValue($lang_field));
        }
    }

    protected function updateConfigValue($key, $value)
    {
        switch ($this->shop_context) {
            case Shop::CONTEXT_SHOP:
                Configuration::updateValue($key, $value, true, $this->shop_group, $this->shop_id);
                break;
            case Shop::CONTEXT_GROUP:
                Configuration::updateValue($key, $value, true, $this->shop_group, null);
                break;
            default:
                Configuration::updateValue($key, $value, true);
                break;
        }
    }

    protected function captureMultilingualValue($key)
    {
        $value = [];
        foreach (Language::getIDs(false) as $id_lang) {
            $value[$id_lang] = Tools::getValue($key . '_' . $id_lang);
        }
        return $value;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     * @param array $form_config
     * @param string $btn_submit_name
     * @return string
     * @throws PrestaShopException
     */
    protected function renderForm($form_config, $btn_submit_name = null)
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = strtoupper(self::PREFIX . ($btn_submit_name ?: 'SAVE_PARAMS'));
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($form_config));
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues(): array
    {
        $fields = $this->getFormFields();

        $single_lang_fields = [];
        foreach ($fields['single_lang'] as $field) {
            $single_lang_fields[$field] = $this->getConfigValue($field);
        }
        $multi_lang_fields = [];
        foreach ($fields['multi_lang'] as $lang_field) {
            $multi_lang_fields[$lang_field] = $this->getMultilingualValue($lang_field);
        }
        return array_merge($single_lang_fields, $multi_lang_fields);
    }

    protected function getConfigValue($key, $lang = null, $default = null)
    {
        switch ($this->shop_context) {
            case Shop::CONTEXT_SHOP:
                return Configuration::get($key, $lang, $this->shop_group, $this->shop_id, $default);
                break;
            case Shop::CONTEXT_GROUP:
                return Configuration::get($key, $lang, $this->shop_group, null, $default);
                break;
        }
        return Configuration::get($key, $lang, null, null, $default);
    }

    protected function getMultilingualValue($key): array
    {
        $value = [];
        foreach (Language::getIDs(false) as $id_lang) {
            $value[$id_lang] = $this->getConfigValue($key, $id_lang);
        }
        return $value;
    }

    private function getConfigForm(): array
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable'),
                        'name' => self::PREFIX . 'ACTIVE',
                        'is_bool' => true,
                        'desc' => $this->l('Enable or disable this module'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => self::PREFIX . 'LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Production')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Demo')
                            )
                        ),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Example Field'),
                        'name' => self::PREFIX . 'TEXT',
                        'label' => $this->l('Text'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') === $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    /**
     * @param $params
     * @return string[]
     * @throws Exception
     */
    public function hookModuleRoutes($params)
    {
        return $this->getFrontControllersRouting();
    }

    /**
     * Load routes automatically from config/front.yml
     *
     * @return string[]
     *
     * @throws Exception
     */
    protected function getFrontControllersRouting(): array
    {
        if (!file_exists($this->local_path . 'config/front.yml')) {
            return [];
        }

        return $this->routingConfigLoader->load('front.yml', true);
    }

    public function renderWidget($hookName, array $configuration)
    {
        $result = $this->getWidgetVariables($hookName, $configuration);
        if (!$result) {
            return;
        }
        //render template and return
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        return [];
    }
}
