import { useState, useEffect } from 'react';
import { MagnifyingGlassIcon, FunnelIcon } from '@heroicons/react/24/outline';
import EmployeeForm from '../../components/employees/EmployeeForm';
import EmployeeList from '../../components/employees/EmployeeList';
import ThirdPartyForm from '../../components/employees/ThirdPartyForm';

const BASE_API_URL = '/api';

// Default fetch options for all API calls
const defaultFetchOptions = {
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
};

// API wrapper with error handling
const apiCall = async (url, options = {}) => {
  try {
    const response = await fetch(url, {
      ...defaultFetchOptions,
      ...options,
    });

    // Handle 401 Unauthorized
    if (response.status === 401) {
      window.location.href = '/login';
      return null;
    }

    // Parse JSON response
    let data;
    try {
      data = await response.json();
    } catch (e) {
      throw new Error('Invalid response format');
    }

    // Handle error responses
    if (!response.ok) {
      throw new Error(data.message || `HTTP error! status: ${response.status}`);
    }

    return data;
  } catch (error) {
    console.error('API call failed:', error);
    throw error;
  }
};

const Employees = () => {
  const [employees, setEmployees] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isThirdPartyFormOpen, setIsThirdPartyFormOpen] = useState(false);
  const [editingEmployee, setEditingEmployee] = useState(null);
  const [selectedEmployee, setSelectedEmployee] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [filters, setFilters] = useState({
    department: '',
    status: '',
  });
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    fetchEmployees();
  }, []);

  const fetchEmployees = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const data = await apiCall(`${BASE_API_URL}/employees`, { method: 'GET' });
      
      // Transform the data to parse JSON strings and format dates
      const transformedData = data.map(emp => ({
        ...emp,
        thirdPartyInfo: emp.third_party_info && typeof emp.third_party_info === 'string' 
          ? JSON.parse(emp.third_party_info)
          : emp.third_party_info || null,
        startDate: emp.start_date,
        status: emp.status || 'active'
      }));

      setEmployees(transformedData);
    } catch (err) {
      setError('Failed to fetch employees: ' + err.message);
      console.error('Error fetching employees:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleAddEmployee = async (employeeData) => {
    try {
      setLoading(true);
      setError(null);
      
      // Validate required fields
      const requiredFields = ['name', 'email', 'position', 'department', 'start_date', 'salary'];
      const missingFields = requiredFields.filter(field => !employeeData[field]);
      
      if (missingFields.length > 0) {
        throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
      }

      // Validate email format
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(employeeData.email)) {
        throw new Error('Invalid email format');
      }

      // Validate salary is a positive number
      if (isNaN(employeeData.salary) || employeeData.salary <= 0) {
        throw new Error('Salary must be a positive number');
      }

      const apiData = {
        ...employeeData,
        third_party_info: employeeData.thirdPartyInfo ? JSON.stringify(employeeData.thirdPartyInfo) : null
      };

      const url = editingEmployee 
        ? `${BASE_API_URL}/employees/${editingEmployee.id}`
        : `${BASE_API_URL}/employees`;

      await apiCall(url, {
        method: editingEmployee ? 'PUT' : 'POST',
        body: JSON.stringify(apiData)
      });

      await fetchEmployees(); // Refresh the list
      setIsFormOpen(false);
      setEditingEmployee(null);
    } catch (err) {
      setError(err.message);
      console.error('Error saving employee:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleEditEmployee = (employee) => {
    setEditingEmployee(employee);
    setIsFormOpen(true);
  };

  const handleCloseForm = () => {
    setIsFormOpen(false);
    setEditingEmployee(null);
  };

  const handleDeleteEmployee = async (employeeId) => {
    if (!window.confirm('Are you sure you want to delete this employee?')) return;
    
    try {
      setLoading(true);
      setError(null);
      
      await apiCall(`${BASE_API_URL}/employees/${employeeId}`, {
        method: 'DELETE'
      });
      
      await fetchEmployees(); // Refresh the list
    } catch (err) {
      setError('Failed to delete employee: ' + err.message);
      console.error('Error deleting employee:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleThirdPartySubmit = async (thirdPartyData) => {
    if (!selectedEmployee) return;
    
    try {
      setLoading(true);
      setError(null);
      
      await apiCall(`${BASE_API_URL}/employees/${selectedEmployee.id}/third-party`, {
        method: 'PUT',
        body: JSON.stringify(thirdPartyData)
      });
      
      await fetchEmployees(); // Refresh the list
      setIsThirdPartyFormOpen(false);
      setSelectedEmployee(null);
    } catch (err) {
      setError('Failed to update third party information: ' + err.message);
      console.error('Error updating third party info:', err);
    } finally {
      setLoading(false);
    }
  };

  // Filter employees based on search query and filters
  const filteredEmployees = employees.filter(employee => {
    const matchesSearch = 
      employee.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      employee.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
      employee.position.toLowerCase().includes(searchQuery.toLowerCase()) ||
      employee.department.toLowerCase().includes(searchQuery.toLowerCase());
    
    const matchesDepartment = !filters.department || employee.department === filters.department;
    const matchesStatus = !filters.status || employee.status === filters.status;
    
    return matchesSearch && matchesDepartment && matchesStatus;
  });

  // Get unique departments for filter options
  const departments = [...new Set(employees.map(emp => emp.department))];
  const statuses = ['active', 'on_leave', 'terminated'];

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Employees</h1>
        <button
          onClick={() => {
            setEditingEmployee(null);
            setIsFormOpen(true);
          }}
          className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
        >
          Add Employee
        </button>
      </div>

      {/* Search and Filters */}
      <div className="bg-white p-6 rounded-xl shadow-sm">
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div className="flex-1 w-full md:w-auto">
            <div className="relative">
              <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                type="text"
                placeholder="Search employees..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
          </div>
          
          <button
            onClick={() => setShowFilters(!showFilters)}
            className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <FunnelIcon className="h-5 w-5 mr-2" />
            Filters
          </button>
        </div>

        {showFilters && (
          <div className="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700">Department</label>
              <select
                value={filters.department}
                onChange={(e) => setFilters({ ...filters, department: e.target.value })}
                className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="">All Departments</option>
                {departments.map(dept => (
                  <option key={dept} value={dept}>{dept}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700">Status</label>
              <select
                value={filters.status}
                onChange={(e) => setFilters({ ...filters, status: e.target.value })}
                className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="">All Statuses</option>
                {statuses.map(status => (
                  <option key={status} value={status}>
                    {status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')}
                  </option>
                ))}
              </select>
            </div>
          </div>
        )}
      </div>

      {/* Error Message */}
      {error && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative">
          {error}
          <button
            onClick={() => setError(null)}
            className="absolute top-0 bottom-0 right-0 px-4 py-3"
          >
            <span className="sr-only">Dismiss</span>
            ×
          </button>
        </div>
      )}

      {/* Loading State */}
      {loading ? (
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
      ) : (
        <>
          {/* Employee Form */}
          {isFormOpen && (
            <EmployeeForm
              employee={editingEmployee}
              onSubmit={handleAddEmployee}
              onClose={handleCloseForm}
            />
          )}

          {/* Third Party Form */}
          {isThirdPartyFormOpen && (
            <ThirdPartyForm
              employee={selectedEmployee}
              onSubmit={handleThirdPartySubmit}
              onClose={() => {
                setIsThirdPartyFormOpen(false);
                setSelectedEmployee(null);
              }}
            />
          )}

          {/* Employee List */}
          <div className="bg-white rounded-xl shadow-sm">
            <EmployeeList
              employees={filteredEmployees}
              onEdit={handleEditEmployee}
              onDelete={handleDeleteEmployee}
            />
          </div>
        </>
      )}
    </div>
  );
};

export default Employees; 