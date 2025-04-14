import { useState, useEffect } from 'react';
import ThirdPartyForm from './ThirdPartyForm';

const EmployeeForm = ({ employee, onSubmit, onCancel }) => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    position: '',
    department: '',
    startDate: '',
    salary: '',
    status: 'active',
    thirdPartyInfo: {
      insurance: {
        medical: false,
        life: false,
        provider: '',
        policyNumber: '',
        expiryDate: '',
      },
      rssb: {
        registered: false,
        registrationNumber: '',
        contributionRate: '',
      },
      documents: {
        idCopy: false,
        degreeCertificates: false,
        professionalCertificates: false,
        contract: false,
        otherDocuments: '',
      },
    },
  });

  useEffect(() => {
    if (employee) {
      setFormData(employee);
    }
  }, [employee]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleThirdPartyChange = (thirdPartyData) => {
    setFormData(prev => ({
      ...prev,
      thirdPartyInfo: thirdPartyData
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit(formData);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="bg-white p-6 rounded-lg shadow">
        <h3 className="text-lg font-semibold text-deepOceanBlue mb-4">Basic Information</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Name</label>
            <input
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-deepOceanBlue focus:ring-deepOceanBlue"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Email</label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-deepOceanBlue focus:ring-deepOceanBlue"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Position</label>
            <input
              type="text"
              name="position"
              value={formData.position}
              onChange={handleChange}
              required
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-deepOceanBlue focus:ring-deepOceanBlue"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Department</label>
            <select
              name="department"
              value={formData.department}
              onChange={handleChange}
              required
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-deepOceanBlue focus:ring-deepOceanBlue"
            >
              <option value="">Select Department</option>
              <option value="HR">HR</option>
              <option value="IT">IT</option>
              <option value="Finance">Finance</option>
              <option value="Marketing">Marketing</option>
              <option value="Operations">Operations</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Start Date</label>
            <input
              type="date"
              name="startDate"
              value={formData.startDate}
              onChange={handleChange}
              required
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-deepOceanBlue focus:ring-deepOceanBlue"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Salary</label>
            <input
              type="number"
              name="salary"
              value={formData.salary}
              onChange={handleChange}
              required
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-deepOceanBlue focus:ring-deepOceanBlue"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Status</label>
            <select
              name="status"
              value={formData.status}
              onChange={handleChange}
              required
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-deepOceanBlue focus:ring-deepOceanBlue"
            >
              <option value="active">Active</option>
              <option value="on_leave">On Leave</option>
              <option value="terminated">Terminated</option>
            </select>
          </div>
        </div>
      </div>

      {/* Third Party Information */}
      <ThirdPartyForm
        employee={employee}
        onSubmit={handleThirdPartyChange}
        onCancel={onCancel}
      />

      {/* Form Actions */}
      <div className="flex justify-end space-x-4">
        <button
          type="button"
          onClick={onCancel}
          className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-deepOceanBlue"
        >
          Cancel
        </button>
        <button
          type="submit"
          className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-deepOceanBlue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-deepOceanBlue"
        >
          {employee ? 'Update' : 'Add'} Employee
        </button>
      </div>
    </form>
  );
};

export default EmployeeForm; 