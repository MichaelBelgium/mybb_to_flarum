<?php
namespace michaelbelgium\mybbtoflarum;

use Flarum\User\User;
use Flarum\Tags\Tag;
use Flarum\Group\Group;
use Flarum\Util\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migrator
{
	private $connection;
	private $db_prefix;
	private $mybb_path;

	const FLARUM_AVATAR_PATH = "assets/avatars/";

	public function __construct(string $host, string $user, string $password, string $db, string $prefix, string $mybbPath = '') 
	{
		$this->connection = mysqli_connect($host, $user, $password, $db);
		$this->db_prefix = $prefix;
		$this->mybb_path = $mybbPath;
	}

	function __destruct() 
	{
		if(!is_null($this->getMybbConnection()))
        	mysqli_close($this->getMybbConnection());
    }

	public function migrateUserGroups()
	{
		$groups = $this->getMybbConnection()->query("SELECT * FROM {$this->getPrefix()}usergroups WHERE type = 2");

		if($groups->num_rows > 0)
		{
			Group::where('id', '>', '4')->delete();

			while($row = $groups->fetch_object())
			{
				$group = new Group();
			
				$group->id = $row->gid;
				$group->name_singular = $row->title;
				$group->name_plural = $row->title;
				$group->color = $this->generateRandomColor();

				$group->save();
			}
		}
	}

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
						// else
						// 	echo "Warning: could not copy avatar of user id {$row->uid}";
					}
					// else
					// 	echo "Warning: avatar of user id {$row->uid} doesn't exist in the mybb avatar path<br />";
				}

				$newUser->save();

				if($migrateWithUserGroups)
				{
					$userGroups = explode(",", $row->additionalgroups);
					$userGroups[] = (int)$row->usergroup;

					foreach($userGroups as $group)
					{
						if((int)$group <= 7) continue;
						$newUser->groups()->save(Group::find($group));
					}
				}
			}
		}

		$this->enableForeignKeyChecks();
	}

	private function enableForeignKeyChecks()
	{
		app('flarum.db')->statement('SET FOREIGN_KEY_CHECKS = 1');
	}

	private function disableForeignKeyChecks()
	{
		app('flarum.db')->statement('SET FOREIGN_KEY_CHECKS = 0');
	}

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
}