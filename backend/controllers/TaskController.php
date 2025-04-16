<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/Task.php';

$database = new Database();
$db = $database->getConnection();

$task = new Task($db);

$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];

// Get query parameters
$query_params = parse_url($request, PHP_URL_QUERY);
parse_str($query_params, $params);

switch($method) {
    case 'GET':
        // Check if status filter is provided
        if(isset($params['status'])) {
            $stmt = $task->readByStatus($params['status']);
        }
        // Check if assignee filter is provided
        else if(isset($params['assignee_id'])) {
            $stmt = $task->readByAssignee($params['assignee_id']);
        }
        // Get task stats if requested
        else if(isset($params['stats']) && $params['stats'] == 'true') {
            $stats = $task->getTaskStats();
            http_response_code(200);
            echo json_encode($stats);
            exit;
        }
        // Get all tasks
        else {
            $stmt = $task->read();
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
            http_response_code(200);
            echo json_encode($tasks_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No tasks found."));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        $task->title = $data->title;
        $task->description = $data->description;
        $task->status = $data->status;
        $task->priority = $data->priority;
        $task->due_date = $data->due_date;
        $task->assignee_id = $data->assignee_id;

        if($task->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Task was created."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create task."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        $task->id = $data->id;
        $task->title = $data->title;
        $task->description = $data->description;
        $task->status = $data->status;
        $task->priority = $data->priority;
        $task->due_date = $data->due_date;
        $task->assignee_id = $data->assignee_id;

        if($task->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Task was updated."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update task."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));

        $task->id = $data->id;

        if($task->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Task was deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete task."));
        }
        break;
}
?> 