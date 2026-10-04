// packages/truvoicer/tf-perspectives/resources/js/pages/categories/show.tsx
import { Link } from '@inertiajs/react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faArrowLeft } from '@fortawesome/free-solid-svg-icons';
import Layout from '../../components/Layout';
import { PerspectiveCard } from '../../components/PerspectiveCard';
import Pagination from '../../components/Pagination';
import EmptyState from '../../components/EmptyState';
import { useAuth } from '../../hooks/useAuth';
import { categoryIcon } from '../../lib/icons';
import type { Category, Paginator, Perspective } from '../../types';

interface CategoryShowProps {
    category: Category;
    perspectives: Paginator<Perspective>;
}

export default function CategoryShow({ category, perspectives }: CategoryShowProps) {
    const { user } = useAuth();

    return (
        <Layout
            hero={
                <div
                    className="tf-hero-inner tf-hero-cat"
                    style={{ ['--cat' as any]: category.color }}
                >
                    <div className="tf-cat-hero-icon">
                        <FontAwesomeIcon icon={categoryIcon(category.icon)} />
                    </div>
                    <h1>{category.name}</h1>
                    {category.description && <p>{category.description}</p>}
                </div>
            }
        >
            <Link href="/categories" className="tf-back">
                <FontAwesomeIcon icon={faArrowLeft} className="me-2" />
                All categories
            </Link>

            <div className="tf-panel">
                {perspectives.data.length === 0 ? (
                    <EmptyState
                        icon={categoryIcon(category.icon)}
                        title="Nothing here yet"
                        body="Be the first to offer a perspective in this category."
                    />
                ) : (
                    <div className="tf-feed">
                        {perspectives.data.map((p) => (
                            <PerspectiveCard
                                key={p.id}
                                perspective={p}
                                canInteract={Boolean(user)}
                                truncate
                            />
                        ))}
                    </div>
                )}
                <Pagination paginator={perspectives} />
            </div>
        </Layout>
    );
}
