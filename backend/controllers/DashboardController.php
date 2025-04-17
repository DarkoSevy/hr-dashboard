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
                'totalEmployees' => $this->employeeModel->getTotalCount(),
                'activeEmployees' => $this->employeeModel->getActiveCount(),
                'totalTasks' => $this->taskModel->getTotalCount(),
                'completedTasks' => $this->taskModel->getCompletedCount(),
                'pendingTasks' => $this->taskModel->getPendingCount()
            ];

            echo json_encode([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch dashboard stats: ' . $e->getMessage()
            ]);
        }
    }

    public function getAttendance() {
        try {
            $attendanceData = $this->employeeModel->getMonthlyAttendance();
            echo json_encode([
                'status' => 'success',
                'data' => $attendanceData
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch attendance data: ' . $e->getMessage()
            ]);
        }
    }

    public function getWorkFormat() {
        try {
            $workFormatData = $this->employeeModel->getWorkFormatDistribution();
            echo json_encode([
                'status' => 'success',
                'data' => $workFormatData
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch work format data: ' . $e->getMessage()
            ]);
        }
    }
} 