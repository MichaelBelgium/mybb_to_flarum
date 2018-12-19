<?php
namespace michaelbelgium\mybbtoflarum\controllers;

use michaelbelgium\mybbtoflarum\Migrator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Flarum\Settings\SettingsRepositoryInterface;
use Zend\Diactoros\Response\JsonResponse;

class MybbToFlarumController implements RequestHandlerInterface
{
    /**
     * @var SettingsRepositoryInterface $settings
     */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(Request $request): Response
    {
        $response = ["error" => false, "message" => "[done] migrating"];

        $migrate_avatars = array_get($request->getParsedBody(), 'avatars');
        $migrate_softposts = array_get($request->getParsedBody(), 'softposts');
        $migrate_softthreads = array_get($request->getParsedBody(), 'softthreads');
        
        $doUsers = array_get($request->getParsedBody(), 'doUsers');
        $doThreadsPosts = array_get($request->getParsedBody(), 'doThreadsPosts');
        $doGroups = array_get($request->getParsedBody(), 'doGroups');
        $doCategories = array_get($request->getParsedBody(), 'doCategories');

        try {
            $migrator = new Migrator(
                $this->settings->get('mybb_host'),
                $this->settings->get('mybb_user'),
                $this->settings->get('mybb_password'),
                $this->settings->get('mybb_db'),
                $this->settings->get('mybb_prefix'),
                $this->settings->get('mybb_path')
            );

            if($doGroups)
                $migrator->migrateUserGroups();

            if($doUsers)
                $migrator->migrateUsers($migrate_avatars, $doGroups);

            if($doCategories)
                $migrator->migrateCategories();
            
        } catch (Exception $e) {
            $response["error"] = true;
            $response["message"] = $e->getMessage();
        }

        return new JsonResponse($response);
    }
}