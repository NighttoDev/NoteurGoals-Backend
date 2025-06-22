import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Notifications({ auth, notifications = {data: []}, filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');
    const [typeFilter, setTypeFilter] = useState(filters.type || 'all');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('admin.notifications'), { search, type: typeFilter }, {
            preserveState: true,
            replace: true
        });
    };

    const handleTypeChange = (type) => {
        setTypeFilter(type);
        router.get(route('admin.notifications'), { search, type }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setSearch('');
        setTypeFilter('all');
        router.get(route('admin.notifications'), {}, {
            preserveState: true,
            replace: true
        });
    };

    const getTypeColor = (type) => {
        switch (type?.toLowerCase()) {
            case 'reminder':
                return 'bg-blue-100 text-blue-800';
            case 'friend_update':
                return 'bg-green-100 text-green-800';
            case 'goal_progress':
                return 'bg-purple-100 text-purple-800';
            case 'ai_suggestion':
                return 'bg-orange-100 text-orange-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const formatType = (type) => {
        switch (type?.toLowerCase()) {
            case 'reminder':
                return 'Reminder';
            case 'friend_update':
                return 'Friend Update';
            case 'goal_progress':
                return 'Goal Progress';
            case 'ai_suggestion':
                return 'AI Suggestion';
            default:
                return type || 'Unknown';
        }
    };

    const truncateContent = (content, maxLength = 100) => {
        if (!content) return 'No content';
        return content.length > maxLength ? content.substring(0, maxLength) + '...' : content;
    };

    return (
        <AdminLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Notifications Management
                </h2>
            }
        >
            <Head title="Notifications - Admin" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Header with Search and Filters */}
                            <div className="flex justify-between items-center mb-6">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900">Notifications</h3>
                                    <p className="text-sm text-gray-600">Manage all system notifications ({notifications.total || 0} total)</p>
                                </div>
                                <div className="flex space-x-3">
                                    <select
                                        value={typeFilter}
                                        onChange={(e) => handleTypeChange(e.target.value)}
                                        className="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="all">All Types</option>
                                        <option value="reminder">Reminder</option>
                                        <option value="friend_update">Friend Update</option>
                                        <option value="goal_progress">Goal Progress</option>
                                        <option value="ai_suggestion">AI Suggestion</option>
                                    </select>
                                    <form onSubmit={handleSearch} className="flex">
                                        <input
                                            type="text"
                                            placeholder="Search notifications..."
                                            value={search}
                                            onChange={(e) => setSearch(e.target.value)}
                                            className="px-4 py-2 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 w-64"
                                        />
                                        <button
                                            type="submit"
                                            className="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500"
                                        >
                                            Search
                                        </button>
                                        {(search || typeFilter !== 'all') && (
                                            <button
                                                type="button"
                                                onClick={clearFilters}
                                                className="px-4 py-2 bg-gray-500 text-white rounded-r-md hover:bg-gray-600 focus:ring-2 focus:ring-gray-500"
                                            >
                                                Clear
                                            </button>
                                        )}
                                    </form>
                                </div>
                            </div>

                            {/* Notifications Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Content
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Recipient
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Type
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Created
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {notifications.data && notifications.data.length > 0 ? (
                                            notifications.data.map((notification) => (
                                                <tr key={notification.notification_id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4">
                                                        <div className="max-w-xs">
                                                            <div className="text-sm text-gray-900 break-words">
                                                                {truncateContent(notification.content)}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="flex items-center">
                                                            <div className="flex-shrink-0 h-8 w-8">
                                                                <div className="h-8 w-8 rounded-full bg-pink-500 flex items-center justify-center">
                                                                    <span className="text-white text-sm font-medium">
                                                                        {(notification.user?.name || notification.user?.email || 'U').charAt(0).toUpperCase()}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div className="ml-3">
                                                                <div className="text-sm font-medium text-gray-900">
                                                                    {notification.user?.name || 'Unknown User'}
                                                                </div>
                                                                <div className="text-sm text-gray-500">
                                                                    {notification.user?.email || 'No email'}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getTypeColor(notification.type)}`}>
                                                            {formatType(notification.type)}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                            notification.is_read ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                                        }`}>
                                                            {notification.is_read ? 'Read' : 'Unread'}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {notification.created_at ? new Date(notification.created_at).toLocaleDateString('vi-VN') : 'Unknown'}
                                                        <div className="text-xs text-gray-400">
                                                            {notification.created_at ? new Date(notification.created_at).toLocaleTimeString('vi-VN') : ''}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <div className="flex space-x-2">
                                                            <button className="text-blue-600 hover:text-blue-900">
                                                                View
                                                            </button>
                                                            <button className={`${notification.is_read ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'}`}>
                                                                {notification.is_read ? 'Mark Unread' : 'Mark Read'}
                                                            </button>
                                                            <button className="text-red-600 hover:text-red-900">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="6" className="px-6 py-4 text-center text-gray-500">
                                                    {search || typeFilter !== 'all' ? 'No notifications found matching your criteria' : 'No notifications found'}
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {notifications.links && notifications.links.length > 3 && (
                                <div className="mt-6 flex justify-between items-center">
                                    <div className="text-sm text-gray-700">
                                        Showing {notifications.from || 0} to {notifications.to || 0} of {notifications.total || 0} results
                                    </div>
                                    <div className="flex space-x-1">
                                        {notifications.links.map((link, index) => (
                                            <button
                                                key={index}
                                                onClick={() => {
                                                    if (link.url) {
                                                        router.get(link.url, { search, type: typeFilter });
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

                            {/* Empty State */}
                            {(!notifications.data || notifications.data.length === 0) && !search && typeFilter === 'all' && (
                                <div className="text-center py-8">
                                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">No notifications yet</h3>
                                    <p className="mt-1 text-sm text-gray-500">No notifications have been sent yet.</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 