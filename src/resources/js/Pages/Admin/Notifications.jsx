import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Notifications({ auth, notifications, filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');
    const [type, setType] = useState(filters.type || 'all');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('admin.notifications'), { search, type: type !== 'all' ? type : undefined }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleTypeChange = (e) => {
        const newType = e.target.value;
        setType(newType);
        router.get(route('admin.notifications'), { 
            search: search || undefined, 
            type: newType !== 'all' ? newType : undefined 
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setSearch('');
        setType('all');
        router.get(route('admin.notifications'), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const truncateText = (text, maxLength = 120) => {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };

    const getTypeColor = (type) => {
        const colors = {
            'reminder': 'bg-blue-100 text-blue-800',
            'friend_update': 'bg-green-100 text-green-800',
            'goal_progress': 'bg-purple-100 text-purple-800',
            'ai_suggestion': 'bg-yellow-100 text-yellow-800',
        };
        return colors[type] || 'bg-gray-100 text-gray-800';
    };

    const getTypeLabel = (type) => {
        const labels = {
            'reminder': 'Reminder',
            'friend_update': 'Friend Update',
            'goal_progress': 'Goal Progress',
            'ai_suggestion': 'AI Suggestion',
        };
        return labels[type] || type;
    };

    const notificationTypes = [
        { value: 'all', label: 'All Types' },
        { value: 'reminder', label: 'Reminder' },
        { value: 'friend_update', label: 'Friend Update' },
        { value: 'goal_progress', label: 'Goal Progress' },
        { value: 'ai_suggestion', label: 'AI Suggestion' },
    ];

    return (
        <AdminLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Notifications Management</h2>}
        >
            <Head title="Notifications - Admin" />
            
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Header Section */}
                    <div className="mb-6">
                        <div className="md:flex md:items-center md:justify-between">
                            <div className="min-w-0 flex-1">
                                <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                                    Notifications Management
                                </h2>
                                <p className="mt-1 text-sm text-gray-500">
                                    Manage all user notifications and system messages.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Search and Filters */}
                    <div className="mb-6">
                        <div className="bg-white shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-4">
                                    <div className="flex-1">
                                        <label htmlFor="search" className="sr-only">Search notifications</label>
                                        <input
                                            type="text"
                                            id="search"
                                            value={search}
                                            onChange={(e) => setSearch(e.target.value)}
                                            placeholder="Search notifications by content..."
                                            className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        />
                                    </div>
                                    <div className="w-full sm:w-48">
                                        <label htmlFor="type" className="sr-only">Notification type</label>
                                        <select
                                            id="type"
                                            value={type}
                                            onChange={handleTypeChange}
                                            className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            {notificationTypes.map((notificationType) => (
                                                <option key={notificationType.value} value={notificationType.value}>
                                                    {notificationType.label}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <button
                                        type="submit"
                                        className="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    >
                                        Search
                                    </button>
                                    {(filters.search || (filters.type && filters.type !== 'all')) && (
                                        <button
                                            type="button"
                                            onClick={clearFilters}
                                            className="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        >
                                            Clear
                                        </button>
                                    )}
                                </form>
                            </div>
                        </div>
                    </div>

                    {/* Notifications Table */}
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
                                                Type
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Content
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                                User
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
                                        {notifications.data && notifications.data.length > 0 ? (
                                            notifications.data.map((notification) => (
                                                <tr key={notification.notification_id} className="hover:bg-gray-50">
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                        #{notification.notification_id}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                        <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${getTypeColor(notification.type)}`}>
                                                            {getTypeLabel(notification.type)}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900">
                                                        <div className="max-w-xs">
                                                            {truncateText(notification.content)}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900">
                                                        <div className="flex items-center">
                                                            <div>
                                                                <div className="font-medium text-gray-900">
                                                                    {notification.user?.display_name || notification.user?.name || 'Unknown'}
                                                                </div>
                                                                <div className="text-gray-500">
                                                                    {notification.user?.email}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                        <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${
                                                            notification.is_read 
                                                                ? 'bg-green-100 text-green-800' 
                                                                : 'bg-red-100 text-red-800'
                                                        }`}>
                                                            {notification.is_read ? 'Read' : 'Unread'}
                                                        </span>
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                        {formatDate(notification.created_at)}
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="6" className="px-6 py-12 text-center">
                                                    <div className="text-center">
                                                        <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1 5h-5m5 0h5m-5 0l-1 5m-5-5H9m10 0L9 17m10 0l5-5m-5 5l5 5" />
                                                        </svg>
                                                        <h3 className="mt-2 text-sm font-medium text-gray-900">No notifications found</h3>
                                                        <p className="mt-1 text-sm text-gray-500">
                                                            {filters.search || filters.type ? 'Try adjusting your search terms or filters.' : 'No notifications have been created yet.'}
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {notifications.last_page > 1 && (
                                <div className="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">
                                    <div className="flex flex-1 justify-between sm:hidden">
                                        {notifications.prev_page_url && (
                                            <a
                                                href={notifications.prev_page_url}
                                                className="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            >
                                                Previous
                                            </a>
                                        )}
                                        {notifications.next_page_url && (
                                            <a
                                                href={notifications.next_page_url}
                                                className="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            >
                                                Next
                                            </a>
                                        )}
                                    </div>
                                    <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                        <div>
                                            <p className="text-sm text-gray-700">
                                                Showing <span className="font-medium">{notifications.from}</span> to{' '}
                                                <span className="font-medium">{notifications.to}</span> of{' '}
                                                <span className="font-medium">{notifications.total}</span> results
                                            </p>
                                        </div>
                                        <div>
                                            <nav className="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                                {notifications.links.map((link, index) => (
                                                    <a
                                                        key={index}
                                                        href={link.url}
                                                        className={`relative inline-flex items-center px-4 py-2 text-sm font-medium ${
                                                            link.active
                                                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                                        } ${index === 0 ? 'rounded-l-md' : ''} ${
                                                            index === notifications.links.length - 1 ? 'rounded-r-md' : ''
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