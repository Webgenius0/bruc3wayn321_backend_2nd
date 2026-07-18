import { Head, Link } from '@inertiajs/react';
import DashboardChart from '../../widget/chart';

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    avatar?: string;
    status?: boolean;
    created_at: string;
    posts_count?: number;
}

interface Post {
    id: number;
    slug: string;
    content?: string;
    location?: string;
    is_published: boolean;
    is_draft: boolean;
    user_id: number;
    created_at: string;
    user?: { id: number; first_name: string; last_name: string; avatar?: string };
}

interface Report {
    id: number;
    status: string;
    description?: string;
    created_at: string;
    reporter?: { id: number; first_name: string; last_name: string };
    reported_user?: { id: number; first_name: string; last_name: string };
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
    topUsers: User[];
    recentUsers: User[];
    recentPosts: Post[];
    recentReports: Report[];
}

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const getAvatarSrc = (avatar?: string) =>
    avatar ? `/${avatar}` : '/backend/assets/images/placeholder.png';

const truncate = (str?: string, len = 60) =>
    str && str.length > len ? str.slice(0, len) + '...' : str ?? '—';

export default function DashboardDemo({ stats, charts, topUsers, recentUsers, recentPosts, recentReports }: Props) {
    return (
        <>
            <Head title="Dashboard — Travel Guide Admin" />
            <div className="main-content-container overflow-hidden">

                {/* ── Stat Cards ───────────────────────────────────────────── */}
                <div className="row g-4 mb-4">
                    {/* Total Travelers */}
                    <div className="col-md-6 col-xl-3">
                        <div className="card bg-white p-20 rounded-10 border border-white h-100">
                            <div className="d-flex align-items-center">
                                <div className="flex-grow-1">
                                    <p className="text-muted fs-14 mb-1">Total Travelers</p>
                                    <h2 className="fs-28 fw-bold mb-0 lh-1">{stats.totalUsers.toLocaleString()}</h2>
                                    <small className="text-success">
                                        <i className="material-symbols-outlined fs-14 align-middle">person_add</i>
                                        &nbsp;{stats.newUsersThisMonth} joined this month
                                    </small>
                                </div>
                                <div className="flex-shrink-0 ms-3">
                                    <div className="bg-primary text-white text-center rounded-circle d-flex align-items-center justify-content-center"
                                        style={{ width: 72, height: 72 }}>
                                        <i className="material-symbols-outlined fs-36">travel_explore</i>
                                    </div>
                                </div>
                            </div>
                            <div className="d-flex gap-3 mt-3 pt-3 border-top">
                                <span className="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fs-13">
                                    {stats.activeUsers} Active
                                </span>
                                <span className="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 fs-13">
                                    {stats.suspendedUsers} Suspended
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Total Posts */}
                    <div className="col-md-6 col-xl-3">
                        <div className="card bg-white p-20 rounded-10 border border-white h-100">
                            <div className="d-flex align-items-center">
                                <div className="flex-grow-1">
                                    <p className="text-muted fs-14 mb-1">Travel Posts</p>
                                    <h2 className="fs-28 fw-bold mb-0 lh-1">{stats.totalPosts.toLocaleString()}</h2>
                                    <small className="text-success">
                                        <i className="material-symbols-outlined fs-14 align-middle">article</i>
                                        &nbsp;{stats.postsThisMonth} this month
                                    </small>
                                </div>
                                <div className="flex-shrink-0 ms-3">
                                    <div className="bg-info text-white text-center rounded-circle d-flex align-items-center justify-content-center"
                                        style={{ width: 72, height: 72 }}>
                                        <i className="material-symbols-outlined fs-36">map</i>
                                    </div>
                                </div>
                            </div>
                            <div className="d-flex gap-3 mt-3 pt-3 border-top">
                                <span className="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fs-13">
                                    {stats.publishedPosts} Published
                                </span>
                                <span className="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-2 py-1 fs-13">
                                    {stats.draftPosts} Draft
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Engagement */}
                    <div className="col-md-6 col-xl-3">
                        <div className="card bg-white p-20 rounded-10 border border-white h-100">
                            <div className="d-flex align-items-center">
                                <div className="flex-grow-1">
                                    <p className="text-muted fs-14 mb-1">Total Engagements</p>
                                    <h2 className="fs-28 fw-bold mb-0 lh-1">
                                        {(stats.totalLikes + stats.totalComments).toLocaleString()}
                                    </h2>
                                    <small className="text-muted">Likes & comments on posts</small>
                                </div>
                                <div className="flex-shrink-0 ms-3">
                                    <div className="bg-warning text-white text-center rounded-circle d-flex align-items-center justify-content-center"
                                        style={{ width: 72, height: 72 }}>
                                        <i className="material-symbols-outlined fs-36">favorite</i>
                                    </div>
                                </div>
                            </div>
                            <div className="d-flex gap-3 mt-3 pt-3 border-top">
                                <span className="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 fs-13">
                                    <i className="material-symbols-outlined fs-12 align-middle">favorite</i> {stats.totalLikes.toLocaleString()} Likes
                                </span>
                                <span className="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1 fs-13">
                                    <i className="material-symbols-outlined fs-12 align-middle">chat_bubble</i> {stats.totalComments.toLocaleString()} Comments
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Reports */}
                    <div className="col-md-6 col-xl-3">
                        <div className="card bg-white p-20 rounded-10 border border-white h-100">
                            <div className="d-flex align-items-center">
                                <div className="flex-grow-1">
                                    <p className="text-muted fs-14 mb-1">User Reports</p>
                                    <h2 className="fs-28 fw-bold mb-0 lh-1">{stats.totalReports.toLocaleString()}</h2>
                                    <small className="text-danger">
                                        <i className="material-symbols-outlined fs-14 align-middle">warning</i>
                                        &nbsp;{stats.pendingReports} pending review
                                    </small>
                                </div>
                                <div className="flex-shrink-0 ms-3">
                                    <div className="bg-danger text-white text-center rounded-circle d-flex align-items-center justify-content-center"
                                        style={{ width: 72, height: 72 }}>
                                        <i className="material-symbols-outlined fs-36">report</i>
                                    </div>
                                </div>
                            </div>
                            <div className="d-flex gap-3 mt-3 pt-3 border-top">
                                <span className="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1 fs-13">
                                    {stats.pendingReports} Pending
                                </span>
                                <span className="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fs-13">
                                    {stats.resolvedReports} Resolved
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ── Charts ───────────────────────────────────────────────── */}
                <div className="row g-4 mb-4">
                    <div className="col-lg-6">
                        <div className="card bg-white p-20 rounded-10 border border-white">
                            <div className="d-flex justify-content-between align-items-center mb-3">
                                <h5 className="fw-semibold mb-0">New Travelers Joined</h5>
                                <span className="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1 fs-13">
                                    {new Date().getFullYear()}
                                </span>
                            </div>
                            <DashboardChart
                                type="area"
                                height="240"
                                categories={months}
                                series={[
                                    { name: 'New Users', data: charts.usersMonthlyData },
                                ]}
                            />
                        </div>
                    </div>
                    <div className="col-lg-6">
                        <div className="card bg-white p-20 rounded-10 border border-white">
                            <div className="d-flex justify-content-between align-items-center mb-3">
                                <h5 className="fw-semibold mb-0">Travel Posts Published</h5>
                                <span className="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1 fs-13">
                                    {new Date().getFullYear()}
                                </span>
                            </div>
                            <DashboardChart
                                type="bar"
                                height="240"
                                categories={months}
                                series={[
                                    { name: 'Posts', data: charts.postsMonthlyData },
                                ]}
                            />
                        </div>
                    </div>
                </div>

                {/* ── Quick Stats Row ───────────────────────────────────────── */}
                <div className="row g-4 mb-4">
                    <div className="col-md-4">
                        <div className="card bg-primary-50 p-20 border rounded-10 border-primary-50">
                            <h6 className="text-white mb-3">Community Overview</h6>
                            <div className="d-flex justify-content-between mb-2">
                                <span className="text-white fs-14">Total Travelers</span>
                                <span className="text-white fw-semibold">{stats.totalUsers.toLocaleString()}</span>
                            </div>
                            <div className="d-flex justify-content-between mb-2">
                                <span className="text-white fs-14">Following Connections</span>
                                <span className="text-white fw-semibold">{stats.totalFollows.toLocaleString()}</span>
                            </div>
                            <div className="d-flex justify-content-between mb-2">
                                <span className="text-white fs-14">Active This Month</span>
                                <span className="text-white fw-semibold">{stats.newUsersThisMonth.toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card bg-white p-20 rounded-10 border border-white">
                            <h6 className="fw-semibold mb-3">Content Overview</h6>
                            <div className="d-flex justify-content-between mb-2">
                                <span className="text-muted fs-14">Total Posts</span>
                                <span className="fw-semibold">{stats.totalPosts.toLocaleString()}</span>
                            </div>
                            <div className="d-flex justify-content-between mb-2">
                                <span className="text-muted fs-14">Published</span>
                                <span className="fw-semibold text-success">{stats.publishedPosts.toLocaleString()}</span>
                            </div>
                            <div className="d-flex justify-content-between mb-2">
                                <span className="text-muted fs-14">Drafts</span>
                                <span className="fw-semibold text-secondary">{stats.draftPosts.toLocaleString()}</span>
                            </div>
                            <div className="d-flex justify-content-between">
                                <span className="text-muted fs-14">Posted This Month</span>
                                <span className="fw-semibold text-info">{stats.postsThisMonth.toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card bg-white p-20 rounded-10 border border-white">
                            <h6 className="fw-semibold mb-3">Engagement Overview</h6>
                            <div className="d-flex justify-content-between mb-2">
                                <span className="text-muted fs-14">Total Likes</span>
                                <span className="fw-semibold text-danger">{stats.totalLikes.toLocaleString()}</span>
                            </div>
                            <div className="d-flex justify-content-between mb-2">
                                <span className="text-muted fs-14">Total Comments</span>
                                <span className="fw-semibold text-info">{stats.totalComments.toLocaleString()}</span>
                            </div>
                            <div className="d-flex justify-content-between mb-2">
                                <span className="text-muted fs-14">Pending Reports</span>
                                <span className="fw-semibold text-warning">{stats.pendingReports.toLocaleString()}</span>
                            </div>
                            <div className="d-flex justify-content-between">
                                <span className="text-muted fs-14">Resolved Reports</span>
                                <span className="fw-semibold text-success">{stats.resolvedReports.toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ── Tables Row ───────────────────────────────────────────── */}
                <div className="row g-4 mb-4">
                    {/* Top Active Travelers */}
                    <div className="col-lg-6">
                        <div className="card bg-white p-20 rounded-10 border border-white">
                            <div className="d-flex justify-content-between align-items-center mb-3">
                                <h5 className="fw-semibold mb-0">Top Active Travelers</h5>
                                <Link href="/admin/users" className="btn btn-outline-primary btn-sm">View All</Link>
                            </div>
                            <div className="table-responsive">
                                <table className="table align-middle mb-0">
                                    <thead className="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Traveler</th>
                                            <th className="text-end">Posts</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {topUsers.length > 0 ? topUsers.map((user, i) => (
                                            <tr key={user.id}>
                                                <td className="text-muted fw-medium">{i + 1}.</td>
                                                <td>
                                                    <div className="d-flex align-items-center">
                                                        <img
                                                            src={getAvatarSrc(user.avatar)}
                                                            alt={user.first_name}
                                                            className="rounded-circle me-2"
                                                            style={{ width: 38, height: 38, objectFit: 'cover' }}
                                                        />
                                                        <div>
                                                            <div className="fw-medium fs-14">
                                                                {user.first_name} {user.last_name}
                                                            </div>
                                                            <small className="text-muted">{user.email}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="text-end">
                                                    <span className="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                                                        {user.posts_count ?? 0}
                                                    </span>
                                                </td>
                                            </tr>
                                        )) : (
                                            <tr>
                                                <td colSpan={3} className="text-center text-muted py-4">
                                                    <i className="material-symbols-outlined fs-36 d-block mb-1">person_off</i>
                                                    No travelers yet
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {/* Recent Travel Posts */}
                    <div className="col-lg-6">
                        <div className="card bg-white p-20 rounded-10 border border-white">
                            <div className="d-flex justify-content-between align-items-center mb-3">
                                <h5 className="fw-semibold mb-0">Recent Travel Posts</h5>
                                <Link href="/admin/posts" className="btn btn-outline-info btn-sm">View All</Link>
                            </div>
                            <div className="table-responsive">
                                <table className="table align-middle mb-0">
                                    <thead className="table-light">
                                        <tr>
                                            <th>Post</th>
                                            <th>Location</th>
                                            <th className="text-end">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {recentPosts.length > 0 ? recentPosts.map((post) => (
                                            <tr key={post.id}>
                                                <td>
                                                    <div className="d-flex align-items-center gap-2">
                                                        {post.user && (
                                                            <img
                                                                src={getAvatarSrc(post.user.avatar)}
                                                                alt={post.user.first_name}
                                                                className="rounded-circle flex-shrink-0"
                                                                style={{ width: 34, height: 34, objectFit: 'cover' }}
                                                            />
                                                        )}
                                                        <div>
                                                            <div className="fw-medium fs-13">
                                                                {post.user ? `${post.user.first_name} ${post.user.last_name}` : 'Unknown'}
                                                            </div>
                                                            <small className="text-muted">{truncate(post.content, 45)}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span className="text-muted fs-13">
                                                        <i className="material-symbols-outlined fs-14 align-middle">location_on</i>
                                                        &nbsp;{post.location || '—'}
                                                    </span>
                                                </td>
                                                <td className="text-end">
                                                    {post.is_published ? (
                                                        <span className="badge bg-success fs-12">Published</span>
                                                    ) : post.is_draft ? (
                                                        <span className="badge bg-secondary fs-12">Draft</span>
                                                    ) : (
                                                        <span className="badge bg-warning text-dark fs-12">Pending</span>
                                                    )}
                                                </td>
                                            </tr>
                                        )) : (
                                            <tr>
                                                <td colSpan={3} className="text-center text-muted py-4">
                                                    <i className="material-symbols-outlined fs-36 d-block mb-1">map</i>
                                                    No posts yet
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ── Recent New Users & Pending Reports ───────────────────── */}
                <div className="row g-4 mb-4">
                    {/* Recently Joined */}
                    <div className="col-lg-6">
                        <div className="card bg-white p-20 rounded-10 border border-white">
                            <div className="d-flex justify-content-between align-items-center mb-3">
                                <h5 className="fw-semibold mb-0">Recently Joined Travelers</h5>
                                <Link href="/admin/users" className="btn btn-outline-success btn-sm">View All</Link>
                            </div>
                            <div className="table-responsive">
                                <table className="table align-middle mb-0">
                                    <thead className="table-light">
                                        <tr>
                                            <th>Traveler</th>
                                            <th className="text-center">Status</th>
                                            <th className="text-end">Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {recentUsers.length > 0 ? recentUsers.map((user) => (
                                            <tr key={user.id}>
                                                <td>
                                                    <div className="d-flex align-items-center gap-2">
                                                        <img
                                                            src={getAvatarSrc(user.avatar)}
                                                            alt={user.first_name}
                                                            className="rounded-circle"
                                                            style={{ width: 36, height: 36, objectFit: 'cover' }}
                                                        />
                                                        <div>
                                                            <div className="fw-medium fs-13">{user.first_name} {user.last_name}</div>
                                                            <small className="text-muted">{user.email}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="text-center">
                                                    {user.status ? (
                                                        <span className="badge bg-success fs-12">Active</span>
                                                    ) : (
                                                        <span className="badge bg-danger fs-12">Suspended</span>
                                                    )}
                                                </td>
                                                <td className="text-end text-muted fs-13">
                                                    {new Date(user.created_at).toLocaleDateString()}
                                                </td>
                                            </tr>
                                        )) : (
                                            <tr>
                                                <td colSpan={3} className="text-center text-muted py-4">
                                                    <i className="material-symbols-outlined fs-36 d-block mb-1">person_off</i>
                                                    No travelers yet
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {/* Pending Reports */}
                    <div className="col-lg-6">
                        <div className="card bg-white p-20 rounded-10 border border-white">
                            <div className="d-flex justify-content-between align-items-center mb-3">
                                <h5 className="fw-semibold mb-0">Pending Reports</h5>
                                <Link href="/admin/reports" className="btn btn-outline-danger btn-sm">View All</Link>
                            </div>
                            <div className="table-responsive">
                                <table className="table align-middle mb-0">
                                    <thead className="table-light">
                                        <tr>
                                            <th>Reporter</th>
                                            <th>Reported User</th>
                                            <th className="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {recentReports.length > 0 ? recentReports.map((report) => (
                                            <tr key={report.id}>
                                                <td className="fs-13 fw-medium">
                                                    {report.reporter
                                                        ? `${report.reporter.first_name} ${report.reporter.last_name}`
                                                        : '—'}
                                                </td>
                                                <td className="fs-13 text-muted">
                                                    {report.reported_user
                                                        ? `${report.reported_user.first_name} ${report.reported_user.last_name}`
                                                        : '—'}
                                                </td>
                                                <td className="text-end">
                                                    <Link
                                                        href={`/admin/reports/show/${report.id}`}
                                                        className="btn btn-warning btn-sm text-white"
                                                    >
                                                        Review
                                                    </Link>
                                                </td>
                                            </tr>
                                        )) : (
                                            <tr>
                                                <td colSpan={3} className="text-center text-muted py-4">
                                                    <i className="material-symbols-outlined fs-36 d-block mb-1">check_circle</i>
                                                    No pending reports
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
        </>
    );
}
