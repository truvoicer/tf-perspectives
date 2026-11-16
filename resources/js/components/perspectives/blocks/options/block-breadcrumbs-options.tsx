import { faPlus, faTrash } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

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
import { Pagination, Page } from '@truvoicer/tru-fetcher-core-ui/types';
import { Modal } from 'react-bootstrap';
import { route } from 'ziggy-js';

export interface BlockBreadcrumbsOptionsProps extends BlockOptionsSharedProps {}

export interface BreadcrumbItem {
    id: string;
    label: string;
    href?: string;
    icon?: any;
}

interface BreadcrumbsBlockData {
    title?: string;
    items?: BreadcrumbItem[];
    // ... styling props
}

export default function BlockBreadcrumbsOptions({ block, columnBlockDeleteFileRequest, columnBlockFileStoreRequest }: BlockBreadcrumbsOptionsProps) {
    const data = (block?.data as BreadcrumbsBlockData) || ({} as BreadcrumbsBlockData);
    const schema = block?.schema;
    const errors = block?.errors;

    const [showSearchResultsPageSelectModal, setShowSearchResultsPageSelectModal] = useState(false);
    const [selectedSearchResultsPage, setSelectedSearchResultsPage] = useState<Page | null>(null);
    const [searchResultsPageToSave, setSearchResultsPageToSave] = useState<Page | null>(null);

    const [selectedPageType, setSelectedPageType] = useState<
        'feed' | 'categories' | 'category_thread' | 'thread' | 'search' | 'search_results' | null
    >(null);

    const [pages, setPages] = useState<Pagination<Page> | null>(null);

    // Initialize state from existing data, fallback to empty array
    const [items, setItems] = useState<BreadcrumbItem[]>([]);

    const auth = useAuthContext();
    const page = usePage();
    const toastProvider = useToastContext();
    const middleware = new AppMiddleware(page);
    middleware.setToastProvider(toastProvider).setAuthProvider(auth);


    const addItem = (e: React.MouseEvent<HTMLButtonElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setItems([
            ...items,
            {
                id: Date.now().toString(),
                label: '',
                href: '',
                icon: null,
            },
        ]);
    };

    const removeItem = (index: number) => {
        const newItems = items.filter((_, i) => i !== index);
        setItems(newItems);
    };

    const updateItem = (index: number, field: keyof BreadcrumbItem, value: string) => {
        const updatedItems = [...items];
        updatedItems[index] = {
            ...updatedItems[index],
            [field]: value,
        };
        setItems(updatedItems);
    };

    async function fetchData({ query }: { query?: { page?: number } }) {
        try {
            const response = await middleware.makeRequest({
                method: Middleware.REQUEST.GET,
                url: route('admin.page.data.index'),
                protectedReq: true,
                query: typeof query === 'object' && query !== null ? query : {},
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
    }
    function customizeIdsRowData(item: Page) {
        switch (selectedPageType) {
            case 'search_results':
                if (!selectedSearchResultsPage) {
                    return item;
                }
                const cloneItemSearchResults = { ...item };
                cloneItemSearchResults['selected'] = cloneItemSearchResults['id'] === selectedSearchResultsPage?.id;
                return cloneItemSearchResults;
            default:
                return item;
        }
    }
    function buildManagePagesProps(pageData: Pagination<Page>) {
        const defaultProps: ManagePagesProps = {
            pages: pageData,
            paginationProps: {
                pagination_type: 'state',
                onPageChange: (page: number) => {
                    fetchData({
                        query: { page },
                    });
                },
            },
        };
        defaultProps['refreshCallback'] = () => {
            fetchData({});
        };
        defaultProps['rowDataCallback'] = customizeIdsRowData;
        defaultProps['selection'] = {
            multiple: false,
            onSelected: (selected: Page[]) => {
                if (!Array.isArray(selected)) {
                    return;
                }
                if (selected.length === 0) {
                    return;
                }

                switch (selectedPageType) {
                    case 'search_results':
                        setSelectedSearchResultsPage(selected[0]);
                        break;
                }
            },
        };
        return defaultProps;
    }

    useEffect(() => {
        setItems(Array.isArray(data?.items) ? data.items : []);
    }, [data]);

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
                        <span>Breadcrumbs</span>
                        <Button onClick={addItem} variant="primary" size="sm">
                            <FontAwesomeIcon icon={faPlus} className="me-2" /> Add Item
                        </Button>
                    </div>
                    <div className="card-body">
                        {/* Hidden input to serialize the array for the backend */}
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
                                    <div className="col-md-6 col-12 mb-3">
                                        <TextInput
                                            label="Label"
                                            defaultValue={item.label}
                                            onChange={(e) => updateItem(index, 'label', e.target.value)}
                                            placeholder="e.g. Home"
                                        />
                                    </div>
                                    <div className="col-md-6 col-12 mb-3">
                                        <div className="mb-3">
                                            <p>Selected Search Results Page: {searchResultsPageToSave ? searchResultsPageToSave?.title : 'None'}</p>
                                        </div>
                                        <Button
                                            type="button"
                                            onClick={() => {
                                                setSelectedPageType('search_results');
                                                setShowSearchResultsPageSelectModal(true);
                                                fetchData({ query: {} });
                                            }}
                                        >
                                            Select Search Results Page
                                        </Button>
                                        {searchResultsPageToSave && (
                                            <input type="hidden" name="search_results_page" value={searchResultsPageToSave.id} />
                                        )}
                                    </div>
                                    <div className="col-md-6 col-12 mb-3">
                                        <TextInput
                                            label="Icon"
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

                <Modal
                    show={showSearchResultsPageSelectModal}
                    onHide={() => setShowSearchResultsPageSelectModal(false)}
                    size="lg"
                    className="modal-full-form"
                >
                    <Modal.Header closeButton>
                        <Modal.Title>Select Search Results Page</Modal.Title>
                    </Modal.Header>
                    <Modal.Body>{pages ? <ManagePages {...buildManagePagesProps(pages)} /> : <LoadingSpinner />}</Modal.Body>
                    <Modal.Footer>
                        <Button
                            type="button"
                            onClick={() => {
                                setSelectedSearchResultsPage(null);
                                setShowSearchResultsPageSelectModal(false);
                            }}
                        >
                            Close
                        </Button>
                        <Button
                            type="button"
                            onClick={() => {
                                if (selectedSearchResultsPage) {
                                    setSearchResultsPageToSave(selectedSearchResultsPage);
                                }
                                setShowSearchResultsPageSelectModal(false);
                            }}
                        >
                            Save
                        </Button>
                    </Modal.Footer>
                </Modal>
            </div>
        </div>
    );
}
