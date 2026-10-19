import { useMemo, useState, type FormEvent } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { logout } from '@/routes';
import { useAppearance } from '@/hooks/use-appearance';
import { UserAvatar } from '@/components/ui/user-avatar';

type SearchItem = {
    label: string;
    description: string;
    route: string;
    keywords: string[];
};

const searchItems: SearchItem[] = [
    {
        label: 'Dashboard',
        description: 'Overview and system summary',
        route: '/admin/dashboard',
        keywords: ['overview', 'home', 'summary', 'analytics'],
    },
    {
        label: 'Users',
        description: 'Manage administrators and members',
        route: '/admin/users',
        keywords: ['people', 'members', 'accounts', 'admins'],
    },
    {
        label: 'Posts',
        description: 'Review and manage content',
        route: '/admin/posts',
        keywords: ['content', 'articles', 'blogs', 'news'],
    },
    {
        label: 'Messages',
        description: 'View inbox conversations',
        route: '/admin/messages',
        keywords: ['chat', 'inbox', 'conversations', 'support'],
    },
    {
        label: 'Support Issues',
        description: 'Track tickets and replies',
        route: '/admin/support-issues',
        keywords: ['tickets', 'help', 'issues', 'requests'],
    },
    {
        label: 'Reports',
        description: 'Review reported accounts and content',
        route: '/admin/reports',
        keywords: ['abuse', 'moderation', 'flagged', 'complaints'],
    },
    {
        label: 'Settings',
        description: 'Configure system preferences',
        route: '/admin/settings/system',
        keywords: ['system', 'preferences', 'configuration', 'setup'],
    },
    {
        label: 'Profile',
        description: 'Update your admin profile',
        route: '/admin/profile',
        keywords: ['account', 'personal', 'info', 'details'],
    },
];

export default function MainNavbar() {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const { auth } = usePage().props;
    const [searchQuery, setSearchQuery] = useState('');
    const [isSearchFocused, setIsSearchFocused] = useState(false);

    const toggleTheme = () => {
        updateAppearance(resolvedAppearance === 'dark' ? 'light' : 'dark');
    };

    const filteredSearchItems = useMemo(() => {
        const query = searchQuery.trim().toLowerCase();

        if (!query) {
            return searchItems.slice(0, 6);
        }

        return searchItems.filter((item) => {
            const haystack = [item.label, item.description, ...item.keywords]
                .join(' ')
                .toLowerCase();

            return haystack.includes(query);
        });
    }, [searchQuery]);

    const handleSearchSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const target = filteredSearchItems[0] ?? searchItems[0];

        if (target) {
            router.visit(target.route);
            setSearchQuery('');
            setIsSearchFocused(false);
        }
    };

    return (
        <>
            <header
                className="header-area bg-white mb-4 rounded-10 border border-white"
                id="header-area"
            >
                <div className="row align-items-center">
                    <div className="col-md-6">
                        <div className="left-header-content">
                            <ul className="d-flex align-items-center ps-0 mb-0 list-unstyled justify-content-center justify-content-md-start">
                                <li className="d-xl-none">
                                    <button
                                        className="header-burger-menu bg-transparent p-0 border-0 position-relative top-3"
                                        id="header-burger-menu"
                                        type="button"
                                    >
                                        <span
                                            className="border-1 d-block for-dark-burger"
                                            style={{
                                                borderBottom: '1px solid #475569',
                                                height: '1px',
                                                width: '25px',
                                            }}
                                        />

                                        <span
                                            className="border-1 d-block for-dark-burger"
                                            style={{
                                                borderBottom: '1px solid #475569',
                                                height: '1px',
                                                width: '25px',
                                                margin: '6px 0',
                                            }}
                                        />

                                        <span
                                            className="border-1 d-block for-dark-burger"
                                            style={{
                                                borderBottom: '1px solid #475569',
                                                height: '1px',
                                                width: '25px',
                                            }}
                                        />
                                    </button>
                                </li>

                                <li className="d-none d-xl-block flex-grow-1 me-3 position-relative">
                                    <form className="src-form position-relative" onSubmit={handleSearchSubmit}>
                                        <input
                                            className="form-control"
                                            placeholder="Search dashboard, users, posts..."
                                            type="text"
                                            value={searchQuery}
                                            onChange={(event) => setSearchQuery(event.target.value)}
                                            onFocus={() => setIsSearchFocused(true)}
                                            onBlur={() => {
                                                window.setTimeout(() => setIsSearchFocused(false), 120);
                                            }}
                                        />

                                        <button
                                            aria-label="Search"
                                            className="src-btn position-absolute top-50 start-0 translate-middle-y bg-transparent p-0 border-0"
                                            type="submit"
                                        >
                                            <span className="material-symbols-outlined">search</span>
                                        </button>

                                        {isSearchFocused && (
                                            <div className="position-absolute top-100 start-0 w-100 mt-2 rounded-10 border border-light bg-white shadow-sm z-3" style={{ maxHeight: '280px', overflowY: 'auto' }}>
                                                {filteredSearchItems.length > 0 ? (
                                                    filteredSearchItems.map((item) => (
                                                        <button
                                                            key={item.label}
                                                            className="d-flex flex-column align-items-start text-start w-100 border-0 bg-transparent px-3 py-2 text-body"
                                                            onMouseDown={(event) => event.preventDefault()}
                                                            onClick={() => {
                                                                router.visit(item.route);
                                                                setSearchQuery('');
                                                                setIsSearchFocused(false);
                                                            }}
                                                            type="button"
                                                        >
                                                            <span className="fw-semibold">{item.label}</span>
                                                            <small className="text-muted">{item.description}</small>
                                                        </button>
                                                    ))
                                                ) : (
                                                    <div className="px-3 py-2 text-muted">
                                                        No matching admin pages found.
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div className="col-md-6">
                        <div className="right-header-content mt-3 mt-md-0">
                            <ul className="d-flex align-items-center justify-content-center justify-content-md-end ps-0 mb-0 list-unstyled gap-2">
                                {/* Dark Mode */}
                                <li className="header-right-item light-dark-item">
                                    <div className="light-dark">
                                        <button
                                            className="switch-toggle dark-btn p-0 bg-transparent lh-0 border-0"
                                            id="switch-toggle"
                                            type="button"
                                            onClick={toggleTheme}
                                            aria-label="Toggle dark mode"
                                        >
                                            <span className="dark">
                                                <i className="material-symbols-outlined">dark_mode</i>
                                            </span>

                                            <span className="light">
                                                <i className="material-symbols-outlined">light_mode</i>
                                            </span>
                                        </button>
                                    </div>
                                </li>

                                {/* Notification Dropdown */}
                                <li className="header-right-item notification-item">
                                    <div className="dropdown notifications position-relative">
                                        <button
                                            className="btn btn-transparent p-0 border-0 position-relative d-flex align-items-center justify-content-center text-body rounded-circle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            aria-label="Notifications"
                                            style={{ width: '40px', height: '40px' }}
                                        >
                                            <i className="material-symbols-outlined fs-24">notifications</i>
                                            {((auth as any)?.unread_notifications_count || 0) > 0 && (
                                                <span
                                                    className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white d-flex align-items-center justify-content-center"
                                                    style={{ fontSize: '10px', minWidth: '18px', height: '18px', padding: '2px 4px' }}
                                                >
                                                    {((auth as any)?.unread_notifications_count || 0) > 99
                                                        ? '99+'
                                                        : (auth as any)?.unread_notifications_count}
                                                </span>
                                            )}
                                        </button>

                                        <div
                                            className="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 p-0 mt-2"
                                            style={{ width: '340px', maxWidth: '90vw' }}
                                        >
                                            <div className="d-flex align-items-center justify-content-between p-3 border-bottom">
                                                <h6 className="mb-0 fw-semibold fs-15 text-dark">
                                                    Notifications{' '}
                                                    {((auth as any)?.unread_notifications_count || 0) > 0 && (
                                                        <span className="badge bg-primary-subtle text-primary ms-1">
                                                            {(auth as any)?.unread_notifications_count} New
                                                        </span>
                                                    )}
                                                </h6>
                                                <Link
                                                    href="/admin/notifications"
                                                    className="fs-13 text-primary text-decoration-none fw-medium"
                                                >
                                                    View All
                                                </Link>
                                            </div>

                                            <div className="notification-list" style={{ maxHeight: '300px', overflowY: 'auto' }}>
                                                {((auth as any)?.recent_notifications || []).length > 0 ? (
                                                    (auth as any)?.recent_notifications.map((notif: any) => {
                                                        const data = notif.data || {};
                                                        const title = data.title || 'Notification Alert';
                                                        const message = data.message || data.body || 'You have a new update';
                                                        const isUnread = !notif.read_at;

                                                        return (
                                                            <Link
                                                                key={notif.id}
                                                                href="/admin/notifications"
                                                                className={`d-flex align-items-start gap-2 p-3 border-bottom text-decoration-none text-body transition ${
                                                                    isUnread ? 'bg-light' : 'bg-white'
                                                                }`}
                                                                style={{
                                                                    borderLeft: isUnread ? '3px solid var(--bs-primary, #4f46e5)' : '3px solid transparent',
                                                                }}
                                                            >
                                                                <div
                                                                    className="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                                                    style={{ width: '36px', height: '36px' }}
                                                                >
                                                                    <i className="material-symbols-outlined fs-18">notifications</i>
                                                                </div>
                                                                <div className="flex-grow-1 overflow-hidden">
                                                                    <div className="fw-semibold fs-13 text-truncate mb-1">{title}</div>
                                                                    <div className="text-muted fs-12 text-truncate mb-1">{message}</div>
                                                                    <small className="text-secondary" style={{ fontSize: '11px' }}>
                                                                        {notif.created_at}
                                                                    </small>
                                                                </div>
                                                            </Link>
                                                        );
                                                    })
                                                ) : (
                                                    <div className="p-4 text-center text-muted">
                                                        <i className="material-symbols-outlined fs-36 d-block mb-1 text-secondary opacity-50">
                                                            notifications_off
                                                        </i>
                                                        <span className="fs-13">No notifications right now</span>
                                                    </div>
                                                )}
                                            </div>

                                            <div className="p-2 border-top text-center bg-light rounded-bottom-3">
                                                <Link
                                                    href="/admin/notifications"
                                                    className="btn btn-sm btn-link text-primary text-decoration-none fw-medium fs-13 w-100"
                                                >
                                                    See all notifications →
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                {/* Profile */}
                                <li className="header-right-item">
                                    <div className="dropdown admin-profile">
                                        <div
                                            className="d-xxl-flex align-items-center bg-transparent border-0 text-start p-0 cursor dropdown-toggle"
                                            data-bs-toggle="dropdown"
                                        >
                                            <div className="flex-shrink-0 position-relative">
                                                <UserAvatar
                                                    src={auth?.user?.avatar}
                                                    name={[auth?.user?.first_name, auth?.user?.last_name].filter(Boolean).join(' ') || 'Admin'}
                                                    size={40}
                                                    showStatus
                                                />
                                            </div>
                                        </div>

                                        <div className="dropdown-menu border-0 bg-white dropdown-menu-end shadow-lg rounded-3">
                                            <div className="d-flex align-items-center info p-3 border-bottom">
                                                <div className="flex-shrink-0">
                                                    <UserAvatar
                                                        src={auth?.user?.avatar}
                                                        name={[auth?.user?.first_name, auth?.user?.last_name].filter(Boolean).join(' ') || 'Admin'}
                                                        size={40}
                                                    />
                                                </div>
                                                <div className="flex-grow-1 ms-10 ps-2">
                                                    <h3 className="fw-medium fs-16 mb-0">
                                                        {[auth?.user?.first_name, auth?.user?.last_name]
                                                            .filter(Boolean)
                                                            .join(' ') || 'User'}
                                                    </h3>

                                                    <span className="fs-13 text-muted fw-medium">
                                                        {auth?.user?.role?.toUpperCase() || 'ADMIN'}
                                                    </span>
                                                </div>
                                            </div>
                                            <ul className="admin-link mb-0 list-unstyled p-2">
                                                <li>
                                                    <a
                                                        className="dropdown-item admin-item-link d-flex align-items-center text-body rounded-2 py-2"
                                                        href={'/admin/profile'}
                                                    >
                                                        <i className="material-symbols-outlined fs-20">person</i>
                                                        <span className="ms-2">My Profile</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <Link
                                                        className="dropdown-item admin-item-link d-flex align-items-center text-body rounded-2 py-2"
                                                        href={logout()}
                                                    >
                                                        <i className="material-symbols-outlined fs-20">logout</i>
                                                        <span className="ms-2">Logout</span>
                                                    </Link>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
        </>
    );
}