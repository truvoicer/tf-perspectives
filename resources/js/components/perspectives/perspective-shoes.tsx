// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/perspective-shoes.tsx
import { useState } from 'react';
import { Button } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faShoePrints, faPlus } from '@fortawesome/free-solid-svg-icons';
import { useAuth } from '@truvoicer/tru-fetcher-core-ui';
import type { Perspective, PerspectiveShoe } from '../../types/perspectives';
import { ShoeCard } from './shoe-card';
import { ShoeComposer } from './shoe-composer';

interface Props {
    perspective: Perspective;
    shoes?: PerspectiveShoe[];
    onClose?: () => void;
}

export function PerspectiveShoes({ perspective, shoes = [], onClose }: Props) {
    const { user } = useAuth();
    const [isAdding, setIsAdding] = useState(false);

    return (
        <div className="tf-perspective-shoes p-3">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h3 className="tf-page-title m-0" style={{ fontSize: '1.25rem' }}>
                    <FontAwesomeIcon icon={faShoePrints} className="me-2 text-primary" />
                    Other Shoes
                </h3>
                {onClose && (
                    <button className="btn-close" onClick={onClose} aria-label="Close" />
                )}
            </div>

            {shoes.length === 0 && !isAdding && (
                <div className="text-center py-5 text-muted">
                    <p>No one has stepped into these shoes yet.</p>
                    {user && (
                        <Button variant="primary" onClick={() => setIsAdding(true)}>
                            Be the first
                        </Button>
                    )}
                </div>
            )}

            <div className="d-flex flex-column gap-3">
                {shoes.map((shoe) => (
                    <ShoeCard key={shoe.id} shoe={shoe} />
                ))}
            </div>

            {user && !isAdding && shoes.length > 0 && (
                <div className="mt-4 text-center">
                    <Button variant="outline-primary" onClick={() => setIsAdding(true)}>
                        <FontAwesomeIcon icon={faPlus} className="me-2" />
                        Step into these shoes
                    </Button>
                </div>
            )}

            {isAdding && (
                <div className="mt-4 p-3 bg-light rounded tf-panel">
                    <ShoeComposer
                        perspective={perspective}
                        onCancel={() => setIsAdding(false)}
                        onSuccess={() => setIsAdding(false)}
                    />
                </div>
            )}
        </div>
    );
}
