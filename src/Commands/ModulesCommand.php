<?php

namespace DrupalModuleTracker\Commands;

use Robo\Tasks;
use Robo\Robo;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class ModulesCommand
 *
 * @package DrupalModuleTracker\Commands
 */
class ModulesCommand extends Tasks
{

    protected $sites;
    protected $database;
    protected $driver;

    public function __construct()
    {
        $this->sites = Robo::config()->get('sites');
        $db = Robo::config()->get('db');
        $this->driver = $db['driver'];
        switch ($this->driver) {
            case 'sqlite':
                $this->database = new \PDO(
                    'sqlite:' . $db['path'],
                    null,
                    null,
                    array(\PDO::ATTR_PERSISTENT => true)
                );
                break;
            case 'mysql':
                $this->database = new \PDO(
                    'mysql:host=' . $db['host'] .
                    ';port=' . $db['port'] .
                    ';dbname=' . $db['database'],
                    $db['username'],
                    $db['password'],
                    array(\PDO::ATTR_PERSISTENT => true)
                );
                break;
            default:
                throw new \Exception('Incorrect driver');
            break;
        }
        $this->database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Runs the module tracker to update information in the database.
     *
     * @command modules:find
     */
    public function modulesTracker()
    {
        $this->clearTable();
        foreach ($this->sites as $site) {
            $this->say("Working on ${site}");
            $process = new Process(array('drush', "@${site}", 'pml', '--format=json'));
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            $modules = (array) json_decode($output);
            $this->database->beginTransaction();
            foreach ($modules as $module_name => $module) {
                $statement = $this->database->prepare(
                    'INSERT INTO modules(
site, machine_name, display_name, status, version)
VALUES(:site, :machine_name, :display_name, :status, :version)'
                );

                $statement->execute(
                    [
                      'site' => $site,
                      'machine_name' => $module_name,
                      'display_name' => isset($module->display_name) ? $module->display_name : $module->name,
                      'status' => $module->status === 'Enabled' ? 1 : 0,
                      'version' => $module->version,
                      ]
                );
            }
            $this->database->commit();
        }
    }

    /**
     * Clears the DB.
     *
     * @command modules:cleartable
     */
    public function clearTable()
    {
        if (!$this->tableExists('modules')) {
            $this->createTable();
        }
        switch ($this->driver) {
            case 'sqlite':
                $this->database->prepare('DELETE FROM modules')->execute();
                break;
            case 'mysql':
                $this->database->prepare('TRUNCATE modules')->execute();
                break;
        }
    }

    /**
     * Creates the DB table.
     *
     * @command modules:createtable
     */
    public function createTable()
    {
        switch ($this->driver) {
            case 'sqlite':
                $this->database->query(
                    'CREATE TABLE IF NOT EXISTS `modules` (
  `site` varchar(60) NOT NULL DEFAULT "",
  `machine_name` varchar(60) NOT NULL DEFAULT "",
  `display_name` varchar(100) NOT NULL DEFAULT "",
  `status` int(1) NOT NULL,
  `version` varchar(16) DEFAULT ""
)'
                );
                break;
            case 'mysql':
                if (!$this->tableExists('modules')) {
                    $this->database->query(
                        'CREATE TABLE `modules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(60) NOT NULL DEFAULT "",
  `machine_name` varchar(60) NOT NULL DEFAULT "",
  `display_name` varchar(100) NOT NULL DEFAULT "",
  `status` int(1) NOT NULL,
  `version` varchar(16) DEFAULT "",
  PRIMARY KEY (`id`)
)'
                    );
                }
                break;
        }
    }

    /**
     * Check if a table exists in the current database.
     *
     * @param  string $table Table to search for.
     * @return bool TRUE if table exists, FALSE if no table found.
     */
    private function tableExists($table)
    {
        switch ($this->driver) {
            case 'sqlite':
                $tablesquery = $this->database->query(
                    "SELECT name FROM sqlite_master WHERE type='table' AND name='$table';"
                );
                while ($table = $tablesquery->fetch(SQLITE3_ASSOC)) {
                    if ($table['name'] === 'modules') {
                        return true;
                    }
                }
                return false;
            break;
            case 'mysql':
                $results = $this->database->query("SHOW TABLES LIKE '$table'");
                if (!$results) {
                    return false;
                }
                if ($results->rowCount() > 0) {
                    return true;
                }
                break;
        }
    }
}
