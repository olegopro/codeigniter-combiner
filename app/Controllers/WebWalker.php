<?php

namespace App\Controllers;

use App\Libraries\GoLoginProfile;
use App\Models\WebWalkerModel;
use App\Projects\MailRu\Tasks\RegisterAccount;
use App\Projects\WebWalker\SalambaRu\Pages\FrontPage;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Database;
use Exception;
use Parallel\Parallel;
use Parallel\Storage\ApcuStorage;
use Throwable;

class WebWalker extends ResourceController
{
    /**
     * @var WebWalkerModel
     */
    protected $model = 'App\Models\WebWalkerModel';
    protected $modelName = 'App\Models\WebWalkerModel';
    protected $format = 'JSON';

    public function createTask()
    {
        $data = $this->request->getPost();
        $this->model->insert($data);

        return $this->respondCreated($data);
    }

    public function allTasks()
    {
        return $this->respond($this->model->findAll());
    }

    public function runTask($profile_id = null)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $activeTask = $this->getActiveTask();

        if (!$activeTask) {
            exit('no tasks to execute' . PHP_EOL);
        }

        $model = new WebWalkerModel();
        $model->where('id', $activeTask['id'])
              ->set('status', 'pending')
              ->update();

        $taskData = $model->where('id', $activeTask['id'])
                          ->first();

        $GoLogin = new GoLoginProfile();
        $profile_id = $GoLogin->createProfile();


        $fdout = fopen($profile_id . '.log', 'wb');
        eio_dup2($fdout, STDOUT);
        eio_event_loop();


        echo 'profile id = ' . $profile_id . PHP_EOL;
        $profile = $GoLogin->gl->getProfile($profile_id);

        echo 'new profile name = ' . $profile->name . PHP_EOL;

        $Parallel = new Parallel(new ApcuStorage());
        $Parallel->run('parallels', function () use ($profile_id, $activeTask) {
            $fp = fopen($profile_id . '.log', "r");
            $currentOffset = ftell($fp);

            while (file_exists($profile_id . '.log')) {
                sleep(1);
                fseek($fp, $currentOffset);
                $stringText = fgets($fp);

                if ($stringText) {
                    try {
                        $db = Database::connect();
                        $builder = $db->table('web_walker_log');

                        $builder->insert([
                            'key' => $activeTask['id'],
                            'log_data' => $stringText
                        ]);
                    } catch (Exception $exception) {
                        echo $exception->getMessage();
                    }

                    $currentOffset = ftell($fp);
                }
            }
            fclose($fp);
        });

        $orbita = null;
        $debugger_address = null;

        try {
            $orbita = $GoLogin->setOrbitaBrowser($profile_id);
            $debugger_address = $orbita->start();
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;

            $model->where('id', $activeTask['id'])
                  ->set('status', 'cancelled')
                  ->update();
        }

        if ($debugger_address) {

            $driver = $GoLogin->runOrbitaBrowser($debugger_address);

            $webWalk = new FrontPage($driver);
            $createAccount = new RegisterAccount($driver);

            try {
                $webWalk->openFromVk('https://vk.com/salamba_ru')
                        ->readArticle()
                        ->findRandomArticlesInFooter();

                $model->where('id', $activeTask['id'])
                      ->set('status', 'done')
                      ->update();

            } catch (Throwable|Exception $e) {
                echo 'ERROR-CODE: ' . $e->getCode() . PHP_EOL;
                echo 'ERROR-FILE: ' . $e->getFile() . PHP_EOL;
                echo 'ERROR-LINE: ' . $e->getLine() . PHP_EOL;
                echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
                // echo 'ERROR-TRACE: ' . $e->getTraceAsString() . PHP_EOL;

                $model->where('id', $activeTask['id'])
                      ->set('status', 'cancelled')
                      ->update();

                $this->deleteLog($profile_id);
            }

            sleep(100);

            $driver->close();
            $orbita->stop();
        }

        sleep(5);

        $GoLogin->gl->delete($profile_id);
        $this->deleteLog($profile_id);

        $Parallel->wait();
        fclose($fdout);
    }

    public function showById($id)
    {
        $task = $this->model->find($id);

        return $this->respond($task);
    }

    public function update($id = null)
    {
        $data = [
            'id'     => $id,
            'status' => $this->request->getVar('status'),

        ];

        $this->model->save($data);

        return $this->respond($data);
    }

    public function deleteTask($id = null)
    {
        $task = $this->model->find($id);

        if ($task) {
            $this->model->delete($id);

            return $this->respondDeleted($task);
        } else {
            return $this->failNotFound('Элемент не существует');
        }
    }

    private function getActiveTask()
    {
        $model = new WebWalkerModel();

        return $active_task = $model->where('status', 'active')->first();
    }

    private function deleteLog($profile_id)
    {
        try {
            unlink($profile_id . '.log');
        } catch (Exception $exception) {
            echo 'Ошибка удаления файла' . PHP_EOL;
            echo $exception->getMessage() . PHP_EOL;
        }
    }
}
