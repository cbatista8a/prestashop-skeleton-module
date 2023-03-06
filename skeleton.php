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

use CubaDevOps\Skeleton\Application\ConfigurationRepository;
use CubaDevOps\Skeleton\Domain\FormFieldsDefinition;
use CubaDevOps\Skeleton\Domain\ValueObjects\FormField;
use CubaDevOps\Skeleton\Utils\RoutesLoader;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Skeleton extends Module implements WidgetInterface
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
    /**
     * @var ConfigurationRepository
     */
    private $config_repository;
    /**
     * @var FormFieldsDefinition
     */
    private $form_fields_definition;

    public function __construct()
    {
        $this->name = 'skeleton';
        $this->tab = 'administration';
        $this->version = '1.1.1';
        $this->author = 'Cuba Devops';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Skeleton Module',[],'Modules.Skeleton.Admin');
        $this->description = $this->trans('A base module for Prestashop development',[],'Modules.Skeleton.Admin');

        $this->confirmUninstall = $this->trans('Are you sure you want uninstall this module?',[],'Modules.Skeleton.Admin');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);


        $this->autoload();
        $this->loadConfigurationService();
    }

    private function autoload()
    {
        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/vendor/autoload.php')) {
            require_once _PS_MODULE_DIR_ . $this->name . '/vendor/autoload.php';
        }
    }


    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        return parent::install() &&
            $this->registerHooks();
    }

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
        include(dirname(__FILE__).'/sql/uninstall.php');
        $this->deleteConfigValues();
        return parent::uninstall();
    }

    protected function deleteConfigValues()
    {
        $this->config_repository->delete($this->form_fields_definition->getFormId());
    }

    /**
     * @return FormFieldsDefinition
     */
    private function getFormFieldsDefinition(): FormFieldsDefinition
    {
        $form = $this->config_repository ? $this->config_repository->findById($this->name) : null;
        if($form){
            return $form;
        }

        $form_fields = new FormFieldsDefinition($this->name);
        $form_fields->addField(new FormField(self::PREFIX . 'ACTIVE', ''))
                    ->addField(new FormField(self::PREFIX . 'LIVE_MODE', ''))
                    ->addField(new FormField(self::PREFIX . 'TEXT', array_fill_keys(Language::getIDs(), ''),true));
        return $form_fields;
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
        foreach ($this->form_fields_definition->getFields() as $field) {
            $value = $field->isMultilang() ? $this->captureMultilingualValue($field->getName()) : Tools::getValue($field->getName());
            $this->form_fields_definition->updateField($field->getName(), $value);
        }
        $this->config_repository->persist($this->form_fields_definition);
        $this->html .= $this->displayConfirmation($this->trans('Form was saved!',[],'Modules.Skeleton.Admin'));
    }

    protected function captureMultilingualValue($key)
    {
        $value = [];
        foreach (Language::getIDs() as $id_lang) {
            $value[$id_lang] = Tools::getValue($key . '_' . $id_lang);
        }
        return $value;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     * @param array $form_config
     * @param string $btn_submit_name
     * @return string
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
            'fields_value' => $this->form_fields_definition->toArray(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($form_config));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function loadConfigurationService()
    {
        try {
            $this->config_repository = $this->get(ConfigurationRepository::class);
        } catch (Exception $e) {
            return; //avoid error on module install because services aren't load yet
        }
        $this->form_fields_definition = $this->getFormFieldsDefinition();
    }

    protected function getConfigValue($key)
    {
        /** @var FormField $field */
        $field = $this->form_fields_definition->getField($key);
        return $field->getValue();
    }

    private function getConfigForm(): array
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings',[],'Modules.Skeleton.Admin'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Enable',[],'Modules.Skeleton.Admin'),
                        'name' => self::PREFIX . 'ACTIVE',
                        'is_bool' => true,
                        'desc' => $this->trans('Enable or disable this module',[],'Modules.Skeleton.Admin'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->trans('Yes',[],'Modules.Skeleton.Admin')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->trans('No',[],'Modules.Skeleton.Admin')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Live mode',[],'Modules.Skeleton.Admin'),
                        'name' => self::PREFIX . 'LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->trans('Use this module in live mode',[],'Modules.Skeleton.Admin'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->trans('Production',[],'Modules.Skeleton.Admin')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->trans('Demo',[],'Modules.Skeleton.Admin')
                            )
                        ),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'lang' => true,
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->trans('Example Field',[],'Modules.Skeleton.Admin'),
                        'name' => self::PREFIX . 'TEXT',
                        'label' => $this->trans('Text',[],'Modules.Skeleton.Admin'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save',[],'Modules.Skeleton.Admin'),
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
        $routingConfigLoader = new RoutesLoader($this->local_path . 'config');

        return $routingConfigLoader->load('front.yml', true);
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

    public function isUsingNewTranslationSystem()
    {
        return true;
    }
}
