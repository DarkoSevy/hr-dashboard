import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
} from 'recharts';

const TaskReports = () => {
  // Sample data - replace with actual data from your state/API
  const taskData = [
    { month: 'Jan', completed: 12, pending: 8, inProgress: 5 },
    { month: 'Feb', completed: 15, pending: 6, inProgress: 7 },
    { month: 'Mar', completed: 18, pending: 4, inProgress: 6 },
    { month: 'Apr', completed: 20, pending: 3, inProgress: 5 },
  ];

  const priorityData = [
    { name: 'High', value: 15 },
    { name: 'Medium', value: 25 },
    { name: 'Low', value: 10 },
  ];

  const COLORS = ['#FF6B6B', '#4ECDC4', '#45B7D1'];

  return (
    <div className="space-y-8">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="bg-mintGray p-4 rounded-lg">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-2">Total Tasks</h3>
          <p className="text-3xl font-bold text-sageGray">50</p>
        </div>
        <div className="bg-mintGray p-4 rounded-lg">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-2">Completed Tasks</h3>
          <p className="text-3xl font-bold text-sageGray">35</p>
        </div>
        <div className="bg-mintGray p-4 rounded-lg">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-2">Pending Tasks</h3>
          <p className="text-3xl font-bold text-sageGray">15</p>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-white p-4 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-4">Task Completion Trend</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={taskData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Bar dataKey="completed" fill="#4ECDC4" name="Completed" />
                <Bar dataKey="pending" fill="#FF6B6B" name="Pending" />
                <Bar dataKey="inProgress" fill="#45B7D1" name="In Progress" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="bg-white p-4 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-4">Task Priority Distribution</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={priorityData}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="value"
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                >
                  {priorityData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TaskReports;
