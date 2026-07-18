import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    avatar?: string;
    status: boolean;
    role?: string;
}

interface UserReport {
    id: number;
    description?: string;
    status: string;
    admin_note?: string;
    reviewd_at?: string;
    reporter: User | null;
    reportedUser: User | null;
}

interface Props {
    report: UserReport;
}

export default function Update({ report }: Props) {
    const [status, setStatus] = useState(report.status);
    const [adminNote, setAdminNote] = useState(report.admin_note || '');
    const [processing, setProcessing] = useState(false);

    const getAvatarSrc = (avatar?: string) => {
        return avatar ? `/${avatar}` : '/backend/assets/images/placholder.png';
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true);

        try {
            await router.put(
                `/admin/reports/update/${report.id}`,
                {
                    status,
                    admin_note: adminNote,
                },
                {
                    onSuccess: () => {
                        // Backend redirects to the show page, no manual navigation needed
                    },
                    onFinish: () => setProcessing(false),
                }
            );
        } catch {
            setProcessing(false);
        }
    };

    const handleToggleUserStatus = () => {
        const user = report.reportedUser;
        if (!user) return;

        const action = user.status ? 'Suspend' : 'Unsuspend';
        const confirmed = window.confirm(`Are you sure you want to ${action.toLowerCase()} ${user.first_name} ${user.last_name}?`);
        if (confirmed) {
            router.put(`/admin/reports/toggle-active-status/${report.id}`, undefined);
        }
    };

    return (
        <>
            <Head title={`Update Report #${report.id}`} />

            <div className="main-content-container overflow-hidden" style={{ minHeight: '75vh' }}>
                <div className="row">
                    <div className="col-lg-12">
                        <div className="d-flex justify-content-between align-items-center mb-4">
                            <h4 className="mb-0">Update Report #{report.id}</h4>
                            <div className="d-flex gap-2">
                                {report.reportedUser && (
                                    <button
                                        className={`btn ${report.reportedUser.status ? 'btn-warning' : 'btn-info'} text-white`}
                                        onClick={handleToggleUserStatus}
                                    >
                                        <i className="material-symbols-outlined fs-16 me-1">
                                            {report.reportedUser.status ? 'block' : 'check_circle'}
                                        </i>
                                        {report.reportedUser.status ? 'Suspend User' : 'Unsuspend User'}
                                    </button>
                                )}
                                <Link href={`/admin/reports/show/${report.id}`} className="btn btn-secondary">
                                    Cancel
                                </Link>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-lg-8">
                                <div className="card bg-white rounded-10 border border-white">
                                    <div className="card-body p-4">
                                        <form onSubmit={handleSubmit}>
                                            <div className="mb-4">
                                                <h5 className="fw-semibold mb-3">Review Report</h5>

                                                <div className="border rounded p-3 bg-light mb-3" style={{ whiteSpace: 'pre-wrap' }}>
                                                    <strong>Description:</strong>
                                                    <p className="mb-0 mt-1">{report.description || 'No description provided.'}</p>
                                                </div>
                                            </div>

                                            <div className="mb-4">
                                                <label className="form-label fw-medium">Action</label>
                                                <select
                                                    className="form-select"
                                                    value={status}
                                                    onChange={(e) => setStatus(e.target.value)}
                                                >
                                                    <option value="pending">Pending</option>
                                                    <option value="resolved">Resolved (Accept Report)</option>
                                                    <option value="declined">Declined (Deny Report)</option>
                                                </select>
                                            </div>

                                            <div className="mb-4">
                                                <label className="form-label fw-medium">Admin Note</label>
                                                <textarea
                                                    className="form-control"
                                                    rows={5}
                                                    value={adminNote}
                                                    onChange={(e) => setAdminNote(e.target.value)}
                                                    placeholder="Add your notes about this report..."
                                                />
                                                <small className="text-muted">
                                                    This note will be visible when viewing the report details.
                                                </small>
                                            </div>

                                            <button
                                                type="submit"
                                                className="btn btn-primary text-white"
                                                disabled={processing}
                                            >
                                                {processing ? 'Updating...' : 'Update Report'}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div className="col-lg-4">
                                <div className="card bg-white rounded-10 border border-white mb-4">
                                    <div className="card-body p-4">
                                        <h5 className="fw-semibold mb-3">Report Info</h5>

                                        <div className="mb-3">
                                            <span className="fs-14 text-muted d-block">Report ID</span>
                                            <span className="fw-medium">#{report.id}</span>
                                        </div>

                                        <div className="mb-3">
                                            <span className="fs-14 text-muted d-block">Current Status</span>
                                            <span className={`badge ${report.status === 'pending' ? 'bg-warning text-dark' : report.status === 'resolved' ? 'bg-success' : 'bg-danger'} fs-13`}>
                                                {report.status.charAt(0).toUpperCase() + report.status.slice(1)}
                                            </span>
                                        </div>

                                        <hr />

                                        <h6 className="fw-medium mb-2">Reporter</h6>
                                        {report.reporter && (
                                            <div className="d-flex align-items-center mb-3">
                                                <img
                                                    src={getAvatarSrc(report.reporter.avatar)}
                                                    alt={report.reporter.first_name}
                                                    className="rounded-circle me-2"
                                                    style={{ width: '36px', height: '36px', objectFit: 'cover' }}
                                                />
                                                <div>
                                                    <p className="mb-0 fw-medium">{report.reporter.first_name} {report.reporter.last_name}</p>
                                                    <small className="text-muted">{report.reporter.email}</small>
                                                </div>
                                            </div>
                                        )}

                                        <h6 className="fw-medium mb-2">Reported User</h6>
                                        {report.reportedUser && (
                                            <div className="d-flex align-items-center mb-3">
                                                <img
                                                    src={getAvatarSrc(report.reportedUser.avatar)}
                                                    alt={report.reportedUser.first_name}
                                                    className="rounded-circle me-2"
                                                    style={{ width: '36px', height: '36px', objectFit: 'cover' }}
                                                />
                                                <div>
                                                    <p className="mb-0 fw-medium">{report.reportedUser.first_name} {report.reportedUser.last_name}</p>
                                                    <small className="text-muted">{report.reportedUser.email}</small>
                                                </div>
                                            </div>
                                        )}

                                        {report.reportedUser && (
                                            <div className="mt-3">
                                                <span className={`badge ${report.reportedUser.status ? 'bg-success' : 'bg-danger'} w-100 py-2`}>
                                                    {report.reportedUser.status ? 'User is Active' : 'User is Suspended'}
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
