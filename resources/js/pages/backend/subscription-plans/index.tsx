import { Head, router } from '@inertiajs/react';

interface Plan {
    id: string;
    name: string;
    price: string | null;
    currency: string | null;
    duration: string;
    type: string;
    bonus: string | null;
    features: string[];
    is_active: boolean;
    offering_name: string;
    product_identifier: string | null;
}

interface Props {
    plans: Plan[];
    connectionError: string | null;
}

export default function SubscriptionPlans({ plans, connectionError }: Props) {
    return (
        <>
            <Head title="Subscription Plans" />

            <div className="d-flex justify-content-between align-items-center mt-1 mb-4 flex-wrap gap-2">
                <div>
                    <h3 className="mb-0">Subscription Plans</h3>
                    <p className="fs-16 mb-0">
                        Plans are loaded directly from your RevenueCat
                        offerings.
                    </p>
                </div>
                <button
                    className="btn btn-primary"
                    onClick={() => router.reload()}
                    type="button"
                >
                    <i className="material-symbols-outlined fs-18 me-1 align-middle">
                        refresh
                    </i>
                    Refresh plans
                </button>
            </div>

            {connectionError ? (
                <div className="alert alert-danger" role="alert">
                    {connectionError}
                </div>
            ) : (
                <div className="card rounded-10 mb-4 border border-white bg-white">
                    <div className="d-flex justify-content-between align-items-center flex-wrap gap-2 p-20">
                        <div>
                            <h3 className="mb-1">RevenueCat plans</h3>
                            <span className="text-body">
                                {plans.length} plan
                                {plans.length === 1 ? '' : 's'} found
                            </span>
                        </div>
                    </div>
                    <div className="default-table-area mx-minus-1 table-contact-list">
                        <div className="table-responsive">
                            <table className="table align-middle">
                                <thead>
                                    <tr>
                                        <th className="fw-medium" scope="col">
                                            Plan name
                                        </th>
                                        <th className="fw-medium" scope="col">
                                            Price
                                        </th>
                                        <th className="fw-medium" scope="col">
                                            Duration
                                        </th>
                                        <th className="fw-medium" scope="col">
                                            Type
                                        </th>
                                        <th className="fw-medium" scope="col">
                                            Bonus
                                        </th>
                                        <th className="fw-medium" scope="col">
                                            Features
                                        </th>
                                        <th className="fw-medium" scope="col">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {plans.length ? (
                                        plans.map((plan) => (
                                            <tr key={plan.id}>
                                                <td>
                                                    <div className="fw-medium text-body">
                                                        {plan.name}
                                                    </div>
                                                    <small className="text-muted">
                                                        {plan.product_identifier ||
                                                            plan.offering_name}
                                                    </small>
                                                </td>
                                                <td className="text-body">
                                                    {plan.price
                                                        ? `${plan.currency || ''} ${plan.price}`
                                                        : 'Store price unavailable'}
                                                </td>
                                                <td className="text-body">
                                                    {plan.duration}
                                                </td>
                                                <td className="text-body text-capitalize">
                                                    {plan.type.replaceAll(
                                                        '_',
                                                        ' ',
                                                    )}
                                                </td>
                                                <td className="text-body">
                                                    {plan.bonus || '-'}
                                                </td>
                                                <td>
                                                    {plan.features.length ? (
                                                        <ul className="text-body mb-0 ps-3">
                                                            {plan.features.map(
                                                                (feature) => (
                                                                    <li
                                                                        key={
                                                                            feature
                                                                        }
                                                                    >
                                                                        {
                                                                            feature
                                                                        }
                                                                    </li>
                                                                ),
                                                            )}
                                                        </ul>
                                                    ) : (
                                                        '-'
                                                    )}
                                                </td>
                                                <td>
                                                    <span
                                                        className={
                                                            plan.is_active
                                                                ? 'text-success bg-success bg-opacity-10 fs-15 fw-normal d-inline-block default-badge'
                                                                : 'text-danger bg-danger bg-opacity-10 fs-15 fw-normal d-inline-block default-badge'
                                                        }
                                                    >
                                                        {plan.is_active
                                                            ? 'Active'
                                                            : 'Inactive'}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td
                                                className="py-4 text-center"
                                                colSpan={7}
                                            >
                                                No RevenueCat packages found.
                                                Add packages to an offering in
                                                RevenueCat to show them here.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
