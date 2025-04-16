const API_BASE_URL = 'http://localhost/hr-dasboard/backend/api';

export const fetchDashboardStats = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/dashboard/stats`);
        if (!response.ok) {
            throw new Error('Failed to fetch dashboard stats');
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
        return {
            employees: { total: 0, newHires: 0, resigned: 0, growth: 0 },
            tasks: { completed: 0, pending: 0, overdue: 0 }
        };
    }
};

export const fetchAttendanceData = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/dashboard/attendance`);
        if (!response.ok) {
            throw new Error('Failed to fetch attendance data');
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching attendance data:', error);
        return [];
    }
};

export const fetchWorkFormatData = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/dashboard/work-format`);
        if (!response.ok) {
            throw new Error('Failed to fetch work format data');
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching work format data:', error);
        return [];
    }
}; 