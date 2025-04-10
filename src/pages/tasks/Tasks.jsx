import { useState } from 'react';
import TaskForm from '../../components/tasks/TaskForm';
import TaskList from '../../components/tasks/TaskList';

const Tasks = () => {
  const [tasks, setTasks] = useState([
    {
      id: 1,
      title: 'Review employee performance',
      description: 'Review and provide feedback on employee performance',
      status: 'In Progress',
      priority: 'High',
      dueDate: '2024-04-15',
      assignee: 'John Doe',
    },
    {
      id: 2,
      title: 'Schedule team meeting',
      description: 'Schedule and prepare agenda for team meeting',
      status: 'Pending',
      priority: 'Medium',
      dueDate: '2024-04-20',
      assignee: 'Jane Smith',
    },
    {
      id: 3,
      title: 'Update company policies',
      description: 'Review and update company policies',
      status: 'Completed',
      priority: 'Low',
      dueDate: '2024-04-10',
      assignee: 'Mike Johnson',
    },
  ]);

  const [isFormOpen, setIsFormOpen] = useState(false);
  const [editingTask, setEditingTask] = useState(null);

  const handleAddTask = (newTask) => {
    setTasks([...tasks, { ...newTask, id: tasks.length + 1 }]);
    setIsFormOpen(false);
  };

  const handleEditTask = (updatedTask) => {
    setTasks(
      tasks.map((task) => (task.id === updatedTask.id ? updatedTask : task))
    );
    setIsFormOpen(false);
    setEditingTask(null);
  };

  const handleDeleteTask = (taskId) => {
    setTasks(tasks.filter((task) => task.id !== taskId));
  };

  const handleEdit = (task) => {
    setEditingTask(task);
    setIsFormOpen(true);
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-900">Tasks</h1>
        <button
          onClick={() => {
            setEditingTask(null);
            setIsFormOpen(true);
          }}
          className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Add Task
        </button>
      </div>

      {isFormOpen && (
        <TaskForm
          onSubmit={editingTask ? handleEditTask : handleAddTask}
          onCancel={() => {
            setIsFormOpen(false);
            setEditingTask(null);
          }}
          task={editingTask}
        />
      )}

      <TaskList
        tasks={tasks}
        onEdit={handleEdit}
        onDelete={handleDeleteTask}
      />
    </div>
  );
};

export default Tasks; 