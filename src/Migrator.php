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
			
				// $group->id = $row->gid;
				$group->name_singular = $row->title;
				$group->name_plural = $row->title;
				$group->color = $this->generateRandomColor();

				$group->save();
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