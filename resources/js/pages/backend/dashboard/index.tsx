import { Head, Link } from '@inertiajs/react';
import DashboardChart from '../../widget/chart';

interface User {
    id: number;
    first_name: string;
    last_name: string;
    avatar?: string;
}

interface Post {
    id: number;
    slug: string;
    content?: string;
    location?: string;
    user_id: number;
    created_at: string;
    likes_count?: number;
    comments_count?: number;
    user?: User;
}

interface Notification {
    id: string;
    type: string;
    data: Record<string, any>;
    read_at: string | null;
    created_at: string;
}

interface Stats {
    totalUsers: number;
    activeUsers: number;
    suspendedUsers: number;
    newUsersThisMonth: number;
    totalPosts: number;
    publishedPosts: number;
    draftPosts: number;
    postsThisMonth: number;
    totalReports: number;
    pendingReports: number;
    resolvedReports: number;
    totalLikes: number;
    totalComments: number;
    totalFollows: number;
}

interface Props {
    stats: Stats;
    charts: {
        usersMonthlyData: number[];
        postsMonthlyData: number[];
    };
    notifications: Notification[];
    communityPosts: Post[];
    // unused but still passed from backend
    topUsers?: any[];
    recentUsers?: any[];
    recentPosts?: any[];
    recentReports?: any[];
}

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const getAvatarSrc = (avatar?: string) =>
    avatar ? `/${avatar}` : '/backend/assets/images/placeholder.png';

const truncate = (str?: string, len = 70) =>
    str && str.length > len ? str.slice(0, len) + '...' : str ?? '—';

const timeAgo = (dateStr: string) => {
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return `${hrs}h ago`;
    return `${Math.floor(hrs / 24)}d ago`;
};

export default function Dashboard({ stats, charts, notifications, communityPosts }: Props) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="main-content-container overflow-hidden">

                {/* ── Top Row: Chart + Stat Cards ─────────────────────────── */}
                <div className="row">
                    <div className="col-lg-6">
                        <div className="card bg-white p-20 rounded-10 border border-white mb-4">
                            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <h3>New Travelers Joined</h3>
                            </div>
                            <DashboardChart
                                type="bar"
                                height="260"
                                categories={months}
                                series={[{ name: 'New Users', data: charts.usersMonthlyData }]}
                            />
                        </div>
                    </div>

                    <div className="col-lg-6 col-xxl-3 col-xxxl-6">
                        <div className="row">
                            <div className="col-md-6 col-lg-12">
                                <div className="card bg-white p-20 rounded-10 border border-white mb-4">
                                    <div className="d-flex">
                                        <div className="flex-grow-1">
                                            <h3 className="mb-10">Total Travellers</h3>
                                            <h2 className="fs-26 fw-medium mb-0 lh-1">
                                                {stats.totalUsers.toLocaleString()}
                                            </h2>
                                        </div>
                                        <div className="flex-shrink-0 ms-3">
                                            <div className="bg-primary text-white text-center rounded-circle d-block"
                                                style={{ width: '75px', height: '75px', lineHeight: '105px' }}>
                                                <i className="material-symbols-outlined fs-40">travel_explore</i>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center" style={{ marginTop: '21px' }}>
                                        <p className="mb-0 fs-14">
                                            <i className="material-symbols-outlined fs-14 align-middle">person_add</i>
                                            &nbsp;{stats.newUsersThisMonth} joined this month
                                        </p>
                                        <span className="d-flex align-content-center gap-1 bg-success bg-opacity-10 border border-success"
                                            style={{ padding: '3px 5px' }}>
                                            <i className="material-symbols-outlined fs-14 text-success">person</i>
                                            <span className="lh-1 fs-14 text-success">{stats.activeUsers} Active</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div className="col-md-6 col-lg-12">
                                <div className="card bg-white p-20 rounded-10 border border-white mb-4">
                                    <div className="d-flex">
                                        <div className="flex-grow-1">
                                            <h3 className="mb-10">Total Posts</h3>
                                            <h2 className="fs-26 fw-medium mb-0 lh-1">
                                                {stats.totalPosts.toLocaleString()}
                                            </h2>
                                        </div>
                                        <div className="flex-shrink-0 ms-3">
                                            <div className="bg-info text-white text-center rounded-circle d-block"
                                                style={{ width: '75px', height: '75px', lineHeight: '105px' }}>
                                                <i className="material-symbols-outlined fs-40">map</i>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center" style={{ marginTop: '21px' }}>
                                        <p className="mb-0 fs-14">
                                            <i className="material-symbols-outlined fs-14 align-middle">article</i>
                                            &nbsp;{stats.postsThisMonth} this month
                                        </p>
                                        <span className="d-flex align-content-center gap-1 bg-success bg-opacity-10 border border-success"
                                            style={{ padding: '3px 5px' }}>
                                            <span className="lh-1 fs-14 text-success">{stats.publishedPosts} Published</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="col-xl-12 col-xxl-3 col-xxxl-12">
                        <div className="row">
                            <div className="col-md-6 col-xxxl-6 col-xxl-12">
                                <div className="card bg-white p-20 rounded-10 border border-white mb-4">
                                    <div className="d-flex">
                                        <div className="flex-grow-1">
                                            <h3 className="mb-10">Total Reports</h3>
                                            <h2 className="fs-26 fw-medium mb-0 lh-1">
                                                {stats.totalReports.toLocaleString()}
                                            </h2>
                                        </div>
                                        <div className="flex-shrink-0 ms-3">
                                            <div className="bg-danger text-white text-center rounded-circle d-block"
                                                style={{ width: '75px', height: '75px', lineHeight: '116px' }}>
                                                <i className="material-symbols-outlined fs-50">report</i>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center" style={{ marginTop: '23px' }}>
                                        <p className="mb-0 fs-14">
                                            <i className="material-symbols-outlined fs-14 align-middle">warning</i>
                                            &nbsp;{stats.pendingReports} pending review
                                        </p>
                                        <span className="d-flex align-content-center gap-1 bg-warning bg-opacity-10 border border-warning"
                                            style={{ padding: '3px 5px' }}>
                                            <span className="lh-1 fs-14 text-warning">{stats.pendingReports} Pending</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="col-md-6 col-xxxl-6 col-xxl-12">
                                <div className="bg-primary-50 p-20 border rounded-10 border-primary-50 mb-4">
                                    <h3 className="text-white mb-12">Community Overview</h3>
                                    <div className="d-flex flex-wrap gap-2 justify-content-between mb-14">
                                        <div>
                                            <span className="fs-14 text-white mb-1 d-block">Total Posts</span>
                                            <h2 className="fs-20 fw-medium lh-1 text-white mb-0">
                                                {stats.totalPosts.toLocaleString()}
                                            </h2>
                                        </div>
                                        <div>
                                            <span className="fs-14 text-white mb-1 d-block">Users</span>
                                            <h2 className="fs-20 fw-medium lh-1 text-white mb-0">
                                                {stats.totalUsers.toLocaleString()}
                                            </h2>
                                        </div>
                                        <div>
                                            <span className="fs-14 text-white mb-1 d-block">Likes</span>
                                            <h2 className="fs-20 fw-medium lh-1 text-white mb-0">
                                                {stats.totalLikes.toLocaleString()}
                                            </h2>
                                        </div>
                                        <div>
                                            <span className="fs-14 text-white mb-1 d-block">Comments</span>
                                            <h2 className="fs-20 fw-medium lh-1 text-white mb-0">
                                                {stats.totalComments.toLocaleString()}
                                            </h2>
                                        </div>
                                    </div>
                                    <span className="fs-14 text-white d-block" style={{ marginBottom: '-6px' }}>
                                        {stats.resolvedReports.toLocaleString()} reports have been resolved.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ── Second Row: Notifications + Community Posts ───────────── */}
                <div className="row">
                    {/* Notifications */}
                    <div className="col-xxl-4 col-xxxl-12">
                        <div className="card bg-white p-20 rounded-10 border border-white mb-4">
                            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-20">
                                <h3>
                                    Travel Posts Published
                                </h3>

                            </div>
                            <DashboardChart
                                type="line"
                                height="225"
                                categories={months}
                                series={[
                                    { name: 'Posts', data: charts.postsMonthlyData },
                                ]}
                            />
                        </div>
                        <div className="card bg-white rounded-10 border border-white mb-4">
                            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 p-20 pb-0">
                                <h3>Notifications</h3>
                                <Link href="/admin/notifications" className="fs-14 text-primary">View All</Link>
                            </div>

                            <ul className="list-unstyled mb-0 p-20" style={{ maxHeight: '310px', overflowY: 'auto' }}>
                                {notifications.length > 0 ? notifications.map((notif) => {
                                    const d = notif.data;
                                    const message = d.message || d.body || d.text || d.title || 'New notification';
                                    const icon = d.icon || 'notifications';
                                    return (
                                        <li
                                            key={notif.id}
                                            className={`d-flex gap-3 pb-3 mb-3${!notif.read_at ? ' border-start border-3 border-primary ps-2' : ''}`}
                                            style={{ borderBottom: '1px dashed #e0e0e0' }}
                                        >
                                            <div className="flex-shrink-0">
                                                <div className="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                                                    style={{ width: 44, height: 44 }}>
                                                    <i className="material-symbols-outlined fs-22 text-primary">{icon}</i>
                                                </div>
                                            </div>
                                            <div className="flex-grow-1">
                                                <p className="mb-1 fs-14 text-secondary lh-sm">{message}</p>
                                                <span className="fs-12 text-muted">{timeAgo(notif.created_at)}</span>
                                                {!notif.read_at && (
                                                    <span className="badge bg-primary fs-11 ms-2">New</span>
                                                )}
                                            </div>
                                        </li>
                                    );
                                }) : (
                                    <li className="text-center text-muted py-5">
                                        <i className="material-symbols-outlined fs-48 d-block mb-2">notifications_off</i>
                                        No notifications yet
                                    </li>
                                )}
                            </ul>
                        </div>
                    </div>

                    {/* Community Posts */}
                    <div className="col-xxl-8 col-xxxl-12">
                        <div className="card bg-white p-20 rounded-10 border border-white mb-4">
                            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-20">
                                <h3>Community Posts</h3>
                                <Link href="/admin/posts" className="fs-14 text-primary">View All</Link>
                            </div>

                            <div className="default-table-area without-header">
                                <div className="table-responsive">
                                    <table className="table align-middle">
                                        <thead className="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Traveler</th>
                                                <th>Post</th>
                                                <th>Location</th>
                                                <th className="text-center">Likes</th>
                                                <th className="text-center">Comments</th>
                                                <th className="text-end">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {communityPosts.length > 0 ? communityPosts.map((post, i) => (
                                                <tr key={post.id}>
                                                    <td className="text-muted fw-medium">
                                                        {String(i + 1).padStart(2, '0')}.
                                                    </td>
                                                    <td>
                                                        {post.user && (
                                                            <div className="d-flex align-items-center gap-2">
                                                                <img
                                                                    src={getAvatarSrc(post.user.avatar)}
                                                                    alt={post.user.first_name}
                                                                    className="rounded-circle"
                                                                    style={{ width: 38, height: 38, objectFit: 'cover' }}
                                                                />
                                                                <div className="fw-medium fs-13">
                                                                    {post.user.first_name} {post.user.last_name}
                                                                </div>
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td>
                                                        <p className="mb-0 fs-13 text-secondary" style={{ maxWidth: 220 }}>
                                                            {truncate(post.content)}
                                                        </p>
                                                    </td>
                                                    <td>
                                                        <span className="text-muted fs-13">
                                                            <i className="material-symbols-outlined fs-14 align-middle">location_on</i>
                                                            &nbsp;{post.location || '—'}
                                                        </span>
                                                    </td>
                                                    <td className="text-center">
                                                        <span className="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 fs-12">
                                                            <i className="material-symbols-outlined fs-12 align-middle">favorite</i>
                                                            &nbsp;{post.likes_count ?? 0}
                                                        </span>
                                                    </td>
                                                    <td className="text-center">
                                                        <span className="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1 fs-12">
                                                            <i className="material-symbols-outlined fs-12 align-middle">chat_bubble</i>
                                                            &nbsp;{post.comments_count ?? 0}
                                                        </span>
                                                    </td>
                                                    <td className="text-end text-muted fs-13">
                                                        {new Date(post.created_at).toLocaleDateString()}
                                                    </td>
                                                </tr>
                                            )) : (
                                                <tr>
                                                    <td colSpan={7} className="text-center text-muted py-5">
                                                        <i className="material-symbols-outlined fs-48 d-block mb-2">map</i>
                                                        No community posts yet
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </>
    );
}
