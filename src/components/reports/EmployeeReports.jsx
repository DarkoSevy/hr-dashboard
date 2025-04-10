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

const EmployeeReports = () => {
  const departmentData = [
    { name: 'HR', value: 15 },
    { name: 'IT', value: 20 },
    { name: 'Finance', value: 12 },
    { name: 'Marketing', value: 8 },
  ];

  const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042'];

  const employeeTrendData = [
    { name: 'Jan', newHires: 5, terminations: 2 },
    { name: 'Feb', newHires: 3, terminations: 1 },
    { name: 'Mar', newHires: 4, terminations: 0 },
    { name: 'Apr', newHires: 6, terminations: 1 },
  ];

  return (
    <div className="p-6">
      <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div>
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            Department Distribution
          </h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={departmentData}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="value"
                  label={({ name, percent }) =>
                    `${name} ${(percent * 100).toFixed(0)}%`
                  }
                >
                  {departmentData.map((entry, index) => (
                    <Cell
                      key={`cell-${index}`}
                      fill={COLORS[index % COLORS.length]}
                    />
                  ))}
                </Pie>
                <Tooltip />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div>
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            Employee Trend
          </h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={employeeTrendData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Bar dataKey="newHires" fill="#10B981" />
                <Bar dataKey="terminations" fill="#EF4444" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>

      <div className="mt-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">
          Employee Summary
        </h3>
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="px-4 py-5 sm:p-6">
              <dt className="text-sm font-medium text-gray-500 truncate">
                Total Employees
              </dt>
              <dd className="mt-1 text-3xl font-semibold text-gray-900">
                55
              </dd>
            </div>
          </div>
          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="px-4 py-5 sm:p-6">
              <dt className="text-sm font-medium text-gray-500 truncate">
                Active Employees
              </dt>
              <dd className="mt-1 text-3xl font-semibold text-gray-900">
                50
              </dd>
            </div>
          </div>
          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="px-4 py-5 sm:p-6">
              <dt className="text-sm font-medium text-gray-500 truncate">
                On Leave
              </dt>
              <dd className="mt-1 text-3xl font-semibold text-gray-900">
                5
              </dd>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default EmployeeReports; 