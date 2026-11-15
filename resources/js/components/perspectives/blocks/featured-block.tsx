// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/blocks/featured-block.tsx
import { faStar } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { Link, usePage } from '@inertiajs/react';
import type { Paginator, Perspective } from '../../../types/perspectives';

interface FeaturedProps {
    featured?: Perspective[];
}

export default function FeaturedBlock() {
    const { featured } = usePage<{ featured?: Paginator<Perspective> }>().props;

    return (
        <div>
            {Array.isArray(featured?.data) && featured.data.length > 0 && (
                <div className="tf-panel tf-panel-side">
                    <h3 className="tf-side-title">
                        <FontAwesomeIcon icon={faStar} className="me-2" />
                        Featured this week
                    </h3>
                    <div className="tf-side-list">
                        {featured.data.map((p) => (
                            <Link key={p.id} href={`/perspectives/${p.id}`} className="tf-side-item">
                                <span className="tf-avatar tf-avatar-sm">{(p.author?.full_name ?? '?').charAt(0).toUpperCase()}</span>
                                <div>
                                    <strong>{p.voice ?? p.author?.full_name}</strong>
                                    <p>{p.body.slice(0, 90)}…</p>
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
