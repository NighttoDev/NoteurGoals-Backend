import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Notes({ auth, notes = {data: []}, filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('admin.notes'), { search }, {
            preserveState: true,
            replace: true
        });
    };

    const clearSearch = () => {
        setSearch('');
        router.get(route('admin.notes'), {}, {
            preserveState: true,
            replace: true
        });
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
                    Notes Management
                </h2>
            }
        >
            <Head title="Notes - Admin" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Header with Search */}
                            <div className="flex justify-between items-center mb-6">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900">Notes</h3>
                                    <p className="text-sm text-gray-600">Manage all user notes ({notes.total || 0} total)</p>
                                </div>
                                <div className="flex space-x-3">
                                    <form onSubmit={handleSearch} className="flex">
                                        <input
                                            type="text"
                                            placeholder="Search notes..."
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

                            {/* Notes Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Note
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Author
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Created
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Updated
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {notes.data && notes.data.length > 0 ? (
                                            notes.data.map((note) => (
                                                <tr key={note.note_id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4">
                                                        <div className="max-w-xs">
                                                            <div className="text-sm font-medium text-gray-900 mb-1">
                                                                {note.title || 'Untitled Note'}
                                                            </div>
                                                            <div className="text-sm text-gray-500 break-words">
                                                                {truncateContent(note.content)}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="flex items-center">
                                                            <div className="flex-shrink-0 h-8 w-8">
                                                                <div className="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center">
                                                                    <span className="text-white text-sm font-medium">
                                                                        {(note.user?.name || note.user?.email || 'U').charAt(0).toUpperCase()}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div className="ml-3">
                                                                <div className="text-sm font-medium text-gray-900">
                                                                    {note.user?.name || 'Unknown User'}
                                                                </div>
                                                                <div className="text-sm text-gray-500">
                                                                    {note.user?.email || 'No email'}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {note.created_at ? new Date(note.created_at).toLocaleDateString('vi-VN') : 'Unknown'}
                                                        <div className="text-xs text-gray-400">
                                                            {note.created_at ? new Date(note.created_at).toLocaleTimeString('vi-VN') : ''}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {note.updated_at ? new Date(note.updated_at).toLocaleDateString('vi-VN') : 'Never'}
                                                        <div className="text-xs text-gray-400">
                                                            {note.updated_at ? new Date(note.updated_at).toLocaleTimeString('vi-VN') : ''}
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
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="5" className="px-6 py-4 text-center text-gray-500">
                                                    {search ? `No notes found matching "${search}"` : 'No notes found'}
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {notes.links && notes.links.length > 3 && (
                                <div className="mt-6 flex justify-between items-center">
                                    <div className="text-sm text-gray-700">
                                        Showing {notes.from || 0} to {notes.to || 0} of {notes.total || 0} results
                                    </div>
                                    <div className="flex space-x-1">
                                        {notes.links.map((link, index) => (
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
                            {(!notes.data || notes.data.length === 0) && !search && (
                                <div className="text-center py-8">
                                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8m9-5H7m14-8V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h8" />
                                    </svg>
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">No notes yet</h3>
                                    <p className="mt-1 text-sm text-gray-500">Users haven't created any notes yet.</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 