<?php
include_once '../config/database.php';
include_once '../models/Task.php';

class TaskController {
    private $taskModel;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->taskModel = new Task($db);
    }

    public function handleRequest() {
        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $this->getAll();
                    break;
                case 'POST':
                    $this->create();
                    break;
                case 'PUT':
                    $this->update();
                    break;
                case 'DELETE':
                    $this->delete();
                    break;
                default:
                    throw new Exception('Method not allowed', 405);
            }
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function getAll() {
        $query_params = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        parse_str($query_params, $params);

        if(isset($params['status'])) {
            $stmt = $this->taskModel->readByStatus($params['status']);
        } else if(isset($params['assignee_id'])) {
            $stmt = $this->taskModel->readByAssignee($params['assignee_id']);
        } else if(isset($params['stats']) && $params['stats'] == 'true') {
            $stats = $this->taskModel->getTaskStats();
            echo json_encode([
                'status' => 'success',
                'data' => $stats
            ]);
            return;
        } else {
            $stmt = $this->taskModel->read();
        }

        $num = $stmt->rowCount();

        if($num > 0) {
            $tasks_arr = array();
            $tasks_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $task_item = array(
                    "id" => $id,
                    "title" => $title,
                    "description" => $description,
                    "status" => $status,
                    "priority" => $priority,
                    "due_date" => $due_date,
                    "assignee_id" => $assignee_id,
                    "assignee_name" => $assignee_name
                );
                array_push($tasks_arr["records"], $task_item);
            }
            echo json_encode([
                'status' => 'success',
                'data' => $tasks_arr
            ]);
        } else {
            throw new Exception('No tasks found', 404);
        }
    }

    private function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            throw new Exception('Invalid input data', 400);
        }

        $this->taskModel->title = $data['title'];
        $this->taskModel->description = $data['description'];
        $this->taskModel->status = $data['status'];
        $this->taskModel->priority = $data['priority'];
        $this->taskModel->due_date = $data['due_date'];
        $this->taskModel->assignee_id = $data['assignee_id'];

        if($this->taskModel->create()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Task created successfully'
            ]);
        } else {
            throw new Exception('Unable to create task', 503);
        }
    }

    private function update() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['id'])) {
            throw new Exception('Invalid input data', 400);
        }

        $this->taskModel->id = $data['id'];
        $this->taskModel->title = $data['title'];
        $this->taskModel->description = $data['description'];
        $this->taskModel->status = $data['status'];
        $this->taskModel->priority = $data['priority'];
        $this->taskModel->due_date = $data['due_date'];
        $this->taskModel->assignee_id = $data['assignee_id'];

        if($this->taskModel->update()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Task updated successfully'
            ]);
        } else {
            throw new Exception('Unable to update task', 503);
        }
    }

    private function delete() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['id'])) {
            throw new Exception('Invalid input data', 400);
        }

        $this->taskModel->id = $data['id'];

        if($this->taskModel->delete()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Task deleted successfully'
            ]);
        } else {
            throw new Exception('Unable to delete task', 503);
        }
    }
}
?> 