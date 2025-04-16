const API_BASE_URL = 'http://localhost:8000/api';

const handleResponse = async (response) => {
    if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        throw new Error(error.message || `HTTP error! status: ${response.status}`);
    }
    return response.json();
};

const getHeaders = () => ({
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'Origin': 'http://localhost:5173',
    'Access-Control-Request-Method': 'GET, POST, PUT, DELETE, OPTIONS',
    'Access-Control-Request-Headers': 'Content-Type, Authorization, X-Requested-With'
});

export const fetchEmployees = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/employees`, {
            headers: getHeaders()
        });
        return await handleResponse(response);
    } catch (error) {
        console.error('Error fetching employees:', error);
        return [];
    }
};

export const fetchTasks = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/tasks`, {
            headers: getHeaders()
        });
        return await handleResponse(response);
    } catch (error) {
        console.error('Error fetching tasks:', error);
        return [];
    }
};

export const fetchTasksByStatus = async (status) => {
    try {
        const response = await fetch(`${API_BASE_URL}/tasks?status=${status}`, {
            headers: getHeaders()
        });
        return await handleResponse(response);
    } catch (error) {
        console.error('Error fetching tasks by status:', error);
        return [];
    }
};

export const fetchTaskStats = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/tasks?stats=true`, {
            headers: getHeaders()
        });
        return await handleResponse(response);
    } catch (error) {
        console.error('Error fetching task stats:', error);
        return {};
    }
};

export const createTask = async (taskData) => {
    try {
        const response = await fetch(`${API_BASE_URL}/tasks`, {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify(taskData),
        });
        return await handleResponse(response);
    } catch (error) {
        console.error('Error creating task:', error);
        throw error;
    }
};

export const updateTask = async (taskData) => {
    try {
        const response = await fetch(`${API_BASE_URL}/tasks`, {
            method: 'PUT',
            headers: getHeaders(),
            body: JSON.stringify(taskData),
        });
        return await handleResponse(response);
    } catch (error) {
        console.error('Error updating task:', error);
        throw error;
    }
};

export const deleteTask = async (taskId) => {
    try {
        const response = await fetch(`${API_BASE_URL}/tasks`, {
            method: 'DELETE',
            headers: getHeaders(),
            body: JSON.stringify({ id: taskId }),
        });
        return await handleResponse(response);
    } catch (error) {
        console.error('Error deleting task:', error);
        throw error;
    }
};

export const fetchDashboardStats = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/dashboard/stats`, {
            headers: getHeaders()
        });
        return await handleResponse(response);
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
        return {
            totalEmployees: 0,
            newEmployees: 0,
            resignedEmployees: 0,
            completedTasks: 0,
            trends: {
                totalGrowth: '0%',
                newGrowth: '0%',
                resignedGrowth: '0%',
                taskGrowth: '0%'
            }
        };
    }
};

export const fetchAttendanceData = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/dashboard/attendance`, {
            headers: getHeaders()
        });
        return await handleResponse(response);
    } catch (error) {
        console.error('Error fetching attendance data:', error);
        return [];
    }
};

export const fetchWorkFormatData = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/dashboard/work-format`, {
            headers: getHeaders()
        });
        return await handleResponse(response);
    } catch (error) {
        console.error('Error fetching work format data:', error);
        return [];
    }
}; 