// packages/truvoicer/tf-perspectives/resources/js/pages/bookmarks.tsx
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faBookmark } from '@fortawesome/free-solid-svg-icons';
import Layout from '../components/Layout';
import { PerspectiveCard } from '../components/PerspectiveCard';
import Pagination from '../components/Pagination';
import EmptyState from '../components/EmptyState';
import { useAuth } from '../hooks/useAuth';
import type { Paginator, Perspective } from '../types';

interface BookmarksProps {
    perspectives: Paginator<Perspective>;
}

export default function Bookmarks({ perspectives }: BookmarksProps) {
    const { user } = useAuth();

    return (
        <Layout>
            <h1 className="tf-page-title mb-4">
                <FontAwesomeIcon icon={faBookmark} className="me-2" />
                Bookmarks
            </h1>

            <div className="tf-panel">
                {perspectives.data.length === 0 ? (
                    <EmptyState
                        icon={faBookmark}
                        title="No bookmarks yet"
                        body="Save perspectives to revisit their branches later."
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
