<?php
class ProjectController
{
    private $model;

    public function __construct($db)
    {
        require_once __DIR__ . '/../models/Project.php';
        $this->model = new Project($db);
    }

    public function generateJson()
    {
        $data = $this->model->getAll();
        $file = __DIR__ . '/../../public/data/projects.json';
        @mkdir(dirname($file), 0755, true);
        file_put_contents($file, json_encode(['data' => $data]));
    }
}
