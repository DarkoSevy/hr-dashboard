import React, { useState, useEffect } from 'react';
import { 
  UsersIcon, UserPlusIcon, UserMinusIcon, 
  CheckCircleIcon, BriefcaseIcon, ChartBarIcon 
} from '@heroicons/react/24/outline';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { fetchDashboardStats, fetchAttendanceData, fetchWorkFormatData, fetchTasks } from '../../services/api';
import TaskList from '../../components/dashboard/TaskList';

const Dashboard = () => {
  const [stats, setStats] = useState(null);
  const [attendanceData, setAttendanceData] = useState([]);
  const [workFormatData, setWorkFormatData] = useState([]);
  const [recentTasks, setRecentTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        setError(null);
        
        const [statsData, attendance, workFormat, tasks] = await Promise.all([
          fetchDashboardStats(),
          fetchAttendanceData(),
          fetchWorkFormatData(),
          fetchTasks()
        ]);

        if (!statsData || !attendance || !workFormat || !tasks) {
          throw new Error('Failed to fetch dashboard data');
        }

        setStats(statsData);
        setAttendanceData(attendance);
        setWorkFormatData(workFormat);
        setRecentTasks(tasks.slice(0, 5));
      } catch (err) {
        console.error('Error loading dashboard data:', err);
        if (err.message.includes('CORS') || err.message.includes('cross-origin')) {
          setError('CORS Error: Please ensure the backend server is running and properly configured for CORS.');
        } else {
          setError('Failed to load dashboard data. Please try again later.');
        }
      } finally {
        setLoading(false);
      }
    };

    loadData();
  }, []);

  const statCards = [
    {
      title: 'Total Employees',
      value: stats?.totalEmployees || 0,
      trend: stats?.trends?.totalGrowth || '0%',
      icon: UsersIcon,
      color: 'bg-blue-500'
    },
    {
      title: 'New Employees',
      value: stats?.newEmployees || 0,
      trend: stats?.trends?.newGrowth || '0%',
      icon: UserPlusIcon,
      color: 'bg-green-500'
    },
    {
      title: 'Resigned Employees',
      value: stats?.resignedEmployees || 0,
      trend: stats?.trends?.resignedGrowth || '0%',
      icon: UserMinusIcon,
      color: 'bg-red-500'
    },
    {
      title: 'Completed Tasks',
      value: stats?.completedTasks || 0,
      trend: stats?.trends?.taskGrowth || '0%',
      icon: CheckCircleIcon,
      color: 'bg-purple-500'
    }
  ];

  if (loading) {
    return (
      <div className="flex items-center justify-center h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center h-screen">
        <div className="text-red-500 text-lg">{error}</div>
      </div>
    );
  }

  return (
    <div className="p-6 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold text-gray-800">
          Welcome to HR Dashboard
        </h1>
        <div className="text-sm text-gray-500">
          {new Date().toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
          })}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {statCards.map((card, index) => (
          <div key={index} className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-500">{card.title}</p>
                <p className="text-2xl font-semibold mt-1">{card.value}</p>
                <p className={`text-sm mt-2 ${
                  parseFloat(card.trend) >= 0 ? 'text-green-500' : 'text-red-500'
                }`}>
                  {card.trend}
                </p>
              </div>
              <div className={`p-3 rounded-full ${card.color}`}>
                <card.icon className="w-6 h-6 text-white" />
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-semibold mb-4 flex items-center">
            <BriefcaseIcon className="w-5 h-5 mr-2" />
            Attendance Overview
          </h2>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={attendanceData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="date" />
                <YAxis />
                <Tooltip />
                <Bar dataKey="present" fill="#4CAF50" name="Present" />
                <Bar dataKey="absent" fill="#f44336" name="Absent" />
                <Bar dataKey="leave" fill="#2196F3" name="On Leave" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-semibold mb-4 flex items-center">
            <ChartBarIcon className="w-5 h-5 mr-2" />
            Work Format Distribution
          </h2>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={workFormatData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="format" />
                <YAxis />
                <Tooltip />
                <Bar dataKey="count" fill="#9C27B0" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow p-6">
        <h2 className="text-lg font-semibold mb-4">Recent Tasks</h2>
        <TaskList tasks={recentTasks} />
      </div>
    </div>
  );
};

export default Dashboard; 