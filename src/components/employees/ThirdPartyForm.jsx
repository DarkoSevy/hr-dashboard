import { useState, useEffect } from 'react';

const ThirdPartyForm = ({ employee, onSubmit, onCancel }) => {
  const [formData, setFormData] = useState({
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
  });

  useEffect(() => {
    if (employee?.thirdPartyInfo) {
      setFormData(employee.thirdPartyInfo);
    }
  }, [employee]);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    const [section, field] = name.split('.');

    if (type === 'checkbox') {
      setFormData(prev => ({
        ...prev,
        [section]: {
          ...prev[section],
          [field]: checked
        }
      }));
    } else {
      setFormData(prev => ({
        ...prev,
        [section]: {
          ...prev[section],
          [field]: value
        }
      }));
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit(formData);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {/* Insurance Section */}
      <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg shadow border border-blue-100">
        <h3 className="text-lg font-semibold text-blue-700 mb-4 flex items-center">
          <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          Insurance Information
        </h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="space-y-2">
            <label className="flex items-center p-2 rounded-md hover:bg-blue-100 transition-colors">
              <input
                type="checkbox"
                name="insurance.medical"
                checked={formData.insurance.medical}
                onChange={handleChange}
                className="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <span className="text-gray-700">Medical Insurance</span>
            </label>
            <label className="flex items-center p-2 rounded-md hover:bg-blue-100 transition-colors">
              <input
                type="checkbox"
                name="insurance.life"
                checked={formData.insurance.life}
                onChange={handleChange}
                className="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <span className="text-gray-700">Life Insurance</span>
            </label>
          </div>
          <div className="space-y-2">
            <div>
              <label className="block text-sm font-medium text-gray-700">Insurance Provider</label>
              <input
                type="text"
                name="insurance.provider"
                value={formData.insurance.provider}
                onChange={handleChange}
                className="mt-1 block w-full rounded-md border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700">Policy Number</label>
              <input
                type="text"
                name="insurance.policyNumber"
                value={formData.insurance.policyNumber}
                onChange={handleChange}
                className="mt-1 block w-full rounded-md border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700">Expiry Date</label>
              <input
                type="date"
                name="insurance.expiryDate"
                value={formData.insurance.expiryDate}
                onChange={handleChange}
                className="mt-1 block w-full rounded-md border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>
      </div>

      {/* RSSB Section */}
      <div className="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-lg shadow border border-green-100">
        <h3 className="text-lg font-semibold text-green-700 mb-4 flex items-center">
          <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          RSSB Information
        </h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="space-y-2">
            <label className="flex items-center p-2 rounded-md hover:bg-green-100 transition-colors">
              <input
                type="checkbox"
                name="rssb.registered"
                checked={formData.rssb.registered}
                onChange={handleChange}
                className="mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
              />
              <span className="text-gray-700">RSSB Registered</span>
            </label>
          </div>
          <div className="space-y-2">
            <div>
              <label className="block text-sm font-medium text-gray-700">Registration Number</label>
              <input
                type="text"
                name="rssb.registrationNumber"
                value={formData.rssb.registrationNumber}
                onChange={handleChange}
                className="mt-1 block w-full rounded-md border-green-200 shadow-sm focus:border-green-500 focus:ring-green-500"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700">Contribution Rate (%)</label>
              <input
                type="number"
                name="rssb.contributionRate"
                value={formData.rssb.contributionRate}
                onChange={handleChange}
                className="mt-1 block w-full rounded-md border-green-200 shadow-sm focus:border-green-500 focus:ring-green-500"
              />
            </div>
          </div>
        </div>
      </div>

      {/* Form Actions */}
      <div className="flex justify-end space-x-4">
        <button
          type="button"
          onClick={onCancel}
          className="px-4 py-2 rounded-md bg-gradient-to-r from-gray-50 to-gray-100 text-gray-700 hover:from-gray-100 hover:to-gray-200 hover:text-gray-900 transition-all duration-200 shadow-sm border border-gray-200"
        >
          Cancel
        </button>
        <button
          type="submit"
          className="px-4 py-2 rounded-md bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 hover:shadow-md transition-all duration-200"
        >
          Save
        </button>
      </div>
    </form>
  );
};

export default ThirdPartyForm; 