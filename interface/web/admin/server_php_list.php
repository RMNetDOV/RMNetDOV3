<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/server_php.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('admin');

$app->uses('listform_actions');

$app->listform_actions->SQLOrderBy = "ORDER BY server_php.server_id, server_php.name";

$app->listform_actions->SQLExtSelect = "(SELECT
    COUNT(w.server_id)
    FROM
    server_php s LEFT JOIN web_domain w ON (w.server_php_id = s.server_php_id AND s.server_id=w.server_id)
    WHERE
    server_php.server_php_id=s.server_php_id
    GROUP BY
    server_php.server_php_id
) AS 'usage'";

$app->listform_actions->onLoad();

