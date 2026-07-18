import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface UserReport {
    id: number;
    description?: string;
    description_short?: string;
    status: string;
    admin_note?: string;
    created_at_formatted?: string;
    created_at: string;
    reporter: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        avatar?: string;
    };
    reportedUser: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        avatar?: string;
        status: boolean;
    };
    reviewer?: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
    };
}

interface PaginatedReports {
    data: UserReport[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

interface Props {
    reports: PaginatedReports;
    filters: {
        status?: string;
        reporter?: string;
        reported_user?: string;
        date_from?: string;
        date_to?: string;
    };
}

const statusBadge: Record<string, string> = {
    pending: 'bg-warning text-dark',
    resolved: 'bg-success',
    declined: 'bg-danger',
};

export default function ReportIndex({ reports, filters }: Props) {
    const [status, setStatus] = useState(filters?.status || '');
    const [reporter, setReporter] = useState(filters?.reporter || '');
    const [reportedUser, setReportedUser] = useState(filters?.reported_user || '');
    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');

    const isFirstRender = useRef(true);

    const buildFilterParams = () => ({
        status: status || undefined,
        reporter: reporter || undefined,
        reported_user: reportedUser || undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
    });

    // Instant search with debounce - skips initial mount
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            router.get('/admin/reports', buildFilterParams(), {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 500);

        return () => clearTimeout(timeout);
    }, [status, reporter, reportedUser, dateFrom, dateTo]);

    const handleFilter = () => {
        router.get('/admin/reports', buildFilterParams(), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const resetFilters = () => {
        setStatus('');
        setReporter('');
        setReportedUser('');
        setDateFrom('');
        setDateTo('');
        // Reset triggers useEffect which will clear the filters
    };

    const getAvatarSrc = (avatar?: string) => {
        return avatar ? `/${avatar}` : '/backend/assets/images/placholder.png';
    };

    return (
        <>
            <Head title="Report Management" />

            <div className="main-content-container overflow-hidden" style={{ minHeight: '75vh' }}>
                <div className="row">
                    <div className="col-md-12">
                        <div className="card bg-white rounded-10 border border-white mb-4">
                            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 p-20">
                                <h5 className="mb-0 fw-semibold">Reports</h5>

                                <div className="d-flex align-items-center gap-2 flex-wrap">
                                    <select
                                        className="form-select"
                                        value={status}
                                        onChange={(e) => setStatus(e.target.value)}
                                        style={{ width: '140px', padding: '14px' }}
                                    >
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="declined">Declined</option>
                                    </select>

                                    <input
                                        className="form-control"
                                        placeholder="Search reporter"
                                        type="text"
                                        value={reporter}
                                        onChange={(e) => setReporter(e.target.value)}
                                        style={{ width: '160px' }}
                                    />

                                    <input
                                        className="form-control"
                                        placeholder="Search reported user"
                                        type="text"
                                        value={reportedUser}
                                        onChange={(e) => setReportedUser(e.target.value)}
                                        style={{ width: '160px' }}
                                    />

                                    <input
                                        className="form-control"
                                        type="date"
                                        value={dateFrom}
                                        onChange={(e) => setDateFrom(e.target.value)}
                                        style={{ width: '150px' }}
                                    />

                                    <input
                                        className="form-control"
                                        type="date"
                                        value={dateTo}
                                        onChange={(e) => setDateTo(e.target.value)}
                                        style={{ width: '150px' }}
                                    />

                                    <button className="btn btn-primary text-white" onClick={handleFilter}>
                                        Filter
                                    </button>

                                    <button className="btn btn-secondary" onClick={resetFilters}>
                                        Reset
                                    </button>
                                </div>
                            </div>

                            <div className="default-table-area mx-minus-1 table-to-do-list">
                                <div className="table-responsive">
                                    <table className="table align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Reporter</th>
                                                <th>Reported User</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th className="text-end">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {reports?.data?.length > 0 ? (
                                                reports.data.map((report: UserReport) => (
                                                    <tr key={report.id}>
                                                        <td>#{report.id}</td>

                                                        <td>
                                                            <div className="d-flex align-items-center">
                                                                <div className="flex-shrink-0">
                                                                    <img
                                                                        alt={report.reporter?.first_name}
                                                                        className="rounded-circle"
                                                                        src={getAvatarSrc(report.reporter?.avatar)}
                                                                        style={{ width: '32px', height: '32px', objectFit: 'cover' }}
                                                                    />
                                                                </div>
                                                                <div className="flex-grow-1 ms-2">
                                                                    <h6 className="fw-medium fs-14 mb-0">
                                                                        {report.reporter?.first_name} {report.reporter?.last_name}
                                                                    </h6>
                                                                    <small className="text-muted">{report.reporter?.email}</small>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <div className="d-flex align-items-center">
                                                                <div className="flex-shrink-0">
                                                                    <img
                                                                        alt={report.reportedUser?.first_name}
                                                                        className="rounded-circle"
                                                                        src={getAvatarSrc(report.reportedUser?.avatar)}
                                                                        style={{ width: '32px', height: '32px', objectFit: 'cover' }}
                                                                    />
                                                                </div>
                                                                <div className="flex-grow-1 ms-2">
                                                                    <h6 className="fw-medium fs-14 mb-0">
                                                                        {report.reportedUser?.first_name} {report.reportedUser?.last_name}
                                                                    </h6>
                                                                    <small className="text-muted">{report.reportedUser?.email}</small>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <span className="text-muted fs-14" title={report.description}>
                                                                {report.description_short}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            <span className={`badge ${statusBadge[report.status] || 'bg-secondary'} fs-12`}>
                                                                {report.status.charAt(0).toUpperCase() + report.status.slice(1)}
                                                            </span>
                                                        </td>

                                                        <td className="text-muted fs-14">{report.created_at_formatted}</td>

                                                        <td>
                                                            <div className="d-flex justify-content-end" style={{ gap: '12px' }}>
                                                                <Link
                                                                    className="bg-transparent p-0 border-0 hover-text-success"
                                                                    href={`/admin/reports/show/${report.id}`}
                                                                    title="View details"
                                                                >
                                                                    <i className="material-symbols-outlined fs-16 fw-normal text-body">
                                                                        Visibility
                                                                    </i>
                                                                </Link>
                                                                <Link
                                                                    className="bg-transparent p-0 border-0 hover-text-primary"
                                                                    href={`/admin/reports/edit/${report.id}`}
                                                                    title="Edit report"
                                                                >
                                                                    <i className="material-symbols-outlined fs-16 fw-normal text-body">
                                                                        edit
                                                                    </i>
                                                                </Link>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan={7} className="text-center py-5">
                                                        <div className="text-muted">
                                                            <i className="material-symbols-outlined fs-40 d-block mb-2">report</i>
                                                            No reports found
                                                        </div>
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="d-flex justify-content-between align-items-center px-3 pb-3 pt-2">
                                    <div className="text-muted fs-14">
                                        Showing {reports?.from ?? 0} - {reports?.to ?? 0} of {reports?.total ?? 0} records
                                    </div>

                                    <ul className="pagination mb-0">
                                        {reports?.links?.map((link, index) => (
                                            <li
                                                key={index}
                                                className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}
                                            >
                                                <button
                                                    type="button"
                                                    className="page-link"
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                    disabled={!link.url}
                                                    onClick={() => {
                                                        if (link.url) {
                                                            router.visit(link.url, {
                                                                preserveState: true,
                                                                preserveScroll: true,
                                                            });
                                                        }
                                                    }}
                                                />
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
