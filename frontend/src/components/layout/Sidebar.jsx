import { Link, useLocation } from 'react-router-dom';
import {
  HomeIcon,
  ClipboardDocumentListIcon,
  UserGroupIcon,
  ChartBarIcon,
} from '@heroicons/react/24/outline';

const Sidebar = () => {
  const location = useLocation();

  const navItems = [
    { name: 'Dashboard', path: '/', icon: HomeIcon },
    { name: 'Tasks', path: '/tasks', icon: ClipboardDocumentListIcon },
    { name: 'Employees', path: '/employees', icon: UserGroupIcon },
    { name: 'Reports', path: '/reports', icon: ChartBarIcon },
  ];

  return (
    <aside className="w-64 bg-deepOceanBlue bg-opacity-60 backdrop-blur-md text-plainWhite p-4 h-screen fixed">
      <div className="mb-8">
        <h2 className="text-2xl font-bold">HR Dashboard</h2>
      </div>
      <nav>
        <ul className="space-y-2">
          {navItems.map((item) => {
            const Icon = item.icon;
            const isActive = location.pathname === item.path;
            return (
              <li key={item.name}>
                <Link
                  to={item.path}
                  className={`flex items-center space-x-3 px-4 py-2 rounded-md transition-colors ${
                    isActive
                      ? 'bg-mintGray bg-opacity-20 text-plainWhite'
                      : 'hover:bg-mintGray hover:bg-opacity-10'
                  }`}
                >
                  <Icon className="h-5 w-5" />
                  <span>{item.name}</span>
                </Link>
              </li>
            );
          })}
        </ul>
      </nav>
    </aside>
  );
};

export default Sidebar; 