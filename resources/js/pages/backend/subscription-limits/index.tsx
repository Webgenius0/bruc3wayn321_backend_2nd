import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState, useMemo } from 'react';

interface TierConfig {
    ai_chat_messages: number;
    ai_voice_requests: number;
    community_posts: number;
}

interface UserUsage {
    id: number;
    name: string;
    email: string;
    tier: string;
    subscription: {
        status: string;
        type: string;
        expires_at: string | null;
        free_expires_at: string | null;
    } | null;
    usage: {
        ai_chat: { used: number; limit: number; remaining: number; percentage_used: number };
        ai_voice: { used: number; limit: number; remaining: number; percentage_used: number };
        posts: { used: number; limit: number; remaining: number; percentage_used: number };
    };
}

interface PaginatedUsers {
    data: UserUsage[];
    links: any[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface Props {
    tiers: Record<string, TierConfig>;
    freeTrialDays: number;
    freeTrialEnabled: boolean;
    userUsageData?: UserUsage[];
    search?: string;
    tierFilter?: string;
    success?: string;
    pagination?: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}

export default function SubscriptionLimits({
    tiers,
    freeTrialDays,
    freeTrialEnabled,
    userUsageData = [],
    search = '',
    tierFilter = '',
    success,
    pagination,
}: Props) {
    const [activeTab, setActiveTab] = useState<'limits' | 'users'>('limits');
    const [showUserModal, setShowUserModal] = useState(false);
    const [selectedUser, setSelectedUser] = useState<UserUsage | null>(null);

    const { data: limitData, setData: setLimitData, post: updateLimits } = useForm({
        free_ai_chat_messages: tiers.free.ai_chat_messages.toString(),
        free_ai_voice_requests: tiers.free.ai_voice_requests.toString(),
        free_community_posts: tiers.free.community_posts.toString(),
        paid_ai_chat_messages: tiers.paid.ai_chat_messages.toString(),
        paid_ai_voice_requests: tiers.paid.ai_voice_requests.toString(),
        paid_community_posts: tiers.paid.community_posts.toString(),
    });

    const { data: trialData, setData: setTrialData, post: updateTrial } = useForm({
        free_trial_days: freeTrialDays.toString(),
        free_trial_enabled: freeTrialEnabled,
    });

    const updateLimitField = (field: string, value: string) => {
        setLimitData(field, value);
    };

    const updateTrialField = (field: string, value: string | boolean) => {
        setTrialData(field as any, value);
    };

    const handleUpdateLimits = (e: React.FormEvent) => {
        e.preventDefault();
        updateLimits('/admin/subscription-limits/limits', {
            onSuccess: () => {
                router.reload({ only: ['tiers'] });
            },
        });
    };

    const handleUpdateTrial = (e: React.FormEvent) => {
        e.preventDefault();
        updateTrial('/admin/subscription-limits/trial-settings', {
            onSuccess: () => {
                router.reload({ only: ['freeTrialDays', 'freeTrialEnabled'] });
            },
        });
    };

    const handleResetUsage = (userId: number, type: string) => {
        if (confirm(`Reset ${type} usage for this user?`)) {
            router.post(`/admin/subscription-limits/${userId}/reset-usage`, {
                type,
            }, {
                onSuccess: () => {
                    router.reload();
                },
            });
        }
    };

    const handleForceUpgrade = (userId: number) => {
        if (confirm('Force upgrade this user to paid tier?')) {
            router.post(`/admin/subscription-limits/${userId}/force-upgrade`, {}, {
                onSuccess: () => {
                    router.reload();
                },
            });
        }
    };

    const handleForceDowngrade = (userId: number) => {
        if (confirm('Force downgrade this user to free tier?')) {
            router.post(`/admin/subscription-limits/${userId}/force-downgrade`, {}, {
                onSuccess: () => {
                    router.reload();
                },
            });
        }
    };

    const applySearch = (value: string) => {
        router.get('/admin/subscription-limits/users', {
            search: value || undefined,
            tier: tierFilter || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const applyTierFilter = (value: string) => {
        router.get('/admin/subscription-limits/users', {
            tier: value || undefined,
            search: search || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const formatDate = (date: string | null) => {
        if (!date) return '-';
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    const getUsageBarColor = (percentage: number) => {
        if (percentage >= 90) return 'bg-danger';
        if (percentage >= 70) return 'bg-warning';
        return 'bg-success';
    };

    return (
        <>
            <Head title="Subscription Limits" />
            <div className="main-content-container" style={{ minHeight: '75vh' }}>
                {success && (
                    <div className="alert alert-success mb-3">
                        {success}
                    </div>
                )}

                {/* Tabs */}
                <div className="card bg-white rounded-10 border border-white mb-4">
                    <div className="card-header d-flex justify-content-between align-items-center p-20">
                        <div className="d-flex gap-2">
                            <button
                                type="button"
                                className={`btn ${activeTab === 'limits' ? 'btn-primary' : 'btn-outline-primary'}`}
                                onClick={() => setActiveTab('limits')}
                            >
                                <i className="material-symbols-outlined fs-14 me-1">settings</i>
                                Plan Limits
                            </button>
                            <button
                                type="button"
                                className={`btn ${activeTab === 'users' ? 'btn-primary' : 'btn-outline-primary'}`}
                                onClick={() => setActiveTab('users')}
                            >
                                <i className="material-symbols-outlined fs-14 me-1">group</i>
                                User Usage
                            </button>
                        </div>
                    </div>

                    {activeTab === 'limits' && (
                        <div className="card-body p-20">
                            <div className="row">
                                {/* Free Tier Configuration */}
                                <div className="col-lg-6">
                                    <div className="mb-30">
                                        <h4 className="mb-2 fs-18 fw-semibold">
                                            Free Tier Limits
                                        </h4>
                                        <p className="text-muted mb-15">
                                            Limits for users on the free plan
                                        </p>

                                        <div className="mb-15">
                                            <label className="label fs-14 mb-2">
                                                AI Chat Messages / Month
                                            </label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                value={limitData.free_ai_chat_messages}
                                                onChange={(e) =>
                                                    updateLimitField('free_ai_chat_messages', e.target.value)
                                                }
                                                min="0"
                                            />
                                        </div>

                                        <div className="mb-15">
                                            <label className="label fs-14 mb-2">
                                                AI Voice Requests / Month
                                            </label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                value={limitData.free_ai_voice_requests}
                                                onChange={(e) =>
                                                    updateLimitField('free_ai_voice_requests', e.target.value)
                                                }
                                                min="0"
                                            />
                                        </div>

                                        <div className="mb-15">
                                            <label className="label fs-14 mb-2">
                                                Community Posts
                                            </label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                value={limitData.free_community_posts}
                                                onChange={(e) =>
                                                    updateLimitField('free_community_posts', e.target.value)
                                                }
                                                min="0"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Paid Tier Configuration */}
                                <div className="col-lg-6">
                                    <div className="mb-30">
                                        <h4 className="mb-2 fs-18 fw-semibold">
                                            Paid Tier Limits
                                        </h4>
                                        <p className="text-muted mb-15">
                                            Limits for users with paid subscription ($19.99)
                                        </p>

                                        <div className="mb-15">
                                            <label className="label fs-14 mb-2">
                                                AI Chat Messages / Month
                                            </label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                value={limitData.paid_ai_chat_messages}
                                                onChange={(e) =>
                                                    updateLimitField('paid_ai_chat_messages', e.target.value)
                                                }
                                                min="0"
                                            />
                                        </div>

                                        <div className="mb-15">
                                            <label className="label fs-14 mb-2">
                                                AI Voice Requests / Month
                                            </label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                value={limitData.paid_ai_voice_requests}
                                                onChange={(e) =>
                                                    updateLimitField('paid_ai_voice_requests', e.target.value)
                                                }
                                                min="0"
                                            />
                                        </div>

                                        <div className="mb-15">
                                            <label className="label fs-14 mb-2">
                                                Community Posts
                                            </label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                value={limitData.paid_community_posts}
                                                onChange={(e) =>
                                                    updateLimitField('paid_community_posts', e.target.value)
                                                }
                                                min="0"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Submit */}
                            <div className="d-flex gap-2 mt-3">
                                <button
                                    type="submit"
                                    className="btn btn-primary fw-normal text-white"
                                    onClick={handleUpdateLimits}
                                >
                                    Save Limits
                                </button>
                            </div>
                        </div>
                    )}

                    {activeTab === 'users' && (
                        <div className=" p-20">
                            {/* Search and Filter */}
                            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-20">
                                <div className="d-flex gap-2">
                                    <input type="text" className="form-control" placeholder="Search users..." defaultValue={search} onChange={(e) => applySearch(e.target.value)} style={{ width: '250px' }}/>
                                    <select className="form-select" value={tierFilter} onChange={(e) => applyTierFilter(e.target.value)} style={{ width: '150px' }}>
                                        <option value="">All Tiers</option>
                                        <option value="free">Free Only</option>
                                        <option value="paid">Paid Only</option>
                                    </select>
                                </div>
                            </div>

                            {/* Users Table */}
                            <div className="default-table-area mx-minus-1 table-to-do-list">
                                <div className="table-responsive">
                                    <table className="table align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Tier</th>
                                                <th>AI Chat</th>
                                                <th>AI Voice</th>
                                                <th>Posts</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {userUsageData.map((user) => (
                                                <tr key={user.id}>
                                                    <td>
                                                        <div>
                                                            <div className="fw-medium">{user.name}</div>
                                                            <div className="text-muted fs-12">{user.email}</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span className={`badge ${user.tier === 'paid' ? 'bg-success' : 'bg-secondary'}`}>
                                                            {user.tier === 'paid' ? 'Paid' : 'Free'}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div className="mb-1">
                                                            <small className="text-muted">
                                                                {user.usage.ai_chat.used} / {user.usage.ai_chat.limit}
                                                            </small>
                                                        </div>
                                                        <div className="progress" style={{ height: '6px' }}>
                                                            <div
                                                                className={`progress-bar ${getUsageBarColor(user.usage.ai_chat.percentage_used)}`}
                                                                style={{ width: `${user.usage.ai_chat.percentage_used}%` }}
                                                            ></div>
                                                        </div>
                                                        <small className="text-muted">
                                                            {user.usage.ai_chat.remaining} remaining
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div className="mb-1">
                                                            <small className="text-muted">
                                                                {user.usage.ai_voice.used} / {user.usage.ai_voice.limit}
                                                            </small>
                                                        </div>
                                                        <div className="progress" style={{ height: '6px' }}>
                                                            <div
                                                                className={`progress-bar ${getUsageBarColor(user.usage.ai_voice.percentage_used)}`}
                                                                style={{ width: `${user.usage.ai_voice.percentage_used}%` }}
                                                            ></div>
                                                        </div>
                                                        <small className="text-muted">
                                                            {user.usage.ai_voice.remaining} remaining
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div className="mb-1">
                                                            <small className="text-muted">
                                                                {user.usage.posts.used} / {user.usage.posts.limit}
                                                            </small>
                                                        </div>
                                                        <div className="progress" style={{ height: '6px' }}>
                                                            <div
                                                                className={`progress-bar ${getUsageBarColor(user.usage.posts.percentage_used)}`}
                                                                style={{ width: `${user.usage.posts.percentage_used}%` }}
                                                            ></div>
                                                        </div>
                                                        <small className="text-muted">
                                                            {user.usage.posts.remaining} remaining
                                                        </small>
                                                    </td>
                                                    <td className='d-flex align-items-center justify-content-end'>
                                                        <div className="d-flex gap-4 text-end">
                                                            <button
                                                                className=""
                                                                onClick={() => {
                                                                    setSelectedUser(user);
                                                                    setShowUserModal(true);
                                                                }}
                                                            >
                                                                <i className="material-symbols-outlined fs-14">visibility</i>
                                                            </button>
                                                            {user.tier === 'free' && (
                                                                <button
                                                                    className=""
                                                                    onClick={() => handleForceUpgrade(user.id)}
                                                                    title="Force upgrade to paid"
                                                                >
                                                                    <i className="material-symbols-outlined fs-14">upgrade</i>
                                                                </button>
                                                            )}
                                                            {user.tier === 'paid' && (
                                                                <button
                                                                    className=""
                                                                    onClick={() => handleForceDowngrade(user.id)}
                                                                    title="Force downgrade to free"
                                                                >
                                                                    <i className="material-symbols-outlined fs-14">downgrade</i>
                                                                </button>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                            {userUsageData.length === 0 && (
                                                <tr>
                                                    <td colSpan={6} className="text-center py-4 text-muted">
                                                        No users found.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Pagination */}
                            {pagination && pagination.last_page > 1 && (
                                <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 pt-20">
                                    <span className="fs-14 text-muted">
                                        Showing {pagination.from ?? 0} - {pagination.to ?? 0} of {pagination.total} users
                                    </span>
                                    <ul className="pagination mb-0">
                                        {pagination.links.map((link, index) => (
                                            <li
                                                key={index}
                                                className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}
                                            >
                                                <button
                                                    className="page-link"
                                                    disabled={!link.url}
                                                    onClick={() => link.url && router.visit(link.url)}
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                />
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            {/* User Detail Modal */}
                            {showUserModal && selectedUser && (
                                <div className="modal fade show" style={{ display: 'block', backgroundColor: 'rgba(0,0,0,0.5)' }}>
                                    <div className="modal-dialog">
                                        <div className="modal-content">
                                            <div className="modal-header">
                                                <h5 className="modal-title">
                                                    User Details - {selectedUser.name}
                                                </h5>
                                                <button
                                                    type="button"
                                                    className="btn-close"
                                                    onClick={() => setShowUserModal(false)}
                                                ></button>
                                            </div>
                                            <div className="modal-body">
                                                <div className="mb-3">
                                                    <strong>Email:</strong> {selectedUser.email}
                                                </div>
                                                <div className="mb-3">
                                                    <strong>Tier:</strong>{' '}
                                                    <span className={`badge ${selectedUser.tier === 'paid' ? 'bg-success' : 'bg-secondary'}`}>
                                                        {selectedUser.tier === 'paid' ? 'Paid' : 'Free'}
                                                    </span>
                                                </div>
                                                {selectedUser.subscription && (
                                                    <div className="mb-3">
                                                        <strong>Subscription Status:</strong>{' '}
                                                        <span className={`badge ${selectedUser.subscription.status === 'active' ? 'bg-success' : 'bg-danger'}`}>
                                                            {selectedUser.subscription.status}
                                                        </span>
                                                        {selectedUser.subscription.expires_at && (
                                                            <div className="mt-1">
                                                                <small className="text-muted">
                                                                    Expires: {formatDate(selectedUser.subscription.expires_at)}
                                                                </small>
                                                            </div>
                                                        )}
                                                        {selectedUser.subscription.free_expires_at && (
                                                            <div>
                                                                <small className="text-muted">
                                                                    Free Trial Expires: {formatDate(selectedUser.subscription.free_expires_at)}
                                                                </small>
                                                            </div>
                                                        )}
                                                    </div>
                                                )}

                                                <h6 className="mt-3">Usage Details</h6>
                                                <div className="row">
                                                    <div className="col-md-4 mb-3">
                                                        <div className="card bg-light">
                                                            <div className="card-body p-2">
                                                                <small className="text-muted">AI Chat</small>
                                                                <div className="fw-bold">{selectedUser.usage.ai_chat.used} / {selectedUser.usage.ai_chat.limit}</div>
                                                                <small className="text-muted">{selectedUser.usage.ai_chat.remaining} remaining</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="col-md-4 mb-3">
                                                        <div className="card bg-light">
                                                            <div className="card-body p-2">
                                                                <small className="text-muted">AI Voice</small>
                                                                <div className="fw-bold">{selectedUser.usage.ai_voice.used} / {selectedUser.usage.ai_voice.limit}</div>
                                                                <small className="text-muted">{selectedUser.usage.ai_voice.remaining} remaining</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="col-md-4 mb-3">
                                                        <div className="card bg-light">
                                                            <div className="card-body p-2">
                                                                <small className="text-muted">Posts</small>
                                                                <div className="fw-bold">{selectedUser.usage.posts.used} / {selectedUser.usage.posts.limit}</div>
                                                                <small className="text-muted">{selectedUser.usage.posts.remaining} remaining</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div className="mt-3">
                                                    <h6>Reset Usage</h6>
                                                    <div className="d-flex gap-2">
                                                        <button
                                                            className="btn btn-sm btn-outline-warning"
                                                            onClick={() => handleResetUsage(selectedUser.id, 'ai_chat')}
                                                        >
                                                            Reset AI Chat
                                                        </button>
                                                        <button
                                                            className="btn btn-sm btn-outline-warning"
                                                            onClick={() => handleResetUsage(selectedUser.id, 'ai_voice')}
                                                        >
                                                            Reset AI Voice
                                                        </button>
                                                        <button
                                                            className="btn btn-sm btn-outline-warning"
                                                            onClick={() => handleResetUsage(selectedUser.id, 'posts')}
                                                        >
                                                            Reset Posts
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="modal-footer">
                                                <button
                                                    className="btn btn-secondary"
                                                    onClick={() => setShowUserModal(false)}
                                                >
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
