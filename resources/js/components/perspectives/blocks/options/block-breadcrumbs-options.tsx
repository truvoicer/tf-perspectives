import { faLink, faPlus, faTrash, faCode } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { usePage } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';

import {
    BlockOptionsSharedProps,
    Button,
    LoadingSpinner,
    ManagePages,
    ManagePagesProps,
    StylingOptions,
    StylingOptionsProps,
    TextInput,
    useAuthContext,
    useToastContext,
} from '@truvoicer/tru-fetcher-core-ui';

import { AppMiddleware, Middleware } from '@truvoicer/tru-fetcher-core-ui/lib';
import { Page, Pagination } from '@truvoicer/tru-fetcher-core-ui/types';
import { Modal } from 'react-bootstrap';
import { route } from 'ziggy-js';
import { insertAtCursor } from '@tf-perspectives/lib/route-parameter-utils';
import RouteParameterPickerModal from '../../route-parameter-picker-modal';


export interface BlockBreadcrumbsOptionsProps extends BlockOptionsSharedProps {}

export interface BreadcrumbItem {
    id: string;
    label: string;
    page_id?: number | string;
    icon?: any;
}

interface BreadcrumbsBlockData {
    title?: string;
    items?: BreadcrumbItem[];
}

export default function BlockBreadcrumbsOptions({ block, columnBlockDeleteFileRequest, columnBlockFileStoreRequest }: BlockBreadcrumbsOptionsProps) {
    const data = (block?.data as BreadcrumbsBlockData) || ({} as BreadcrumbsBlockData);
    const schema = block?.schema;
    const errors = block?.errors;

    // >>> Route definition for the current block's page.
    // Adjust the path if your API nests it differently (e.g. block?.page?.pageData?.route_definition).
    const routeDefinition: string | null =
        (block as any)?.page?.route_definition ?? (block as any)?.page?.pageData?.route_definition ?? null;

    const [showPageSelectModal, setShowPageSelectModal] = useState(false);
    const [pages, setPages] = useState<Pagination<Page> | null>(null);

    const [activeEditIndex, setActiveEditIndex] = useState<number | null>(null);
    const [selectedPage, setSelectedPage] = useState<Page | null>(null);

    const [items, setItems] = useState<BreadcrumbItem[]>([]);

    const [pageQuery, setPageQuery] = useState<string | null>(null);
    const [pagePageNumber, setPagePageNumber] = useState<number | null>(null);
    const [pagePerPage, setPagePerPage] = useState<number | null>(null);

    // >>> Route-parameter picker state
    const [pickerIndex, setPickerIndex] = useState<number | null>(null);
    const labelRefs = useRef<Array<HTMLInputElement | null>>([]);
    const caretRefs = useRef<Array<{ start: number; end: number }>>([]);

    const auth = useAuthContext();
    const page = usePage();
    const toastProvider = useToastContext();
    const middleware = new AppMiddleware(page);
    middleware.setToastProvider(toastProvider).setAuthProvider(auth);

    useEffect(() => {
        setItems(Array.isArray(data?.items) ? data.items : []);
    }, [data]);

    const addItem = (e: React.MouseEvent<HTMLButtonElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setItems([
            ...items,
            {
                id: Date.now().toString(),
                label: '',
                page_id: undefined,
                icon: null,
            },
        ]);
    };

    const removeItem = (index: number) => {
        const newItems = items.filter((_, i) => i !== index);
        setItems(newItems);
    };

    const updateItem = (index: number, field: keyof BreadcrumbItem, value: any) => {
        const updatedItems = [...items];
        updatedItems[index] = {
            ...updatedItems[index],
            [field]: value,
        };
        setItems(updatedItems);
    };

    // >>> Remember caret for the given item's label input
    const rememberCaret = (index: number) => {
        const el = labelRefs.current[index];
        if (!el) return;
        caretRefs.current[index] = {
            start: el.selectionStart ?? el.value.length,
            end: el.selectionEnd ?? el.value.length,
        };
    };

    // >>> Open picker for a specific item, capturing caret first
    const openRouteParamPicker = (index: number) => {
        rememberCaret(index);
        setPickerIndex(index);
    };

    // >>> Insert placeholder at the saved caret position
    const handleInsertRouteParameter = (index: number, placeholder: string) => {
        const current = items[index]?.label ?? '';
        const caret = caretRefs.current[index] ?? { start: current.length, end: current.length };
        const { value, caret: newCaret } = insertAtCursor(current, caret.start, caret.end, placeholder);

        updateItem(index, 'label', value);

        // Restore focus + caret after React re-renders
        requestAnimationFrame(() => {
            const el = labelRefs.current[index];
            if (!el) return;
            el.focus();
            el.setSelectionRange(newCaret, newCaret);
            caretRefs.current[index] = { start: newCaret, end: newCaret };
        });
    };

    function customizeIdsRowData(item: Page) {
        const cloneItem = { ...item };

        if (activeEditIndex !== null && items[activeEditIndex]?.page_id) {
            cloneItem['selected'] = cloneItem['id'].toString() === items[activeEditIndex].page_id?.toString();
        }

        return cloneItem;
    }

    function buildManagePagesProps(pageData: Pagination<Page>) {
        const defaultProps: ManagePagesProps = {
            pages: pageData,
            paginationProps: {
                pagination_type: 'state',
                onPageChange: (page: number) => {
                    fetchData();
                },
            },
            toolbarCallbacks: {
                onSearchSubmit: (query: string) => {
                    setPageQuery(query);
                    setPagePageNumber(1);
                },
                onPerPageChange: (perPage: number) => {
                    setPagePerPage(perPage);
                    setPagePageNumber(1);
                },
                onBulkActionChange: () => {},
            },
            refreshCallback: () => {
                fetchData();
            },
        };

        defaultProps['refreshCallback'] = () => {
            fetchData();
        };

        defaultProps['rowDataCallback'] = customizeIdsRowData;

        defaultProps['selection'] = {
            multiple: false,
            onSelected: (selected: Page[]) => {
                if (!Array.isArray(selected) || selected.length === 0) return;
                setSelectedPage(selected[0]);
            },
        };

        return defaultProps;
    }

    const openPageSelector = (index: number) => {
        setActiveEditIndex(index);
        setSelectedPage(null);
        setShowPageSelectModal(true);
        fetchData();
    };

    const savePageSelection = () => {
        if (activeEditIndex !== null && selectedPage) {
            updateItem(activeEditIndex, 'page_id', selectedPage.id);
            if (!items[activeEditIndex].label) {
                updateItem(activeEditIndex, 'label', selectedPage.title);
            }
        }
        setShowPageSelectModal(false);
        setActiveEditIndex(null);
    };

    const clearItems = (e: React.MouseEvent<HTMLButtonElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setItems([]);
    };

    const fetchData = useCallback(
        async (query?: { page?: number; query?: string; per_page?: number }) => {
            try {
                const response = await AppMiddleware.instance(page)
                    .setToastProvider(toastProvider)
                    .setAuthProvider(auth)
                    .makeRequest({
                        method: Middleware.REQUEST.GET,
                        url: route('admin.page.data.index'),
                        protectedReq: true,
                        ...(typeof query === 'object' && query !== null ? { query } : {}),
                    });
                if (response) {
                    setPages(response);
                } else {
                    console.warn(`No data found for menu items.`);
                }
            } catch (error) {
                console.error('Error fetching menu items:', error);
                throw error;
            }
        },
        [page, auth, toastProvider],
    );

    useEffect(() => {
        if (pageQuery === null && pagePerPage === null && pagePageNumber === null) {
            return;
        }
        fetchData({
            ...(pageQuery || pageQuery === '' ? { query: pageQuery } : {}),
            ...(pagePerPage ? { per_page: pagePerPage } : {}),
            ...(pagePageNumber ? { page: pagePageNumber } : {}),
        });
    }, [pageQuery, pagePerPage, pagePageNumber, fetchData]);

    return (
        <div className="row align-items-center">
            <div className="col-12">
                <div className="card">
                    <div className="card-header">Fetch Options</div>
                    <div className="card-body">
                        <div className="row align-items-center">
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput name="title" label="Block Title (optional)" defaultValue={data?.title || ''} />
                            </div>
                        </div>
                    </div>

                    <div className="card-header d-flex justify-content-between align-items-center">
                        <div className="d-flex align-items-center">
                            <span>Breadcrumbs</span>
                        </div>
                        <div className="d-flex align-items-center gap-2">
                            <Button onClick={clearItems} variant="primary" size="sm">
                                <FontAwesomeIcon icon={faPlus} className="me-2" /> Clear Items
                            </Button>
                            <Button onClick={addItem} variant="primary" size="sm">
                                <FontAwesomeIcon icon={faPlus} className="me-2" /> Add Item
                            </Button>
                        </div>
                    </div>
                    <div className="card-body">
                        <input type="hidden" name="items" value={JSON.stringify(items)} />

                        {items.length === 0 && <p className="mb-0 text-muted">No items added yet. Click "Add Item" to start.</p>}

                        {items.map((item, index) => (
                            <div key={item.id || index} className="bg-light mb-3 rounded border p-3">
                                <div className="d-flex justify-content-between align-items-center mb-3">
                                    <h6 className="fw-bold mb-0">Item {index + 1}</h6>
                                    <Button onClick={() => removeItem(index)} variant="danger" size="sm">
                                        <FontAwesomeIcon icon={faTrash} />
                                    </Button>
                                </div>

                                <div className="row">
                                    <div className="col-md-4 col-12 mb-3">
                                        {/* >>> Route parameter trigger ABOVE the label field */}
                                        <div className="d-flex justify-content-end mb-1">
                                            <Button
                                                type="button"
                                                variant="primary"
                                                size="sm"
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    e.stopPropagation();
                                                    openRouteParamPicker(index);
                                                }}
                                                disabled={!routeDefinition}
                                                title={
                                                    routeDefinition
                                                        ? 'Insert a route parameter at the cursor'
                                                        : 'This page has no route parameters'
                                                }
                                            >
                                                Insert Parameter
                                            </Button>
                                        </div>

                                        <TextInput
                                            label="Label"
                                            defaultValue={item.label}
                                            onChange={(e) => updateItem(index, 'label', e.target.value)}
                                            placeholder="e.g. Home or Category {perspective_categories:id}"
                                            inputRef={(el: HTMLInputElement | null) => {
                                                labelRefs.current[index] = el;
                                            }}
                                            onSelect={() => rememberCaret(index)}
                                            onKeyUp={() => rememberCaret(index)}
                                            onClick={() => rememberCaret(index)}
                                            onBlur={() => rememberCaret(index)}
                                        />
                                    </div>

                                    <div className="col-md-4 col-12 mb-3">
                                        <div className="mb-1">
                                            <label className="form-label">Linked Page</label>
                                            <Button
                                                type="button"
                                                variant={item.page_id ? 'primary' : 'danger'}
                                                border={true}
                                                className="d-flex justify-content-between align-items-center w-100"
                                                onClick={() => openPageSelector(index)}
                                            >
                                                <span>{item.page_id ? `Page ID: ${item.page_id}` : 'Select a Page'}</span>
                                                <FontAwesomeIcon icon={faLink} />
                                            </Button>
                                        </div>
                                    </div>

                                    <div className="col-md-4 col-12 mb-3">
                                        <TextInput
                                            label="Icon (FontAwesome class)"
                                            defaultValue={item.icon}
                                            onChange={(e) => updateItem(index, 'icon', e.target.value)}
                                            placeholder="e.g. fa-home"
                                        />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="card-header">Styling</div>
                    <div className="card-body">
                        <div className="row align-items-center">
                            <StylingOptions
                                defaultValues={{
                                    background_color: '#ffffff',
                                    border_color: '#dee2e6',
                                    border_width: 1,
                                    border_style: 'solid',
                                    border_radius: '0px',
                                    box_shadow: 'none',
                                    padding: '15px',
                                    margin: '0px',
                                }}
                                enableThemeFields={true}
                                schema={schema}
                                data={data as StylingOptionsProps['data']}
                                errors={errors}
                                collapsible={true}
                                defaultExpanded={true}
                                title="Design & Styling"
                                showBackground={true}
                                showBorder={true}
                                showShadow={true}
                                showSpacing={true}
                            />
                        </div>
                    </div>
                </div>

                <Modal show={showPageSelectModal} onHide={() => setShowPageSelectModal(false)} size="lg" className="modal-full-form">
                    <Modal.Header closeButton>
                        <Modal.Title>Select Page for Breadcrumb Item</Modal.Title>
                    </Modal.Header>
                    <Modal.Body>{pages ? <ManagePages {...buildManagePagesProps(pages)} /> : <LoadingSpinner />}</Modal.Body>
                    <Modal.Footer>
                        <Button type="button" variant="danger" onClick={() => setShowPageSelectModal(false)}>
                            Cancel
                        </Button>
                        <Button type="button" variant="primary" onClick={savePageSelection} disabled={!selectedPage}>
                            Confirm Selection
                        </Button>
                    </Modal.Footer>
                </Modal>

                {/* >>> Route parameter picker modal — single instance, driven by pickerIndex */}
                <RouteParameterPickerModal
                    show={pickerIndex !== null}
                    onHide={() => setPickerIndex(null)}
                    routeDefinition={routeDefinition}
                    onInsert={(placeholder) => {
                        if (pickerIndex === null) return;
                        handleInsertRouteParameter(pickerIndex, placeholder);
                    }}
                />
            </div>
        </div>
    );
}
