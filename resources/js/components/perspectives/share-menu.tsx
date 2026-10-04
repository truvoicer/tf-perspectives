// packages/truvoicer/tf-perspectives/resources/js/components/ShareMenu.tsx
import { Dropdown } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faLink, faShareNodes, faXTwitter, faFacebook,
} from '@fortawesome/free-solid-svg-icons';
import { useClipboard } from '../hooks/useClipboard';

interface ShareMenuProps {
    url: string;
    title: string;
}

export function ShareMenu({ url, title }: ShareMenuProps) {
    const { copy } = useClipboard();

    const encodedUrl = encodeURIComponent(url);
    const encodedTitle = encodeURIComponent(title);

    return (
        <Dropdown align="end">
            <Dropdown.Toggle as="button" className="tf-icon-btn-sm" title="Share">
                <FontAwesomeIcon icon={faShareNodes} />
            </Dropdown.Toggle>
            <Dropdown.Menu className="tf-dropdown">
                <Dropdown.Item onClick={() => copy(url)}>
                    <FontAwesomeIcon icon={faLink} className="me-2" />
                    Copy link
                </Dropdown.Item>
                <Dropdown.Item
                    as="a"
                    href={`https://twitter.com/intent/tweet?text=${encodedTitle}&url=${encodedUrl}`}
                    target="_blank"
                    rel="noreferrer"
                >
                    <FontAwesomeIcon icon={faXTwitter} className="me-2" />
                    Share on X
                </Dropdown.Item>
                <Dropdown.Item
                    as="a"
                    href={`https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`}
                    target="_blank"
                    rel="noreferrer"
                >
                    <FontAwesomeIcon icon={faFacebook} className="me-2" />
                    Share on Facebook
                </Dropdown.Item>
            </Dropdown.Menu>
        </Dropdown>
    );
}
