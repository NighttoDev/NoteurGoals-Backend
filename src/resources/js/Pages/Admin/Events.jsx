import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Events({ auth, events = {data: []}, filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('admin.events'), { search }, {
            preserveState: true,
            replace: true
        });
    };

    const clearSearch = () => {
        setSearch('');
        router.get(route('admin.events'), {}, {
            preserveState: true,
            replace: true
        });
    };

    const getEventStatus = (eventTime) => {
        if (!eventTime) return { status: 'unknown', color: 'bg-gray-100 text-gray-800' };
        
        const now = new Date();
        const eventDate = new Date(eventTime);
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const eventDateOnly = new Date(eventDate.getFullYear(), eventDate.getMonth(), eventDate.getDate());
        
        if (eventDateOnly < today) {
            return { status: 'Past', color: 'bg-gray-100 text-gray-800' };
        } else if (eventDateOnly.getTime() === today.getTime()) {
            return { status: 'Today', color: 'bg-blue-100 text-blue-800' };
        } else {
            return { status: 'Upcoming', color: 'bg-green-100 text-green-800' };
        }
    };

    const formatEventTime = (eventTime) => {
        if (!eventTime) return 'No time set';
        
        const date = new Date(eventTime);
        return date.toLocaleDateString('vi-VN', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <AdminLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Events Management
                </h2>
            }
        >
            <Head title="Events - Admin" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Header with Search */}
                            <div className="flex justify-between items-center mb-6">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900">Events</h3>
                                    <p className="text-sm text-gray-600">Manage all user events and appointments ({events.total || 0} total)</p>
                                </div>
                                <div className="flex space-x-3">
                                    <form onSubmit={handleSearch} className="flex">
                                        <input
                                            type="text"
                                            placeholder="Search events..."
                                            value={search}
                                            onChange={(e) => setSearch(e.target.value)}
                                            className="px-4 py-2 border border-gray-300 rounded-l-md focus:ring-blue-500 focus:border-blue-500 w-64"
                                        />
                                        <button
                                            type="submit"
                                            className="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500"
                                        >
                                            Search
                                        </button>
                                        {search && (
                                            <button
                                                type="button"
                                                onClick={clearSearch}
                                                className="px-4 py-2 bg-gray-500 text-white rounded-r-md hover:bg-gray-600 focus:ring-2 focus:ring-gray-500"
                                            >
                                                Clear
                                            </button>
                                        )}
                                    </form>
                                </div>
                            </div>

                            {/* Events Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Event
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Organizer
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Event Time
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
                                        {events.data && events.data.length > 0 ? (
                                            events.data.map((event) => {
                                                const eventStatus = getEventStatus(event.event_time);
                                                return (
                                                    <tr key={event.event_id} className="hover:bg-gray-50">
                                                        <td className="px-6 py-4">
                                                            <div className="max-w-xs">
                                                                <div className="text-sm font-medium text-gray-900 mb-1">
                                                                    {event.title || 'Untitled Event'}
                                                                </div>
                                                                <div className="text-sm text-gray-500 break-words">
                                                                    {event.description || 'No description'}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="flex items-center">
                                                                <div className="flex-shrink-0 h-8 w-8">
                                                                    <div className="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                                                        <span className="text-white text-sm font-medium">
                                                                            {(event.user?.name || event.user?.email || 'U').charAt(0).toUpperCase()}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div className="ml-3">
                                                                    <div className="text-sm font-medium text-gray-900">
                                                                        {event.user?.name || 'Unknown User'}
                                                                    </div>
                                                                    <div className="text-sm text-gray-500">
                                                                        {event.user?.email || 'No email'}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <div className="font-medium">
                                                                {formatEventTime(event.event_time)}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${eventStatus.color}`}>
                                                                {eventStatus.status}
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            {event.created_at ? new Date(event.created_at).toLocaleDateString('vi-VN') : 'Unknown'}
                                                            <div className="text-xs text-gray-400">
                                                                {event.created_at ? new Date(event.created_at).toLocaleTimeString('vi-VN') : ''}
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
                                                                <button className="text-red-600 hover:text-red-900">
                                                                    Delete
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                );
                                            })
                                        ) : (
                                            <tr>
                                                <td colSpan="6" className="px-6 py-4 text-center text-gray-500">
                                                    {search ? `No events found matching "${search}"` : 'No events found'}
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {events.links && events.links.length > 3 && (
                                <div className="mt-6 flex justify-between items-center">
                                    <div className="text-sm text-gray-700">
                                        Showing {events.from || 0} to {events.to || 0} of {events.total || 0} results
                                    </div>
                                    <div className="flex space-x-1">
                                        {events.links.map((link, index) => (
                                            <button
                                                key={index}
                                                onClick={() => {
                                                    if (link.url) {
                                                        router.get(link.url, { search });
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
                            {(!events.data || events.data.length === 0) && !search && (
                                <div className="text-center py-8">
                                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3a2 2 0 012-2h8a2 2 0 012 2v4l4 4v10a2 2 0 01-2 2H10a2 2 0 01-2-2V11l4-4z" />
                                    </svg>
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">No events yet</h3>
                                    <p className="mt-1 text-sm text-gray-500">Users haven't created any events yet.</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 