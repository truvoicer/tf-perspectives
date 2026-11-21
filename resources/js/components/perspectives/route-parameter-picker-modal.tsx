import { parseRouteParameters, RouteParameter } from '@tf-perspectives/lib/route-parameter-utils';
import React, { useEffect, useMemo, useState } from 'react';
import { Button, Form, Modal } from 'react-bootstrap';

interface RouteParameterPickerModalProps {
    show: boolean;
    onHide: () => void;
    routeDefinition: string | null | undefined;
    /** Called with the fully-formed placeholder, e.g. "{perspective_categories:id}" */
    onInsert: (placeholder: string) => void;
}

const RouteParameterPickerModal: React.FC<RouteParameterPickerModalProps> = ({
    show,
    onHide,
    routeDefinition,
    onInsert,
}) => {
    const parameters = useMemo<RouteParameter[]>(
        () => parseRouteParameters(routeDefinition),
        [routeDefinition],
    );

    const [selectedEntity, setSelectedEntity] = useState<string>('');
    const [column, setColumn] = useState<string>('id');

    // Reset / default selection whenever the modal is opened
    useEffect(() => {
        if (!show) return;
        const first = parameters[0];
        setSelectedEntity(first?.entity ?? '');
        setColumn(first?.column ?? 'id');
    }, [show, parameters]);

    // Keep column in sync with the entity's default when the user switches entity
    const handleEntityChange = (entity: string) => {
        setSelectedEntity(entity);
        const match = parameters.find((p) => p.entity === entity);
        if (match) setColumn(match.column);
    };

    const handleConfirm = () => {
        if (!selectedEntity) return;
        const safeColumn = (column || 'id').trim();
        onInsert(`{${selectedEntity}:${safeColumn}}`);
        onHide();
    };

    return (
        <Modal show={show} onHide={onHide} centered>
            <Modal.Header closeButton>
                <Modal.Title>Insert Route Parameter</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                {parameters.length === 0 ? (
                    <div className="text-muted">
                        This page's route definition has no parameters.
                    </div>
                ) : (
                    <div className="row g-3">
                        {/* Left: entity list */}
                        <div className="col-6">
                            <Form.Label className="fw-semibold">Entity</Form.Label>
                            <div className="list-group">
                                {parameters.map((p) => (
                                    <button
                                        key={p.entity}
                                        type="button"
                                        className={
                                            'list-group-item list-group-item-action' +
                                            (selectedEntity === p.entity ? ' active' : '')
                                        }
                                        onClick={() => handleEntityChange(p.entity)}
                                    >
                                        {p.entity}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Right: column text box */}
                        <div className="col-6">
                            <Form.Label className="fw-semibold">Column</Form.Label>
                            <Form.Control
                                type="text"
                                value={column}
                                onChange={(e) => setColumn(e.target.value)}
                                placeholder="e.g. id"
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        e.preventDefault();
                                        handleConfirm();
                                    }
                                }}
                            />
                            <Form.Text className="text-muted">
                                Will insert{' '}
                                <code>{`{${selectedEntity || '…'}:${column || 'id'}}`}</code>
                            </Form.Text>
                        </div>
                    </div>
                )}
            </Modal.Body>
            <Modal.Footer>
                <Button variant="secondary" onClick={onHide}>
                    Cancel
                </Button>
                <Button
                    variant="primary"
                    onClick={handleConfirm}
                    disabled={!selectedEntity || parameters.length === 0}
                >
                    OK
                </Button>
            </Modal.Footer>
        </Modal>
    );
};

export default RouteParameterPickerModal;
