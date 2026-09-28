import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface User {
    id: number;
    name?: string;
    first_name?: string;
    last_name?: string;
    email?: string;
    avatar?: string | null;
}

interface Subscription {
    id: number;
    user_id: number;
    store: string | null;
    product_id: string | null;
    entitlement: string | null;
    transaction_id: string | null;
    original_transaction_id: string | null;
    status: string | null;
    purchase_date: string | null;
    expires_at: string | null;
    will_renew: boolean;
    created_at: string;
    user?: User;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedSubscriptions {
    data: Subscription[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface Props {
    subscriptions: PaginatedSubscriptions;
    search?: string;
    connectionError?: string | null;
}

export default function SubscriptionPlans({
    subscriptions,
    search = '',
    connectionError = null,
}: Props) {
    const [searchValue, setSearchValue] = useState(search);

    const applySearch = () => {
        router.get(
            '/admin/subscriptions',
            {
                search: searchValue || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };

    const resetSearch = () => {
        setSearchValue('');

        router.get(
            '/admin/subscriptions',
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };

    const getUserName = (user?: User) => {
        if (!user) {
            return 'Unknown User';
        }

        if (user.first_name || user.last_name) {
            return `${user.first_name ?? ''} ${user.last_name ?? ''}`.trim();
        }

        return user.name || 'Unknown User';
    };

    const formatDate = (date: string | null) => {
        if (!date) {
            return '-';
        }

        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    const getStatusClass = (status: string | null) => {
        switch (status?.toLowerCase()) {
            case 'active':
                return 'bg-success';

            case 'expired':
            case 'cancelled':
            case 'canceled':
                return 'bg-danger';

            case 'pending':
                return 'bg-warning text-dark';

            default:
                return 'bg-secondary';
        }
    };

    const getStoreLabel = (store: string | null) => {
        if (!store) {
            return '-';
        }

        switch (store.toLowerCase()) {
            case 'app_store':
            case 'appstore':
                return 'App Store';

            case 'play_store':
            case 'google_play':
                return 'Google Play';

            default:
                return store;
        }
    };

    return (
        <>
            <Head title="Subscriptions" />

            <div
                className="main-content-container overflow-hidden"
                style={{ minHeight: '75vh' }}
            >
                <div className="row">
                    <div className="col-md-12">
                        <div className="card bg-white rounded-10 border border-white mb-4">

                            {/* Header */}
                            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 p-20">

                                <div>
                                    <h3 className="mb-1 fs-18 fw-semibold">
                                        Subscriptions
                                    </h3>

                                    <p className="text-muted mb-0 fs-13">
                                        Manage user subscriptions and billing
                                    </p>
                                </div>

                                {/* Search */}
                                <div className="d-flex align-items-center gap-2 flex-wrap">

                                    <input
                                        type="text"
                                        className="form-control"
                                        placeholder="Search user, product, transaction..."
                                        value={searchValue}
                                        onChange={(e) =>
                                            setSearchValue(e.target.value)
                                        }
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter') {
                                                applySearch();
                                            }
                                        }}
                                        style={{ width: '300px' }}
                                    />

                                    <button
                                        type="button"
                                        className="btn btn-primary text-white"
                                        onClick={applySearch}
                                    >
                                        Search
                                    </button>

                                    {searchValue && (
                                        <button
                                            type="button"
                                            className="btn btn-secondary"
                                            onClick={resetSearch}
                                        >
                                            Reset
                                        </button>
                                    )}
                                </div>
                            </div>

                            {connectionError && (
                                <div className="px-20 pb-3">
                                    <div className="alert alert-danger mb-0">
                                        {connectionError}
                                    </div>
                                </div>
                            )}

                            {/* Table */}
                            <div className="default-table-area mx-minus-1 table-to-do-list">
                                <div className="table-responsive">
                                    <table className="table align-middle w-100">

                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Store</th>
                                                <th>Product</th>
                                                <th>Entitlement</th>
                                                <th>Status</th>
                                                <th>Purchase Date</th>
                                                <th>Expires</th>
                                                <th>Renew</th>
                                                {/* <th className="text-end">
                                                    Action
                                                </th> */}
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {subscriptions.data.length > 0 ? (
                                                subscriptions.data.map(
                                                    (subscription) => (
                                                        <tr key={subscription.id}>

                                                            {/* User */}
                                                            <td>
                                                                <div className="d-flex align-items-center">
                                                                    <div className="flex-shrink-0">
                                                                        <img
                                                                            src={
                                                                                subscription.user?.avatar
                                                                                    ? `/${subscription.user.avatar}`
                                                                                    : '/backend/assets/images/placholder.png'
                                                                            }
                                                                            alt="user"
                                                                            className="rounded-circle"
                                                                            style={{
                                                                                width: '38px',
                                                                                height: '38px',
                                                                                objectFit: 'cover',
                                                                            }}
                                                                        />
                                                                    </div>

                                                                    <div className="flex-grow-1 ms-12">
                                                                        <h4 className="fw-medium fs-14 mb-0">
                                                                            {getUserName(
                                                                                subscription.user
                                                                            )}
                                                                        </h4>

                                                                        <span className="text-muted fs-12">
                                                                            {subscription.user?.email ||
                                                                                `User #${subscription.user_id}`}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>

                                                            {/* Store */}
                                                            <td>
                                                                <span className="badge bg-light text-dark">
                                                                    {getStoreLabel(
                                                                        subscription.store
                                                                    )}
                                                                </span>
                                                            </td>

                                                            {/* Product */}
                                                            <td>
                                                                <span className="fw-medium">
                                                                    {subscription.product_id || '-'}
                                                                </span>
                                                            </td>

                                                            {/* Entitlement */}
                                                            <td>
                                                                {subscription.entitlement || '-'}
                                                            </td>

                                                            {/* Status */}
                                                            <td>
                                                                <span
                                                                    className={`badge ${getStatusClass(
                                                                        subscription.status
                                                                    )}`}
                                                                >
                                                                    {subscription.status
                                                                        ? subscription.status
                                                                            .charAt(0)
                                                                            .toUpperCase() +
                                                                          subscription.status.slice(1)
                                                                        : 'Unknown'}
                                                                </span>
                                                            </td>

                                                            {/* Purchase Date */}
                                                            <td>
                                                                {formatDate(
                                                                    subscription.purchase_date
                                                                )}
                                                            </td>

                                                            {/* Expiry */}
                                                            <td>
                                                                {formatDate(
                                                                    subscription.expires_at
                                                                )}
                                                            </td>

                                                            {/* Renew */}
                                                            <td>
                                                                {subscription.will_renew ? (
                                                                    <span className="badge bg-success">
                                                                        Yes
                                                                    </span>
                                                                ) : (
                                                                    <span className="badge bg-secondary">
                                                                        No
                                                                    </span>
                                                                )}
                                                            </td>

                                                            {/* Action */}
                                                            {/* <td>
                                                                <div
                                                                    className="d-flex justify-content-end"
                                                                    style={{
                                                                        gap: '12px',
                                                                    }}
                                                                >
                                                                    <Link
                                                                        href={`/admin/subscriptions/${subscription.id}`}
                                                                        className="bg-transparent p-0 border-0 hover-text-success"
                                                                        title="View"
                                                                    >
                                                                        <i className="material-symbols-outlined fs-16 fw-normal text-body">
                                                                            visibility
                                                                        </i>
                                                                    </Link>
                                                                </div>
                                                            </td> */}

                                                        </tr>
                                                    )
                                                )
                                            ) : (
                                                <tr>
                                                    <td
                                                        colSpan={9}
                                                        className="text-center py-5"
                                                    >
                                                        <div className="text-muted">
                                                            No subscriptions found
                                                        </div>
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>

                                {/* Pagination */}
                                <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 pt-20 p-20">

                                    <span className="fs-14 text-muted">
                                        Showing{' '}
                                        {subscriptions.from ?? 0}
                                        {' - '}
                                        {subscriptions.to ?? 0}
                                        {' of '}
                                        {subscriptions.total}
                                        {' subscriptions'}
                                    </span>

                                    <ul className="pagination mb-0">

                                        {subscriptions.links.map(
                                            (link, index) => (
                                                <li
                                                    key={index}
                                                    className={`page-item ${
                                                        link.active
                                                            ? 'active'
                                                            : ''
                                                    } ${
                                                        !link.url
                                                            ? 'disabled'
                                                            : ''
                                                    }`}
                                                >
                                                    <button
                                                        type="button"
                                                        className="page-link"
                                                        disabled={!link.url}
                                                        onClick={() => {
                                                            if (link.url) {
                                                                router.visit(
                                                                    link.url,
                                                                    {
                                                                        preserveState:
                                                                            true,
                                                                        preserveScroll:
                                                                            true,
                                                                    }
                                                                );
                                                            }
                                                        }}
                                                        dangerouslySetInnerHTML={{
                                                            __html: link.label,
                                                        }}
                                                    />
                                                </li>
                                            )
                                        )}

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