import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Subscriptions({ auth, subscriptions = {data: []}, plans = [], stats = {}, filters = {} }) {
    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');

    const handleStatusChange = (status) => {
        setStatusFilter(status);
        router.get(route('admin.subscriptions'), { status }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setStatusFilter('all');
        router.get(route('admin.subscriptions'), {}, {
            preserveState: true,
            replace: true
        });
    };

    const getStatusColor = (status) => {
        switch (status?.toLowerCase()) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'expired':
                return 'bg-red-100 text-red-800';
            case 'cancelled':
                return 'bg-gray-100 text-gray-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const formatStatus = (status) => {
        return status?.charAt(0).toUpperCase() + status?.slice(1) || 'Unknown';
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount || 0);
    };

    const formatDate = (dateString) => {
        if (!dateString) return 'Not set';
        return new Date(dateString).toLocaleDateString('vi-VN');
    };

    const calculateDaysRemaining = (endDate) => {
        if (!endDate) return null;
        const end = new Date(endDate);
        const now = new Date();
        const diffTime = end - now;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    };

    return (
        <AdminLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Subscriptions Management
                </h2>
            }
        >
            <Head title="Subscriptions - Admin" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Statistics Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                            <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-medium text-gray-600">Total Subscriptions</p>
                                        <p className="text-2xl font-semibold text-gray-900">{stats.total_subscriptions || 0}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                            <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-medium text-gray-600">Active Subscriptions</p>
                                        <p className="text-2xl font-semibold text-gray-900">{stats.active_subscriptions || 0}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <div className="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                            <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-medium text-gray-600">Expired Subscriptions</p>
                                        <p className="text-2xl font-semibold text-gray-900">{stats.expired_subscriptions || 0}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <div className="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                            <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-medium text-gray-600">Total Revenue</p>
                                        <p className="text-2xl font-semibold text-gray-900">{formatCurrency(stats.total_revenue)}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Header with Filters */}
                            <div className="flex justify-between items-center mb-6">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900">Subscriptions</h3>
                                    <p className="text-sm text-gray-600">Manage user subscriptions and billing ({subscriptions.total || 0} total)</p>
                                </div>
                                <div className="flex space-x-3">
                                    <select
                                        value={statusFilter}
                                        onChange={(e) => handleStatusChange(e.target.value)}
                                        className="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="all">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="expired">Expired</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                    {statusFilter !== 'all' && (
                                        <button
                                            onClick={clearFilters}
                                            className="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:ring-2 focus:ring-gray-500"
                                        >
                                            Clear
                                        </button>
                                    )}
                                </div>
                            </div>

                            {/* Subscriptions Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                User
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Plan
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Period
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Renewals
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {subscriptions.data && subscriptions.data.length > 0 ? (
                                            subscriptions.data.map((subscription) => {
                                                const daysRemaining = calculateDaysRemaining(subscription.end_date);
                                                return (
                                                    <tr key={subscription.subscription_id} className="hover:bg-gray-50">
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="flex items-center">
                                                                <div className="flex-shrink-0 h-8 w-8">
                                                                    <div className="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                                                        <span className="text-white text-sm font-medium">
                                                                            {(subscription.user?.name || subscription.user?.email || 'U').charAt(0).toUpperCase()}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div className="ml-3">
                                                                    <div className="text-sm font-medium text-gray-900">
                                                                        {subscription.user?.name || 'Unknown User'}
                                                                    </div>
                                                                    <div className="text-sm text-gray-500">
                                                                        {subscription.user?.email || 'No email'}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="text-sm font-medium text-gray-900">
                                                                {subscription.plan?.name || 'Unknown Plan'}
                                                            </div>
                                                            <div className="text-sm text-gray-500">
                                                                {formatCurrency(subscription.plan?.price)} / {subscription.plan?.duration}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(subscription.payment_status)}`}>
                                                                {formatStatus(subscription.payment_status)}
                                                            </span>
                                                            {daysRemaining !== null && subscription.payment_status === 'active' && (
                                                                <div className={`text-xs mt-1 ${daysRemaining <= 7 ? 'text-red-600' : 'text-gray-500'}`}>
                                                                    {daysRemaining > 0 ? `${daysRemaining} days left` : 'Expired'}
                                                                </div>
                                                            )}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <div>
                                                                Start: {formatDate(subscription.start_date)}
                                                            </div>
                                                            <div>
                                                                End: {formatDate(subscription.end_date)}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <div className="text-center">
                                                                <div className="text-lg font-medium">
                                                                    {subscription.renewal_count || 0}
                                                                </div>
                                                                <div className="text-xs">
                                                                    {subscription.auto_renewal_id ? 'Auto-renewal' : 'Manual'}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <div className="flex space-x-2">
                                                                <button className="text-blue-600 hover:text-blue-900">
                                                                    View
                                                                </button>
                                                                <button className="text-yellow-600 hover:text-yellow-900">
                                                                    Edit
                                                                </button>
                                                                {subscription.payment_status === 'active' ? (
                                                                    <button className="text-red-600 hover:text-red-900">
                                                                        Cancel
                                                                    </button>
                                                                ) : (
                                                                    <button className="text-green-600 hover:text-green-900">
                                                                        Reactivate
                                                                    </button>
                                                                )}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                );
                                            })
                                        ) : (
                                            <tr>
                                                <td colSpan="6" className="px-6 py-4 text-center text-gray-500">
                                                    {statusFilter !== 'all' ? `No ${statusFilter} subscriptions found` : 'No subscriptions found'}
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {subscriptions.links && subscriptions.links.length > 3 && (
                                <div className="mt-6 flex justify-between items-center">
                                    <div className="text-sm text-gray-700">
                                        Showing {subscriptions.from || 0} to {subscriptions.to || 0} of {subscriptions.total || 0} results
                                    </div>
                                    <div className="flex space-x-1">
                                        {subscriptions.links.map((link, index) => (
                                            <button
                                                key={index}
                                                onClick={() => {
                                                    if (link.url) {
                                                        router.get(link.url, { status: statusFilter });
                                                    }
                                                }}
                                                disabled={!link.url}
                                                className={`px-3 py-2 text-sm rounded-md ${
                                                    link.active 
                                                        ? 'bg-blue-600 text-white' 
                                                        : link.url 
                                                            ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300' 
                                                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Subscription Plans Section */}
                            {plans && plans.length > 0 && (
                                <div className="mt-8 border-t border-gray-200 pt-8">
                                    <h4 className="text-lg font-medium text-gray-900 mb-4">Available Plans</h4>
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        {plans.map((plan) => (
                                            <div key={plan.plan_id} className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                                <div className="text-sm font-medium text-gray-900 mb-2">
                                                    {plan.name}
                                                </div>
                                                <div className="text-2xl font-bold text-blue-600 mb-2">
                                                    {formatCurrency(plan.price)}
                                                    <span className="text-sm font-normal text-gray-500">/{plan.duration}</span>
                                                </div>
                                                <div className="text-sm text-gray-600">
                                                    {plan.description || 'No description available'}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Empty State */}
                            {(!subscriptions.data || subscriptions.data.length === 0) && statusFilter === 'all' && (
                                <div className="text-center py-8">
                                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8m9-5H7m14-8V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h8" />
                                    </svg>
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">No subscriptions yet</h3>
                                    <p className="mt-1 text-sm text-gray-500">No users have subscribed to any plans yet.</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 