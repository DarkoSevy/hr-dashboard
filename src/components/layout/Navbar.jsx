import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { ArrowRightOnRectangleIcon } from '@heroicons/react/24/outline';

const Navbar = () => {
  const navigate = useNavigate();
  const { logout } = useAuth();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <nav className="bg-plainWhite shadow p-4">
      <div className="flex justify-between items-center">
        <h1 className="text-xl font-bold text-deepOceanBlue">HR Dashboard</h1>
        <button
          onClick={handleLogout}
          className="flex items-center space-x-2 text-deepOceanBlue hover:text-mintGray transition-colors"
        >
          <span>Logout</span>
          <ArrowRightOnRectangleIcon className="h-5 w-5" />
        </button>
      </div>
    </nav>
  );
};

export default Navbar; 