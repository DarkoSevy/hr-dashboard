import { useState } from 'react';
import TaskList from '../../components/dashboard/TaskList';
import WorkloadStatus from '../../components/dashboard/WorkloadStatus';
import UrgentTasks from '../../components/dashboard/UrgentTasks';

const Dashboard = () => {
  const [tasks, setTasks] = useState([
    {
      id: 1,
      title: 'Review employee performance',
      status: 'In Progress',
      priority: 'High',
      dueDate: '2024-04-15',
      assignee: 'John Doe',
    },
    {
      id: 2,
      title: 'Schedule team meeting',
      status: 'Pending',
      priority: 'Medium',
      dueDate: '2024-04-20',
      assignee: 'Jane Smith',
    },
    {
      id: 3,
      title: 'Update company policies',
      status: 'Completed',
      priority: 'Low',
      dueDate: '2024-04-10',
      assignee: 'Mike Johnson',
    },
  ]);

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div>
          <h2 className="text-lg font-medium text-gray-900 mb-4">Task Overview</h2>
          <TaskList tasks={tasks} />
        </div>

        <div>
          <h2 className="text-lg font-medium text-gray-900 mb-4">Workload Status</h2>
          <WorkloadStatus tasks={tasks} />
        </div>
      </div>

      <div>
        <h2 className="text-lg font-medium text-gray-900 mb-4">Urgent Tasks</h2>
        <UrgentTasks tasks={tasks} />
      </div>
    </div>
  );
};

export default Dashboard; 