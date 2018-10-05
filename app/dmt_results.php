<?php

use Consolidation\Config\Loader\YamlConfigLoader;

function dmt_render()
{
    header("Cache-Control: max-age=86400");
    $header = dmt_header();
    $rows = dmt_rows();
    return '<table>' . $header . '<tbody>' . $rows . '</tbody></table>';
}

function dmt_header()
{
    return '<thead>
            <tr>
              <th>Site</th>
              <th>Module (machine)</th>
              <th>Module (Human)</th>
              <th>Status</th>
              <th>Version</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>Site</th>
              <th>Module (machine)</th>
              <th>Module (Human)</th>
              <th>Status</th>
              <th>Version</th>
            </tr>
            <tr>
            <th colspan="5" class="ts-pager form-horizontal">
              <button type="button" class="btn first"><i class="icon-step-backward glyphicon glyphicon-step-backward"></i></button>
              <button type="button" class="btn prev"><i class="icon-arrow-left glyphicon glyphicon-backward"></i></button>
              <span class="pagedisplay"></span> <!-- this can be any element, including an input -->
              <button type="button" class="btn next"><i class="icon-arrow-right glyphicon glyphicon-forward"></i></button>
              <button type="button" class="btn last"><i class="icon-step-forward glyphicon glyphicon-step-forward"></i></button>
              <select class="pagesize input-mini" title="Select page size">
                <option selected="selected" value="400">400</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="200">200</option>
              </select>
              <select class="pagenum input-mini" title="Select page number"></select>
            </th>
          </tr>
        </tfoot>';
}

function dmt_rows()
{

    $loader = new YamlConfigLoader();
    $config = $loader->load(dirname(__DIR__) . '/config/config.yml');
    $loadedConfig = $config->export();
    $db = $loadedConfig['db'];

    try {
        switch ($db['driver']) {
            case 'sqlite':
                $dbh = new \PDO(
                    'sqlite:' . '../' . $db['path'],
                    null,
                    null,
                    array(\PDO::ATTR_PERSISTENT => true)
                );
                break;
            case 'mysql':
                $dbh = new \PDO(
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


        $sth = $dbh->prepare('SELECT m.site, m.machine_name, m.display_name, m.status, m.version from modules m');

        $sth->execute();
        $rows = '';

        foreach ($sth->fetchAll() as $row) {
            $status = $row['status'] ? 'Enabled' : 'Disabled';
            $rows .= '<tr><td>' . $row['site'] . '</td>';
            $rows .= '<td>' . $row['machine_name'] . '</td>';
            $rows .= '<td>' . $row['display_name'] . '</td>';
            $rows .= '<td>' . $status . '</td>';
            $rows .= '<td>' . $row['version'] . '</td></tr>';
        }
        $dbh = null;
        return $rows;
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}
