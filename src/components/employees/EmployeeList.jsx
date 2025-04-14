import { useState } from 'react';
import { PencilIcon, TrashIcon } from '@heroicons/react/24/outline';

const EmployeeList = ({ employees, onEdit, onDelete }) => {
  const [expandedEmployee, setExpandedEmployee] = useState(null);

  const toggleExpand = (employeeId) => {
    setExpandedEmployee(expandedEmployee === employeeId ? null : employeeId);
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'active':
        return 'bg-green-100 text-green-800';
      case 'on_leave':
        return 'bg-yellow-100 text-yellow-800';
      case 'terminated':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="bg-white shadow rounded-lg overflow-hidden">
      <table className="min-w-full divide-y divide-gray-200">
        <thead className="bg-gray-50">
          <tr>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Name
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Position
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Department
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-200">
          {employees.map((employee) => (
            <>
              <tr
                key={employee.id}
                className="hover:bg-gray-50 cursor-pointer"
                onClick={() => toggleExpand(employee.id)}
              >
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="text-sm font-medium text-gray-900">{employee.name}</div>
                  <div className="text-sm text-gray-500">{employee.email}</div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="text-sm text-gray-900">{employee.position}</div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="text-sm text-gray-900">{employee.department}</div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span
                    className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(
                      employee.status
                    )}`}
                  >
                    {employee.status}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div className="flex space-x-2">
                    <button
                      onClick={(e) => {
                        e.stopPropagation();
                        onEdit(employee);
                      }}
                      className="p-2 rounded-md bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-600 hover:from-blue-100 hover:to-indigo-100 hover:text-blue-700 transition-all duration-200 shadow-sm border border-blue-100"
                    >
                      <PencilIcon className="h-5 w-5" />
                    </button>
                    <button
                      onClick={(e) => {
                        e.stopPropagation();
                        onDelete(employee.id);
                      }}
                      className="p-2 rounded-md bg-gradient-to-r from-red-50 to-pink-50 text-red-600 hover:from-red-100 hover:to-pink-100 hover:text-red-700 transition-all duration-200 shadow-sm border border-red-100"
                    >
                      <TrashIcon className="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
              {expandedEmployee === employee.id && (
                <tr>
                  <td colSpan="5" className="px-4 py-4">
                    <div className="space-y-4">
                      {/* Insurance Information */}
                      <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg shadow border border-blue-100">
                        <h4 className="text-md font-semibold text-blue-700 mb-2 flex items-center">
                          <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                          </svg>
                          Insurance Information
                        </h4>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div>
                            <p className="text-sm text-gray-600">
                              <span className="font-medium">Medical Insurance:</span>{' '}
                              {employee.thirdPartyInfo?.insurance?.medical ? 'Yes' : 'No'}
                            </p>
                            <p className="text-sm text-gray-600">
                              <span className="font-medium">Life Insurance:</span>{' '}
                              {employee.thirdPartyInfo?.insurance?.life ? 'Yes' : 'No'}
                            </p>
                          </div>
                          <div>
                            <p className="text-sm text-gray-600">
                              <span className="font-medium">Provider:</span>{' '}
                              {employee.thirdPartyInfo?.insurance?.provider || 'N/A'}
                            </p>
                            <p className="text-sm text-gray-600">
                              <span className="font-medium">Policy Number:</span>{' '}
                              {employee.thirdPartyInfo?.insurance?.policyNumber || 'N/A'}
                            </p>
                            <p className="text-sm text-gray-600">
                              <span className="font-medium">Expiry Date:</span>{' '}
                              {employee.thirdPartyInfo?.insurance?.expiryDate || 'N/A'}
                            </p>
                          </div>
                        </div>
                      </div>

                      {/* RSSB Information */}
                      <div className="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg shadow border border-green-100">
                        <h4 className="text-md font-semibold text-green-700 mb-2 flex items-center">
                          <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          RSSB Information
                        </h4>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div>
                            <p className="text-sm text-gray-600">
                              <span className="font-medium">Registered:</span>{' '}
                              {employee.thirdPartyInfo?.rssb?.registered ? 'Yes' : 'No'}
                            </p>
                          </div>
                          <div>
                            <p className="text-sm text-gray-600">
                              <span className="font-medium">Registration Number:</span>{' '}
                              {employee.thirdPartyInfo?.rssb?.registrationNumber || 'N/A'}
                            </p>
                            <p className="text-sm text-gray-600">
                              <span className="font-medium">Contribution Rate:</span>{' '}
                              {employee.thirdPartyInfo?.rssb?.contributionRate ? `${employee.thirdPartyInfo.rssb.contributionRate}%` : 'N/A'}
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              )}
            </>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default EmployeeList; 