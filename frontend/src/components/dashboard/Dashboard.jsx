import React, { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, PieChart, Pie, Cell } from 'recharts';
import { fetchDashboardStats, fetchAttendanceData, fetchWorkFormatData } from '../../services/dashboardApi';

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042'];

const Dashboard = () => {
    const [stats, setStats] = useState({
        employees: { total: 0, newHires: 0, resigned: 0, growth: 0 },
        tasks: { completed: 0, pending: 0, overdue: 0 }
    });
    const [attendance, setAttendance] = useState([]);
    const [workFormat, setWorkFormat] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const loadDashboardData = async () => {
            try {
                setLoading(true);
                const [statsData, attendanceData, workFormatData] = await Promise.all([
                    fetchDashboardStats(),
                    fetchAttendanceData(),
                    fetchWorkFormatData()
                ]);
                setStats(statsData);
                setAttendance(attendanceData);
                setWorkFormat(workFormatData);
            } catch (err) {
                setError('Failed to load dashboard data');
                console.error(err);
            } finally {
                setLoading(false);
            }
        };

        loadDashboardData();
    }, []);

    if (loading) {
        return (
            <div className="flex justify-center items-center h-full">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="text-red-500 text-center p-4">
                {error}
            </div>
        );
    }

    return (
        <div className="p-6 space-y-6">
            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm font-medium">Total Employees</h3>
                    <p className="text-3xl font-bold text-gray-900">{stats.employees.total}</p>
                    <p className="text-sm text-green-600">+{stats.employees.growth}% growth</p>
                </div>
                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm font-medium">New Hires</h3>
                    <p className="text-3xl font-bold text-gray-900">{stats.employees.newHires}</p>
                    <p className="text-sm text-gray-500">This month</p>
                </div>
                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm font-medium">Tasks Completed</h3>
                    <p className="text-3xl font-bold text-gray-900">{stats.tasks.completed}</p>
                    <p className="text-sm text-gray-500">Out of {stats.tasks.completed + stats.tasks.pending}</p>
                </div>
                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm font-medium">Overdue Tasks</h3>
                    <p className="text-3xl font-bold text-red-600">{stats.tasks.overdue}</p>
                    <p className="text-sm text-gray-500">Need attention</p>
                </div>
            </div>

            {/* Charts */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Attendance Chart */}
                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-lg font-semibold mb-4">Monthly Attendance</h3>
                    <BarChart width={500} height={300} data={attendance}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis dataKey="month" />
                        <YAxis />
                        <Tooltip />
                        <Legend />
                        <Bar dataKey="present" fill="#0088FE" />
                        <Bar dataKey="absent" fill="#FF8042" />
                    </BarChart>
                </div>

                {/* Work Format Distribution */}
                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-lg font-semibold mb-4">Work Format Distribution</h3>
                    <PieChart width={400} height={300}>
                        <Pie
                            data={workFormat}
                            cx={200}
                            cy={150}
                            labelLine={false}
                            outerRadius={100}
                            fill="#8884d8"
                            dataKey="value"
                        >
                            {workFormat.map((entry, index) => (
                                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                            ))}
                        </Pie>
                        <Tooltip />
                        <Legend />
                    </PieChart>
                </div>
            </div>
        </div>
    );
};

export default Dashboard; 