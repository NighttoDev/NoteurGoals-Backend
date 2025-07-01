import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Goals({ auth, goals = {data: []}, filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('admin.goals'), { search, status: statusFilter }, {
            preserveState: true,
            replace: true
        });
    };

    const handleStatusChange = (status) => {
        setStatusFilter(status);
        router.get(route('admin.goals'), { search, status }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setSearch('');
        setStatusFilter('all');
        router.get(route('admin.goals'), {}, {
            preserveState: true,
            replace: true
        });
    };

    const getStatusColor = (status) => {
        switch (status?.toLowerCase()) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'in_progress':
                return 'bg-blue-100 text-blue-800';
            case 'new':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const formatStatus = (status) => {
        return status?.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Unknown';
    };

    // Calculate progress based on milestones or dates
    const calculateProgress = (goal) => {
        if (goal.status === 'completed') return 100;
        if (goal.status === 'cancelled') return 0;
        
        // If has start and end dates, calculate based on time
        if (goal.start_date && goal.end_date) {
            const now = new Date();
            const start = new Date(goal.start_date);
            const end = new Date(goal.end_date);
            
            if (now < start) return 0;
            if (now > end) return 100;
            
            const total = end - start;
            const elapsed = now - start;
            return Math.round((elapsed / total) * 100);
        }
        
        // Default progress for in_progress goals
        if (goal.status === 'in_progress') return 25;
        return 0;
    };

    return (
        <AdminLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Goals Management
                </h2>
            }
        >
            <Head title="Goals - Admin" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Header with Search and Filters */}
                            <div className="flex justify-between items-center mb-6">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900">Goals</h3>
                                    <p className="text-sm text-gray-600">Manage user goals and track progress ({goals.total || 0} total)</p>
                                </div>
                                <div className="flex space-x-3">
                                    <select
                                        value={statusFilter}
                                        onChange={(e) => handleStatusChange(e.target.value)}
                                        className="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="all">All Status</option>
                                        <option value="new">New</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <form onSubmit={handleSearch} className="flex">
                                        <input
                                            type="text"
                                            placeholder="Search goals..."
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
                                        {(search || statusFilter !== 'all') && (
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

                            {/* Goals Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Goal
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Owner
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Timeline
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Progress
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {goals.data && goals.data.length > 0 ? (
                                            goals.data.map((goal) => {
                                                const progress = calculateProgress(goal);
                                                return (
                                                    <tr key={goal.goal_id} className="hover:bg-gray-50">
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div>
                                                                <div className="text-sm font-medium text-gray-900">
                                                                    {goal.title || 'Untitled Goal'}
                                                                </div>
                                                                <div className="text-sm text-gray-500 max-w-xs truncate">
                                                                    {goal.description || 'No description'}
                                                                </div>
                                                                {goal.milestones_count > 0 && (
                                                                    <div className="text-xs text-blue-600 mt-1">
                                                                        {goal.milestones_count} milestones
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="text-sm text-gray-900">
                                                                {goal.user?.name || 'Unknown User'}
                                                            </div>
                                                            <div className="text-sm text-gray-500">
                                                                {goal.user?.email || 'No email'}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(goal.status)}`}>
                                                                {formatStatus(goal.status)}
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <div>
                                                                {goal.start_date ? new Date(goal.start_date).toLocaleDateString() : 'No start date'}
                                                            </div>
                                                            <div className="text-xs text-gray-400">
                                                                to {goal.end_date ? new Date(goal.end_date).toLocaleDateString() : 'No end date'}
                                                            </div>
                                                            <div className="text-xs text-gray-400 mt-1">
                                                                Created: {goal.created_at ? new Date(goal.created_at).toLocaleDateString() : 'Unknown'}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="w-full bg-gray-200 rounded-full h-2">
                                                                <div 
                                                                    className={`h-2 rounded-full ${
                                                                        goal.status === 'completed' ? 'bg-green-600' :
                                                                        goal.status === 'cancelled' ? 'bg-red-600' :
                                                                        'bg-blue-600'
                                                                    }`}
                                                                    style={{ width: `${progress}%` }}
                                                                ></div>
                                                            </div>
                                                            <div className="text-xs text-gray-500 mt-1">
                                                                {progress}%
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
                                                    {search || statusFilter !== 'all' ? 'No goals found matching your criteria' : 'No goals found'}
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {goals.links && goals.links.length > 3 && (
                                <div className="mt-6 flex justify-between items-center">
                                    <div className="text-sm text-gray-700">
                                        Showing {goals.from || 0} to {goals.to || 0} of {goals.total || 0} results
                                    </div>
                                    <div className="flex space-x-1">
                                        {goals.links.map((link, index) => (
                                            <button
                                                key={index}
                                                onClick={() => {
                                                    if (link.url) {
                                                        router.get(link.url, { search, status: statusFilter });
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
                            {(!goals.data || goals.data.length === 0) && !search && statusFilter === 'all' && (
                                <div className="text-center py-8">
                                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">No goals yet</h3>
                                    <p className="mt-1 text-sm text-gray-500">Users haven't created any goals yet.</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 