import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function Reports({ auth, reports, userStats, goalStats, revenueStats }) {
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
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

    return (
        <AdminLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Reports & Analytics</h2>}
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
                                    Comprehensive insights and statistics about your platform.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Overview Stats */}
                    <div className="mb-8">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">Platform Overview</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <StatCard
                                title="Total Users"
                                value={userStats?.total_users?.toLocaleString() || '0'}
                                subtitle={`${userStats?.new_users_this_month || 0} new this month`}
                                bgColor="bg-blue-500"
                                textColor="text-gray-900"
                                icon={
                                    <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                }
                            />
                            
                            <StatCard
                                title="Active Users"
                                value={userStats?.active_users?.toLocaleString() || '0'}
                                subtitle={formatPercent(userStats?.active_users, userStats?.total_users)}
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
                                value={userStats?.premium_users?.toLocaleString() || '0'}
                                subtitle={formatPercent(userStats?.premium_users, userStats?.total_users)}
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
                                value={formatCurrency(revenueStats?.total_revenue || 0)}
                                subtitle={`${formatCurrency(revenueStats?.monthly_revenue || 0)} this month`}
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

                    {/* Detailed Statistics */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        {/* User Statistics */}
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">User Statistics</h3>
                                <div className="space-y-4">
                                    <ProgressBar
                                        label="Active Users"
                                        value={userStats?.active_users || 0}
                                        total={userStats?.total_users || 0}
                                        color="bg-green-500"
                                    />
                                    <ProgressBar
                                        label="Premium Users"
                                        value={userStats?.premium_users || 0}
                                        total={userStats?.total_users || 0}
                                        color="bg-yellow-500"
                                    />
                                    <ProgressBar
                                        label="New Users This Month"
                                        value={userStats?.new_users_this_month || 0}
                                        total={userStats?.total_users || 0}
                                        color="bg-blue-500"
                                    />
                                </div>
                                
                                <div className="mt-6 pt-6 border-t border-gray-200">
                                    <div className="grid grid-cols-2 gap-4 text-center">
                                        <div>
                                            <p className="text-2xl font-semibold text-gray-900">{userStats?.total_users || 0}</p>
                                            <p className="text-sm text-gray-500">Total Users</p>
                                        </div>
                                        <div>
                                            <p className="text-2xl font-semibold text-green-600">{userStats?.active_users || 0}</p>
                                            <p className="text-sm text-gray-500">Active Users</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Goal Statistics */}
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Goal Statistics</h3>
                                <div className="space-y-4">
                                    <ProgressBar
                                        label="Completed Goals"
                                        value={goalStats?.completed_goals || 0}
                                        total={goalStats?.total_goals || 0}
                                        color="bg-green-500"
                                    />
                                    <ProgressBar
                                        label="Active Goals"
                                        value={goalStats?.active_goals || 0}
                                        total={goalStats?.total_goals || 0}
                                        color="bg-blue-500"
                                    />
                                    <ProgressBar
                                        label="New Goals This Month"
                                        value={goalStats?.new_goals_this_month || 0}
                                        total={goalStats?.total_goals || 0}
                                        color="bg-purple-500"
                                    />
                                </div>
                                
                                <div className="mt-6 pt-6 border-t border-gray-200">
                                    <div className="grid grid-cols-2 gap-4 text-center">
                                        <div>
                                            <p className="text-2xl font-semibold text-gray-900">{goalStats?.total_goals || 0}</p>
                                            <p className="text-sm text-gray-500">Total Goals</p>
                                        </div>
                                        <div>
                                            <p className="text-2xl font-semibold text-green-600">{goalStats?.completed_goals || 0}</p>
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
                                    <div className="text-center p-6 bg-gradient-to-r from-green-50 to-green-100 rounded-lg">
                                        <div className="text-3xl font-bold text-green-600 mb-2">
                                            {formatCurrency(revenueStats?.total_revenue || 0)}
                                        </div>
                                        <div className="text-sm text-green-800">Total Revenue</div>
                                    </div>
                                    
                                    <div className="text-center p-6 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg">
                                        <div className="text-3xl font-bold text-blue-600 mb-2">
                                            {formatCurrency(revenueStats?.monthly_revenue || 0)}
                                        </div>
                                        <div className="text-sm text-blue-800">This Month</div>
                                    </div>
                                    
                                    <div className="text-center p-6 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg">
                                        <div className="text-3xl font-bold text-purple-600 mb-2">
                                            {userStats?.premium_users || 0}
                                        </div>
                                        <div className="text-sm text-purple-800">Paying Customers</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Performance Metrics */}
                    <div className="mb-8">
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Performance Metrics</h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <div className="border border-gray-200 rounded-lg p-4">
                                        <div className="text-2xl font-semibold text-gray-900">
                                            {goalStats?.total_goals > 0 
                                                ? formatPercent(goalStats?.completed_goals, goalStats?.total_goals)
                                                : '0%'
                                            }
                                        </div>
                                        <div className="text-sm text-gray-500">Goal Completion Rate</div>
                                    </div>
                                    
                                    <div className="border border-gray-200 rounded-lg p-4">
                                        <div className="text-2xl font-semibold text-gray-900">
                                            {userStats?.total_users > 0 
                                                ? formatPercent(userStats?.premium_users, userStats?.total_users)
                                                : '0%'
                                            }
                                        </div>
                                        <div className="text-sm text-gray-500">Premium Conversion</div>
                                    </div>
                                    
                                    <div className="border border-gray-200 rounded-lg p-4">
                                        <div className="text-2xl font-semibold text-gray-900">
                                            {userStats?.total_users > 0 
                                                ? formatPercent(userStats?.active_users, userStats?.total_users)
                                                : '0%'
                                            }
                                        </div>
                                        <div className="text-sm text-gray-500">User Engagement</div>
                                    </div>
                                    
                                    <div className="border border-gray-200 rounded-lg p-4">
                                        <div className="text-2xl font-semibold text-gray-900">
                                            {userStats?.premium_users > 0 
                                                ? formatCurrency((revenueStats?.total_revenue || 0) / userStats.premium_users)
                                                : formatCurrency(0)
                                            }
                                        </div>
                                        <div className="text-sm text-gray-500">ARPU</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Growth Indicators */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Growth Indicators</h3>
                                
                                <div className="space-y-6">
                                    <div className="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                        <div>
                                            <div className="font-medium text-green-900">New Users This Month</div>
                                            <div className="text-sm text-green-600">User acquisition rate</div>
                                        </div>
                                        <div className="text-2xl font-bold text-green-700">
                                            {userStats?.new_users_this_month || 0}
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                        <div>
                                            <div className="font-medium text-blue-900">New Goals This Month</div>
                                            <div className="text-sm text-blue-600">Goal creation rate</div>
                                        </div>
                                        <div className="text-2xl font-bold text-blue-700">
                                            {goalStats?.new_goals_this_month || 0}
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                                        <div>
                                            <div className="font-medium text-purple-900">Monthly Revenue</div>
                                            <div className="text-sm text-purple-600">Revenue growth</div>
                                        </div>
                                        <div className="text-2xl font-bold text-purple-700">
                                            {formatCurrency(revenueStats?.monthly_revenue || 0)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">System Health</h3>
                                
                                <div className="space-y-4">
                                    <div className="flex justify-between items-center">
                                        <span className="text-sm font-medium text-gray-700">Platform Status</span>
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Operational
                                        </span>
                                    </div>
                                    
                                    <div className="flex justify-between items-center">
                                        <span className="text-sm font-medium text-gray-700">Data Integrity</span>
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Good
                                        </span>
                                    </div>
                                    
                                    <div className="flex justify-between items-center">
                                        <span className="text-sm font-medium text-gray-700">User Satisfaction</span>
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            High
                                        </span>
                                    </div>
                                    
                                    <div className="mt-6 p-4 bg-gray-50 rounded-lg">
                                        <div className="text-sm text-gray-600">
                                            Last updated: {new Date().toLocaleDateString('vi-VN', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            })}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 