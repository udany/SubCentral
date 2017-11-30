<?php

/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 27/03/2017
 * Time: 17:35
 */
class MigrationManager {
	private static $dir = '_database';

	public static function Check($project) {
		$migrationsDir = GetProjectDirectory($project) . self::$dir . '/migrations/';
		$logsDir = GetProjectDirectory($project) . self::$dir . '/logs/';

		if (!file_exists($migrationsDir)) FileSystem::CreateDirectory($migrationsDir);
		if (!file_exists($logsDir)) FileSystem::CreateDirectory($logsDir);

		$migrations = glob($migrationsDir.'*.php');
		$logs       = glob($logsDir.'*.txt');

		$migrations = array_map(function ($e) use($migrationsDir){ return str_ireplace([$migrationsDir, '.php'], '', $e); }, $migrations);
		$logs       = array_map(function ($e) use($logsDir){ return str_ireplace([$logsDir, '.txt'], '', $e); }, $logs);

		$migrationsToRun = array_filter($migrations, function ($e) use ($logs){ return !in_array($e, $logs); });

		foreach ($migrationsToRun as $migration){
			$file = $migrationsDir.$migration.'.php';

			try {
				include_once($file);
			}catch (Exception $e){
				Log::Exception($e, null, true);

				throw new Exception('Failed to run migration: '.$migration);
			}

			FileSystem::Write($logsDir.$migration.'.txt', '1');
		}
	}
}