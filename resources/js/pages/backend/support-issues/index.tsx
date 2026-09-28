import { Head, Link, router } from '@inertiajs/react';

interface Issue {
    id: number;
    subject: string;
    message: string;
    status: string;
    created_at: string;
    name: string;
    email: string;
}

interface Props {
    issues: {
        data: Issue[];
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    filters: {
        status?: string;
    };
}

const statusClass: Record<string, string> = {
    open: 'bg-warning text-dark',
    in_progress: 'bg-info',
    resolved: 'bg-success',
    closed: 'bg-secondary',
};

export default function SupportIssuesIndex({ issues, filters }: Props) {
    const updateStatus = (status: string) => {
        router.get(
            '/admin/support-issues',
            status ? { status } : {},
            {
                preserveState: true,
            }
        );
    };

    return (
        <>
            <Head title="Support Issues" />

            <div className="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
                <div>
                    <h3 className="mb-0">Support Issues</h3>
                    <p className="fs-16 mb-0">
                        Review customer support requests and send replies.
                    </p>
                </div>

                <select
                    className="form-select"
                    style={{ width: '160px' }}
                    value={filters.status || ''}
                    onChange={(event) => updateStatus(event.target.value)}
                >
                    <option value="">All statuses</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            <div className="card bg-white rounded-10 border border-white">
                <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 p-20">
                    {/* <h5 className="mb-0 fw-semibold">Support Message</h5> */}

                </div>

                <div className="default-table-area mx-minus-1 table-to-do-list">
                    <div className="table-responsive">
                        <table className="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th className="text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                {issues.data.length ? (
                                    issues.data.map((issue) => (
                                        <tr key={issue.id}>
                                            <td>#{issue.id}</td>

                                            <td>
                                                <div className="fw-medium">
                                                    {issue.name}
                                                </div>
                                                <small className="text-muted">
                                                    {issue.email}
                                                </small>
                                            </td>

                                            <td>
                                                <div className="fw-medium">
                                                    {issue.subject}
                                                </div>
                                                <small className="text-muted">
                                                    {issue.message.length > 70
                                                        ? `${issue.message.slice(
                                                            0,
                                                            70
                                                        )}...`
                                                        : issue.message}
                                                </small>
                                            </td>

                                            <td>
                                                <span
                                                    className={`badge ${statusClass[issue.status] ||
                                                        'bg-secondary'
                                                        }`}
                                                >
                                                    {issue.status.replace('_', ' ')}
                                                </span>
                                            </td>

                                            <td>
                                                {new Date(
                                                    issue.created_at
                                                ).toLocaleDateString()}
                                            </td>

                                            <td className="text-end">
                                                <Link
                                                    href={`/admin/support-issues/${issue.id}`}
                                                    className="btn btn-sm btn-primary text-white"
                                                >
                                                    View &amp; Reply
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan={6}
                                            className="text-center py-5 text-muted"
                                        >
                                            No support issues found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="d-flex justify-content-between align-items-center p-3">
                        <span className="text-muted">
                            Showing {issues.from || 0}–{issues.to || 0} of{' '}
                            {issues.total}
                        </span>

                        <ul className="pagination mb-0">
                            {issues.links.map((link, index) => (
                                <li
                                    key={index}
                                    className={`page-item ${link.active ? 'active' : ''
                                        } ${!link.url ? 'disabled' : ''}`}
                                >
                                    <button
                                        className="page-link"
                                        disabled={!link.url}
                                        onClick={() =>
                                            link.url && router.visit(link.url)
                                        }
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </>
    );
}