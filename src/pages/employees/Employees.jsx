import { useState } from 'react';
import EmployeeForm from '../../components/employees/EmployeeForm';
import EmployeeList from '../../components/employees/EmployeeList';
import ThirdPartyForm from '../../components/employees/ThirdPartyForm';

const Employees = () => {
  const [employees, setEmployees] = useState([
    {
      id: 1,
      name: 'John Doe',
      email: 'john@example.com',
      position: 'Software Engineer',
      department: 'IT',
      startDate: '2023-01-15',
      salary: '5000',
      status: 'active',
      thirdPartyInfo: {
        insurance: {
          medical: true,
          life: true,
          provider: 'RSSB Insurance',
          policyNumber: 'INS12345',
          expiryDate: '2024-12-31',
        },
        rssb: {
          registered: true,
          registrationNumber: 'RSSB12345',
          contributionRate: '5',
        },
      },
    },
    // Add more sample employees as needed
  ]);

  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isThirdPartyFormOpen, setIsThirdPartyFormOpen] = useState(false);
  const [editingEmployee, setEditingEmployee] = useState(null);
  const [selectedEmployee, setSelectedEmployee] = useState(null);

  const handleAddEmployee = (employeeData) => {
    if (editingEmployee) {
      setEmployees(employees.map(emp => 
        emp.id === editingEmployee.id ? { ...emp, ...employeeData } : emp
      ));
    } else {
      const newEmployee = {
        ...employeeData,
        id: employees.length + 1,
      };
      setEmployees([...employees, newEmployee]);
    }
    setIsFormOpen(false);
    setEditingEmployee(null);
  };

  const handleEditEmployee = (employee) => {
    setEditingEmployee(employee);
    setIsFormOpen(true);
  };

  const handleDeleteEmployee = (employeeId) => {
    setEmployees(employees.filter(emp => emp.id !== employeeId));
  };

  const handleThirdPartySubmit = (thirdPartyData) => {
    if (selectedEmployee) {
      setEmployees(employees.map(emp => 
        emp.id === selectedEmployee.id 
          ? { ...emp, thirdPartyInfo: thirdPartyData } 
          : emp
      ));
    }
    setIsThirdPartyFormOpen(false);
    setSelectedEmployee(null);
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-semibold text-gray-900">Employees</h1>
        <div className="space-x-4">
          <button
            onClick={() => setIsThirdPartyFormOpen(true)}
            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-sageGray hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sageGray"
          >
            Manage Third Party Info
          </button>
          <button
            onClick={() => setIsFormOpen(true)}
            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-deepOceanBlue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-deepOceanBlue"
          >
            Add Employee
          </button>
        </div>
      </div>

      {isFormOpen && (
        <div className="bg-white p-6 rounded-lg shadow">
          <EmployeeForm
            employee={editingEmployee}
            onSubmit={handleAddEmployee}
            onCancel={() => {
              setIsFormOpen(false);
              setEditingEmployee(null);
            }}
          />
        </div>
      )}

      {isThirdPartyFormOpen && (
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700">Select Employee</label>
            <select
              value={selectedEmployee?.id || ''}
              onChange={(e) => {
                const employee = employees.find(emp => emp.id === parseInt(e.target.value));
                setSelectedEmployee(employee);
              }}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-deepOceanBlue focus:ring-deepOceanBlue"
            >
              <option value="">Select an employee</option>
              {employees.map(employee => (
                <option key={employee.id} value={employee.id}>
                  {employee.name}
                </option>
              ))}
            </select>
          </div>
          {selectedEmployee && (
            <ThirdPartyForm
              employee={selectedEmployee}
              onSubmit={handleThirdPartySubmit}
              onCancel={() => {
                setIsThirdPartyFormOpen(false);
                setSelectedEmployee(null);
              }}
            />
          )}
        </div>
      )}

      <EmployeeList
        employees={employees}
        onEdit={handleEditEmployee}
        onDelete={handleDeleteEmployee}
      />
    </div>
  );
};

export default Employees; 