<?php

use Parallel\Parallel;
use Parallel\Storage\ApcuStorage;

$strOldSTDOUT = (posix_ttyname(STDOUT));
fclose(STDOUT);
$STDOUT = fopen('application.log', 'wb');

echo 'my echo';
sleep(1);
echo 'my echo2';
sleep(1);
echo 'my echo3';
sleep(1);

$Parallel = new Parallel(new ApcuStorage());
$Parallel->run('parallels', function () {
    $fp = fopen("application.log", "r");

    while (!feof($fp)) {
        $stringText = fgets($fp);
        if ($stringText) {
            try {
                $db = \Config\Database::connect();
                $builder = $db->table('log');

                $builder->insert([
                    'task_key' => $activeTask['task_id'],
                    'log' => $stringText
                ]);
            } catch (Exception $exception) {
                echo $exception->getMessage();
            }
        }
    }

    fclose($fp);
});

fclose($STDOUT);
$STDOUT = fopen($strOldSTDOUT, "r+");