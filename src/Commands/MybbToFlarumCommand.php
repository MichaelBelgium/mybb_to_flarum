<?php

namespace Michaelbelgium\Mybbtoflarum\Commands;

use Exception;
use Flarum\Console\AbstractCommand;
use Flarum\Extension\ExtensionManager;
use Flarum\Settings\SettingsRepositoryInterface;
use Michaelbelgium\Mybbtoflarum\Migrator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;

class MybbToFlarumCommand extends AbstractCommand
{
    public function __construct(
        protected ExtensionManager $extensionManager,
        protected SettingsRepositoryInterface $settings,
        protected LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('migrate-data:from-mybb')
            ->setDescription('Migrates data from an existing mybb forum')

            // connection options
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Host of the mybb database', $this->settings->get('mybb_host', '127.0.0.1'))
            ->addOption('user', 'u',  InputOption::VALUE_REQUIRED, 'User of the mybb database', $this->settings->get('mybb_user'))
            ->addOption('password', 'p',  InputOption::VALUE_OPTIONAL, 'Password for the mybb database', $this->settings->get('mybb_password', ''))
            ->addOption('db', 'd',  InputOption::VALUE_REQUIRED, 'Name of the mybb database', $this->settings->get('mybb_db'))
            ->addOption('prefix', null, InputOption::VALUE_OPTIONAL, 'Prefix of the mybb database tables', $this->settings->get('mybb_prefix', 'mybb_'))
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Path to the mybb forum (required for avatar and attachment migration)', $this->settings->get('mybb_path', ''))

            // main options
            ->addOption('users', null, InputOption::VALUE_NONE, 'Import users (excluding avatars)')
            ->addOption('threads-posts', null, InputOption::VALUE_NONE, 'Import posts (excluding soft deleted posts/threads)')
            ->addOption('groups', null, InputOption::VALUE_NONE, 'Import groups')
            ->addOption('categories', null, InputOption::VALUE_NONE, 'Import categories')

            // sub options for users
            ->addOption('avatars', null, InputOption::VALUE_NONE, 'Import avatars')

            // sub options for threads/posts
            ->addOption('soft-posts', null, InputOption::VALUE_NONE, 'Import soft deleted posts')
            ->addOption('soft-threads', null, InputOption::VALUE_NONE, 'Import soft deleted threads')
            ->addOption('attachments', null, InputOption::VALUE_NONE, 'Import attachments')

            ->addOption('debug', null, InputOption::VALUE_NONE, 'Enable logging')
        ;
    }

    protected function fire(): int
    {
        $host = $this->getOptionOrPrompt('host');
        $user = $this->getOptionOrPrompt('user');
        $password = $this->input->getOption('password') ?? '';
        $db = $this->getOptionOrPrompt('db');

        $migrate_avatars = $this->input->getOption('avatars');
        $migrate_softposts = $this->input->getOption('soft-posts');
        $migrate_softthreads = $this->input->getOption('soft-threads');
        $migrate_attachments = $this->input->getOption('attachments');

        $doUsers = $this->input->getOption('users');
        $doThreadsPosts = $this->input->getOption('threads-posts');
        $doGroups = $this->input->getOption('groups');
        $doCategories = $this->input->getOption('categories');
        $path = $this->input->getOption('path');
        $prefix = $this->input->getOption('prefix');
        $debug = $this->input->getOption('debug');

        if (!$doUsers && !$doCategories && !$doGroups && !$doThreadsPosts) {
            $this->error('Nothing will be imported. Please provide the option if you want to import users (--users), groups (--groups), threads/posts (--threads-posts) or categories (--categories).');
            return Command::FAILURE;
        }

        if ($doUsers && $migrate_avatars && empty($path)) {
            $this->error('Mybb path (--path) needs to be provided when importing users + avatars');
            return Command::FAILURE;
        }

        if ($doThreadsPosts && $migrate_attachments) {
            if (empty($path)) {
                $this->error('Mybb path (--path) needs to be provided when importing threads/posts + attachments');
                return Command::FAILURE;
            }

            if (!$this->extensionManager->isEnabled('fof-upload'))
                $this->info('WARNING: fof/upload not installed. Migrating attachments won\'t work.');
        }

        try {
            $migrator = new Migrator(
                $host,
                $user,
                $password,
                $db,
                $prefix,
                $path,
                $debug ? $this->logger : null
            );

            if ($doGroups)
                $migrator->migrateUserGroups();

            if ($doUsers)
                $migrator->migrateUsers($migrate_avatars, $doGroups);

            if ($doCategories)
                $migrator->migrateCategories();

            if ($doThreadsPosts)
                $migrator->migrateDiscussions($doUsers, $doCategories, $migrate_softthreads, $migrate_softposts, $this->extensionManager->isEnabled('fof-upload') && $migrate_attachments);

            $counts = $migrator->getProcessedCount();

            $this->info("Migration successful:");
            $this->info("{$counts["groups"]} user groups migrated");
            $this->info("{$counts["users"]} users migrated");
            $this->info("{$counts["categories"]} categories migrated");
            $this->info("{$counts["discussions"]} discussions migrated");
            $this->info("{$counts["posts"]} posts migrated with {$counts["attachments"]} attachments");

            $settingsMap = [
                'mybb_host' => $host,
                'mybb_user' => $user,
                'mybb_password' => $password,
                'mybb_db' => $db,
                'mybb_prefix' => $prefix,
                'mybb_path' => $path,
            ];

            foreach ($settingsMap as $key => $value)
            {
                if ($this->settings->get($key) !== $value)
                    $this->settings->set($key, $value);
            }

        } catch (Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function getOptionOrPrompt(string $optionName)
    {
        $value = $this->input->getOption($optionName);
        if (empty($value)) {
            if ($this->input->getOption('no-interaction')) {
                $this->error("missing required value for {$optionName}");
            }
            $helper = $this->getHelper('question');
            $question = new Question("Please input the {$optionName}: ");

            $value = $helper->ask($this->input, $this->output, $question);
        }
        return $value;
    }
}