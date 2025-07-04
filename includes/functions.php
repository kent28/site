<?php
$connections = array('account' => false, 'game' => false);

function selectDB($db) {
    global $connections, $config;
    if (!isset($config['db'][$db])) {
        die('Критическая ошибка: Конфигурация базы данных не найдена');
    }
    $dbConfig = $config['db'][$db];
    if (!isset($dbConfig['host']) || !isset($dbConfig['user']) || !isset($dbConfig['pass']) || !isset($dbConfig['db'])) {
        die('Критическая ошибка: Неполная конфигурация базы данных');
    }
    if ($connections[$db] === false) {
        try {
            $dsn = "sqlsrv:Server={$dbConfig['host']};Database={$dbConfig['db']};TrustServerCertificate=true";
            $connections[$db] = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
            $connections[$db]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            echo '<b>Критическая ошибка</b>: Не удалось подключиться к базе данных!<br />';
            if (!empty($msg)) {
                echo 'Ошибка: ' . htmlspecialchars($msg);
            } else {
                if (trim(strtolower($dbConfig['host'])) == '(local)') {
                    $dbConfig['host'] = 'localhost';
                }
                $file = @fsockopen($dbConfig['host'], 80, $errno, $errstr, 10);
                if (!$file) {
                    $status = -1;
                } else {
                    $status = 0;
                    fclose($file);
                }
                if ($status == -1) {
                    echo 'Ошибка: #' . $errno . ', ' . htmlspecialchars($errstr);
                } else {
                    echo 'Ошибка: Пожалуйста, проверьте, запущена ли служба MSSQL и доступна ли она (фаервол и т.д.).';
                }
            }
            if (defined('DEBUG') && DEBUG) {
                echo '<br /><br /><b>Детали подключения</b>:<br /><br />';
                echo '<table width="400">';
                echo '<tr><td>Хост:</td><td>' . htmlspecialchars($dbConfig['host']) . '</td></tr>';
                echo '<tr><td>Пользователь:</td><td>' . htmlspecialchars($dbConfig['user']) . '</td></tr>';
                echo '<tr><td>Пароль:</td><td>' . htmlspecialchars($dbConfig['pass']) . '</td></tr>';
                echo '<tr><td>База данных:</td><td>' . htmlspecialchars($dbConfig['db']) . '</td></tr>';
                echo '</table>';
            }
            die('');
        }
    }
}

function doQuery($query, $db = '') {
    global $connections;
    if ($db !== '') {
        selectDB($db);
    }
    try {
        return $connections[$db]->query($query);
    } catch (PDOException $e) {
        if (defined('DEBUG') && DEBUG) {
            $fh = @fopen(BASEDIR . 'data' . DIRECTORY_SEPARATOR . 'dblog.txt', 'a');
            @fputs($fh, $query . "\n");
            @fputs($fh, "\n" . 'ОШИБКА: ' . $e->getMessage() . "\n");
            @fputs($fh, "\n\n" . str_repeat('=', 80) . "\n\n\n");
            @fclose($fh);
        }
        return false;
    }
}

function getInsert($table, $data) {
    $fields = array();
    $values = array();

    foreach ($data as $curdata) {
        $fields[] = $curdata['field'];
        switch (strtolower($curdata['type'])) {
            case 'i':
                $values[] = (int)$curdata['value'];
                break;
            case 's':
                $values[] = '\'' . addslashes_mssql($curdata['value']) . '\'';
                break;
            case 'd':
                $values[] = 'CONVERT(datetime, \'' . date('Y-m-d H:i:s', (int)$curdata['value']) . '\', 120)';
                break;
        }
    }

    return 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
}

function addslashes_mssql($str, $inlike = false, $escape = '!') {
    if (is_array($str)) {
        foreach ($str as $id => $value) {
            $str[$id] = addslashes_mssql($value, $inlike);
        }
    } else {
        $str = str_replace("'", "''", $str);
        if ($inlike) {
            $str = str_replace($escape, $escape . $escape, $str);
            $str = str_replace('%', $escape . '%', $str);
            $str = str_replace('[', $escape . '[', $str);
            $str = str_replace(']', $escape . ']', $str);
            $str = str_replace('_', $escape . '_', $str);
        }
    }
    return $str;
}

function validAccountname($username) {
    // Проверка, что имя пользователя содержит только разрешенные символы и имеет длину от 5 до 20 символов
    return preg_match('/^[a-zA-Z0-9_]{5,20}$/', $username);
}

function validPassword($password) {
    $errors = [];

    // Проверка длины пароля
    if (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать как минимум 8 символов.';
    }

    // Проверка наличия букв в разных регистрах
    if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Пароль должен содержать буквы в верхнем и нижнем регистре.';
    }

    // Если есть ошибки, вернуть их
    if (!empty($errors)) {
        return implode(' ', $errors);
    }

    // Если ошибок нет, пароль допустим
    return true;
}

function validEMail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function checkDuplicateAccountName($username) {
    $query = doQuery("SELECT COUNT(*) FROM account_login WHERE name = '{$username}'", 'account');
    return $query->fetchColumn() > 0;
}

function checkDuplicateEMail($email) {
    $query = doQuery("SELECT COUNT(*) FROM account_login WHERE email = '{$email}'", 'account');
    return $query->fetchColumn() > 0;
}

function createAccount($username, $password, $email) {
    global $connections, $config;

    $data = [
        ['field' => 'name', 'value' => $username, 'type' => 's'],
        ['field' => 'password', 'value' => strtoupper(md5($password)), 'type' => 's'],
        ['field' => 'originalpassword', 'value' => $password, 'type' => 's'],
        ['field' => 'sid', 'value' => 0, 'type' => 'i'],
        ['field' => 'login_status', 'value' => 0, 'type' => 'i'],
        ['field' => 'enable_login_tick', 'value' => 0, 'type' => 'i'],
        ['field' => 'login_group', 'value' => '', 'type' => 's'],
        ['field' => 'last_login_time', 'value' => time(), 'type' => 'd'],
        ['field' => 'last_logout_time', 'value' => time(), 'type' => 'd'],
        ['field' => 'last_login_ip', 'value' => '', 'type' => 's'],
        ['field' => 'enable_login_time', 'value' => time(), 'type' => 'd'],
        ['field' => 'total_live_time', 'value' => 0, 'type' => 'i'],
        ['field' => 'last_login_mac', 'value' => '', 'type' => 's'],
        ['field' => 'ban', 'value' => 0, 'type' => 'i'],
        ['field' => 'donat', 'value' => 0, 'type' => 'i'],
        ['field' => 'email', 'value' => $email, 'type' => 's']
    ];

    $sql = getInsert(TABLE_ACCOUNT_LOGIN, $data);
    error_log("Generated SQL: $sql");

    try {
        $query = doQuery($sql, DATABASE_ACCOUNT);
        if ($query !== false) {
            error_log("First query executed successfully");
            if ($config['max_compatibility_mode']) {
                $query = doQuery('SELECT id FROM ' . TABLE_ACCOUNT_LOGIN . ' WHERE name = \'' . addslashes_mssql($username) . '\'', DATABASE_ACCOUNT);
            } else {
                $query = doQuery('SELECT (MAX(act_id) + 1) AS id FROM ' . TABLE_ACCOUNT, DATABASE_GAME);
            }
            if ($query !== false) {
                error_log("Second query executed successfully");
                $row = $query->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $id = (int)$row['id'];
                    error_log("Fetched ID: $id");
                    $gm = $config['is_gm_server'] ? (int)$config['new_gm_level'] : 0;
                    $gm = max(0, min($gm, 99));
                    if ($id > 0) {
                        $insertQuery = 'INSERT INTO account (act_id, act_name, gm) VALUES (' . $id . ',\'' . addslashes_mssql($username) . '\',' . $gm . ')';
                        error_log("Insert query: $insertQuery");
                        $query = doQuery($insertQuery, DATABASE_GAME);
                        if ($query !== false) {
                            error_log("Account created successfully");
                            return true;
                        } else {
                            error_log("Failed to insert into account table");
                        }
                    } else {
                        error_log("Invalid ID fetched");
                    }
                } else {
                    error_log("Failed to fetch ID");
                }
            } else {
                error_log("Second query failed");
            }
        } else {
            $error = true;
            $errorMessage = $query->errorInfo()[2];
            error_log("First query failed: $errorMessage");
        }
    } catch (PDOException $e) {
        error_log("Ошибка при создании аккаунта: " . $e->getMessage());
        return false;
    }
    return false;
}

function loginUser($username, $password) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $query = doQuery(
        'SELECT password FROM ' . TABLE_ACCOUNT_LOGIN .
        ' WHERE name = \'' . addslashes_mssql($username) . '\'',
        DATABASE_ACCOUNT
    );

    if ($query !== false) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row && strtoupper(md5($password)) === $row['password']) {
            $_SESSION['user'] = $username;
            return true;
        }
    }

    return false;
}

function isUserLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user']);
}

function logoutUser($username) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    doQuery('UPDATE ' . TABLE_ACCOUNT_LOGIN . ' SET last_logout_time = ' . time() . " WHERE name = '" . addslashes_mssql($username) . "'", DATABASE_ACCOUNT);
    unset($_SESSION['user']);
    session_destroy();
    return true;
}

function getAccountInfo($username) {
    $query = doQuery("SELECT * FROM " . TABLE_ACCOUNT_LOGIN . " WHERE name = '" . addslashes_mssql($username) . "'", DATABASE_ACCOUNT);
    if ($query !== false) {
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

function getAccountCharacters($username) {
    $sql = 'SELECT act_id, cha_ids FROM ' . TABLE_ACCOUNT . ' WHERE act_name = \'' . addslashes_mssql($username) . '\'';
    $query = doQuery($sql, DATABASE_GAME);
    if ($query !== false) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $names = [];

            if (!empty($row['cha_ids'])) {
                $ids = array_filter(array_map('intval', explode(',', $row['cha_ids'])));
                if (!empty($ids)) {
                    $idList = implode(',', $ids);
                    $charQuery = doQuery('SELECT cha_name FROM ' . TABLE_CHARACTERS . ' WHERE cha_id IN (' . $idList . ')', DATABASE_GAME);
                    if ($charQuery !== false) {
                        while ($charRow = $charQuery->fetch(PDO::FETCH_ASSOC)) {
                            $names[] = $charRow['cha_name'];
                        }
                    }
                }
            }

            if (empty($names) && isset($row['act_id'])) {
                $actId = (int)$row['act_id'];
                $charQuery = doQuery('SELECT cha_name FROM ' . TABLE_CHARACTERS . ' WHERE act_id = ' . $actId, DATABASE_GAME);
                if ($charQuery !== false) {
                    while ($charRow = $charQuery->fetch(PDO::FETCH_ASSOC)) {
                        $names[] = $charRow['cha_name'];
                    }
                }
            }

            return $names;
        }
    }
    return [];
}

function updateAccountEmail($username, $email) {
    $query = doQuery("UPDATE " . TABLE_ACCOUNT_LOGIN . " SET email = '" . addslashes_mssql($email) . "' WHERE name = '" . addslashes_mssql($username) . "'", DATABASE_ACCOUNT);
    return $query !== false;
}

function updateAccountPassword($username, $password) {
    $hash = strtoupper(md5($password));
    $query = doQuery("UPDATE " . TABLE_ACCOUNT_LOGIN . " SET password = '" . $hash . "', originalpassword = '" . addslashes_mssql($password) . "' WHERE name = '" . addslashes_mssql($username) . "'", DATABASE_ACCOUNT);
    return $query !== false;
}

function getAccountCharactersInfo($username) {
    $sql = 'SELECT act_id, cha_ids FROM ' . TABLE_ACCOUNT . ' WHERE act_name = '\'' . addslashes_mssql($username) . '\'';
    $query = doQuery($sql, DATABASE_GAME);
    if ($query !== false) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $chars = [];

            if (!empty($row['cha_ids'])) {
                $ids = array_filter(array_map('intval', explode(',', $row['cha_ids'])));
                if (!empty($ids)) {
                    $idList = implode(',', $ids);
                    $charQuery = doQuery('SELECT cha_id, cha_name FROM ' . TABLE_CHARACTERS . ' WHERE cha_id IN (' . $idList . ')', DATABASE_GAME);
                    if ($charQuery !== false) {
                        while ($charRow = $charQuery->fetch(PDO::FETCH_ASSOC)) {
                            $chars[] = ['id' => (int)$charRow['cha_id'], 'name' => $charRow['cha_name']];
                        }
                    }
                }
            }

            if (empty($chars) && isset($row['act_id'])) {
                $actId = (int)$row['act_id'];
                $charQuery = doQuery('SELECT cha_id, cha_name FROM ' . TABLE_CHARACTERS . ' WHERE act_id = ' . $actId, DATABASE_GAME);
                if ($charQuery !== false) {
                    while ($charRow = $charQuery->fetch(PDO::FETCH_ASSOC)) {
                        $chars[] = ['id' => (int)$charRow['cha_id'], 'name' => $charRow['cha_name']];
                    }
                }
            }

            return $chars;
        }
    }
    return [];
}

function updateDonationBalance($username, $delta) {
    $delta = (int)$delta;
    $query = doQuery(
        "UPDATE " . TABLE_ACCOUNT_LOGIN . " SET donat = donat + " . $delta .
        " WHERE name = '" . addslashes_mssql($username) . "'",
        DATABASE_ACCOUNT
    );
    return $query !== false;
}

function isBanned($username) {
    $query = doQuery("SELECT ban FROM " . TABLE_ACCOUNT_LOGIN . " WHERE name = '" . addslashes_mssql($username) . "'", DATABASE_ACCOUNT);
    if ($query !== false) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['ban'] > 0;
    }
    return false;
}

function isAdmin($username) {
    $query = doQuery("SELECT gm FROM " . TABLE_ACCOUNT . " WHERE act_name = '" . addslashes_mssql($username) . "'", DATABASE_GAME);
    if ($query !== false) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['gm'] == 99;
    }
    return false;
}

// ----- Функции статистики -----

function getAccountsCount() {
    $query = doQuery('SELECT COUNT(*) AS cnt FROM ' . TABLE_ACCOUNT_LOGIN, DATABASE_ACCOUNT);
    return $query !== false ? (int)$query->fetchColumn() : 0;
}

function getCharactersCount() {
    $query = doQuery('SELECT COUNT(*) AS cnt FROM ' . TABLE_CHARACTERS, DATABASE_GAME);
    return $query !== false ? (int)$query->fetchColumn() : 0;
}

function getGuildsCount() {
    $query = doQuery('SELECT COUNT(*) AS cnt FROM ' . TABLE_GUILDS, DATABASE_GAME);
    return $query !== false ? (int)$query->fetchColumn() : 0;
}

function getOnlineCount() {
    $query = doQuery('SELECT COUNT(*) AS cnt FROM ' . TABLE_ACCOUNT_LOGIN . ' WHERE login_status > 0', DATABASE_ACCOUNT);
    return $query !== false ? (int)$query->fetchColumn() : 0;
}

function getGMOnline() {
    $sql = "SELECT TOP 1 name FROM " . TABLE_ACCOUNT_LOGIN . " WHERE login_status > 0 AND gm > 0";
    $query = doQuery($sql, DATABASE_ACCOUNT);
    $name = ($query !== false) ? $query->fetchColumn() : '';
    return $name ? '[GM] ' . $name : '';
}

function getServerLoad() {
    // Попытка вычислить загрузку процессора в процентах
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        if ($load !== false && isset($load[0])) {
            // Количество ядер, если возможно определить
            $cores = (int) trim(@shell_exec('nproc 2>/dev/null'));
            if ($cores <= 0) {
                $cores = 1;
            }
            // 1.0 нагрузки на одно ядро = 100%
            $percent = ($load[0] / $cores) * 100;
            return (int) max(0, min(100, round($percent)));
        }
    }
    return 0;
}

function getServerStatus() {
    global $config;
    $fp = @fsockopen($config['server_host'], $config['server_port'], $errno, $errstr, 1);
    if ($fp) {
        fclose($fp);
        return 'Онлайн';
    }
    return 'Оффлайн';
}

function getCachedRanking($file, $time, $callback) {
    if (file_exists($file) && (time() - filemtime($file) < $time)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            return $data;
        }
    }
    $data = $callback();
    if ($data !== false) {
        @file_put_contents($file, json_encode($data));
        return $data;
    }
    return [];
}

function getTopExpPlayers($limit = 10) {
    global $config;
    $cache = __DIR__ . '/../data/rank_exp.json';
    return getCachedRanking($cache, $config['ranking_cache'], function () use ($limit) {
       $sql = 'SELECT TOP ' . (int)$limit .
       ' c.cha_name, c.degree, c.exp FROM ' . TABLE_CHARACTERS . ' c ' .
       'JOIN ' . TABLE_ACCOUNT . ' a ON c.act_id = a.act_id ' .
       'WHERE a.gm <> 99 ' .
       'ORDER BY CASE WHEN c.exp < 0 THEN c.exp + 4294967296 ELSE c.exp END DESC';
        $query = doQuery($sql, DATABASE_GAME);
        if ($query === false) {
            return false;
        }
        $result = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'name'  => $row['cha_name'],
                'level' => (int)$row['degree'],
                'exp'   => (int)$row['exp']
            ];
        }
        return $result;
    });
}

function getTopGoldPlayers($limit = 10) {
    global $config;
    $cache = __DIR__ . '/../data/rank_gold.json';
    return getCachedRanking($cache, $config['ranking_cache'], function () use ($limit) {
        $sql = 'SELECT TOP ' . (int)$limit .
       ' c.cha_name, c.degree, c.gd FROM ' . TABLE_CHARACTERS . ' c ' .
       'JOIN ' . TABLE_ACCOUNT . ' a ON c.act_id = a.act_id ' .
       'WHERE a.gm <> 99 ' .
       'ORDER BY c.gd DESC';
        $query = doQuery($sql, DATABASE_GAME);
        if ($query === false) {
            return false;
        }
        $result = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'name'  => $row['cha_name'],
                'level' => (int)$row['degree'],
                'gold'  => (int)$row['gd']
            ];
        }
        return $result;
    });
}

function getTopGuilds($limit = 10) {
    global $config;
    $cache = __DIR__ . '/../data/rank_guild.json';
    return getCachedRanking($cache, $config['ranking_cache'], function () use ($limit) {
        $sql = 'SELECT TOP ' . (int)$limit .
               ' guild_name, motto, member_total FROM ' . TABLE_GUILDS .
               ' WHERE member_total > 0 ORDER BY member_total DESC';
        $query = doQuery($sql, DATABASE_GAME);
        if ($query === false) {
            return false;
        }
        $result = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'name'    => $row['guild_name'],
                'motto'   => $row['motto'],
                'members' => (int)$row['member_total']
            ];
        }
        return $result;
    });
}

?>