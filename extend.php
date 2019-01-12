<?php

use Illuminate\Contracts\View\Factory;
use Flarum\Extend\Locales;
use Flarum\Extend\Routes;
use Flarum\Extend\Frontend;
use Flarum\Extend\Formatter;
use Michaelbelgium\Mybbtoflarum\Controllers\MybbToFlarumController;
use s9e\TextFormatter\Configurator;

return [
	new Locales(__DIR__ . '/locale'),
	(new Frontend('admin'))
		->route('/mybb-to-flarum', 'mybbtoflarum')
		->css(__DIR__ . '/less/admin.less')
		->js(__DIR__ .'/js/dist/admin.js'),

	(new Routes('api'))
		->post('/mybb-to-flarum', 'mybbtoflarum.execute', MybbToFlarumController::class),

	(new Formatter)->configure(function (Configurator $config) {
		$config->BBCodes->delete("SIZE");
		$config->BBCodes->addFromRepository('ALIGN');
		$config->BBCodes->addFromRepository('HR');
		$config->BBCodes->addCustom('[size={CHOICE=large,small,xx-small,x-small,medium,x-large,xx-large}]{TEXT}[/size]','<span style="font-size:{CHOICE}">{TEXT}</span>');
	})
];