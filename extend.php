<?php

use Illuminate\Contracts\View\Factory;
use Flarum\Extend\Locales;
use Flarum\Extend\Routes;
use Flarum\Extend\Frontend;
use michaelbelgium\mybbtoflarum\controllers\MybbToFlarumController;

return [
	new Locales(__DIR__ . '/locale'),
	(new Frontend('admin'))
		->route('/mybb-to-flarum', 'mybbtoflarum')
		->css(__DIR__ . '/less/admin.less')
		->js(__DIR__ .'/js/dist/admin.js'),

	(new Routes('api'))
		->post('/mybb-to-flarum', 'mybbtoflarum.execute', MybbToFlarumController::class),
];