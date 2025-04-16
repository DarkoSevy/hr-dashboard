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
  LineChart,
  Line,
} from 'recharts';

const EmployeeReports = () => {
  // Sample data - replace with actual data from your state/API
  const departmentData = [
    { name: 'HR', value: 8 },
    { name: 'IT', value: 15 },
    { name: 'Finance', value: 6 },
    { name: 'Marketing', value: 10 },
    { name: 'Operations', value: 12 },
  ];

  const hiringTrendData = [
    { month: 'Jan', newHires: 3 },
    { month: 'Feb', newHires: 5 },
    { month: 'Mar', newHires: 2 },
    { month: 'Apr', newHires: 4 },
  ];

  const COLORS = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEEAD'];

  // Third-party data
  const insuranceData = [
    { name: 'Medical Insurance', value: 45 },
    { name: 'Life Insurance', value: 40 },
    { name: 'Not Covered', value: 6 },
  ];

  const rssbData = [
    { name: 'Registered', value: 48 },
    { name: 'Not Registered', value: 3 },
  ];

  const documentData = [
    { name: 'Complete', value: 42 },
    { name: 'Incomplete', value: 9 },
  ];

  return (
    <div className="space-y-8">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="bg-mintGray p-4 rounded-lg">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-2">Total Employees</h3>
          <p className="text-3xl font-bold text-sageGray">51</p>
        </div>
        <div className="bg-mintGray p-4 rounded-lg">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-2">Active Employees</h3>
          <p className="text-3xl font-bold text-sageGray">45</p>
        </div>
        <div className="bg-mintGray p-4 rounded-lg">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-2">On Leave</h3>
          <p className="text-3xl font-bold text-sageGray">6</p>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-white p-4 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-4">Department Distribution</h3>
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
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                >
                  {departmentData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="bg-white p-4 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-4">Hiring Trend</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={hiringTrendData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Line
                  type="monotone"
                  dataKey="newHires"
                  stroke="#4ECDC4"
                  strokeWidth={2}
                  name="New Hires"
                />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>

      <div className="bg-white p-4 rounded-lg shadow">
        <h3 className="text-lg font-semibold text-deepOceanBlue mb-4">Employee Status Overview</h3>
        <div className="h-80">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={[
              { status: 'Active', count: 45 },
              { status: 'On Leave', count: 6 },
              { status: 'Terminated', count: 0 },
            ]}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="status" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Bar dataKey="count" fill="#45B7D1" name="Count" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Third-party Information Section */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-4 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-4">Insurance Coverage</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={insuranceData}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="value"
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                >
                  {insuranceData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="bg-white p-4 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-4">RSSB Registration</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={rssbData}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="value"
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                >
                  {rssbData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="bg-white p-4 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-deepOceanBlue mb-4">Document Status</h3>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={documentData}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="value"
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                >
                  {documentData.map((entry, index) => (
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

export default EmployeeReports; 