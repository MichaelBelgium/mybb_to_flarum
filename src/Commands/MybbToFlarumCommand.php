<?php

namespace Michaelbelgium\Mybbtoflarum\Commands;

use Exception;
use Flarum\Console\AbstractCommand;
use Michaelbelgium\Mybbtoflarum\Migrator;
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
        'path'=> ['path', null, InputOption::VALUE_OPTIONAL, 'path to the mybb forum (used for avatar migration)', false],
        'avatars'=> ['avatars', null, InputOption::VALUE_OPTIONAL, 'import avatars', true],
        'soft-posts'=> ['soft-posts', null, InputOption::VALUE_OPTIONAL, 'import soft deleted posts', true],
        'soft-threads'=> ['soft-threads', null, InputOption::VALUE_OPTIONAL, 'import soft deleted threads', true],
        'do-users'=> ['do-users', null, InputOption::VALUE_OPTIONAL, 'import users', true],
        'do-threads-posts'=> ['do-threads-posts', null, InputOption::VALUE_OPTIONAL, 'import posts', true],
        'do-groups'=> ['do-groups', null, InputOption::VALUE_OPTIONAL, 'import groups', true],
        'do-categories'=> ['do-categories', null, InputOption::VALUE_OPTIONAL, 'import categories', true],
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

        $doUsers = $this->input->getOption('do-users');
        $doThreadsPosts = $this->input->getOption('do-threads-posts');
        $doGroups = $this->input->getOption('do-groups');
        $doCategories = $this->input->getOption('do-categories');

        try {
            $migrator = new Migrator(
                $host,
                $user,
                $password,
                $db,
                $this->input->getOption('prefix'),
                $this->input->getOption('path')
            );

            if ($doGroups) {
                $migrator->migrateUserGroups();
            }
            $counts = $migrator->getProcessedCount();
            $this->info("{$counts["groups"]} user groups migrated");

            if ($doUsers) {
                $migrator->migrateUsers($migrate_avatars, $doGroups);
            }
            $counts = $migrator->getProcessedCount();
            $this->info("{$counts["users"]} users migrated");

            if ($doCategories) {
                $migrator->migrateCategories();
            }
            $counts = $migrator->getProcessedCount();
            $this->info("{$counts["categories"]} categories migrated");

            if ($doThreadsPosts) {
                $migrator->migrateDiscussions($doUsers, $doCategories, $migrate_softthreads, $migrate_softposts);
            }
            $counts = $migrator->getProcessedCount();
            $this->info("{$counts["discussions"]} discussions migrated");
            $this->info("{$counts["posts"]} posts migrated");

            $this->info("Migration successful\n\n");

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
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