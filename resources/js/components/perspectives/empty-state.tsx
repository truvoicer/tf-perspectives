// packages/truvoicer/tf-perspectives/resources/js/components/EmptyState.tsx
import type { ReactNode } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core';

interface EmptyStateProps {
    icon: IconDefinition;
    title: string;
    body?: string;
    action?: ReactNode;
}

export default function EmptyState({ icon, title, body, action }: EmptyStateProps) {
    return (
        <div className="tf-empty">
            <div className="tf-empty-icon">
                <FontAwesomeIcon icon={icon} />
            </div>
            <h3>{title}</h3>
            {body && <p>{body}</p>}
            {action}
        </div>
    );
}
