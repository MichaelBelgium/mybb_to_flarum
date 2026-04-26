<?php
namespace Michaelbelgium\Mybbtoflarum;

use Carbon\Carbon;
use Flarum\Tags\Tag;
use Flarum\User\User;
use Flarum\Group\Group;
use Flarum\Discussion\Discussion;
use Flarum\Http\UrlGenerator;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Migrator class
 * 
 * Connects to a mybb forum and migrates different elements
 */
class Migrator
{
    private \mysqli $connection;
    private string $db_prefix;
    private string $mybb_path;
    /** @var array<int> $count */
    private array $count = [
        "users" => 0,
        "groups" => 0,
        "categories" => 0,
        "discussions" => 0,
        "posts" => 0,
        "attachments" => 0,
    ];

    const string FLARUM_AVATAR_PATH = "assets/avatars/";
    const string FLARUM_UPLOAD_PATH = "assets/files/";

    public function __construct(string $host, string $user, string $password, string $db, string $prefix, string $mybbPath = '', private ?LoggerInterface $logger = null)
    {
        $this->connection = new \mysqli($host, $user, $password, $db);
        $this->connection->set_charset('utf8');
        $this->db_prefix = $prefix;

        if(!str_ends_with($mybbPath, '/'))
            $mybbPath .= '/';
        
        $this->mybb_path = $mybbPath;
    }

    function __destruct() 
    {
        if(!is_null($this->getMybbConnection()))
            $this->getMybbConnection()->close();
    }

    /**
     * Migrate custom user groups
     */
    public function migrateUserGroups()
    {
        $groups = $this->getMybbConnection()->query("SELECT * FROM {$this->getPrefix()}usergroups WHERE type = 2");

        if($groups->num_rows > 0)
        {
            $this->logger?->debug("=== Starting user group migration (Amount: {$groups->num_rows}) ===");
            Group::where('id', '>', '4')->delete();

            while($row = $groups->fetch_object())
            {
                $this->logger?->debug("Migrating group: {$row->title} (ID: {$row->gid})");

                $group = new Group();
                $group->id = $row->gid;
                $group->name_singular = $row->title;
                $group->name_plural = $row->title;
                $group->color = $this->generateRandomColor();
                $group->save();

                $this->logger?->debug("Group '{$row->gid}' migrated successfully.");

                $this->count["groups"]++;
            }
        }
        else
            $this->logger->debug("No user groups found to migrate.");
    }

    /**
     * Migrate users with their avatars and link them to their group(s)
     */
    public function migrateUsers(bool $migrateAvatars = false, bool $migrateWithUserGroups = false)
    {
        $this->disableForeignKeyChecks();
        
        $users = $this->getMybbConnection()->query("SELECT uid, username, email, postnum, threadnum, FROM_UNIXTIME( regdate ) AS regdate, FROM_UNIXTIME( lastvisit ) AS lastvisit, usergroup, additionalgroups, avatar FROM {$this->getPrefix()}users");
        
        if($users->num_rows > 0)
        {
            $this->logger?->debug("=== Starting user migration (Amount: {$users->num_rows}) ===");
            User::truncate();

            while($row = $users->fetch_object())
            {
                $this->logger?->debug("Migrating user: {$row->username} (ID: {$row->uid})");

                $newUser = new User();
                $newUser->username = $row->username;
                $newUser->email = $row->email;
                $newUser->password = '';

                $newUser->activate();
                $newUser->id = $row->uid;
                $newUser->joined_at = $row->regdate;
                $newUser->last_seen_at = $row->lastvisit;
                $newUser->discussion_count = $row->threadnum;
                $newUser->comment_count = $row->postnum;

                if($migrateAvatars && !empty($this->getMybbPath()) && !empty($row->avatar))
                {
                    $fullpath = $this->getMybbPath() . explode("?", $row->avatar)[0];
                    $avatar = basename($fullpath);
                    $this->logger?->debug("Attempting to migrate avatar for user ID {$row->uid} from path: {$fullpath} to " . self::FLARUM_AVATAR_PATH . $avatar);

                    if(file_exists($fullpath))
                    {
                        if(!file_exists(self::FLARUM_AVATAR_PATH))
                            mkdir(self::FLARUM_AVATAR_PATH, 0777, true);

                        if(copy($fullpath,self::FLARUM_AVATAR_PATH.$avatar))
                        {
                            $newUser->changeAvatarPath($avatar);
                            $this->logger?->debug("Avatar for user ID {$row->uid} migrated successfully.");
                        }
                        else
                            $this->logger?->debug("Failed to copy avatar for user ID {$row->uid}");
                    }
                    else
                        $this->logger?->debug("Avatar file for user ID {$row->uid} not found at path: {$fullpath}");
                }

                $newUser->save();

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
                        $this->logger?->debug("Associating user ID {$row->uid} with Flarum group ID: {$group}");
                        $newUser->groups()->save(Group::find($group));
                    }
                }

                $this->count["users"]++;
            }
        }

        $this->enableForeignKeyChecks();
    }

    /**
     * Transform/migrate categories and forums into tags
     */
    public function migrateCategories()
    {
        $categories = $this->getMybbConnection()->query("SELECT fid, name, description, linkto, disporder, pid FROM {$this->getPrefix()}forums order by fid");

        if($categories->num_rows > 0)
        {
            $this->logger?->debug("=== Starting category migration (Amount: {$categories->num_rows}) ===");
            Tag::getQuery()->delete();

            while($row = $categories->fetch_object())
            {
                if(!empty($row->linkto)) continue; //forums with links are not supported in flarum

                $this->logger?->debug("Migrating category/forum: {$row->name} (ID: {$row->fid})");

                $tag = Tag::build($row->name, $this->slugTag($row->name), $row->description, $this->generateRandomColor(), null, false);
                $tag->id = $row->fid;
                $tag->position = (int)$row->disporder - 1;

                if($row->pid != 0)
                    $tag->parent()->associate(Tag::find($row->pid));

                $tag->save();

                $this->count["categories"]++;
            }
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
    ) {
        /** @var UrlGenerator $generator */
        $generator = resolve(UrlGenerator::class);
            
        $query = "SELECT tid, fid, subject, FROM_UNIXTIME(dateline) as dateline, uid, firstpost, FROM_UNIXTIME(lastpost) as lastpost, lastposteruid, closed, sticky, visible FROM {$this->getPrefix()}threads";
        if(!$migrateSoftDeletedThreads)
        {
            $query .= " WHERE visible != -1";
        }

        $threads = $this->getMybbConnection()->query($query);

        if($threads->num_rows > 0)
        {
            $this->logger?->debug("=== Starting discussion migration (Amount: {$threads->num_rows}) ===");

            Discussion::getQuery()->delete();
            Post::getQuery()->delete();
            $usersToRefresh = [];

            while($trow = $threads->fetch_object())
            {
                $this->logger?->debug("Migrating thread: {$trow->subject} (ID: {$trow->tid})");

                $tag = Tag::find($trow->fid);

                $discussion = new Discussion();
                $discussion->id = $trow->tid;
                $discussion->title = $trow->subject;

                if($migrateWithUsers && $trow->uid != 0)
                {
                    $discussion->user()->associate(User::find($trow->uid));
                    $this->logger?->debug("Associating discussion ID {$trow->tid} with user ID {$trow->uid}");
                }

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
                        $this->logger?->debug("Associating discussion ID {$trow->tid} with tag ID {$tag->id}");
    
                        if(is_null($tag->parent_id))
                            $continue = false;
                        else
                            $tag = Tag::find($tag->parent_id);
                        
                    } while($continue);
                }

                $query = "SELECT pid, tid, FROM_UNIXTIME(dateline) as dateline, uid, message, visible FROM {$this->getPrefix()}posts WHERE tid = {$discussion->id}";
                if(!$migrateSoftDeletePosts)
                {
                    $query .= " AND visible != -1";
                }
                $query .= " order by pid";

                $posts = $this->getMybbConnection()->query($query);

                $this->logger?->debug("=== Starting posts migration for thread {$trow->tid} (Amount: {$posts->num_rows}) ===");

                $number = 0;
                $firstPost = null;
                while($prow = $posts->fetch_object())
                {
                    $this->logger?->debug("Migrating post ID {$prow->pid} for discussion/thread ID {$trow->tid}");
                    $user = User::find($prow->uid);

                    $post = new CommentPost();
                    $post->discussion_id = $discussion->id;
                    $post->user_id = $user?->id;
                    $post->setContentAttribute($prow->message);
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
                        $attachments = $this->getMybbConnection()->query("SELECT * FROM {$this->getPrefix()}attachments WHERE pid = {$prow->pid}");

                        $this->logger?->debug("=== Starting attachments migration for post ID {$prow->pid} (Amount: {$attachments->num_rows}) ===");

                        while ($arow = $attachments->fetch_object())
                        {
                            $filePath = $this->getMybbPath().'uploads/'.$arow->attachname;
                            $toFilePath = self::FLARUM_UPLOAD_PATH.$arow->attachname;
                            $dirFilePath = dirname($toFilePath);

                            $this->logger?->debug("Migrating attachment ID {$arow->aid} for post ID {$prow->pid} from path: {$filePath} to {$toFilePath}");

                            if(!file_exists($dirFilePath))
                                mkdir($dirFilePath, 0777, true);

                            if(!copy($filePath,$toFilePath))
                            {
                                $this->logger?->debug("Failed to copy attachment ID {$arow->aid} for post ID {$prow->pid}");
                                continue;
                            }

                            $uploader = User::find($arow->uid);

                            if (str_starts_with($arow->filetype, 'image/'))
                                $fileTemplate = resolve(\FoF\Upload\Templates\ImageTemplate::class);
                            else
                                $fileTemplate = resolve(\FoF\Upload\Templates\FileTemplate::class);

                            $file = new \FoF\Upload\File();
                            $file->posts()->save($post);
                            $file->actor()->associate($uploader);
                            $file->base_name = $arow->filename;
                            $file->path = $arow->attachname;
                            $file->type = $arow->filetype;
                            $file->size = (int)$arow->filesize;
                            $file->upload_method = 'local';
                            $file->url = $generator->to('forum')->path($toFilePath);
                            $file->uuid = Uuid::uuid4()->toString();
                            $file->tag = $fileTemplate;
                            $file->save();

                            $post->setContentAttribute($post->content . $fileTemplate->preview($file));
                            $post->save();

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
    }

    private function enableForeignKeyChecks()
    {
        app('flarum.db')->statement('SET FOREIGN_KEY_CHECKS = 1');
        $this->logger?->debug("Foreign key checks enabled.");
    }

    private function disableForeignKeyChecks()
    {
        app('flarum.db')->statement('SET FOREIGN_KEY_CHECKS = 0');
        $this->logger?->debug("Foreign key checks disabled.");
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

    private function getPrefix(): string
    {
        return $this->db_prefix;
    }

    private function getMybbPath(): string
    {
        return $this->mybb_path;
    }

    private function getMybbConnection()
    {
        return $this->connection;
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
