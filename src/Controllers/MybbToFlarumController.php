<?php
namespace Michaelbelgium\Mybbtoflarum\Controllers;

use Exception;
use Flarum\Extension\ExtensionManager;
use Illuminate\Database\ConnectionInterface;
use Michaelbelgium\Mybbtoflarum\Migrator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Log\LoggerInterface;

class MybbToFlarumController implements RequestHandlerInterface
{
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected ExtensionManager $extensionManager,
        protected LoggerInterface $logger,
        protected ConnectionInterface $db,
    ) {
    }

    public function handle(Request $request): Response
    {
        $response = ["error" => false, "message" => ""];

        $migrate_avatars = Arr::get($request->getParsedBody(), 'avatars');
        $migrate_softposts = Arr::get($request->getParsedBody(), 'softposts');
        $migrate_softthreads = Arr::get($request->getParsedBody(), 'softthreads');
        $migrate_attachments = Arr::get($request->getParsedBody(), 'attachments');
        $migrate_privatemessages = Arr::get($request->getParsedBody(), 'privatemessages');
        
        $doUsers = Arr::get($request->getParsedBody(), 'doUsers');
        $doThreadsPosts = Arr::get($request->getParsedBody(), 'doThreadsPosts');
        $doGroups = Arr::get($request->getParsedBody(), 'doGroups');
        $doCategories = Arr::get($request->getParsedBody(), 'doCategories');
        $debug = Arr::get($request->getParsedBody(), 'debug');

        try {
            $migrator = new Migrator(
                $this->settings->get('mybb_host'),
                $this->settings->get('mybb_user'),
                $this->settings->get('mybb_password'),
                $this->settings->get('mybb_db'),
                $this->settings->get('mybb_prefix'),
                $this->settings->get('mybb_path'),
                $debug ? $this->logger : null
            );

            $this->db->transaction(fn() => $migrator->migrate(
                $doGroups,
                $doUsers,
                $doCategories,
                $doThreadsPosts,
                $migrate_avatars,
                $this->extensionManager->isEnabled('flarum-messages') && $migrate_privatemessages,
                $migrate_softthreads,
                $migrate_softposts,
                $this->extensionManager->isEnabled('fof-upload') && $migrate_attachments,
            ));

            $counts = $migrator->getProcessedCount();

            $response["message"] = "Migration successful\n\n• {$counts["users"]} users\n• {$counts["private_messages"]} private messages\n• {$counts["groups"]} user groups\n• {$counts["categories"]} categories\n• {$counts["discussions"]} discussions\n• {$counts["posts"]} posts with {$counts["attachments"]} attachments";

            if($migrate_privatemessages && !$doUsers)
                $response["message"] .= "\n\nPrivate messages were skipped: they are only migrated together with users.";
            elseif($migrate_privatemessages && !$this->extensionManager->isEnabled('flarum-messages'))
                $response["message"] .= "\n\nPrivate messages were skipped: flarum/messages is not enabled.";
        } catch (Exception $e) {
            $response["error"] = true;
            $response["message"] = $e->getMessage();
        }

        return new JsonResponse($response);
    }
}