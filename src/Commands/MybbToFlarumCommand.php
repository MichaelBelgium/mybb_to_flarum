<?php

namespace Michaelbelgium\Mybbtoflarum\Commands;

use Exception;
use Flarum\Console\AbstractCommand;
use Michaelbelgium\Mybbtoflarum\Migrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;

class MybbToFlarumCommand extends AbstractCommand
{
    protected $options = [
        'host'=> ['host', null, InputOption::VALUE_REQUIRED, 'host of the mybb database'],
        'user'=> ['user', 'u', InputOption::VALUE_REQUIRED, 'user of the mybb database'],
        'password'=> ['password', 'p', InputOption::VALUE_OPTIONAL, 'password for the mybb database', ''],
        'db'=> ['db', 'd', InputOption::VALUE_REQUIRED, 'name of the mybb database'],
        'prefix'=> ['prefix', null, InputOption::VALUE_OPTIONAL, 'prefix of the mybb database tables', 'mybb_'],

        //main options
        'users' => ['users', null, InputOption::VALUE_NONE, 'Import users (excluding avatars)'],
        'threads-posts' => ['threads-posts', null, InputOption::VALUE_NONE, 'Import posts (excluding soft deleted posts/threads)'],
        'groups' => ['groups', null, InputOption::VALUE_NONE, 'Import groups'],
        'categories' => ['categories', null, InputOption::VALUE_NONE, 'Import categories'],
        'privatemessages' => ['privatemessages', null, InputOption::VALUE_NONE, 'Import private messages'],

        //sub options for avatars
        'avatars' => ['avatars', null, InputOption::VALUE_NONE, 'Import avatars'],
        'path' => ['path', null, InputOption::VALUE_OPTIONAL, 'Path to the mybb forum (required for avatar and attachment migration)', ''],

        //sub options for threads/posts
        'soft-posts' => ['soft-posts', null, InputOption::VALUE_NONE, 'Import soft deleted posts'],
        'soft-threads' => ['soft-threads', null, InputOption::VALUE_NONE, 'Import soft deleted threads'],
        'attachments' => ['attachments', null, InputOption::VALUE_NONE, 'Import attachments'],
    ];

    protected function configure()
    {
        $this
            ->setName('migrate-data:from-mybb')
            ->setDescription('Migrates data from an existing mybb forum');
        
        foreach ($this->options as $option) {
            $this->addOption(...$option);
        }
    }

    protected function fire()
    {
        $host = $this->getOptionOrPrompt('host');
        $user = $this->getOptionOrPrompt('user');
        $password = $this->getOptionOrPrompt('password');
        $db = $this->getOptionOrPrompt('db');
        $migrate_avatars = $this->input->getOption('avatars');
        $migrate_softposts = $this->input->getOption('soft-posts');
        $migrate_softthreads = $this->input->getOption('soft-threads');
        $migrate_attachments = $this->input->getOption('attachments');

        $doUsers = $this->input->getOption('users');
        $doPrivateMessages = $this->input->getOption('privatemessages');
        $doThreadsPosts = $this->input->getOption('threads-posts');
        $doGroups = $this->input->getOption('groups');
        $doCategories = $this->input->getOption('categories');
        $path = $this->input->getOption('path');
        $prefix = $this->input->getOption('prefix');

        if(!$doUsers && !$doCategories && !$doGroups && !$doThreadsPosts) {
            $this->error('Nothing will be imported. Please provide the option if you want to import users (--users), groups (--groups), threads/posts (--threads-posts) or categories (--categories).');
            return Command::FAILURE;
        }

        if($doUsers && $migrate_avatars && empty($path)) {
            $this->error('Mybb path (--path) needs to be provided when importing users + avatars');
            return Command::FAILURE;
        }

        if($doThreadsPosts && $migrate_attachments) {

            if(empty($path)) {
                $this->error('Mybb path (--path) needs to be provided when importing threads/posts + attachments');
                return Command::FAILURE;
            }

            if(!class_exists('FoF\Upload\File'))
                $this->info('WARNING: fof/upload not installed. Migrating attachments won\'t work.');
        }

        try {
            $migrator = new Migrator(
                $host,
                $user,
                $password,
                $db,
                $prefix,
                $path
            );

            if ($doGroups)
                $migrator->migrateUserGroups();

            if ($doUsers)
                $migrator->migrateUsers($migrate_avatars, $doGroups);

            if ($doPrivateMessages)
                $migrator->migratePrivateMessages($doUsers);

            if ($doCategories)
                $migrator->migrateCategories();

            if ($doThreadsPosts)
                $migrator->migrateDiscussions($doUsers, $doCategories, $migrate_softthreads, $migrate_softposts, $migrate_attachments);

            $counts = $migrator->getProcessedCount();

            $this->info("Migration successful:");
            $this->info("{$counts["groups"]} user groups migrated");
            $this->info("{$counts["users"]} users migrated");
            $this->info("{$counts["categories"]} categories migrated");
            $this->info("{$counts["discussions"]} discussions migrated");
            $this->info("{$counts["posts"]} posts migrated with {$counts["attachments"]} attachments");


        } catch (Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function getOptionOrPrompt($optionName)
    {
        $value = $this->input->getOption($optionName);
        if (empty($value)) {
            if($this->input->getOption('no-interaction')) {
                $this->error("missing required value for {$optionName}");
            }
            $helper = $this->getHelper('question');
            $question = new Question("Please input the {$this->options[$optionName][3]}: ");
            if($optionName == 'password') {
                $question->setHidden(true);
            }

            $value = $helper->ask($this->input, $this->output, $question);
        }
        return $value;
    }
}