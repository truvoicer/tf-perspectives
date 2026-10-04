// packages/truvoicer/tf-perspectives/resources/js/pages/categories/index.tsx
import { Link } from '@inertiajs/react';
import { Row, Col, Card } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faLayerGroup } from '@fortawesome/free-solid-svg-icons';
import Layout from '../../components/Layout';
import { categoryIcon } from '../../lib/icons';
import type { Category } from '../../types';

interface CategoriesIndexProps {
    categories: Category[];
}

export default function CategoriesIndex({ categories }: CategoriesIndexProps) {
    return (
        <Layout>
            <div className="tf-panel">
                <h1 className="tf-page-title">
                    <FontAwesomeIcon icon={faLayerGroup} className="me-2" />
                    Categories
                </h1>
                <p className="text-muted mb-4">
                    Every perspective lives somewhere. Choose a lens.
                </p>

                <Row className="g-3">
                    {categories.map((c) => (
                        <Col key={c.id} md={6} lg={4}>
                            <Link
                                href={`/categories/${c.slug}`}
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
        </Layout>
    );
}
