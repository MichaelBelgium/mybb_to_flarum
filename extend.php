<?php

use Illuminate\Contracts\View\Factory;
use Flarum\Extend\Frontend;
use michaelbelgium\mybbtoflarum\controllers\MybbToFlarumController;

return [
	(new Frontend('admin'))
		->route('/mybb-to-flarum', 'mybbtoflarum')
		->js(__DIR__ .'/js/dist/admin.js')
];