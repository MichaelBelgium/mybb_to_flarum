<?php
namespace Michaelbelgium\Mybbtoflarum;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Flarum\User\User;
use Flarum\Tags\Tag;
use Flarum\Group\Group;
use Flarum\Discussion\Discussion;
use Flarum\Http\UrlGenerator;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * Migrator class
 *
 * Connects to a mybb forum and migrates different elements
 */
class Migrator
{
    private MyBB16Extractor|MyBBExtractor $extractor;
    private string $mybb_path;
    private array $count = [
        "users" => 0,
        "groups" => 0,
        "categories" => 0,
        "discussions" => 0,
        "posts" => 0,
        "attachments" => 0,
    ];

    private array $offsets = [
        "users" => 0,
        "groups" => 0,
        "categories" => 0,
        "discussions" => 0,
        // Posts and Attachments are part of each discussion
    ];

    const FLARUM_AVATAR_PATH = "public/assets/avatars/";
    const FLARUM_UPLOAD_PATH = "public/assets/files/";

    private Logger $logger;
    private ?Filesystem $countsDir = null;
    private string $countsPath;

    /**
     * Migrator constructor
     *
     * @param Logger $logger
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $db
     * @param string $prefix
     * @param string $mybbPath
     * @param array|null $offsetsCount
     */
    public function __construct(Logger $logger, string $host, string $user, string $password, string $db, string $prefix, string $mybbPath = '', array $offsetsCount = null)
    {
        $this->extractor = new MyBB16Extractor($host,$user,$password,$db,$prefix,$mybbPath);

        if(!str_ends_with($mybbPath, '/'))
            $mybbPath .= '/';

        $this->mybb_path = $mybbPath;

        $this->logger = $logger;
        if($offsetsCount !== null){
            $this->offsets = $offsetsCount;
        }
    }

    function __destruct()
    {

    }

    public function setCountsDir( Filesystem $dir, string $path): void
    {
        $this->countsDir = $dir;
        $this->countsPath = $path;
    }

    public function saveCounts(): bool
    {
        return $this->countsDir->put($this->countsPath, json_encode($this->count));
    }

    /**
     * Migrate custom user groups
     */
    public function migrateUserGroups(): void
    {
        if($this->offsets['groups'] === 0 ) { //only delete if we're not resuming
            Group::where('id', '>', '4')->delete();
        } else {
            $this->logger->info("restarting categories migration from position {$this->offsets['categories']}");
        }

        $groups = $this->extractor->getGroups($this->offsets['groups']);
        foreach($groups as $row)
        {
            $group = Group::build($row->title, $row->title, $this->generateRandomColor(), null);
            $group->id = $row->gid;
            $group->save();

            $this->count["groups"]++;
            $this->saveCounts();
        }
        $this->logger->info("migrated {$this->count['groups']} groups");

    }

    /**
     * Migrate users with their avatars and link them to their group(s)
     *
     * @param bool $migrateAvatars
     * @param bool $migrateWithUserGroups
     */
    public function migrateUsers(bool $migrateAvatars = false, bool $migrateWithUserGroups = false): void
    {
        $this->disableForeignKeyChecks();

        if($this->offsets['users'] === 0 ) { //only delete if we're not resuming
            User::truncate();
        } else {
            $this->logger->info("restarting users migration from position {$this->offsets['users']}");
        }

        $users = $this->extractor->getUsers($this->offsets['users']);

        foreach($users as $row)
        {
            $newUser = User::register(
                $row->username,
                $row->email,
                password_hash(time(), PASSWORD_BCRYPT)
            );

            $newUser->activate();
            $newUser->id = $row->uid;
            $newUser->joined_at = $row->regdate;
            $newUser->last_seen_at = $row->lastvisit;
            $newUser->comment_count = $row->postnum;

            if($migrateAvatars && !empty($this->getMybbPath()) && !empty($row->avatar))
            {
                $fullpath = $this->getMybbPath().explode("?", $row->avatar)[0];
                $avatar = basename($fullpath);
                if(file_exists($fullpath))
                {
                    if(!file_exists(self::FLARUM_AVATAR_PATH))
                        mkdir(self::FLARUM_AVATAR_PATH, 0777, true);

                    if(copy($fullpath,self::FLARUM_AVATAR_PATH.$avatar))
                        $newUser->changeAvatarPath($avatar);
                }
            }

            $newUser->save();
            $newUser->refreshDiscussionCount();
            if($migrateWithUserGroups)
            {
                $userGroups = [(int)$row->usergroup];

                if(!empty($row->additionalgroups))
                {
                    $userGroups = array_merge(
                        $userGroups,
                        array_map("intval", explode(",", $row->additionalgroups))
                    );
                }

                foreach($userGroups as $group)
                {
                    if($group <= 7) continue;
                    $newUser->groups()->save(Group::find($group));
                }
            }
            $this->logger->debug("migrated user with id: {$newUser->id}");
            $this->count["users"]++;
            $this->saveCounts();
        }

        $this->enableForeignKeyChecks();
    }

    /**
     * Transform/migrate categories and forums into tags
     */
    public function migrateCategories(): void
    {
        if($this->offsets['categories'] === 0 ) { //only delete if we're not resuming
            Tag::getQuery()->delete();
        } else {
            $this->logger->info("restarting categories migration from position {$this->offsets['categories']}");
        }

        $categories = $this->extractor->getCategories($this->offsets['categories']);

        foreach($categories as $row)
        {
            if(!empty($row->linkto)) continue; //forums with links are not supported in flarum

            $tag = Tag::build($row->name, $this->slugTag($row->name), $row->description, $this->generateRandomColor(), null, false);

            $tag->id = $row->fid;
            $tag->position = (int)$row->disporder - 1;

            if($row->pid != 0)
                $tag->parent()->associate(Tag::find($row->pid));

            $tag->save();

            $this->count["categories"]++;
            $this->saveCounts();
        }
    }

    /**
     * Migrate threads and their posts
     *
     * @param bool $migrateWithUsers Link with migrated users
     * @param bool $migrateWithCategories Link with migrated categories
     * @param bool $migrateSoftDeletedThreads Migrate also soft deleted threads from mybb
     * @param bool $migrateSoftDeletePosts Migrate also soft deleted posts from mybb
     */
    public function migrateDiscussions(
        bool $migrateWithUsers, bool $migrateWithCategories, bool $migrateSoftDeletedThreads,
        bool $migrateSoftDeletePosts, bool $migrateAttachments
    ): void
    {
        $migrateAttachments = class_exists('FoF\Upload\File') && $migrateAttachments;

        /** @var UrlGenerator $generator */
        $generator = resolve(UrlGenerator::class);

        if($this->offsets['discussions'] === 0 ) { //only delete if we're not resuming
            Discussion::getQuery()->delete();
            Post::getQuery()->delete();
        } else {
            $this->logger->info("restarting discussions migration from position {$this->offsets['discussions']}");
        }

        $threads = $this->extractor->getThreads($migrateSoftDeletedThreads, $this->offsets['discussions']);

        $usersToRefresh = [];

        foreach($threads as $trow)
        {
            $tag = Tag::find($trow->fid);

            $discussion = new Discussion();
            $discussion->id = $trow->tid;
            $discussion->title = $trow->subject;

            if($migrateWithUsers)
                $discussion->user()->associate(User::find($trow->uid));

            $discussion->slug = $this->slugDiscussion($trow->subject);
            $discussion->is_approved = true;
            $discussion->is_locked = $trow->closed == "1";
            $discussion->is_sticky = $trow->sticky;
            if($trow->visible == -1)
                $discussion->hidden_at = Carbon::now();

            $discussion->save();

            $this->count["discussions"]++;

            if(!in_array($trow->uid, $usersToRefresh) && $trow->uid != 0)
                $usersToRefresh[] = $trow->uid;

            $continue = true;

            if(!is_null($tag) && $migrateWithCategories)
            {
                do {
                    $tag->discussions()->save($discussion);

                    if(is_null($tag->parent_id))
                        $continue = false;
                    else
                        $tag = Tag::find($tag->parent_id);

                } while($continue);
            }

            $posts = $this->extractor->getPosts($discussion->id,$migrateSoftDeletePosts);

            $number = 0;
            $firstPost = null;
            foreach($posts as $prow)
            {
                $user = User::find($prow->uid);

                $post = CommentPost::reply($discussion->id, $prow->message, optional($user)->id, null);
                $post->created_at = $prow->dateline;
                $post->is_approved = true;
                $post->number = ++$number;
                if($prow->visible == -1)
                    $post->hidden_at = Carbon::now();

                $post->save();

                if($firstPost === null)
                    $firstPost = $post;

                if(!in_array($prow->uid, $usersToRefresh) && $user !== null)
                    $usersToRefresh[] = $prow->uid;

                $this->count["posts"]++;

                if($migrateAttachments)
                {
                    $attachments = $this->extractor->getAttachments($prow->pid);

                    foreach ($attachments as $arow)
                    {
                        $filePath = $this->getMybbPath().'uploads/'.$arow->attachname;
                        $toFilePath = self::FLARUM_UPLOAD_PATH.$arow->attachname;
                        $dirFilePath = dirname($toFilePath);

                        if(!file_exists($dirFilePath))
                            mkdir($dirFilePath, 0777, true);

                        if(!copy($filePath,$toFilePath)) continue;

                        $uploader = User::find($arow->uid);
                        $fileTemplate = new \FoF\Upload\Templates\FileTemplate();

                        $file = new \FoF\Upload\File();
                        $file->post()->associate($post);
                        $file->discussion()->associate($post->discussion);
                        $file->actor()->associate($uploader);
                        $file->base_name = $arow->filename;
                        $file->path = $arow->attachname;
                        $file->type = $arow->filetype;
                        $file->size = (int)$arow->filesize;
                        $file->upload_method = 'local';
                        $file->url = $generator->to('forum')->path('assets/files/'.$arow->attachname);
                        $file->uuid = Uuid::uuid4()->toString();
                        $file->tag = $fileTemplate;
                        $file->save();

                        $file->post->content = $file->post->content . $fileTemplate->preview($file);
                        $file->post->save();

                        $this->count["attachments"]++;
                    }
                }
            }

            if($firstPost !== null)
                $discussion->setFirstPost($firstPost);

            $discussion->refreshCommentCount();
            $discussion->refreshLastPost();
            $discussion->refreshParticipantCount();

            $discussion->save();
        }

        if($migrateWithUsers)
        {
            foreach ($usersToRefresh as $userId)
            {
                $user = User::find($userId);
                $user->refreshCommentCount();
                $user->refreshDiscussionCount();
                $user->save();
            }
        }
    }

    private function enableForeignKeyChecks()
    {
        app('flarum.db')->statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function disableForeignKeyChecks()
    {
        app('flarum.db')->statement('SET FOREIGN_KEY_CHECKS = 0');
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

    private function getMybbPath(): string
    {
        return $this->mybb_path;
    }

    private function slugTag(string $value)
    {
        $slug = Str::slug($value);
        $count = Tag::where('slug', 'LIKE', $slug . '%')->get()->count();

        return $slug . ($count > 0 ? "-$count" : "");
    }

    private function slugDiscussion(string $value)
    {
        $slug = Str::slug($value);
        $count = Discussion::where('slug', 'LIKE', $slug . '%')->get()->count();

        return $slug . ($count > 0 ? "-$count": "");
    }

    public function getProcessedCount()
    {
        return $this->count;
    }
}
