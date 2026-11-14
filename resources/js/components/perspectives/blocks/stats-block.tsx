// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/stats.tsx
import { faChartLine, faLayerGroup, faShoePrints, faUsers } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

interface StatsBlockProps {
    stats?: {
        total_perspectives: number;
        total_branches: number;
        total_voices: number;
        today: number;
    };
}

export default function StatsBlock({ stats }: StatsBlockProps) {
    return (
        <div className="tf-stats">
            {stats && stats.hasOwnProperty('total_perspectives') && (
                <div className="tf-stat">
                    <FontAwesomeIcon icon={faShoePrints} />
                    <strong>{stats.total_perspectives}</strong>
                    <span>perspectives</span>
                </div>
            )}
            {stats && stats.hasOwnProperty('total_branches') && (
                <div className="tf-stat">
                    <FontAwesomeIcon icon={faLayerGroup} />
                    <strong>{stats.total_branches}</strong>
                    <span>branches</span>
                </div>
            )}
            {stats && stats.hasOwnProperty('total_voices') && (
                <div className="tf-stat">
                    <FontAwesomeIcon icon={faUsers} />
                    <strong>{stats.total_voices}</strong>
                    <span>voices</span>
                </div>
            )}
            {stats && stats.hasOwnProperty('today') && (
                <div className="tf-stat">
                    <FontAwesomeIcon icon={faChartLine} />
                    <strong>{stats.today}</strong>
                    <span>today</span>
                </div>
            )}
        </div>
    );
}
