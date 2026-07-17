import React from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface NotificationData {
    title: string;
    message: string;
    type: string;
    metadata: any;
}

interface NotificationItem {
    id: string;
    type: string;
    notifiable_type: string;
    notifiable_id: number;
    data: NotificationData;
    read_at: string | null;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedNotifications {
    data: NotificationItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

interface Props {
    notifications: PaginatedNotifications;
    filters?: {
        date?: string | null;
        per_page?: number;
    };
}

export default function NotificationsIndex({ notifications, filters }: Props) {
    const [date, setDate] = React.useState(filters?.date || '');
    const [perPage, setPerPage] = React.useState(filters?.per_page || 5);

    const handleFilterChange = (newDate: string, newPerPage: number) => {
        router.get(window.location.pathname, {
            date: newDate,
            per_page: newPerPage
        }, {
            preserveState: true,
            replace: true
        });
    };

    // Simple helper to format relative time (e.g., "4m ago", "1h ago", "2d ago")
    const getRelativeTime = (dateString: string) => {
        const now = new Date();
        const date = new Date(dateString);
        const diffMs = now.getTime() - date.getTime();

        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) {
            return 'Just now';
        }
        if (diffMins < 60) {
            return `${diffMins}m ago`;
        }
        if (diffHours < 24) {
            return `${diffHours}h ago`;
        }
        return `${diffDays}d ago`;
    };

    return (
        <>
            <Head title="Notifications" />

            <div className="main-content-container overflow-hidden" style={{ minHeight: "75vh" }}>
                <div className="d-flex justify-content-center">
                    <div className="col-12 col-md-8 col-lg-6">
                        {/* Header Area */}
                        <div className="d-flex align-items-center mb-4 position-relative py-3">
                            <Link
                                href="/admin/dashboard"
                                className="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center border-0"
                                style={{ width: '40px', height: '40px', backgroundColor: '#f8f9fa' }}
                            >
                                <i className="material-symbols-outlined fs-24 text-dark" style={{ width: '40px', height: '40px', backgroundColor: '#f8f9fa' }}>arrow_back</i>
                            </Link>
                            <h2 className="fs-22 fw-semibold text-center w-100 m-0" style={{ position: 'absolute', left: 0, right: 0, zIndex: -1 }}>
                                Notification
                            </h2>
                        </div>

                        {/* Filter Area */}
                        <div className="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
                            <div className="d-flex align-items-center gap-2">
                                <label className="fw-medium text-dark mb-0 text-nowrap">Date:</label>
                                <div className="d-flex align-items-center gap-2">
                                    <input 
                                        type="date" 
                                        className="form-control" 
                                        value={date} 
                                        onChange={(e) => {
                                            setDate(e.target.value);
                                            handleFilterChange(e.target.value, perPage);
                                        }}
                                        style={{ width: 'auto' }}
                                    />
                                    {date && (
                                        <button 
                                            type="button" 
                                            className="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center px-2 py-1"
                                            onClick={() => {
                                                setDate('');
                                                handleFilterChange('', perPage);
                                            }}
                                            title="Clear Date Filter"
                                        >
                                            <i className="material-symbols-outlined fs-18">close</i>
                                        </button>
                                    )}
                                </div>
                            </div>
                            <div className="d-flex align-items-center gap-2">
                                <label className="fw-medium text-dark mb-0 text-nowrap">Show:</label>
                                <select 
                                    className="form-select" 
                                    value={perPage} 
                                    onChange={(e) => {
                                        const val = Number(e.target.value);
                                        setPerPage(val);
                                        handleFilterChange(date, val);
                                    }}
                                    style={{ width: 'auto' }}
                                >
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>

                        {/* Notifications List */}
                        <div className="d-flex flex-column gap-3">
                            {notifications.data.length === 0 ? (
                                <div className="card bg-white p-40 rounded-10 border border-white text-center">
                                    <div className="bg-warning bg-opacity-10 text-warning text-center rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: "80px", height: "80px" }}>
                                        <i className="material-symbols-outlined fs-40">notifications_off</i>
                                    </div>
                                    <h3 className="fs-18 fw-medium text-dark mb-2">No Notifications</h3>
                                    <p className="text-secondary fs-14">You are all caught up! When you receive alerts, they will appear here.</p>
                                </div>
                            ) : (
                                notifications.data.map((notification) => (
                                    <div
                                        key={notification.id}
                                        className={`card card-body rounded-20 border border-0 shadow-sm d-flex flex-row align-items-start gap-3 ${!notification.read_at ? 'bg-white' : 'bg-light'}`}
                                        style={{ backgroundColor: !notification.read_at ? '#f4f6f8' : '#ffffff' }}
                                    >
                                        {/* Yellow Bell Icon */}
                                        <div
                                            className="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                            style={{
                                                width: '56px',
                                                height: '56px',
                                                backgroundColor: '#FEF3C7', // soft yellow light tint
                                                color: '#D97706' // dark yellow warning tone
                                            }}
                                        >
                                            <i className="material-symbols-outlined fs-26">notifications</i>
                                        </div>

                                        {/* Content details */}
                                        <div className="flex-grow-1">
                                            <h4 className="fs-16 fw-semibold text-dark mb-2">
                                                {notification.data.title || 'Notification Alert'}
                                            </h4>
                                            <p className="text-secondary fs-14 mb-2 lh-base">
                                                {notification.data.message}
                                            </p>
                                            <span className="text-muted fs-12">
                                                {getRelativeTime(notification.created_at)}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>

                        {/* Pagination Area */}
                        {notifications.last_page > 1 && (
                            <div className="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap pt-15 p-20">
                                <span className="fs-15">
                                    Showing {notifications.from || 0} to {notifications.to || 0} of {notifications.total} entries
                                </span>
                                <nav aria-label="Page navigation example" className="custom-pagination">
                                    <ul className="pagination mb-0 justify-content-center">
                                        {notifications.links.map((link, idx) => {
                                            const isPrevious = link.label.includes('Previous');
                                            const isNext = link.label.includes('Next');

                                            let content = <span dangerouslySetInnerHTML={{ __html: link.label }} />;
                                            if (isPrevious) {
                                                content = <i className="material-symbols-outlined">west</i>;
                                            } else if (isNext) {
                                                content = <i className="material-symbols-outlined">east</i>;
                                            }

                                            if (link.url) {
                                                return (
                                                    <li key={idx} className={`page-item ${link.active ? 'active' : ''}`}>
                                                        <Link
                                                            href={link.url}
                                                            className={`page-link ${link.active ? 'active' : ''} ${isPrevious || isNext ? 'icon' : ''}`}
                                                            aria-label={isPrevious ? 'Previous' : isNext ? 'Next' : undefined}
                                                        >
                                                            {content}
                                                        </Link>
                                                    </li>
                                                );
                                            } else {
                                                return (
                                                    <li key={idx} className="page-item disabled">
                                                        <button
                                                            className={`page-link ${isPrevious || isNext ? 'icon' : ''}`}
                                                            disabled
                                                            aria-label={isPrevious ? 'Previous' : isNext ? 'Next' : undefined}
                                                        >
                                                            {content}
                                                        </button>
                                                    </li>
                                                );
                                            }
                                        })}
                                    </ul>
                                </nav>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
