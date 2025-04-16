import { useState, useEffect } from 'react';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell,
} from 'recharts';
import { fetchTaskStats, fetchTasks, fetchEmployees } from '../../services/api';

const COLORS = ['#4F46E5', '#10B981', '#F59E0B', '#EF4444'];

const TaskReports = () => {
  const [taskStats, setTaskStats] = useState(null);
  const [tasks, setTasks] = useState([]);
  const [employees, setEmployees] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [timeRange, setTimeRange] = useState('week'); // 'week' or 'month'

  useEffect(() => {
    loadData();
  }, [timeRange]);

  const loadData = async () => {
    try {
      setLoading(true);
      const [statsData, tasksData, employeesData] = await Promise.all([
        fetchTaskStats(),
        fetchTasks(),
        fetchEmployees()
      ]);
      
      setTaskStats(statsData);
      setTasks(tasksData);
      setEmployees(employeesData);
      setError(null);
    } catch (err) {
      setError('Failed to load report data');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  // Calculate department-wise task distribution
  const getDepartmentTaskData = () => {
    const deptTasks = {};
    tasks.forEach(task => {
      const employee = employees.find(emp => emp.id === task.assignee_id);
      if (employee) {
        deptTasks[employee.department] = deptTasks[employee.department] || { total: 0, completed: 0 };
        deptTasks[employee.department].total++;
        if (task.status === 'completed') {
          deptTasks[employee.department].completed++;
        }
      }
    });

    return Object.entries(deptTasks).map(([dept, data]) => ({
      name: dept,
      total: data.total,
      completed: data.completed,
      completionRate: ((data.completed / data.total) * 100).toFixed(1)
    }));
  };

  // Calculate employee workload
  const getWorkloadData = () => {
    const workload = {};
    tasks.forEach(task => {
      if (task.assignee_id) {
        workload[task.assignee_id] = workload[task.assignee_id] || 0;
        workload[task.assignee_id]++;
      }
    });

    return employees.map(emp => ({
      name: emp.name,
      tasks: workload[emp.id] || 0
    }));
  };

  if (loading) return (
    <div className="flex justify-center items-center h-64">
      <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>
  );

  if (error) return (
    <div className="text-red-600 text-center p-4">{error}</div>
  );

  return (
    <div className="space-y-6">
      {/* Report Header */}
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold text-gray-900">HR Task Analytics Dashboard</h2>
        <div className="flex gap-2">
          <button
            onClick={() => setTimeRange('week')}
            className={`px-4 py-2 rounded ${
              timeRange === 'week'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            }`}
          >
            Weekly
          </button>
          <button
            onClick={() => setTimeRange('month')}
            className={`px-4 py-2 rounded ${
              timeRange === 'month'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            }`}
          >
            Monthly
          </button>
        </div>
      </div>

      {/* Key Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-gray-900">Total Tasks</h3>
          <p className="text-3xl font-bold text-blue-600">{taskStats?.total_tasks || 0}</p>
          <p className="text-sm text-gray-500 mt-1">Across all departments</p>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-gray-900">Completion Rate</h3>
          <p className="text-3xl font-bold text-green-600">
            {taskStats?.total_tasks 
              ? ((taskStats.completed_tasks / taskStats.total_tasks) * 100).toFixed(1)
              : 0}%
          </p>
          <p className="text-sm text-gray-500 mt-1">Overall task completion</p>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-gray-900">Active Tasks</h3>
          <p className="text-3xl font-bold text-yellow-600">
            {(taskStats?.in_progress_tasks || 0) + (taskStats?.pending_tasks || 0)}
          </p>
          <p className="text-sm text-gray-500 mt-1">Tasks in progress or pending</p>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-gray-900">High Priority</h3>
          <p className="text-3xl font-bold text-red-600">{taskStats?.high_priority_tasks || 0}</p>
          <p className="text-sm text-gray-500 mt-1">Requiring immediate attention</p>
        </div>
      </div>

      {/* Department Performance */}
      <div className="bg-white p-6 rounded-lg shadow">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Department Performance</h3>
        <div className="h-80">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={getDepartmentTaskData()}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis yAxisId="left" />
              <YAxis yAxisId="right" orientation="right" unit="%" />
              <Tooltip />
              <Legend />
              <Bar yAxisId="left" dataKey="total" name="Total Tasks" fill="#4F46E5" />
              <Bar yAxisId="left" dataKey="completed" name="Completed" fill="#10B981" />
              <Line yAxisId="right" type="monotone" dataKey="completionRate" name="Completion Rate %" stroke="#F59E0B" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Employee Workload */}
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Employee Workload</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={getWorkloadData()} layout="vertical">
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis type="number" />
                <YAxis dataKey="name" type="category" width={100} />
                <Tooltip />
                <Legend />
                <Bar dataKey="tasks" name="Assigned Tasks" fill="#4F46E5" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Task Priority Distribution */}
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Task Priority Distribution</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={[
                    { name: 'High', value: taskStats?.high_priority_tasks || 0 },
                    { name: 'Medium', value: taskStats?.medium_priority_tasks || 0 },
                    { name: 'Low', value: taskStats?.low_priority_tasks || 0 }
                  ]}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="value"
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                >
                  {COLORS.map((color, index) => (
                    <Cell key={`cell-${index}`} fill={color} />
                  ))}
                </Pie>
                <Tooltip />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>

      {/* Additional Insights */}
      <div className="bg-white p-6 rounded-lg shadow">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Key Insights</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 className="font-medium text-gray-700 mb-2">Department Highlights</h4>
            <ul className="space-y-2 text-sm text-gray-600">
              {getDepartmentTaskData().map(dept => (
                <li key={dept.name} className="flex justify-between">
                  <span>{dept.name}</span>
                  <span className="font-medium">{dept.completionRate}% completion rate</span>
                </li>
              ))}
            </ul>
          </div>
          <div>
            <h4 className="font-medium text-gray-700 mb-2">Task Distribution</h4>
            <ul className="space-y-2 text-sm text-gray-600">
              <li className="flex justify-between">
                <span>Average tasks per employee</span>
                <span className="font-medium">
                  {employees.length ? (tasks.length / employees.length).toFixed(1) : 0}
                </span>
              </li>
              <li className="flex justify-between">
                <span>Overdue tasks</span>
                <span className="font-medium text-red-600">
                  {tasks.filter(t => new Date(t.due_date) < new Date()).length}
                </span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TaskReports;
