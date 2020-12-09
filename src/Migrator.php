<?php

namespace Michaelbelgium\Mybbtoflarum;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Group\Group;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\User\User;
use Illuminate\Support\Str;

/**
 * Migrator class
 *
 * Connects to a mybb forum and migrates different elements
 */
class Migrator
{
    const FLARUM_AVATAR_PATH = "assets/avatars/";
    private $mybb_path;
    private $count = [
        "users" => 0,
        "groups" => 0,
        "categories" => 0,
        "discussions" => 0,
        "posts" => 0
    ];
    /**
     * @var MyBBExtractor
     */
    private $extractor;

    /**
     * Migrator constructor
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $db
     * @param string $prefix
     * @param string $mybbPath
     */
    public function __construct(string $host, string $user, string $password, string $db, string $prefix, string $mybbPath = '')
    {
        $this->extractor = new MyBBExtractor($host, $user, $password, $db, $prefix, $mybbPath);
    }

    /**
     * Migrate custom user groups
     */
    public function migrateUserGroups()
    {
        $groups = $this->extractor->getGroups();

        Group::where('id', '>', '4')->delete();
        foreach ($groups as $row) {
            $group = Group::build($row->title, $row->title, $this->generateRandomColor(), null);
            $group->id = $row->gid;
            $group->save();

            $this->count["groups"]++;
        }
    }

    /**
     * Generate a random color
     *
     * @return string
     */
    private function generateRandomColor(): string
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Migrate users with their avatars and link them to their group(s)
     *
     * @param bool $migrateAvatars
     * @param bool $migrateWithUserGroups
     */
    public function migrateUsers(bool $migrateAvatars = false, bool $migrateWithUserGroups = false)
    {
        $this->disableForeignKeyChecks();

        $users = $this->extractor->getUsers();
        User::truncate();


        foreach ($users as $row) {
            $newUser = User::register(
                $row->username,
                $row->email,
                password_hash(time(), PASSWORD_BCRYPT)
            );

            $newUser->activate();
            $newUser->id = $row->uid;
            $newUser->joined_at = $row->regdate;
            $newUser->last_seen_at = $row->lastvisit;
            $newUser->discussion_count = $row->threadnum;
            $newUser->comment_count = $row->postnum;

            if ($migrateAvatars && !empty($this->getMybbPath()) && !empty($row->avatar)) {
                $fullpath = $this->getMybbPath() . explode("?", $row->avatar)[0];
                $avatar = basename($fullpath);
                if (file_exists($fullpath)) {
                    if (copy($fullpath, self::FLARUM_AVATAR_PATH . $avatar))
                        $newUser->changeAvatarPath($avatar);
                }
            }

            $newUser->save();

            if ($migrateWithUserGroups) {
                $userGroups = [(int)$row->usergroup];

                if (!empty($row->additionalgroups)) {
                    $userGroups = array_merge(
                        $userGroups,
                        array_map("intval", explode(",", $row->additionalgroups))
                    );
                }

                foreach ($userGroups as $group) {
                    if ($group <= 7) continue;
                    $newUser->groups()->save(Group::find($group));
                }
            }

            $this->count["users"]++;
        }

        $this->enableForeignKeyChecks();
    }

    private function disableForeignKeyChecks()
    {
        app('flarum.db')->statement('SET FOREIGN_KEY_CHECKS = 0');
    }

    private function getMybbPath(): string
    {
        return $this->mybb_path;
    }

    private function enableForeignKeyChecks()
    {
        app('flarum.db')->statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Transform/migrate categories and forums into tags
     */
    public function migrateCategories()
    {
        $categories = $this->extractor->getCategories();
        // FIXME: why are we deleting here instead of truncating?
        Tag::getQuery()->delete();

        foreach ($categories as $row) {
            if (!empty($row->linkto)) continue; //forums with links are not supported in flarum

            $tag = Tag::build($row->name, $this->slugTag($row->name), $row->description, $this->generateRandomColor(), null, false);

            $tag->id = $row->fid;
            $tag->position = (int)$row->disporder - 1;

            if ($row->pid != 0)
                $tag->parent()->associate(Tag::find($row->pid));

            $tag->save();

            $this->count["categories"]++;
        }
    }

    private function slugTag(string $value)
    {
        $slug = Str::slug($value);
        $count = Tag::where('slug', 'LIKE', $slug . '%')->get()->count();

        return $slug . ($count > 0 ? "-$count" : "");
    }

    /**
     * Migrate threads and their posts
     *
     * @param bool $migrateWithUsers Link with migrated users
     * @param bool $migrateWithCategories Link with migrated categories
     * @param bool $migrateSoftDeletedThreads Migrate also soft deleted threads from mybb
     * @param bool $migrateSoftDeletePosts Migrate also soft deleted posts from mybb
     */
    public function migrateDiscussions(bool $migrateWithUsers, bool $migrateWithCategories, bool $migrateSoftDeletedThreads, bool $migrateSoftDeletePosts)
    {
        $threads = $this->extractor->getThreads($migrateSoftDeletedThreads);

        Discussion::getQuery()->delete();
        Post::getQuery()->delete();
        $usersToRefresh = [];

        foreach ($threads as $trow) {
            $tag = Tag::find($trow->fid);

            $discussion = new Discussion();
            $discussion->id = $trow->tid;
            $discussion->title = $trow->subject;

            if ($migrateWithUsers)
                $discussion->user()->associate(User::find($trow->uid));

            $discussion->slug = $this->slugDiscussion($trow->subject);
            $discussion->is_approved = true;
            $discussion->is_locked = $trow->closed == "1";
            $discussion->is_sticky = $trow->sticky;
            if ($trow->visible == -1)
                $discussion->hidden_at = Carbon::now();

            $discussion->save();

            $this->count["discussions"]++;

            if (!in_array($trow->uid, $usersToRefresh) && $trow->uid != 0)
                $usersToRefresh[] = $trow->uid;

            $continue = true;

            if (!is_null($tag) && $migrateWithCategories) {
                do {
                    $tag->discussions()->save($discussion);

                    if (is_null($tag->parent_id))
                        $continue = false;
                    else
                        $tag = Tag::find($tag->parent_id);

                } while ($continue);
            }

            $posts = $this->extractor->getPosts($discussion->id, $migrateSoftDeletePosts);

            $number = 0;
            $firstPost = null;
            foreach ($posts as $prow) {
                $user = User::find($prow->uid);

                $post = CommentPost::reply($discussion->id, $prow->message, optional($user)->id, null);
                $post->created_at = $prow->dateline;
                $post->is_approved = true;
                $post->number = ++$number;
                if ($prow->visible == -1)
                    $post->hidden_at = Carbon::now();

                $post->save();

                if ($firstPost === null)
                    $firstPost = $post;

                if (!in_array($prow->uid, $usersToRefresh) && $user !== null)
                    $usersToRefresh[] = $prow->uid;

                $this->count["posts"]++;
            }

            if ($firstPost !== null)
                $discussion->setFirstPost($firstPost);

            $discussion->refreshCommentCount();
            $discussion->refreshLastPost();
            $discussion->refreshParticipantCount();

            $discussion->save();
        }

        if ($migrateWithUsers) {
            // TODO do this with a single query. or a batched one
            foreach ($usersToRefresh as $userId) {
                $user = User::find($userId);
                $user->refreshCommentCount();
                $user->refreshDiscussionCount();
                $user->save();
            }
        }
    }

    private function slugDiscussion(string $value)
    {
        $slug = Str::slug($value);
        $count = Discussion::where('slug', 'LIKE', $slug . '%')->get()->count();

        return $slug . ($count > 0 ? "-$count" : "");
    }

    public function getProcessedCount()
    {
        return $this->count;
    }
}
