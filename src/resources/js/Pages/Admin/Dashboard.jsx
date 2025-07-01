import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard({ auth, stats = {} }) {
    const {
        total_users = 0,
        total_goals = 0,
        total_notes = 0,
        total_events = 0,
        active_users = 0,
        completed_goals = 0,
        premium_users = 0,
        monthly_revenue = 0,
        recent_users = [],
        recent_goals = [],
        recent_activities = []
    } = stats;

    // Calculate metrics
    const userGrowth = total_users > 0 ? Math.round((active_users / total_users) * 100) : 0;
    const goalCompletion = total_goals > 0 ? Math.round((completed_goals / total_goals) * 100) : 0;
    const premiumConversion = total_users > 0 ? Math.round((premium_users / total_users) * 100) : 0;

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount || 0);
    };

    const StatCard = ({ title, value, subtitle, icon, bgColor, textColor, trend = null }) => (
        <div className="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
            <div className="p-6">
                <div className="flex items-center justify-between">
                    <div className="flex-1">
                        <p className="text-sm font-medium text-gray-600 uppercase tracking-wider">{title}</p>
                        <p className={`text-3xl font-bold mt-2 ${textColor}`}>{value}</p>
                        {subtitle && <p className="text-sm text-gray-500 mt-1">{subtitle}</p>}
                        {trend && (
                            <div className="flex items-center mt-2">
                                <span className={`text-xs font-medium px-2 py-1 rounded-full ${
                                    trend.direction === 'up' ? 'bg-green-100 text-green-800' : 
                                    trend.direction === 'down' ? 'bg-red-100 text-red-800' : 
                                    'bg-gray-100 text-gray-800'
                                }`}>
                                    {trend.direction === 'up' && '↗'} 
                                    {trend.direction === 'down' && '↘'} 
                                    {trend.value}
                                </span>
                            </div>
                        )}
                    </div>
                    <div className="flex-shrink-0">
                        <div className={`w-12 h-12 ${bgColor} rounded-xl flex items-center justify-center shadow-lg`}>
                            {icon}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );

    const ProgressCard = ({ title, value, total, color, description }) => {
        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
        
        return (
            <div className="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-lg font-semibold text-gray-900">{title}</h3>
                    <span className="text-2xl font-bold text-gray-900">{percentage}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-3 mb-2">
                    <div 
                        className={`${color} h-3 rounded-full transition-all duration-500 ease-out`}
                        style={{ width: `${percentage}%` }}
                    ></div>
                </div>
                <div className="flex justify-between text-sm text-gray-600">
                    <span>{value.toLocaleString()} of {total.toLocaleString()}</span>
                    <span>{description}</span>
                </div>
            </div>
        );
    };

    return (
        <AdminLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Admin Dashboard
                    </h2>
                    <div className="text-sm text-gray-500">
                        Last updated: {new Date().toLocaleString('vi-VN')}
                    </div>
                </div>
            }
        >
            <Head title="Admin Dashboard" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Welcome Section */}
                    <div className="mb-8">
                        <div className="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg">
                            <div className="p-8 text-white">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h1 className="text-3xl font-bold mb-2">Welcome back, {auth.user.name}!</h1>
                                        <p className="text-blue-100 text-lg">Here's your platform overview for today</p>
                                    </div>
                                    <div className="hidden md:block">
                                        <div className="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                            <svg className="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Main Statistics */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <StatCard
                            title="Total Users"
                            value={total_users.toLocaleString()}
                            subtitle={`${active_users} active users`}
                            bgColor="bg-gradient-to-br from-blue-500 to-blue-600"
                            textColor="text-blue-600"
                            trend={{ direction: 'up', value: '+12% this month' }}
                            icon={
                                <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                                </svg>
                            }
                        />

                        <StatCard
                            title="Total Goals"
                            value={total_goals.toLocaleString()}
                            subtitle={`${completed_goals} completed`}
                            bgColor="bg-gradient-to-br from-green-500 to-green-600"
                            textColor="text-green-600"
                            trend={{ direction: 'up', value: '+8% this week' }}
                            icon={
                                <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            }
                        />

                        <StatCard
                            title="Total Notes"
                            value={total_notes.toLocaleString()}
                            subtitle="Active notes"
                            bgColor="bg-gradient-to-br from-yellow-500 to-orange-500"
                            textColor="text-orange-600"
                            trend={{ direction: 'up', value: '+15% this month' }}
                            icon={
                                <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
                                </svg>
                            }
                        />

                        <StatCard
                            title="Revenue"
                            value={formatCurrency(monthly_revenue)}
                            subtitle="This month"
                            bgColor="bg-gradient-to-br from-purple-500 to-purple-600"
                            textColor="text-purple-600"
                            trend={{ direction: 'up', value: '+23% vs last month' }}
                            icon={
                                <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" />
                                </svg>
                            }
                        />
                    </div>

                    {/* Progress Metrics */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <ProgressCard
                            title="User Engagement"
                            value={active_users}
                            total={total_users}
                            color="bg-gradient-to-r from-blue-500 to-blue-600"
                            description="Active users"
                        />
                        <ProgressCard
                            title="Goal Completion"
                            value={completed_goals}
                            total={total_goals}
                            color="bg-gradient-to-r from-green-500 to-green-600"
                            description="Goals completed"
                        />
                        <ProgressCard
                            title="Premium Conversion"
                            value={premium_users}
                            total={total_users}
                            color="bg-gradient-to-r from-purple-500 to-purple-600"
                            description="Premium users"
                        />
                    </div>

                    {/* Activity Dashboard */}
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        {/* Recent Users */}
                        <div className="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <div className="flex items-center justify-between">
                                    <h3 className="text-lg font-semibold text-gray-900">Recent Users</h3>
                                    <a href={route('admin.users')} className="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        View all →
                                    </a>
                                </div>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    {recent_users.length > 0 ? (
                                        recent_users.slice(0, 5).map((user, index) => (
                                            <div key={index} className="flex items-center space-x-3">
                                                <div className="flex-shrink-0">
                                                    <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                        {(user.display_name || user.name || 'U').charAt(0).toUpperCase()}
                                                    </div>
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm font-medium text-gray-900 truncate">
                                                        {user.display_name || user.name || 'Unknown User'}
                                                    </p>
                                                    <p className="text-sm text-gray-500 truncate">{user.email || 'No email'}</p>
                                                </div>
                                                <div className="text-xs text-gray-400">
                                                    {user.created_at ? new Date(user.created_at).toLocaleDateString('vi-VN') : 'Recent'}
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="text-center py-8">
                                            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.712-3.714M14 40v-4a9.971 9.971 0 01.712-3.714M28 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            <p className="text-gray-500 text-sm mt-2">No recent users</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Recent Goals */}
                        <div className="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <div className="flex items-center justify-between">
                                    <h3 className="text-lg font-semibold text-gray-900">Recent Goals</h3>
                                    <a href={route('admin.goals')} className="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        View all →
                                    </a>
                                </div>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    {recent_goals.length > 0 ? (
                                        recent_goals.slice(0, 5).map((goal, index) => (
                                            <div key={index} className="flex items-start space-x-3">
                                                <div className="flex-shrink-0">
                                                    <div className={`w-3 h-3 rounded-full mt-2 ${
                                                        goal.status === 'completed' ? 'bg-green-500' :
                                                        goal.status === 'in_progress' ? 'bg-blue-500' :
                                                        goal.status === 'cancelled' ? 'bg-red-500' : 'bg-gray-400'
                                                    }`}></div>
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm font-medium text-gray-900 truncate">
                                                        {goal.title || 'Untitled Goal'}
                                                    </p>
                                                    <p className="text-sm text-gray-500">
                                                        By {goal.user_name || goal.user?.display_name || 'Unknown User'}
                                                    </p>
                                                    <div className="text-xs text-gray-400 mt-1">
                                                        {goal.created_at ? new Date(goal.created_at).toLocaleDateString('vi-VN') : 'Recent'}
                                                    </div>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="text-center py-8">
                                            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2H9z" />
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4" />
                                            </svg>
                                            <p className="text-gray-500 text-sm mt-2">No recent goals</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* System Status */}
                        <div className="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h3 className="text-lg font-semibold text-gray-900">System Status</h3>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-3">
                                            <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                                            <span className="text-sm font-medium text-gray-700">Database</span>
                                        </div>
                                        <span className="text-sm text-green-600 font-medium">Operational</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-3">
                                            <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                                            <span className="text-sm font-medium text-gray-700">API Services</span>
                                        </div>
                                        <span className="text-sm text-green-600 font-medium">Operational</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-3">
                                            <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                                            <span className="text-sm font-medium text-gray-700">File Storage</span>
                                        </div>
                                        <span className="text-sm text-green-600 font-medium">Operational</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-3">
                                            <div className="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                            <span className="text-sm font-medium text-gray-700">Email Service</span>
                                        </div>
                                        <span className="text-sm text-yellow-600 font-medium">Monitoring</span>
                                    </div>
                                </div>
                                
                                <div className="mt-6 pt-4 border-t border-gray-200">
                                    <div className="text-center">
                                        <p className="text-sm text-gray-600">Server Uptime</p>
                                        <p className="text-lg font-semibold text-green-600">99.9%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Quick Actions */}
                    <div className="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h3 className="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        </div>
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <a href={route('admin.users')} className="group block p-4 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-xl transition-all duration-200">
                                    <div className="flex items-center">
                                        <div className="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                                            </svg>
                                        </div>
                                        <div className="ml-3">
                                            <span className="font-semibold text-blue-900">Manage Users</span>
                                            <p className="text-sm text-blue-700">View & edit users</p>
                                        </div>
                                    </div>
                                </a>

                                <a href={route('admin.goals')} className="group block p-4 bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-xl transition-all duration-200">
                                    <div className="flex items-center">
                                        <div className="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div className="ml-3">
                                            <span className="font-semibold text-green-900">View Goals</span>
                                            <p className="text-sm text-green-700">Track progress</p>
                                        </div>
                                    </div>
                                </a>

                                <a href={route('admin.reports')} className="group block p-4 bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-xl transition-all duration-200">
                                    <div className="flex items-center">
                                        <div className="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                            </svg>
                                        </div>
                                        <div className="ml-3">
                                            <span className="font-semibold text-purple-900">View Reports</span>
                                            <p className="text-sm text-purple-700">Analytics & insights</p>
                                        </div>
                                    </div>
                                </a>

                                <a href={route('admin.settings')} className="group block p-4 bg-gradient-to-br from-orange-50 to-orange-100 hover:from-orange-100 hover:to-orange-200 rounded-xl transition-all duration-200">
                                    <div className="flex items-center">
                                        <div className="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clipRule="evenodd" />
                                            </svg>
                                        </div>
                                        <div className="ml-3">
                                            <span className="font-semibold text-orange-900">Settings</span>
                                            <p className="text-sm text-orange-700">System config</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 