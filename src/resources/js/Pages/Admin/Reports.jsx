import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function Reports({ auth, userStats = {}, goalStats = {}, revenueStats = {} }) {
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount || 0);
    };

    const formatPercent = (value, total) => {
        if (!total || total === 0) return '0%';
        return `${Math.round((value / total) * 100)}%`;
    };

    const StatCard = ({ title, value, subtitle, icon, bgColor, textColor }) => (
        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6">
                <div className="flex items-center">
                    <div className="flex-shrink-0">
                        <div className={`w-8 h-8 ${bgColor} rounded-full flex items-center justify-center`}>
                            {icon}
                        </div>
                    </div>
                    <div className="ml-4">
                        <p className="text-sm font-medium text-gray-600">{title}</p>
                        <p className={`text-2xl font-semibold ${textColor}`}>{value}</p>
                        {subtitle && <p className="text-sm text-gray-500">{subtitle}</p>}
                    </div>
                </div>
            </div>
        </div>
    );

    const ProgressBar = ({ label, value, total, color = 'bg-blue-500' }) => {
        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
        
        return (
            <div className="mb-4">
                <div className="flex justify-between text-sm font-medium text-gray-700">
                    <span>{label}</span>
                    <span>{value} ({percentage}%)</span>
                </div>
                <div className="mt-1 bg-gray-200 rounded-full h-2">
                    <div 
                        className={`${color} h-2 rounded-full transition-all duration-300`}
                        style={{ width: `${percentage}%` }}
                    ></div>
                </div>
            </div>
        );
    };

    // Calculate additional metrics
    const conversionRate = formatPercent(userStats.premium_users, userStats.total_users);
    const completionRate = formatPercent(goalStats.completed_goals, goalStats.total_goals);
    const arpu = userStats.total_users > 0 ? (revenueStats.total_revenue || 0) / userStats.total_users : 0;
    const monthlyGrowth = userStats.total_users > 0 ? formatPercent(userStats.new_users_this_month, userStats.total_users) : '0%';

    return (
        <AdminLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Reports & Analytics
                </h2>
            }
        >
            <Head title="Reports - Admin" />
            
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Header Section */}
                    <div className="mb-6">
                        <div className="md:flex md:items-center md:justify-between">
                            <div className="min-w-0 flex-1">
                                <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                                    Reports & Analytics
                                </h2>
                                <p className="mt-1 text-sm text-gray-500">
                                    Comprehensive insights and statistics about your platform performance.
                                </p>
                            </div>
                            <div className="mt-4 flex md:mt-0 md:ml-4">
                                <span className="text-sm text-gray-500">
                                    Last updated: {new Date().toLocaleString('vi-VN')}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Overview Stats */}
                    <div className="mb-8">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">Platform Overview</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <StatCard
                                title="Total Users"
                                value={userStats.total_users?.toLocaleString() || '0'}
                                subtitle={`${userStats.new_users_this_month || 0} new this month`}
                                bgColor="bg-blue-500"
                                textColor="text-gray-900"
                                icon={
                                    <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                                    </svg>
                                }
                            />
                            
                            <StatCard
                                title="Active Users"
                                value={userStats.active_users?.toLocaleString() || '0'}
                                subtitle={`${conversionRate} of total users`}
                                bgColor="bg-green-500"
                                textColor="text-gray-900"
                                icon={
                                    <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                    </svg>
                                }
                            />
                            
                            <StatCard
                                title="Premium Users"
                                value={userStats.premium_users?.toLocaleString() || '0'}
                                subtitle={`${conversionRate} conversion rate`}
                                bgColor="bg-yellow-500"
                                textColor="text-gray-900"
                                icon={
                                    <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                }
                            />
                            
                            <StatCard
                                title="Total Revenue"
                                value={formatCurrency(revenueStats.total_revenue)}
                                subtitle={`${formatCurrency(revenueStats.monthly_revenue)} this month`}
                                bgColor="bg-purple-500"
                                textColor="text-gray-900"
                                icon={
                                    <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" />
                                </svg>
                                }
                            />
                        </div>
                    </div>

                    {/* Key Performance Indicators */}
                    <div className="mb-8">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">Key Performance Indicators</h3>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div className="bg-white p-6 rounded-lg shadow-sm">
                                <div className="text-center">
                                    <div className="text-3xl font-bold text-blue-600">{completionRate}</div>
                                    <div className="text-sm text-gray-500 mt-1">Goal Completion Rate</div>
                                </div>
                            </div>
                            <div className="bg-white p-6 rounded-lg shadow-sm">
                                <div className="text-center">
                                    <div className="text-3xl font-bold text-green-600">{formatCurrency(arpu)}</div>
                                    <div className="text-sm text-gray-500 mt-1">Average Revenue Per User</div>
                                </div>
                            </div>
                            <div className="bg-white p-6 rounded-lg shadow-sm">
                                <div className="text-center">
                                    <div className="text-3xl font-bold text-purple-600">{monthlyGrowth}</div>
                                    <div className="text-sm text-gray-500 mt-1">Monthly User Growth</div>
                                </div>
                            </div>
                            <div className="bg-white p-6 rounded-lg shadow-sm">
                                <div className="text-center">
                                    <div className="text-3xl font-bold text-orange-600">{conversionRate}</div>
                                    <div className="text-sm text-gray-500 mt-1">Premium Conversion Rate</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Detailed Statistics */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        {/* User Statistics */}
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">User Analytics</h3>
                                <div className="space-y-4">
                                    <ProgressBar
                                        label="Active Users"
                                        value={userStats.active_users || 0}
                                        total={userStats.total_users || 0}
                                        color="bg-green-500"
                                    />
                                    <ProgressBar
                                        label="Premium Users"
                                        value={userStats.premium_users || 0}
                                        total={userStats.total_users || 0}
                                        color="bg-yellow-500"
                                    />
                                    <ProgressBar
                                        label="New Users This Month"
                                        value={userStats.new_users_this_month || 0}
                                        total={userStats.total_users || 1}
                                        color="bg-blue-500"
                                    />
                                </div>
                                
                                <div className="mt-6 pt-6 border-t border-gray-200">
                                    <div className="grid grid-cols-2 gap-4 text-center">
                                        <div>
                                            <p className="text-2xl font-semibold text-gray-900">{userStats.total_users || 0}</p>
                                            <p className="text-sm text-gray-500">Total Users</p>
                                        </div>
                                        <div>
                                            <p className="text-2xl font-semibold text-green-600">{userStats.active_users || 0}</p>
                                            <p className="text-sm text-gray-500">Active Users</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Goal Statistics */}
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Goal Analytics</h3>
                                <div className="space-y-4">
                                    <ProgressBar
                                        label="Completed Goals"
                                        value={goalStats.completed_goals || 0}
                                        total={goalStats.total_goals || 0}
                                        color="bg-green-500"
                                    />
                                    <ProgressBar
                                        label="Active Goals"
                                        value={goalStats.active_goals || 0}
                                        total={goalStats.total_goals || 0}
                                        color="bg-blue-500"
                                    />
                                    <ProgressBar
                                        label="New Goals This Month"
                                        value={goalStats.new_goals_this_month || 0}
                                        total={goalStats.total_goals || 1}
                                        color="bg-purple-500"
                                    />
                                </div>
                                
                                <div className="mt-6 pt-6 border-t border-gray-200">
                                    <div className="grid grid-cols-2 gap-4 text-center">
                                        <div>
                                            <p className="text-2xl font-semibold text-gray-900">{goalStats.total_goals || 0}</p>
                                            <p className="text-sm text-gray-500">Total Goals</p>
                                        </div>
                                        <div>
                                            <p className="text-2xl font-semibold text-green-600">{goalStats.completed_goals || 0}</p>
                                            <p className="text-sm text-gray-500">Completed</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Revenue Analytics */}
                    <div className="mb-8">
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Revenue Analytics</h3>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div className="text-center p-4 bg-green-50 rounded-lg">
                                        <div className="text-2xl font-bold text-green-600">
                                            {formatCurrency(revenueStats.total_revenue)}
                                        </div>
                                        <div className="text-sm text-gray-600 mt-1">Total Revenue</div>
                                    </div>
                                    <div className="text-center p-4 bg-blue-50 rounded-lg">
                                        <div className="text-2xl font-bold text-blue-600">
                                            {formatCurrency(revenueStats.monthly_revenue)}
                                        </div>
                                        <div className="text-sm text-gray-600 mt-1">Monthly Revenue</div>
                                    </div>
                                    <div className="text-center p-4 bg-purple-50 rounded-lg">
                                        <div className="text-2xl font-bold text-purple-600">
                                            {formatCurrency(arpu)}
                                        </div>
                                        <div className="text-sm text-gray-600 mt-1">ARPU</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* System Health */}
                    <div className="mb-8">
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">System Health</h3>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div className="flex items-center">
                                        <div className="flex-shrink-0">
                                            <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                                        </div>
                                        <div className="ml-3">
                                            <p className="text-sm font-medium text-gray-900">Database</p>
                                            <p className="text-sm text-gray-500">Operational</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center">
                                        <div className="flex-shrink-0">
                                            <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                                        </div>
                                        <div className="ml-3">
                                            <p className="text-sm font-medium text-gray-900">API Services</p>
                                            <p className="text-sm text-gray-500">Operational</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center">
                                        <div className="flex-shrink-0">
                                            <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                                        </div>
                                        <div className="ml-3">
                                            <p className="text-sm font-medium text-gray-900">File Storage</p>
                                            <p className="text-sm text-gray-500">Operational</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Empty State */}
                    {(!userStats.total_users && !goalStats.total_goals && !revenueStats.total_revenue) && (
                        <div className="text-center py-12">
                            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <h3 className="mt-2 text-sm font-medium text-gray-900">No data available</h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Start using the platform to see analytics and reports here.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
} 