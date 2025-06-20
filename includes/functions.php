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

function doQuery($query, $db = '', $params = []) {
    global $connections;
    if ($db !== '') {
        selectDB($db);
    }
    try {
        if (!empty($params)) {
            $stmt = $connections[$db]->prepare($query);
            $stmt->execute($params);
            return $stmt;
        }
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
    $query = doQuery('SELECT COUNT(*) FROM account_login WHERE name = ?', DATABASE_ACCOUNT, [$username]);
    return $query && $query->fetchColumn() > 0;
}

function checkDuplicateEMail($email) {
    $query = doQuery('SELECT COUNT(*) FROM account_login WHERE email = ?', DATABASE_ACCOUNT, [$email]);
    return $query && $query->fetchColumn() > 0;
}

function createAccount($username, $password, $email) {
    global $connections, $config;

    $timestamp = time();
    $hash = strtoupper(md5($password));

    try {
        // Insert into account_login
        $sql = 'INSERT INTO ' . TABLE_ACCOUNT_LOGIN . ' (name, password, originalpassword, sid, login_status, enable_login_tick, login_group, last_login_time, last_logout_time, last_login_ip, enable_login_time, total_live_time, last_login_mac, ban, email) VALUES (?, ?, ?, 0, 0, 0, \'\', ?, ?, \'\', ?, 0, \'\', 0, ?)';
        $params = [$username, $hash, $password, $timestamp, $timestamp, $timestamp, $email];
        $query = doQuery($sql, DATABASE_ACCOUNT, $params);
        if ($query === false) {
            return false;
        }

        // Determine account id
        if ($config['max_compatibility_mode']) {
            $query = doQuery('SELECT id FROM ' . TABLE_ACCOUNT_LOGIN . ' WHERE name = ?', DATABASE_ACCOUNT, [$username]);
        } else {
            $query = doQuery('SELECT (MAX(act_id) + 1) AS id FROM ' . TABLE_ACCOUNT, DATABASE_GAME);
        }

        if ($query === false) {
            return false;
        }

        $row = $query->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        $id = (int)$row['id'];
        $gm = $config['is_gm_server'] ? (int)$config['new_gm_level'] : 0;
        $gm = max(0, min($gm, 99));

        // Insert into account table
        $query = doQuery('INSERT INTO account (act_id, act_name, gm) VALUES (?, ?, ?)', DATABASE_GAME, [$id, $username, $gm]);

        return $query !== false;
    } catch (PDOException $e) {
        error_log('Ошибка при создании аккаунта: ' . $e->getMessage());
        return false;
    }
}

function loginUser($username, $password) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $query = doQuery('SELECT password FROM ' . TABLE_ACCOUNT_LOGIN . ' WHERE name = ?', DATABASE_ACCOUNT, [$username]);

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
    doQuery('UPDATE ' . TABLE_ACCOUNT_LOGIN . ' SET last_logout_time = ? WHERE name = ?', DATABASE_ACCOUNT, [time(), $username]);
    unset($_SESSION['user']);
    session_destroy();
    return true;
}

function getAccountInfo($username) {
    $query = doQuery('SELECT * FROM ' . TABLE_ACCOUNT_LOGIN . ' WHERE name = ?', DATABASE_ACCOUNT, [$username]);
    if ($query !== false) {
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    return false;
}

function getAccountCharacters($username) {
    $query = doQuery('SELECT act_id, cha_ids FROM ' . TABLE_ACCOUNT . ' WHERE act_name = ?', DATABASE_GAME, [$username]);
    if ($query !== false) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $names = [];

            if (!empty($row['cha_ids'])) {
                $ids = array_filter(array_map('intval', explode(',', $row['cha_ids'])));
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $charQuery = doQuery('SELECT cha_name FROM ' . TABLE_CHARACTERS . ' WHERE cha_id IN (' . $placeholders . ')', DATABASE_GAME, $ids);
                    if ($charQuery !== false) {
                        while ($charRow = $charQuery->fetch(PDO::FETCH_ASSOC)) {
                            $names[] = $charRow['cha_name'];
                        }
                    }
                }
            }

            if (empty($names) && isset($row['act_id'])) {
                $actId = (int)$row['act_id'];
                $charQuery = doQuery('SELECT cha_name FROM ' . TABLE_CHARACTERS . ' WHERE act_id = ?', DATABASE_GAME, [$actId]);
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
    $query = doQuery('UPDATE ' . TABLE_ACCOUNT_LOGIN . ' SET email = ? WHERE name = ?', DATABASE_ACCOUNT, [$email, $username]);
    return $query !== false;
}

function updateAccountPassword($username, $password) {
    $hash = strtoupper(md5($password));
    $query = doQuery('UPDATE ' . TABLE_ACCOUNT_LOGIN . ' SET password = ?, originalpassword = ? WHERE name = ?', DATABASE_ACCOUNT, [$hash, $password, $username]);
    return $query !== false;
}

function isBanned($username) {
    $query = doQuery('SELECT ban FROM ' . TABLE_ACCOUNT_LOGIN . ' WHERE name = ?', DATABASE_ACCOUNT, [$username]);
    if ($query !== false) {
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['ban'] > 0;
    }
    return false;
}

function isAdmin($username) {
    $query = doQuery('SELECT gm FROM ' . TABLE_ACCOUNT . ' WHERE act_name = ?', DATABASE_GAME, [$username]);
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
    // Здесь может быть логика вычисления нагрузки сервера
    return 0;
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
               ' c.cha_name, c.degree, c.exp FROM ' . TABLE_CHARACTERS . ' c '
               'JOIN ' . TABLE_ACCOUNT . ' a ON c.act_id = a.act_id '
               'WHERE a.gm <> 99 '
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
               ' c.cha_name, c.degree, c.gd FROM ' . TABLE_CHARACTERS . ' c '
               'JOIN ' . TABLE_ACCOUNT . ' a ON c.act_id = a.act_id '
               'WHERE a.gm <> 99 '
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
