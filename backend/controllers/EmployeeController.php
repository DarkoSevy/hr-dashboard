<?php
require_once '../models/Employee.php';

class EmployeeController {
    private $employeeModel;

    public function __construct() {
        $this->employeeModel = new Employee();
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
        $employees = $this->employeeModel->getAll();
        echo json_encode([
            'status' => 'success',
            'data' => $employees
        ]);
    }

    private function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            throw new Exception('Invalid input data', 400);
        }
        
        $result = $this->employeeModel->create($data);
        echo json_encode([
            'status' => 'success',
            'message' => 'Employee created successfully',
            'data' => $result
        ]);
    }

    private function update() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['id'])) {
            throw new Exception('Invalid input data', 400);
        }
        
        $result = $this->employeeModel->update($data['id'], $data);
        echo json_encode([
            'status' => 'success',
            'message' => 'Employee updated successfully',
            'data' => $result
        ]);
    }

    private function delete() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['id'])) {
            throw new Exception('Invalid input data', 400);
        }
        
        $result = $this->employeeModel->delete($data['id']);
        echo json_encode([
            'status' => 'success',
            'message' => 'Employee deleted successfully',
            'data' => $result
        ]);
    }
}
?> 