<?php
namespace Michaelbelgium\Mybbtoflarum;

use Flarum\User\User;
use Flarum\Tags\Tag;
use Flarum\Group\Group;
use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Flarum\Util\Str;

/**
 * Migrator class
 * 
 * Connects to a mybb forum and migrates different elements
 */
class Migrator
{
	private $connection;
	private $db_prefix;
	private $mybb_path;
	private $count = [
		"users" => 0,
		"groups" => 0,
		"categories" => 0,
		"discussions" => 0,
		"posts" => 0
	];

	const FLARUM_AVATAR_PATH = "assets/avatars/";

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
		$this->connection = new \mysqli($host, $user, $password, $db);
		$this->connection->set_charset('utf8');
		$this->db_prefix = $prefix;
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
			Group::where('id', '>', '4')->delete();

			while($row = $groups->fetch_object())
			{
				$group = Group::build($row->title, $row->title, $this->generateRandomColor(), null);
				$group->id = $row->gid;
				$group->save();

				$this->count["groups"]++;
			}
		}
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
		
		$users = $this->getMybbConnection()->query("SELECT uid, username, email, postnum, threadnum, FROM_UNIXTIME( regdate ) AS regdate, FROM_UNIXTIME( lastvisit ) AS lastvisit, usergroup, additionalgroups, avatar, lastip FROM {$this->getPrefix()}users");
		
		if($users->num_rows > 0)
		{
			User::truncate();

			while($row = $users->fetch_object())
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
				$newUser->discussion_count = $row->threadnum;
				$newUser->comment_count = $row->postnum;

				if($migrateAvatars && !empty($this->getMybbPath()) && !empty($row->avatar))
				{
					$fullpath = $this->getMybbPath().explode("?", $row->avatar)[0];
					$avatar = basename($fullpath);
					if(file_exists($fullpath))
					{
						if(copy($fullpath,self::FLARUM_AVATAR_PATH.$avatar))
							$newUser->changeAvatarPath($avatar);
					}
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
			Tag::getQuery()->delete();

			while($row = $categories->fetch_object())
			{
				if(!empty($row->linkto)) continue; //forums with links are not supported in flarum

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
	public function migrateDiscussions(bool $migrateWithUsers, bool $migrateWithCategories, bool $migrateSoftDeletedThreads, bool $migrateSoftDeletePosts)
	{
		$threads = $this->getMybbConnection()->query("SELECT tid, fid, subject, FROM_UNIXTIME(dateline) as dateline, uid, firstpost, FROM_UNIXTIME(lastpost) as lastpost, lastposteruid, closed, sticky, visible FROM {$this->getPrefix()}threads");

		if($threads->num_rows > 0)
		{
			Discussion::getQuery()->delete();
			Post::getQuery()->delete();
			$usersToRefresh = [];

			while($trow = $threads->fetch_object())
			{
				if(!$migrateSoftDeletedThreads && $trow->visible == -1) continue;

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

				$posts = $this->getMybbConnection()->query("SELECT pid, tid, FROM_UNIXTIME(dateline) as dateline, uid, message, visible FROM {$this->getPrefix()}posts WHERE tid = {$discussion->id} order by pid");

				$number = 0;
				$firstPost = null;
				while($prow = $posts->fetch_object())
				{
					if(!$migrateSoftDeletePosts && $prow->visible == -1) continue;

					$user = User::find($prow->uid);

					$post = CommentPost::reply($discussion->id, $prow->message, optional($user)->id, null);
					$post->created_at = $prow->dateline;
					$post->is_approved = true;
					$post->number = ++$number;

					$post->save();

					if(is_null($firstPost))
						$firstPost = $post;

					if(!in_array($prow->uid, $usersToRefresh) && $user !== null)
						$usersToRefresh[] = $prow->uid;

					$this->count["posts"]++;					
				}

                if (is_null($firstPost)) {
                    $discussion->delete();
                } else {
                    $discussion->setFirstPost($firstPost);
                    $discussion->refreshCommentCount();
                    $discussion->refreshLastPost();
                    $discussion->refreshParticipantCount();

                    $discussion->save();
                }
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

	private function getPrefix(): string
	{
		return $this->db_prefix;
	}

	private function getMybbPath(): string
	{
		return $this->mybb_path;
	}

	private function escapeString(string $source): string
	{
		return $this->connection->escape_string($source);
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
