import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    avatar?: string;
    status: boolean;
    created_at?: string;
    phone?: string;
    role?: string;
}

interface UserReport {
    id: number;
    description?: string;
    status: string;
    admin_note?: string;
    created_at_formatted?: string;
    created_at: string;
    reviewd_at?: string;
    reporter: User | null;
    reportedUser: User | null;
    reviewer?: User | null;
}

interface Props {
    report: UserReport;
}

export default function Show({ report }: Props) {
    const [showActionModal, setShowActionModal] = useState(false);
    const [actionStatus, setActionStatus] = useState('resolved');
    const [adminNote, setAdminNote] = useState(report.admin_note || '');
    const [processing, setProcessing] = useState(false);

    const getAvatarSrc = (avatar?: string) => {
        return avatar ? `/${avatar}` : '/backend/assets/images/placholder.png';
    };

    const statusBadge: Record<string, string> = {
        pending: 'bg-warning text-dark',
        resolved: 'bg-success',
        declined: 'bg-danger',
    };

    const handleUpdateStatus = async () => {
        setProcessing(true);
        try {
            await router.put(
                `/admin/reports/update/${report.id}`,
                {
                    status: actionStatus,
                    admin_note: adminNote,
                },
                {
                    onSuccess: () => {
                        setShowActionModal(false);
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

    const openActionModal = (status: string) => {
        setActionStatus(status);
        setAdminNote(report.admin_note || '');
        setShowActionModal(true);
    };

    return (
        <>
            <Head title={`Report #${report.id}`} />

            <div className="main-content-container overflow-hidden" style={{ minHeight: '75vh' }}>
                <div className="row">
                    <div className="col-lg-12">
                        <div className="d-flex justify-content-between align-items-center mb-4">
                            <h4 className="mb-0">Report #{report.id}</h4>
                            <div className="d-flex gap-2 flex-wrap">
                                {report.status === 'pending' && (
                                    <>
                                        <button
                                            className="btn btn-success text-white"
                                            onClick={() => openActionModal('resolved')}
                                        >
                                            <i className="material-symbols-outlined fs-16 me-1">check_circle</i>
                                            Mark Resolved
                                        </button>
                                        <button
                                            className="btn btn-danger text-white"
                                            onClick={() => openActionModal('declined')}
                                        >
                                            <i className="material-symbols-outlined fs-16 me-1">cancel</i>
                                            Decline Report
                                        </button>
                                    </>
                                )}
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
                                <Link
                                    href={`/admin/reports/edit/${report.id}`}
                                    className="btn btn-primary text-white"
                                >
                                    <i className="material-symbols-outlined fs-16 me-1">edit</i>
                                    Edit Report
                                </Link>
                                <Link href="/admin/reports" className="btn btn-secondary">
                                    Back to List
                                </Link>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-lg-8">
                                <div className="card bg-white rounded-10 border border-white mb-4">
                                    <div className="card-body p-4">
                                        <h5 className="fw-semibold mb-3">Report Details</h5>

                                        <div className="mb-4">
                                            <span className="label fs-14 text-muted mb-2 d-block">Status</span>
                                            <span className={`badge ${statusBadge[report.status] || 'bg-secondary'} fs-14 py-2 px-3`}>
                                                {report.status.charAt(0).toUpperCase() + report.status.slice(1)}
                                            </span>
                                            {report.reportedUser && !report.reportedUser.status && (
                                                <span className="badge bg-danger fs-14 py-2 px-3 ms-2">
                                                    Reported User Suspended
                                                </span>
                                            )}
                                        </div>

                                        <div className="mb-4">
                                            <span className="label fs-14 text-muted mb-2 d-block">Description</span>
                                            <div
                                                className="border rounded p-3 bg-light"
                                                style={{ whiteSpace: 'pre-wrap', minHeight: '100px' }}
                                            >
                                                {report.description || 'No description provided.'}
                                            </div>
                                        </div>

                                        <div className="mb-4">
                                            <span className="label fs-14 text-muted mb-2 d-block">Admin Note</span>
                                            <div
                                                className="border rounded p-3 bg-light"
                                                style={{ whiteSpace: 'pre-wrap', minHeight: '80px' }}
                                            >
                                                {report.admin_note || 'No admin note yet.'}
                                            </div>
                                        </div>

                                        <div className="mb-3">
                                            <span className="label fs-14 text-muted mb-2 d-block">Reviewed By</span>
                                            <span className="fw-medium">
                                                {report.reviewer
                                                    ? `${report.reviewer.first_name} ${report.reviewer.last_name}`
                                                    : 'Not reviewed yet'}
                                            </span>
                                            {report.reviewd_at && report.reviewd_at !== 'Not reviewed yet' && (
                                                <span className="text-muted ms-2 fs-14">({report.reviewd_at})</span>
                                            )}
                                        </div>

                                        <div>
                                            <span className="label fs-14 text-muted mb-2 d-block">Submitted At</span>
                                            <span className="fw-medium">{report.created_at_formatted}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="col-lg-4">
                                <div className="card bg-white rounded-10 border border-white mb-4">
                                    <div className="card-body p-4">
                                        <h5 className="fw-semibold mb-3">
                                            <i className="material-symbols-outlined fs-18 me-1">person</i>
                                            Reporter
                                        </h5>
                                        {report.reporter ? (
                                            <>
                                                <div className="d-flex align-items-center mb-3">
                                                    <div className="flex-shrink-0 me-3">
                                                        <img
                                                            src={getAvatarSrc(report.reporter.avatar)}
                                                            alt={report.reporter.first_name}
                                                            className="rounded-circle border"
                                                            style={{ width: '56px', height: '56px', objectFit: 'cover' }}
                                                        />
                                                    </div>
                                                    <div className="flex-grow-1 overflow-hidden">
                                                        <h6 className="fw-semibold mb-0 text-truncate">
                                                            {report.reporter.first_name} {report.reporter.last_name}
                                                        </h6>
                                                        <p className="text-muted mb-0 fs-14 text-truncate">{report.reporter.email}</p>
                                                    </div>
                                                </div>
                                                <div className="d-grid">
                                                    <Link
                                                        href={`/admin/user/show/${report.reporter.id}`}
                                                        className="btn btn-outline-primary btn-sm"
                                                    >
                                                        View Profile
                                                    </Link>
                                                </div>
                                            </>
                                        ) : (
                                            <p className="text-muted">Reporter not found</p>
                                        )}
                                    </div>
                                </div>

                                <div className="card bg-white rounded-10 border border-white mb-4">
                                    <div className="card-body p-4">
                                        <h5 className="fw-semibold mb-3">
                                            <i className="material-symbols-outlined fs-18 me-1">block</i>
                                            Reported User
                                        </h5>
                                        {report.reportedUser ? (
                                            <>
                                                <div className="d-flex align-items-center mb-3">
                                                    <div className="flex-shrink-0 me-3">
                                                        <img
                                                            src={getAvatarSrc(report.reportedUser.avatar)}
                                                            alt={report.reportedUser.first_name}
                                                            className="rounded-circle border"
                                                            style={{ width: '56px', height: '56px', objectFit: 'cover' }}
                                                        />
                                                    </div>
                                                    <div className="flex-grow-1 overflow-hidden">
                                                        <h6 className="fw-semibold mb-0 text-truncate">
                                                            {report.reportedUser.first_name} {report.reportedUser.last_name}
                                                        </h6>
                                                        <p className="text-muted mb-0 fs-14 text-truncate">{report.reportedUser.email}</p>
                                                    </div>
                                                </div>

                                                <div className="mb-3">
                                                    <span className={`badge ${report.reportedUser.status ? 'bg-success' : 'bg-danger'} w-100 py-2 fs-13`}>
                                                        {report.reportedUser.status ? 'Active' : 'Suspended'}
                                                    </span>
                                                </div>

                                                <div className="d-grid gap-2">
                                                    <Link
                                                        href={`/admin/user/show/${report.reportedUser.id}`}
                                                        className="btn btn-outline-primary btn-sm"
                                                    >
                                                        View Profile
                                                    </Link>
                                                    <button
                                                        className={`btn ${report.reportedUser.status ? 'btn-warning' : 'btn-info'} btn-sm text-white`}
                                                        onClick={handleToggleUserStatus}
                                                    >
                                                        {report.reportedUser.status ? 'Suspend User' : 'Unsuspend User'}
                                                    </button>
                                                </div>
                                            </>
                                        ) : (
                                            <p className="text-muted">Reported user not found</p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {showActionModal && (
                <div
                    className="modal fade show d-block"
                    tabIndex={-1}
                    style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
                    onClick={() => setShowActionModal(false)}
                >
                    <div className="modal-dialog" onClick={(e) => e.stopPropagation()}>
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">
                                    {actionStatus === 'resolved' ? 'Resolve Report' : 'Decline Report'}
                                </h5>
                                <button
                                    type="button"
                                    className="btn-close"
                                    onClick={() => setShowActionModal(false)}
                                />
                            </div>
                            <div className="modal-body">
                                <div className="mb-3">
                                    <label className="form-label fw-medium">Status</label>
                                    <div>
                                        <span className={`badge ${actionStatus === 'resolved' ? 'bg-success' : 'bg-danger'} fs-14 py-2 px-3`}>
                                            {actionStatus.charAt(0).toUpperCase() + actionStatus.slice(1)}
                                        </span>
                                    </div>
                                </div>

                                <div className="mb-3">
                                    <label className="form-label fw-medium">Admin Note</label>
                                    <textarea
                                        className="form-control"
                                        rows={4}
                                        value={adminNote}
                                        onChange={(e) => setAdminNote(e.target.value)}
                                        placeholder="Add admin notes about this decision..."
                                    />
                                </div>
                            </div>
                            <div className="modal-footer">
                                <button
                                    className="btn btn-secondary"
                                    onClick={() => setShowActionModal(false)}
                                >
                                    Cancel
                                </button>
                                <button
                                    className={`btn ${actionStatus === 'resolved' ? 'btn-success' : 'btn-danger'} text-white`}
                                    onClick={handleUpdateStatus}
                                    disabled={processing}
                                >
                                    {processing ? 'Processing...' : `Confirm ${actionStatus.charAt(0).toUpperCase() + actionStatus.slice(1)}`}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
