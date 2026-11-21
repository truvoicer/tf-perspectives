// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/breadcrumbs.tsx
import { Link, usePage } from '@inertiajs/react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faChevronRight, faHouse } from '@fortawesome/free-solid-svg-icons';
import { Fragment } from 'react';
import { route } from 'ziggy-js';

export interface BreadcrumbItem {
    label: string;
    href?: string;
    icon?: any;
}

interface BreadcrumbsProps {
    items: BreadcrumbItem[];
    /** Show a home icon as the first item. Defaults to true. */
    showHome?: boolean;
    /** Optional class name for the container. */
    className?: string;
}

export default function BreadcrumbsBlock() {
    const { items, showHome = true, className = '' } = usePage<{ items: BreadcrumbItem[]; showHome?: boolean; className?: string }>().props;

    return (
        <nav aria-label="breadcrumb" className={`mb-3 ${className}`}>
            {/* Removed .breadcrumb here to avoid Bootstrap's ::before pseudo-elements */}
            <ol className="list-unstyled d-flex flex-wrap align-items-center mb-0" style={{ fontSize: '14px' }}>
                {Array.isArray(items) && items.map((item, index) => {
                    const isLast = index === items.length - 1;

                    return (
                        <Fragment key={index}>
                            <li className="d-flex align-items-center">
                                {item.href && !isLast ? (
                                    <Link
                                        href={item.href}
                                        className="text-decoration-none text-muted d-inline-flex align-items-center gap-2"
                                    >
                                        {item.icon && <FontAwesomeIcon icon={item.icon} />}
                                        <span>{item.label}</span>
                                    </Link>
                                ) : (
                                    <span
                                        className={`d-inline-flex align-items-center gap-2 ${
                                            isLast ? 'fw-semibold text-dark' : 'text-muted'
                                        }`}
                                    >
                                        {item.icon && <FontAwesomeIcon icon={item.icon} />}
                                        <span>{item.label}</span>
                                    </span>
                                )}
                            </li>
                            {!isLast && (
                                <li className="d-flex align-items-center text-muted" style={{ padding: '0 8px' }}>
                                    <FontAwesomeIcon icon={faChevronRight} size="xs" style={{ opacity: 0.5 }} />
                                </li>
                            )}
                        </Fragment>
                    );
                })}
            </ol>
        </nav>
    );
}
