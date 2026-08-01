import { Head, Link, useForm } from '@inertiajs/react';

interface Issue {
    id: number;
    subject: string;
    message: string;
    status: string;
    reply?: string;
    replied_at?: string;
    created_at: string;
    name: string;
    email: string;
    replied_by?: { first_name?: string; last_name?: string; name?: string };
}

export default function SupportIssueShow({ issue }: { issue: Issue }) {
    const { data, setData, put, processing, errors } = useForm({ reply: issue.reply || '', status: issue.status });
    const submit = (event: React.FormEvent) => { event.preventDefault(); put(`/admin/support-issues/${issue.id}/reply`); };
    const customerName = issue.name;

    return <>
        <Head title={`Support Issue #${issue.id}`} />
        <div className="d-flex justify-content-between align-items-center mb-4 mt-1"><div><h3 className="mb-0">Support Issue #{issue.id}</h3><p className="fs-16 mb-0">Replying sends an email to the customer.</p></div><Link href="/admin/support-issues" className="btn btn-secondary">Back to issues</Link></div>
        <div className="row"><div className="col-lg-7"><div className="card bg-white border border-white rounded-10 p-20 mb-4"><h5 className="mb-3">{issue.subject}</h5><p className="mb-0" style={{ whiteSpace: 'pre-wrap' }}>{issue.message}</p></div>
            {issue.reply && <div className="card bg-light border rounded-10 p-20 mb-4"><h6 className="mb-2">Last reply</h6><p className="mb-0" style={{ whiteSpace: 'pre-wrap' }}>{issue.reply}</p></div>}
            <div className="card bg-white border border-white rounded-10 p-20"><h5 className="mb-3">Reply by email</h5><form onSubmit={submit}><div className="mb-3"><label className="form-label">Reply</label><textarea className="form-control" rows={6} value={data.reply} onChange={(event) => setData('reply', event.target.value)} />{errors.reply && <div className="text-danger mt-1">{errors.reply}</div>}</div><div className="mb-3"><label className="form-label">Status</label><select className="form-select" value={data.status} onChange={(event) => setData('status', event.target.value)}><option value="open">Open</option><option value="in_progress">In progress</option><option value="resolved">Resolved</option><option value="closed">Closed</option></select></div><button className="btn btn-primary text-white" disabled={processing}>Save reply & send email</button></form></div>
        </div><div className="col-lg-5"><div className="card bg-white border border-white rounded-10 p-20"><h5 className="mb-3">Customer</h5><p className="mb-1 fw-medium">{customerName || 'Customer'}</p><p className="mb-0 text-muted">{issue.email}</p><hr /><p className="mb-1"><strong>Submitted:</strong> {new Date(issue.created_at).toLocaleString()}</p>{issue.replied_at && <p className="mb-0"><strong>Last replied:</strong> {new Date(issue.replied_at).toLocaleString()}</p>}</div></div></div>
    </>;
}
