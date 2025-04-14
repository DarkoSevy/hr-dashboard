import { useState } from 'react';
import { ChartBarIcon, ArrowDownTrayIcon } from '@heroicons/react/24/outline';
import TaskReports from '../../components/reports/TaskReports';
import EmployeeReports from '../../components/reports/EmployeeReports';

const Reports = () => {
  const [activeTab, setActiveTab] = useState('tasks');

  const handleExport = () => {
    console.log('Exporting report...');
    // Add export functionality here
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-semibold text-gray-900">Reports</h1>
        <button
          onClick={handleExport}
          className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-deepOceanBlue hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-deepOceanBlue"
        >
          <ArrowDownTrayIcon className="h-5 w-5 mr-2" />
          Export Report
        </button>
      </div>

      <div className="border-b border-gray-200">
        <nav className="-mb-px flex space-x-8">
          <button
            onClick={() => setActiveTab('tasks')}
            className={`${
              activeTab === 'tasks'
                ? 'border-deepOceanBlue text-deepOceanBlue'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            } whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm`}
          >
            <ChartBarIcon className="h-5 w-5 inline-block mr-2" />
            Task Reports
          </button>
          <button
            onClick={() => setActiveTab('employees')}
            className={`${
              activeTab === 'employees'
                ? 'border-deepOceanBlue text-deepOceanBlue'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            } whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm`}
          >
            <ChartBarIcon className="h-5 w-5 inline-block mr-2" />
            Employee Reports
          </button>
        </nav>
      </div>

      <div className="mt-6">
        {activeTab === 'tasks' ? <TaskReports /> : <EmployeeReports />}
      </div>
    </div>
  );
};

export default Reports; 