import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Subscriptions({ auth, subscriptions, plans, stats, filters = {} }) {
    const [status, setStatus] = useState(filters.status || 'all');

    const handleStatusChange = (e) => {
        const newStatus = e.target.value;
        setStatus(newStatus);
        router.get(route('admin.subscriptions'), { 
            status: newStatus !== 'all' ? newStatus : undefined 
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setStatus('all');
        router.get(route('admin.subscriptions'), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    };

    const getStatusColor = (status) => {
        const colors = {
            'active': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800',
            'expired': 'bg-gray-100 text-gray-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    const getStatusLabel = (status) => {
        const labels = {
            'active': 'Active',
            'cancelled': 'Cancelled',
            'expired': 'Expired',
        };
        return labels[status] || status;
    };

    const subscriptionStatuses = [
        { value: 'all', label: 'All Status' },
        { value: 'active', label: 'Active' },
        { value: 'cancelled', label: 'Cancelled' },
        { value: 'expired', label: 'Expired' },
    ];

    const isExpiringSoon = (endDate) => {
        const end = new Date(endDate);
        const now = new Date();
        const diffDays = Math.ceil((end - now) / (1000 * 60 * 60 * 24));
        return diffDays <= 7 && diffDays > 0;
    };

    return (
        <AdminLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Subscriptions Management</h2>}
        >
            <Head title="Subscriptions - Admin" />
            
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Header Section */}
                    <div className="mb-6">
                        <div className="md:flex md:items-center md:justify-between">
                            <div className="min-w-0 flex-1">
                                <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                                    Subscriptions Management
                                </h2>
                                <p className="mt-1 text-sm text-gray-500">
                                    Manage user subscriptions, plans and billing information.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Statistics Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
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
                                        <p className="text-sm font-medium text-gray-600">Total</p>
                                        <p className="text-2xl font-semibold text-gray-900">{stats?.total_subscriptions || 0}</p>
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
                                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-medium text-gray-600">Active</p>
                                        <p className="text-2xl font-semibold text-gray-900">{stats?.active_subscriptions || 0}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <div className="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center">
                                            <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-medium text-gray-600">Expired</p>
                                        <p className="text-2xl font-semibold text-gray-900">{stats?.expired_subscriptions || 0}</p>
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
                                        <p className="text-sm font-medium text-gray-600">Cancelled</p>
                                        <p className="text-2xl font-semibold text-gray-900">{stats?.cancelled_subscriptions || 0}</p>
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
                                        <p className="text-sm font-medium text-gray-600">Revenue</p>
                                        <p className="text-lg font-semibold text-gray-900">{formatCurrency(stats?.total_revenue || 0)}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Subscription Plans */}
                    <div className="mb-8">
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Available Plans</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {plans && plans.length > 0 ? (
                                        plans.map((plan) => (
                                            <div key={plan.plan_id} className="border border-gray-200 rounded-lg p-4">
                                                <h4 className="font-medium text-gray-900">{plan.name}</h4>
                                                <p className="text-sm text-gray-500 mt-1">{plan.description}</p>
                                                <div className="mt-2">
                                                    <span className="text-lg font-semibold text-gray-900">{formatCurrency(plan.price)}</span>
                                                    <span className="text-sm text-gray-500">/{plan.duration} month{plan.duration > 1 ? 's' : ''}</span>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <p className="text-gray-500 col-span-3">No subscription plans available.</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Filters */}
                    <div className="mb-6">
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex flex-col sm:flex-row gap-4">
                                    <div className="w-full sm:w-48">
                                        <label htmlFor="status" className="sr-only">Subscription status</label>
                                        <select
                                            id="status"
                                            value={status}
                                            onChange={handleStatusChange}
                                            className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            {subscriptionStatuses.map((subscriptionStatus) => (
                                                <option key={subscriptionStatus.value} value={subscriptionStatus.value}>
                                                    {subscriptionStatus.label}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    {filters.status && (
                                        <button
                                            type="button"
                                            onClick={clearFilters}
                                            className="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        >
                                            Clear Filters
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Subscriptions Table */}
                    <div className="bg-white shadow-sm sm:rounded-lg">
                        <div className="px-4 py-5 sm:p-6">
                            <div className="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                <table className="min-w-full divide-y divide-gray-300">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                ID
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                User
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Plan
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Start Date
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                End Date
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Status
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Created At
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200 bg-white">
                                        {subscriptions.data && subscriptions.data.length > 0 ? (
                                            subscriptions.data.map((subscription) => (
                                                <tr key={subscription.subscription_id} className="hover:bg-gray-50">
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                        #{subscription.subscription_id}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900">
                                                        <div className="flex items-center">
                                                            <div>
                                                                <div className="font-medium text-gray-900">
                                                                    {subscription.user?.display_name || subscription.user?.name || 'Unknown'}
                                                                </div>
                                                                <div className="text-gray-500">
                                                                    {subscription.user?.email}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900">
                                                        <div>
                                                            <div className="font-medium">
                                                                {subscription.subscription_plan?.name || 'Unknown Plan'}
                                                            </div>
                                                            <div className="text-gray-500">
                                                                {formatCurrency(subscription.subscription_plan?.price || 0)}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                        {formatDate(subscription.start_date)}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                        <div>
                                                            {formatDate(subscription.end_date)}
                                                            {subscription.payment_status === 'active' && isExpiringSoon(subscription.end_date) && (
                                                                <div className="text-xs text-orange-600 font-medium">Expires soon</div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                        <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${getStatusColor(subscription.payment_status)}`}>
                                                            {getStatusLabel(subscription.payment_status)}
                                                        </span>
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                        {formatDate(subscription.created_at)}
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="7" className="px-6 py-12 text-center">
                                                    <div className="text-center">
                                                        <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" />
                                                        </svg>
                                                        <h3 className="mt-2 text-sm font-medium text-gray-900">No subscriptions found</h3>
                                                        <p className="mt-1 text-sm text-gray-500">
                                                            {filters.status ? 'Try adjusting your filters.' : 'No subscriptions have been created yet.'}
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {subscriptions.last_page > 1 && (
                                <div className="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">
                                    <div className="flex flex-1 justify-between sm:hidden">
                                        {subscriptions.prev_page_url && (
                                            <a
                                                href={subscriptions.prev_page_url}
                                                className="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            >
                                                Previous
                                            </a>
                                        )}
                                        {subscriptions.next_page_url && (
                                            <a
                                                href={subscriptions.next_page_url}
                                                className="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            >
                                                Next
                                            </a>
                                        )}
                                    </div>
                                    <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                        <div>
                                            <p className="text-sm text-gray-700">
                                                Showing <span className="font-medium">{subscriptions.from}</span> to{' '}
                                                <span className="font-medium">{subscriptions.to}</span> of{' '}
                                                <span className="font-medium">{subscriptions.total}</span> results
                                            </p>
                                        </div>
                                        <div>
                                            <nav className="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                                {subscriptions.links.map((link, index) => (
                                                    <a
                                                        key={index}
                                                        href={link.url}
                                                        className={`relative inline-flex items-center px-4 py-2 text-sm font-medium ${
                                                            link.active
                                                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                                        } ${index === 0 ? 'rounded-l-md' : ''} ${
                                                            index === subscriptions.links.length - 1 ? 'rounded-r-md' : ''
                                                        }`}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                    />
                                                ))}
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 