// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/blocks/categories-block.tsx
import { faLayerGroup } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { Link, router, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { Col, Row } from 'react-bootstrap';
import { route } from 'ziggy-js';
import { categoryIcon } from '../../../lib/icons';
import type { Category } from '../../../types/perspectives';

export default function CategoriesBlock() {
    const { categories } = usePage().props as unknown as { categories: Category[] };
    useEffect(() => {
        router.reload({ only: ['categories'] });
    }, []);
    return (
        <div className="tf-panel">
            <h1 className="tf-page-title">
                <FontAwesomeIcon icon={faLayerGroup} className="me-2" />
                Categories
            </h1>
            <p className="mb-4 text-muted">Every perspective lives somewhere. Choose a lens.</p>

            <Row className="g-3">
                {Array.isArray(categories) &&
                    categories.map((c) => (
                        <Col key={c.id} md={6} lg={4}>
                            <Link
                                href={route('perspectives.categories.show', { slug: c.slug })}
                                className="tf-cat-card"
                                style={{ ['--cat' as any]: c.color }}
                            >
                                <div className="tf-cat-card-icon">
                                    <FontAwesomeIcon icon={categoryIcon(c.icon)} />
                                </div>
                                <div className="tf-cat-card-body">
                                    <h3>{c.name}</h3>
                                    {c.description && <p>{c.description}</p>}
                                    <small>
                                        {c.perspectives_count ?? 0} perspective
                                        {(c.perspectives_count ?? 0) === 1 ? '' : 's'}
                                    </small>
                                </div>
                            </Link>
                        </Col>
                    ))}
            </Row>
        </div>
    );
}
