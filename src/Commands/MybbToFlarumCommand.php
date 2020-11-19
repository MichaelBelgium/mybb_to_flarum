<?php

namespace Michaelbelgium\Mybbtoflarum\Commands;

use Exception;
use Flarum\Console\AbstractCommand;
use Michaelbelgium\Mybbtoflarum\Migrator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;

class MybbToFlarumCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate-data:from-mybb')
            ->setDescription('Migrates data from an existing mybb forum')
            ->addOption(
                'host',
                null,
                InputOption::VALUE_REQUIRED,
                'host of the mybb database'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_REQUIRED,
                'user of the mybb database'
            )
            ->addOption(
                'password',
                'p',
                InputOption::VALUE_OPTIONAL,
                'password for the mybb database',
                false
            )
            ->addOption(
                'db',
                'd',
                InputOption::VALUE_REQUIRED,
                'name of the mybb database'
            )
            ->addOption(
                'prefix',
                null,
                InputOption::VALUE_OPTIONAL,
                'prefix of the mybb database tables',
                'mybb_'
            )
            ->addOption(
                'path',
                null,
                InputOption::VALUE_OPTIONAL,
                'path to the mybb forum (used for avatar migration)',
                false
            )
            ->addOption(
                'prefix',
                null,
                InputOption::VALUE_OPTIONAL,
                'prefix of the mybb database tables',
                'mybb_'
            )
            ->addOption('avatars',null,InputOption::VALUE_OPTIONAL,'import avatars',true)
            ->addOption('softposts',null,InputOption::VALUE_OPTIONAL,'import soft deleted posts',true)
            ->addOption('softthreads',null,InputOption::VALUE_OPTIONAL,'import soft deleted threads',true)
            ->addOption('doUsers',null,InputOption::VALUE_OPTIONAL,'import users',true)
            ->addOption('doThreadsPosts',null,InputOption::VALUE_OPTIONAL,'import posts',true)
            ->addOption('doGroups',null,InputOption::VALUE_OPTIONAL,'import groups',true)
            ->addOption('doCategories',null,InputOption::VALUE_OPTIONAL,'import categories',true);
    }

    protected function fire()
    {
        $password = $this->input->getOption('password');

        if($password === false || empty($password)) {
            $helper = $this->getHelper('question');
            $question = new Question('Please input the database password');
            $question->setHidden(true);

            $password = $helper->ask($this->input, $this->output, $question);
        }
        $migrate_avatars = $this->input->getOption('avatars');
        $migrate_softposts = $this->input->getOption('softposts');
        $migrate_softthreads = $this->input->getOption('softthreads');

        $doUsers = $this->input->getOption('doUsers');
        $doThreadsPosts = $this->input->getOption('doThreadsPosts');
        $doGroups = $this->input->getOption('doGroups');
        $doCategories = $this->input->getOption('doCategories');

        try {
            $migrator = new Migrator(
                $this->input->getOption('host'),
                $this->input->getOption('user'),
                $password,
                $this->input->getOption('db'),
                $this->input->getOption('prefix'),
                $this->input->getOption('path')
            );

            if($doGroups) {
                $migrator->migrateUserGroups();
                $this->info("user groups migrated");
            }
            $this->showCounts($migrator);

            if($doUsers) {
                $migrator->migrateUsers($migrate_avatars, $doGroups);
                $this->info("users migrated");
            }
            $this->showCounts($migrator);

            if($doCategories) {
                $migrator->migrateCategories();
                $this->info("categories migrated");
            }
            $this->showCounts($migrator);

            if($doThreadsPosts) {
                $migrator->migrateDiscussions($doUsers, $doCategories, $migrate_softthreads, $migrate_softposts);
                $this->info("discussions migrated");
            }

            $this->info("Migration successful\n\n");

            $this->showCounts($migrator);

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function showCounts($migrator) {
        $counts = $migrator->getProcessedCount();
        $this->info("• {$counts["users"]} users\n• {$counts["groups"]} user groups\n• {$counts["categories"]} categories\n• {$counts["discussions"]} discussions\n• {$counts["posts"]} posts");
    }
}