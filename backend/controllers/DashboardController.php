<?php
require_once '../models/Employee.php';
require_once '../models/Task.php';

class DashboardController {
    private $employeeModel;
    private $taskModel;

    public function __construct() {
        $this->employeeModel = new Employee();
        $this->taskModel = new Task();
    }

    public function getStats() {
        try {
            $stats = [
                'employees' => [
                    'total' => $this->employeeModel->getTotalCount(),
                    'newHires' => $this->employeeModel->getNewHiresCount(),
                    'resigned' => $this->employeeModel->getResignedCount(),
                    'growth' => $this->employeeModel->calculateGrowth()
                ],
                'tasks' => [
                    'completed' => $this->taskModel->getCompletedCount(),
                    'pending' => $this->taskModel->getPendingCount(),
                    'overdue' => $this->taskModel->getOverdueCount()
                ]
            ];

            echo json_encode([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getAttendance() {
        try {
            $attendance = $this->employeeModel->getMonthlyAttendance();
            echo json_encode([
                'status' => 'success',
                'data' => $attendance
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getWorkFormat() {
        try {
            $workFormat = $this->employeeModel->getWorkFormatDistribution();
            echo json_encode([
                'status' => 'success',
                'data' => $workFormat
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
} 