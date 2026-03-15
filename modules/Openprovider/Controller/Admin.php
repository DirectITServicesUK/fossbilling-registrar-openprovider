<?php

/**
 * OpenProvider DNS Admin Controller
 * Registers admin routes and navigation for DNS zone management.
 */

namespace Box\Mod\Openprovider\Controller;

class Admin implements \FOSSBilling\InjectionAwareInterface
{
    protected ?\Pimple\Container $di = null;

    public function setDi(\Pimple\Container $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?\Pimple\Container
    {
        return $this->di;
    }

    public function fetchNavigation(): array
    {
        return [
            'group' => [
                'index' => 650,
                'location' => 'openprovider',
                'label' => __trans('DNS Manager'),
                'class' => 'server',
            ],
            'subpages' => [
                [
                    'location' => 'openprovider',
                    'label' => __trans('DNS Zones'),
                    'index' => 100,
                    'uri' => $this->di['url']->adminLink('openprovider'),
                    'class' => '',
                ],
                [
                    'location' => 'openprovider/templates',
                    'label' => __trans('DNS Templates'),
                    'index' => 200,
                    'uri' => $this->di['url']->adminLink('openprovider/templates'),
                    'class' => '',
                ],
            ],
        ];
    }

    public function register(\Box_App &$app): void
    {
        $app->get('/openprovider', 'get_index', [], static::class);
        $app->get('/openprovider/zone/:name', 'get_zone', ['name' => '[a-zA-Z0-9.\-]+'], static::class);
        $app->get('/openprovider/templates', 'get_templates', [], static::class);
    }

    public function get_index(\Box_App $app): string
    {
        $this->di['is_admin_logged'];
        return $app->render('mod_openprovider_index');
    }

    public function get_zone(\Box_App $app, string $name): string
    {
        $this->di['is_admin_logged'];
        return $app->render('mod_openprovider_zone', ['zone_name' => $name]);
    }

    public function get_templates(\Box_App $app): string
    {
        $this->di['is_admin_logged'];
        return $app->render('mod_openprovider_templates');
    }
}
