import { useState, useEffect } from 'react';
import { XMarkIcon } from '@heroicons/react/24/outline';

const ThirdPartyForm = ({ employee, onSubmit, onClose }) => {
  const [formData, setFormData] = useState({
    insurance: {
      medical: false,
      life: false,
      provider: '',
      policyNumber: '',
      expiryDate: ''
    },
    rssb: {
      registered: false,
      registrationNumber: '',
      contributionRate: ''
    }
  });

  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (employee?.thirdPartyInfo) {
      setFormData({
        insurance: {
          medical: employee.thirdPartyInfo.insurance?.medical || false,
          life: employee.thirdPartyInfo.insurance?.life || false,
          provider: employee.thirdPartyInfo.insurance?.provider || '',
          policyNumber: employee.thirdPartyInfo.insurance?.policyNumber || '',
          expiryDate: employee.thirdPartyInfo.insurance?.expiryDate || ''
        },
        rssb: {
          registered: employee.thirdPartyInfo.rssb?.registered || false,
          registrationNumber: employee.thirdPartyInfo.rssb?.registrationNumber || '',
          contributionRate: employee.thirdPartyInfo.rssb?.contributionRate || ''
        }
      });
    }
  }, [employee]);

  const validateForm = () => {
    const newErrors = {};

    // Insurance validation
    if (formData.insurance.medical || formData.insurance.life) {
      if (!formData.insurance.provider) {
        newErrors.insurance = { ...newErrors.insurance, provider: 'Provider is required' };
      }
      if (!formData.insurance.policyNumber) {
        newErrors.insurance = { ...newErrors.insurance, policyNumber: 'Policy number is required' };
      }
      if (!formData.insurance.expiryDate) {
        newErrors.insurance = { ...newErrors.insurance, expiryDate: 'Expiry date is required' };
      }
    }

    // RSSB validation
    if (formData.rssb.registered) {
      if (!formData.rssb.registrationNumber) {
        newErrors.rssb = { ...newErrors.rssb, registrationNumber: 'Registration number is required' };
      }
      if (!formData.rssb.contributionRate) {
        newErrors.rssb = { ...newErrors.rssb, contributionRate: 'Contribution rate is required' };
      } else if (isNaN(formData.rssb.contributionRate) || formData.rssb.contributionRate <= 0) {
        newErrors.rssb = { ...newErrors.rssb, contributionRate: 'Contribution rate must be a positive number' };
      }
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validateForm()) {
      onSubmit(formData);
    }
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    const [section, field] = name.split('.');

    setFormData(prev => ({
      ...prev,
      [section]: {
        ...prev[section],
        [field]: type === 'checkbox' ? checked : value
      }
    }));

    // Clear error when user starts typing
    if (errors[section]?.[field]) {
      setErrors(prev => ({
        ...prev,
        [section]: {
          ...prev[section],
          [field]: ''
        }
      }));
    }
  };

  return (
    <div className="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4">
      <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full">
        <div className="flex justify-between items-center p-6 border-b">
          <h2 className="text-xl font-semibold text-gray-900">
            Third Party Information - {employee?.name}
          </h2>
          <button
            onClick={onClose}
            className="text-gray-400 hover:text-gray-500"
          >
            <XMarkIcon className="h-6 w-6" />
          </button>
        </div>

        <div className="p-6 space-y-6">
          {/* Insurance Section */}
          <div className="space-y-4">
            <h3 className="text-lg font-medium text-gray-900">Insurance Information</h3>
            <div className="space-y-4">
              <div className="flex items-center space-x-4">
                <div className="flex items-center">
                  <input
                    type="checkbox"
                    name="insurance.medical"
                    checked={formData.insurance.medical}
                    onChange={handleChange}
                    className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label className="ml-2 block text-sm text-gray-900">
                    Medical Insurance
                  </label>
                </div>
                <div className="flex items-center">
                  <input
                    type="checkbox"
                    name="insurance.life"
                    checked={formData.insurance.life}
                    onChange={handleChange}
                    className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label className="ml-2 block text-sm text-gray-900">
                    Life Insurance
                  </label>
                </div>
              </div>

              {(formData.insurance.medical || formData.insurance.life) && (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">
                      Provider *
                    </label>
                    <input
                      type="text"
                      name="insurance.provider"
                      value={formData.insurance.provider}
                      onChange={handleChange}
                      className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ${
                        errors.insurance?.provider ? 'border-red-500' : ''
                      }`}
                    />
                    {errors.insurance?.provider && (
                      <p className="mt-1 text-sm text-red-600">{errors.insurance.provider}</p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700">
                      Policy Number *
                    </label>
                    <input
                      type="text"
                      name="insurance.policyNumber"
                      value={formData.insurance.policyNumber}
                      onChange={handleChange}
                      className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ${
                        errors.insurance?.policyNumber ? 'border-red-500' : ''
                      }`}
                    />
                    {errors.insurance?.policyNumber && (
                      <p className="mt-1 text-sm text-red-600">{errors.insurance.policyNumber}</p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700">
                      Expiry Date *
                    </label>
                    <input
                      type="date"
                      name="insurance.expiryDate"
                      value={formData.insurance.expiryDate}
                      onChange={handleChange}
                      className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ${
                        errors.insurance?.expiryDate ? 'border-red-500' : ''
                      }`}
                    />
                    {errors.insurance?.expiryDate && (
                      <p className="mt-1 text-sm text-red-600">{errors.insurance.expiryDate}</p>
                    )}
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* RSSB Section */}
          <div className="space-y-4">
            <h3 className="text-lg font-medium text-gray-900">RSSB Information</h3>
            <div className="space-y-4">
              <div className="flex items-center">
                <input
                  type="checkbox"
                  name="rssb.registered"
                  checked={formData.rssb.registered}
                  onChange={handleChange}
                  className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label className="ml-2 block text-sm text-gray-900">
                  Registered with RSSB
                </label>
              </div>

              {formData.rssb.registered && (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">
                      Registration Number *
                    </label>
                    <input
                      type="text"
                      name="rssb.registrationNumber"
                      value={formData.rssb.registrationNumber}
                      onChange={handleChange}
                      className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ${
                        errors.rssb?.registrationNumber ? 'border-red-500' : ''
                      }`}
                    />
                    {errors.rssb?.registrationNumber && (
                      <p className="mt-1 text-sm text-red-600">{errors.rssb.registrationNumber}</p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700">
                      Contribution Rate (%) *
                    </label>
                    <input
                      type="number"
                      name="rssb.contributionRate"
                      value={formData.rssb.contributionRate}
                      onChange={handleChange}
                      min="0"
                      step="0.01"
                      className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ${
                        errors.rssb?.contributionRate ? 'border-red-500' : ''
                      }`}
                    />
                    {errors.rssb?.contributionRate && (
                      <p className="mt-1 text-sm text-red-600">{errors.rssb.contributionRate}</p>
                    )}
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Form Actions */}
          <div className="flex justify-end space-x-3">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Cancel
            </button>
            <button
              type="button"
              onClick={handleSubmit}
              className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ThirdPartyForm; 