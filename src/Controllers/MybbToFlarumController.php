<?php
namespace Michaelbelgium\Mybbtoflarum\Controllers;

use Michaelbelgium\Mybbtoflarum\Migrator;
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

    /**
     * MybbToFlarumController constructor
     *
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Handle the post request
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $response = ["error" => false, "message" => ""];

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
            
            if($doThreadsPosts)
                $migrator->migrateDiscussions($doUsers, $doCategories, $migrate_softthreads, $migrate_softposts);

            $counts = $migrator->getProcessedCount();

            $response["message"] = "Migration successful\n\n• {$counts["users"]} users\n• {$counts["groups"]} user groups\n• {$counts["categories"]} categories\n• {$counts["discussions"]} discussions\n• {$counts["posts"]} posts";
        } catch (Exception $e) {
            $response["error"] = true;
            $response["message"] = $e->getMessage();
        }

        return new JsonResponse($response);
    }
}