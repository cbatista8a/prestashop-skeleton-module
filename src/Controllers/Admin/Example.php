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

namespace CubaDevOps\Skeleton\Controllers\Admin;


use CubaDevOps\Skeleton\Application\ConfigurationRepository;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class Example extends FrameworkBundleAdminController
{

    /**
     * @var string
     */
    public $template_path = '@Modules/skeleton/views/templates/admin/';

    public function example()
    {
        $name = $this->get(ConfigurationRepository::class)->getModuleName();
        return $this->render($this->template_path.'admin-example.html.twig',['text' => "Welcome to Example Admin Controller on $name module"]);

        /* this for ajax or API response */
        /* return $this->json('Example Admin Controller'); */
    }
}